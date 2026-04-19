<?php

namespace Modules\Finances\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Finances\src\Repositories\FinancesRepositoryInterface;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\Finances\src\Support\FinanceCalculator as TeacherFinanceCalculator;

class EarningController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected FinancesRepositoryInterface $financesRepo,
        protected TeacherPackageLifecycleManager $packageLifecycleManager
    ) {}

    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        // Handle Range Persistence (Session based - resets on browser close)
        $rangeKey = $request->query('range');
        if ($rangeKey) {
            session(['teacher_finance_range' => $rangeKey]);
        } else {
            $rangeKey = session('teacher_finance_range', 'today');
        }

        $effectiveCommissionRate = $this->resolveEffectiveCommissionRate($teacher);
        $dashboardRange = $this->resolveTeacherDashboardRange($rangeKey);
        $dashboardRangeOptions = $this->resolveTeacherDashboardRangeOptions();

        $paidQuery = $this->applyTeacherDashboardRangeToPaidQuery(
            $this->paidOrderDetailsQuery($teacher),
            $dashboardRange
        );

        $summary = TeacherFinanceCalculator::summarize(
            (clone $paidQuery)->get(),
            fn () => $effectiveCommissionRate
        );

        $revenueInsights = $this->buildTeacherRevenueInsights($teacher, $effectiveCommissionRate, $dashboardRange);
        $coursePerformance = $this->buildTeacherCoursePerformance($teacher, $effectiveCommissionRate, $dashboardRange);

        $earningsPayload = $this->buildTeacherEarningsDashboardPayload(
            $teacher,
            $effectiveCommissionRate,
            $dashboardRange,
            $summary,
            $revenueInsights,
            $coursePerformance
        );

        if ($request->boolean('ajax')) {
            return response()->json($earningsPayload);
        }

        $items = $paidQuery->latest()->paginate(20);
        $items->setCollection(
            TeacherFinanceCalculator::decorate($items->getCollection(), fn () => $effectiveCommissionRate)
        );

        $currentRangeKey = $rangeKey;
        $pageTitle = __('finances::teacher/earnings.title') . ' - ' . now()->format('d/m/Y');

        return view('finances::teacher.earnings.index', compact(
            'pageTitle',
            'teacher',
            'summary',
            'items',
            'revenueInsights',
            'coursePerformance',
            'dashboardRange',
            'dashboardRangeOptions',
            'earningsPayload',
            'currentRangeKey',
            'effectiveCommissionRate'
        ));
    }
}
