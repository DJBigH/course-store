<?php

namespace Modules\Packages\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Packages\src\Models\Package;
use Modules\Packages\src\Models\PackageFeature;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UpgradeController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected PackageLifecycleManager $packageLifecycleManager,
        protected TeacherNotificationCenter $notificationCenter,
        protected PackageUsageResolver $packageUsageResolver,
    ) {}

    public function upgradePackage()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        if ($pendingUpgrade) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $currentPackage = $teacher->application?->package;
        $upgradePackages = $this->resolveAvailablePackageChanges($currentPackage);
        if ($upgradePackages->isEmpty()) {
            return redirect()->route('teacher.dashboard.index')
                ->with('msg_danger', __('packages::teacher.flash.no_upgrade_available'));
        }

        $pageTitle = __('packages::teacher.upgrade.upgrade_title');
        $pageName = $pageTitle;

        $features = PackageFeature::query()
            ->whereIn('is_enabled', [
                PackageFeature::STATUS_ACTIVE,
                PackageFeature::STATUS_MAINTENANCE_VISIBLE
            ])
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get();

        $selectedPackageId = (int) request('package_id', $upgradePackages->first()?->id);

        return view('packages::teacher.upgrade', compact('pageTitle', 'pageName', 'teacher', 'currentPackage', 'upgradePackages', 'features', 'selectedPackageId'));
    }

    public function storeUpgradePackage(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        if ($pendingUpgrade) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $data = $request->validate([
            'package_id' => ['required', 'integer'],
            'payment_method' => ['nullable', 'string', 'in:bank_transfer,vnpay,momo'],
        ]);

        $currentPackage = $teacher->application?->package;
        $targetPackage = Package::query()->findOrFail($data['package_id']);

        if (!$this->canChangePackage($currentPackage, $targetPackage)) {
            return back()->with('msg_danger', __('packages::teacher.flash.invalid_upgrade'));
        }

        // Nếu là gói có phí thì bắt buộc chọn phương thức thanh toán
        if ($targetPackage->price > 0 && empty($data['payment_method'])) {
            return back()->withErrors(['payment_method' => __('packages::teacher.form.package.payment_required')]);
        }

        $changeRequest = DB::transaction(function () use ($teacher, $targetPackage, $data) {
            $isFree = $targetPackage->price <= 0;
            
            $application = TeacherApplication::query()->create([
                'teacher_id' => $teacher->id,
                'student_id' => $teacher->student_id,
                'full_name' => $teacher->name,
                'display_name' => $teacher->name,
                'email' => $teacher->student?->email,
                'phone' => $teacher->student?->phone,
                'package_id' => $targetPackage->id,
                'payment_method' => $isFree ? 'free' : $data['payment_method'],
                'status' => $isFree ? 'approved' : 'pending_payment',
                'type' => 'upgrade',
                'submitted_at' => now(),
                'reviewed_at' => $isFree ? now() : null,
                'reviewed_by' => $isFree ? null : null, // System auto-approved
                'note' => __('packages::teacher.upgrade.request_note'),
            ]);

            if ($isFree) {
                $packageAction = $this->packageLifecycleManager->applyApprovedChange($teacher, $application->fresh(['package']));
                
                activity_log(
                    action: 'teacher_package_upgraded_auto',
                    subject: $teacher,
                    properties: [
                        'application_id' => $application->id,
                        'package' => $targetPackage->name,
                        'action' => $packageAction,
                        'is_free' => true
                    ],
                    logName: __('teacher::admin.logs.approve_title'),
                    description: __('teacher::admin.logs.approve_upgrade_desc')
                );
            }

            return $application;
        });

        if ($targetPackage->price <= 0) {
            return redirect()->route('teacher.dashboard.index')
                ->with('msg_success', __('packages::teacher.flash.auto_activated'));
        }

        return redirect()->route('teacher.dashboard.package.upgrade.status');
    }

    public function upgradePackageStatus()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.package.upgrade');
        }

        $pageTitle = __('packages::teacher.upgrade.status_title');
        $pageName = $pageTitle;

        $currentPackage = $teacher->application?->package;
        return view('packages::teacher.upgrade_status', compact('pageTitle', 'pageName', 'teacher', 'upgradeRequest', 'currentPackage'));
    }

    public function markUpgradePaid()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.index');
        }

        if (!in_array($upgradeRequest->status, ['pending_payment', 'pending_review'], true)) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $upgradeRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        $packageAction = $this->packageLifecycleManager->applyApprovedChange($teacher, $upgradeRequest->fresh(['package']));
        
        activity_log(
            action: 'teacher_package_upgraded_auto_test',
            subject: $teacher,
            properties: [
                'application_id' => $upgradeRequest->id,
                'package' => $upgradeRequest->package?->name,
                'action' => $packageAction,
                'is_test_auto' => true
            ],
            logName: __('teacher::admin.logs.approve_title'),
            description: __('teacher::admin.logs.approve_upgrade_desc')
        );

        return redirect()->route('teacher.dashboard.index')
            ->with('msg_success', __('packages::teacher.flash.auto_activated'));
    }

    public function cancelUpgradePackage()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest || $upgradeRequest->status !== 'pending_payment') {
            return redirect()->route('teacher.dashboard.index');
        }

        $upgradeRequest->update(['status' => 'cancelled']);

        return redirect()->route('teacher.dashboard.index')
            ->with('msg_success', __('packages::teacher.flash.cancelled'));
    }
}
