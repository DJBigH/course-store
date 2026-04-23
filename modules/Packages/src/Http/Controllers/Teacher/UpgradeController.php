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

        return view('packages::teacher.upgrade', compact('pageTitle', 'pageName', 'teacher', 'currentPackage', 'upgradePackages', 'features'));
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
        ]);

        $currentPackage = $teacher->application?->package;
        $targetPackage = Package::query()->findOrFail($data['package_id']);

        if (!$this->canChangePackage($currentPackage, $targetPackage)) {
            return back()->with('msg_danger', __('packages::teacher.flash.invalid_upgrade'));
        }

        $changeRequest = DB::transaction(function () use ($teacher, $targetPackage) {
            return TeacherApplication::query()->create([
                'teacher_id' => $teacher->id,
                'package_id' => $targetPackage->id,
                'status' => 'pending_payment',
                'type' => 'upgrade',
                'note' => __('packages::teacher.upgrade.request_note'),
            ]);
        });

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

        return view('packages::teacher.upgrade_status', compact('pageTitle', 'pageName', 'teacher', 'upgradeRequest'));
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
            'status' => 'pending_review',
        ]);

        return redirect()->route('teacher.dashboard.package.upgrade.status')
            ->with('msg_success', __('packages::teacher.flash.marked_paid'));
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

        $upgradeRequest->delete();

        return redirect()->route('teacher.dashboard.index')
            ->with('msg_success', __('packages::teacher.flash.cancelled'));
    }
}
