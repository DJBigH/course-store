<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TeacherApplicationApprovedMail;
use App\Mail\TeacherApplicationRejectedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Packages\src\Support\PackageLifecycleManager;

class TeacherUpgradeController extends Controller
{
    public function __construct(
        private readonly PackageLifecycleManager $packageLifecycleManager
    ) {}

    public function index(Request $request)
    {
        $pageTitle = __('teacher::admin.titles.package_upgrades') ?: 'Quản lý nâng cấp gói';
        $upgrades = TeacherApplication::query()
            ->where('type', 'upgrade')
            ->with(['student', 'package', 'teacher', 'reviewer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('teacher::admin.upgrades.index', compact('pageTitle', 'upgrades'));
    }

    public function show($id)
    {
        $pageTitle = __('teacher::admin.titles.upgrade_detail') ?: 'Chi tiết yêu cầu nâng cấp';
        $upgrade = TeacherApplication::query()
            ->where('type', 'upgrade')
            ->with(['student', 'package', 'teacher', 'reviewer'])
            ->findOrFail($id);

        return view('teacher::admin.upgrades.show', compact('pageTitle', 'upgrade'));
    }

    public function approve(Request $request, $id)
    {
        $upgrade = TeacherApplication::query()
            ->where('type', 'upgrade')
            ->with(['student', 'package', 'teacher.student'])
            ->findOrFail($id);

        if ($upgrade->status === 'approved') {
            return back()->with('msg_danger', 'Yêu cầu này đã được phê duyệt trước đó.');
        }

        if ($upgrade->status === 'pending_payment') {
            return back()->with('msg_danger', __('teacher::admin.messages.pending_payment_error'));
        }

        $teacher = $upgrade->teacher;
        
        $upgrade->update([
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'admin_note' => $request->input('admin_note'),
        ]);

        $packageAction = $this->packageLifecycleManager->applyApprovedChange($teacher, $upgrade->fresh(['package']));
        
        activity_log(
            action: 'teacher_upgrade_approved',
            subject: $teacher,
            properties: [
                'application_id' => $upgrade->id,
                'package' => $upgrade->package?->name,
                'package_action' => $packageAction,
            ],
            logName: 'Phê duyệt nâng cấp gói',
            description: "Đã phê duyệt yêu cầu nâng cấp lên gói {$upgrade->package?->name}"
        );

        Mail::to($upgrade->email)
            ->locale($upgrade->locale ?: config('app.locale'))
            ->queue(new TeacherApplicationApprovedMail(
                $upgrade->fresh(['package', 'teacher', 'student']),
                null, // No password setup needed for existing teachers
                false, // Account was already created
                $packageAction,
                $upgrade->locale ?: config('app.locale')
            ));

        return redirect()->route('teacher-upgrades.show', $upgrade->id)
            ->with('msg', 'Phê duyệt nâng cấp gói thành công.');
    }

    public function reject(Request $request, $id)
    {
        $upgrade = TeacherApplication::query()
            ->where('type', 'upgrade')
            ->findOrFail($id);

        $upgrade->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'admin_note' => trim((string) $request->input('admin_note')) ?: 'Yêu cầu nâng cấp bị từ chối.',
        ]);

        activity_log(
            action: 'teacher_upgrade_rejected',
            subject: $upgrade->fresh(),
            properties: [
                'application_id' => $upgrade->id,
                'email' => $upgrade->email,
                'admin_note' => $upgrade->admin_note,
            ],
            logName: 'Từ chối nâng cấp gói',
            description: 'Đã từ chối yêu cầu nâng cấp gói giảng viên'
        );

        Mail::to($upgrade->email)
            ->locale($upgrade->locale ?: config('app.locale'))
            ->queue(new TeacherApplicationRejectedMail($upgrade, $upgrade->locale ?: config('app.locale')));

        return redirect()->route('teacher-upgrades.show', $upgrade->id)
            ->with('msg', 'Đã từ chối yêu cầu nâng cấp.');
    }
}
