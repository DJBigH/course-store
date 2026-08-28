<?php

namespace Modules\Announcements\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Announcements\src\Models\Announcement;
use Modules\Announcements\src\Models\AnnouncementRead;
use Modules\Students\src\Models\Student;

class InboxController extends Controller
{
    public function index()
    {
        $student = auth('students')->user();
        if (!$student) {
            return redirect()->route('students.login');
        }
        
        $pageTitle = 'Hộp thư của tôi';
        $isTeacher = $student->teacher()->exists();
        
        $announcements = Announcement::query()
            ->where(function ($query) use ($student, $isTeacher) {
                $query->where('target_type', 'all')
                    ->orWhere('target_type', 'all_students');
                
                if ($isTeacher) {
                    $query->orWhere('target_type', 'all_teachers');
                }
                
                $query->orWhere(function ($q) use ($student) {
                    $q->where('target_type', 'selected')
                      ->whereHas('recipients', function ($r) use ($student) {
                          $r->where('students.id', $student->id);
                      });
                });
            })
            ->latest('sent_at')
            ->paginate(15);

        // Map read status
        $readIds = AnnouncementRead::where('user_id', $student->id)
            ->pluck('announcement_id')
            ->toArray();

        $layout = $isTeacher ? 'layouts.teacher' : 'layouts.client';

        return view('announcements::clients.index', compact('pageTitle', 'announcements', 'readIds', 'layout'));
    }

    public function show($locale, $id)
    {
        $student = auth('students')->user();
        $announcement = Announcement::findOrFail($id);
        
        // Mark as read
        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id' => $student->id,
        ], [
            'read_at' => now(),
        ]);

        $pageTitle = $announcement->title_locale;
        $layout = $student->teacher()->exists() ? 'layouts.teacher' : 'layouts.client';

        return view('announcements::clients.show', compact('pageTitle', 'announcement', 'layout'));
    }
}
