<?php

namespace Modules\Announcements\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Announcements\src\Models\Announcement;
use Modules\Announcements\src\Notifications\AnnounceNotification;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;
use Illuminate\Support\Facades\Notification;

class AnnouncementController extends Controller
{
    public function index()
    {
        $pageTitle = 'Quản lý Thông báo hệ thống';
        $announcements = Announcement::query()->withCount('reads')->latest()->paginate(20);

        return view('announcements::admin.index', compact('pageTitle', 'announcements'));
    }

    public function create()
    {
        $pageTitle = 'Soạn thông báo mới';
        return view('announcements::admin.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'content' => 'required|string',
            'target_type' => 'required|in:all,all_students,all_teachers,selected',
            'selected_user_ids' => 'required_if:target_type,selected|array',
            'action_url' => 'nullable|url',
            'action_label' => 'nullable|string|max:255',
        ]);

        $announcement = DB::transaction(function () use ($request) {
            $announcement = Announcement::create([
                'title' => $request->title,
                'message' => $request->message,
                'content' => $request->content,
                'target_type' => $request->target_type,
                'action_url' => $request->action_url,
                'action_label' => $request->action_label,
                'send_email' => $request->has('send_email'),
                'created_by' => auth()->id(),
                'sent_at' => now(),
            ]);

            if ($request->target_type === 'selected') {
                $announcement->recipients()->sync($request->selected_user_ids);
            }

            return $announcement;
        });

        // Gửi thông báo
        $this->sendAnnouncementNotifications($announcement);

        activity_log(
            action: 'create',
            subject: $announcement,
            properties: [
                'data' => [
                    'title' => $announcement->title,
                    'target_type' => $announcement->target_type,
                    'send_email' => $announcement->send_email,
                ],
            ],
            logName: 'admin_announcement_management',
            description: 'Tạo thông báo hệ thống mới: ' . $announcement->title
        );

        return redirect()->route('admin.announcements.index')->with('msg', 'Đã gửi thông báo thành công.');
    }

    public function searchUsers(Request $request)
    {
        $q = $request->get('q');
        
        $users = Student::query()
            ->with('teacher')
            ->where(function($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%");
            })
            ->limit(10)
            ->get()
            ->map(function($student) {
                $role = $student->teacher ? ' [Giảng viên]' : ' [Học viên]';
                return [
                    'id' => $student->id,
                    'text' => $student->name . ' (' . $student->email . ')' . $role
                ];
            });

        return response()->json(['items' => $users]);
    }

    private function sendAnnouncementNotifications(Announcement $announcement)
    {
        $query = Student::query();

        if ($announcement->target_type === 'all_students') {
            $query->doesntHave('teacher');
        } elseif ($announcement->target_type === 'all_teachers') {
            $query->has('teacher');
        } elseif ($announcement->target_type === 'selected') {
            $query->whereIn('id', $announcement->recipients()->pluck('students.id'));
        }
        // If 'all', no extra where needed as it defaults to everyone in the students table

        // Chunking to handle large number of users
        $query->chunk(100, function ($students) use ($announcement) {
            Notification::send($students, new AnnounceNotification($announcement));
        });
    }

    public function show(Announcement $announcement)
    {
        $pageTitle = 'Chi tiết thông báo';
        $announcement->load(['creator', 'recipients']);
        $announcement->loadCount('reads');
        
        return view('announcements::admin.show', compact('pageTitle', 'announcement'));
    }

    public function destroy(Announcement $announcement)
    {
        $snapshot = $announcement->toArray();
        $announcement->delete();

        activity_log(
            action: 'delete',
            subject: null,
            properties: ['data' => $snapshot],
            logName: 'admin_announcement_management',
            description: 'Xóa thông báo hệ thống: ' . ($snapshot['title'] ?? 'N/A')
        );

        return back()->with('msg', 'Đã xóa thông báo.');
    }
}
