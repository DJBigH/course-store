<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Courses\src\Models\Courses;
use Modules\Teacher\src\Models\Teacher;
use Modules\Finances\src\Support\FinanceCalculator as TeacherFinanceCalculator;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\Teacher\src\Support\TeacherPackageUsageResolver;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;

class TeacherDashboardController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected TeacherPackageLifecycleManager $packageLifecycleManager,
        protected TeacherNotificationCenter $notificationCenter,
        protected TeacherPackageUsageResolver $packageUsageResolver,
    ) {}

    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }
        $effectiveCommissionRate = $this->resolveEffectiveCommissionRate($teacher);
        $dashboardRange = $this->resolveTeacherDashboardRange((string) $request->query('range', 'today'));
        $dashboardRangeOptions = $this->resolveTeacherDashboardRangeOptions();

        $coursesQuery = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id);

        $orderDetails = $this->applyTeacherDashboardRangeToPaidQuery(
            $this->paidOrderDetailsQuery($teacher),
            $dashboardRange
        )->get();
        $summary = TeacherFinanceCalculator::summarize(
            $orderDetails,
            fn () => $effectiveCommissionRate
        );
        $payoutRequested = $this->resolveCommittedPayoutAmount($teacher);

        $pageTitle = __('teacher::teacher/dashboard.pages.overview');
        $pageName = __('teacher::teacher/dashboard.pages.overview');
        $stats = [
            'courses' => (clone $coursesQuery)->count(),
            'active_courses' => (clone $coursesQuery)->where('status', 1)->count(),
            'students' => $orderDetails->pluck('order.student_id')->filter()->unique()->count(),
            'gross_revenue' => $summary['gross_amount'],
            'allocated_discount' => $summary['allocated_discount'],
            'estimated_revenue' => $summary['teacher_revenue'],
            'platform_revenue' => $summary['platform_revenue'],
            'available_balance' => max($summary['teacher_revenue'] - $payoutRequested, 0),
        ];
        $conversionSummary = $this->buildTeacherConversionSummary($teacher, $dashboardRange);
        $revenueInsights = $this->buildTeacherRevenueInsights($teacher, $effectiveCommissionRate, $dashboardRange);
        $coursePerformance = $this->buildTeacherCoursePerformance($teacher, $effectiveCommissionRate, $dashboardRange);
        $recentCourses = $coursesQuery->latest('id')->take(4)->get();
        $recentSales = TeacherFinanceCalculator::decorate($orderDetails->sortByDesc('created_at')->take(6)->values(), fn () => $effectiveCommissionRate);
        $topBundles = $this->resolveTopBundles($teacher);
        $currentPackage = $teacher->application?->package;
        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        $pendingUpgradeStartsAt = $pendingUpgrade?->activates_at;
        $pendingUpgradeIsQueued = $pendingUpgrade?->status === 'approved'
            && $pendingUpgradeStartsAt !== null
            && $pendingUpgrade?->activated_at === null;
        $nextPackage = $this->resolveNextPackage($currentPackage);
        $availablePackageChanges = $this->resolveAvailablePackageChanges($currentPackage);
        $packageSummary = $currentPackage ? [
            'name' => $currentPackage->name_locale ?: $currentPackage->name,
            'badge' => $currentPackage->badge_text_locale ?: strtoupper((string) $currentPackage->code),
            'price' => (float) $currentPackage->price,
            'billing_cycle' => $currentPackage->billing_cycle,
            'course_limit' => $currentPackage->effective_course_limit,
            'commission_rate' => (float) $currentPackage->commission_rate,
            'support' => $currentPackage->support_level_locale ?: '',
            'started_at' => $teacher->package_started_at,
            'expires_at' => $teacher->package_expires_at,
            'days_left' => $this->packageLifecycleManager->daysLeft($teacher),
            'can_upgrade' => $availablePackageChanges->isNotEmpty() && $pendingUpgrade === null,
            'has_higher_package' => $nextPackage !== null,
            'upgrade_name' => $nextPackage?->name_locale ?: $nextPackage?->name,
            'upgrade_url' => route('teacher.dashboard.package.upgrade'),
            'pending_upgrade' => $pendingUpgrade !== null,
            'pending_upgrade_status' => $pendingUpgrade?->display_status,
            'pending_upgrade_url' => $pendingUpgrade ? route('teacher.dashboard.package.upgrade.status') : null,
            'pending_upgrade_name' => $pendingUpgrade?->package?->name_locale ?: $pendingUpgrade?->package?->name,
            'pending_upgrade_starts_at' => $pendingUpgradeStartsAt,
            'pending_upgrade_days_until_activation' => $pendingUpgradeStartsAt
                ? max(now()->startOfDay()->diffInDays($pendingUpgradeStartsAt->copy()->startOfDay(), false), 0)
                : null,
            'pending_upgrade_is_queued' => $pendingUpgradeIsQueued,
        ] : null;

        $overviewPayload = $this->buildTeacherOverviewDashboardPayload(
            $teacher,
            $effectiveCommissionRate,
            $dashboardRange,
            $stats,
            $conversionSummary,
            $revenueInsights,
            $coursePerformance
        );

        if ($request->boolean('ajax')) {
            return response()->json($overviewPayload);
        }

        return view('teacher::clients.dashboard.index', compact('pageTitle', 'pageName', 'teacher', 'stats', 'dashboardRange', 'dashboardRangeOptions', 'conversionSummary', 'revenueInsights', 'coursePerformance', 'recentCourses', 'recentSales', 'topBundles', 'packageSummary', 'effectiveCommissionRate', 'overviewPayload'));
    }
}
