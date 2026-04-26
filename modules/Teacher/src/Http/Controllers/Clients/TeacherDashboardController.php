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
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
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
        protected PackageLifecycleManager $packageLifecycleManager,
        protected PackageUsageResolver $packageUsageResolver,
        protected TeacherNotificationCenter $notificationCenter,
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

        $previousRange = $this->resolveTeacherDashboardPreviousRange($dashboardRange);
        $previousOrderDetails = $this->applyTeacherDashboardRangeToPaidQuery(
            $this->paidOrderDetailsQuery($teacher),
            $previousRange
        )->get();
        $previousSummary = TeacherFinanceCalculator::summarize(
            $previousOrderDetails,
            fn () => $effectiveCommissionRate
        );

        $calcTrend = function ($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 1);
        };

        $trendStats = [
            'students' => $calcTrend($orderDetails->pluck('order.student_id')->filter()->unique()->count(), $previousOrderDetails->pluck('order.student_id')->filter()->unique()->count()),
            'gross_revenue' => $calcTrend($summary['gross_amount'], $previousSummary['gross_amount']),
            'estimated_revenue' => $calcTrend($summary['teacher_revenue'], $previousSummary['teacher_revenue']),
        ];

        $pageTitle = __('teacher::teacher/dashboard.pages.overview');
        $pageName = __('teacher::teacher/dashboard.pages.overview');
        $allTimeOrderDetails = $this->paidOrderDetailsQuery($teacher)->get();
        $allTimeSummary = TeacherFinanceCalculator::summarize(
            $allTimeOrderDetails,
            fn () => $effectiveCommissionRate
        );

        $stats = [
            'courses' => (clone $coursesQuery)->count(),
            'active_courses' => (clone $coursesQuery)->where('status', 1)->count(),
            'students' => $orderDetails->pluck('order.student_id')->filter()->unique()->count(),
            'gross_revenue' => $summary['gross_amount'],
            'allocated_discount' => $summary['allocated_discount'],
            'estimated_revenue' => $summary['teacher_revenue'],
            'platform_revenue' => $summary['platform_revenue'],
            'available_balance' => max($allTimeSummary['teacher_revenue'] - $payoutRequested, 0),
            'trends' => $trendStats,
        ];
        $conversionSummary = $this->buildTeacherConversionSummary($teacher, $dashboardRange);
        $revenueInsights = $this->buildTeacherRevenueInsights($teacher, $effectiveCommissionRate, $dashboardRange);
        $coursePerformance = $this->buildTeacherCoursePerformance($teacher, $effectiveCommissionRate, $dashboardRange);
        $recentCourses = $coursesQuery->latest('id')->take(4)->get();
        $recentSales = TeacherFinanceCalculator::decorate($orderDetails->sortByDesc('created_at')->take(6)->values(), fn () => $effectiveCommissionRate);
        $topBundles = $this->resolveTopBundles($teacher);
        $packageSummary = $this->resolvePackageSummary($teacher);

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

        return view('teacher::teacher.dashboard.dashboard', compact('pageTitle', 'pageName', 'teacher', 'stats', 'dashboardRange', 'dashboardRangeOptions', 'conversionSummary', 'revenueInsights', 'coursePerformance', 'recentCourses', 'recentSales', 'topBundles', 'packageSummary', 'effectiveCommissionRate', 'overviewPayload'));
    }
    public function locked()
    {
        $teacher = $this->resolveTeacher();
        
        // Nếu không thực sự bị khóa thì redirect về index
        if (!$teacher || !$teacher->is_locked) {
            return redirect()->route('teacher.dashboard.index');
        }

        $pageTitle = 'Tài khoản bị khóa';
        
        return view('teacher::teacher.locked', compact('pageTitle', 'teacher'));
    }
}
