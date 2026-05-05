<?php

namespace Modules\DashBoard\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Courses\src\Models\Courses;
use Modules\Lessons\src\Models\Lesson;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Models\OrderStatus;
use Modules\Students\src\Models\Student;
use Modules\Finances\src\Models\PayoutRequest;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Teacher\src\Models\Teacher;
use Modules\Finances\src\Repositories\FinancesRepositoryInterface;

class DashboardController extends Controller
{
    protected $financesRepo;
    protected $systemHealthService;

    public function __construct(
        FinancesRepositoryInterface $financesRepo,
        \Modules\Settings\src\Support\SystemHealthService $systemHealthService
    ) {
        $this->financesRepo = $financesRepo;
        $this->systemHealthService = $systemHealthService;
    }

    public function index(Request $request)
    {
        $pageTitle = 'Tổng quan';

        // Config
        $paidStatusId = (int) (OrderStatus::query()->where('is_success', true)->value('id') ?? 2);
        $dateColumn = 'payment_complete_date';

        // Currencies
        $currencies = collect(['VND', 'USD', 'KRW', 'JPY', 'CNY']);
        $currency = $request->query('currency', 'ALL');

        // Range filter
        $range = $request->query('range', 'today');
        
        if ($range === 'custom') {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $from = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfDay();
            $to = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();
            
            if ($from > $to) {
                $temp = $from;
                $from = $to;
                $to = $temp;
            }
            
            $rangeLabel = $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y');
            $compareLabel = 'kỳ trước';
            $daysDiff = $from->diffInDays($to) + 1;
            $prevFrom = (clone $from)->subDays($daysDiff);
            $prevTo = (clone $to)->subDays($daysDiff);
        } else {
            [$from, $to, $rangeLabel, $prevFrom, $prevTo, $compareLabel] = match ($range) {
                'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), 'Hôm qua', now()->subDays(2)->startOfDay(), now()->subDays(2)->endOfDay(), 'hôm kia'],
                'today' => [now()->startOfDay(), now()->endOfDay(), 'Hôm nay', now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), 'hôm qua'],
                '7d'    => [now()->subDays(6)->startOfDay(), now()->endOfDay(), '7 ngày qua', now()->subDays(13)->startOfDay(), now()->subDays(7)->endOfDay(), '7 ngày trước'],
                '14d'   => [now()->subDays(13)->startOfDay(), now()->endOfDay(), '14 ngày qua', now()->subDays(27)->startOfDay(), now()->subDays(14)->endOfDay(), '14 ngày trước'],
                '1m'    => [now()->subMonth()->addDay()->startOfDay(), now()->endOfDay(), '1 tháng qua', now()->subMonths(2)->addDay()->startOfDay(), now()->subMonth()->endOfDay(), 'tháng trước'],
                '1y'    => [now()->subYear()->addDay()->startOfDay(), now()->endOfDay(), '1 năm qua', now()->subYears(2)->addDay()->startOfDay(), now()->subYear()->endOfDay(), 'năm trước'],
                default => [now()->startOfDay(), now()->endOfDay(), 'Hôm nay', now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), 'hôm qua'],
            };
        }

        $chartTitle = "Doanh thu {$rangeLabel}";

        // Revenue Calculation - Optimized with Repository (SQL Aggregation)
        $summary = $this->financesRepo->getEarningsSummary(0, null, $from->toDateTimeString(), $to->toDateTimeString(), $currency);
        $prevSummary = $this->financesRepo->getEarningsSummary(0, null, $prevFrom->toDateTimeString(), $prevTo->toDateTimeString(), $currency);

        $revenue = $summary['gross_amount'];
        $prevRevenue = $prevSummary['gross_amount'];

        $revenueChangePercent = $prevRevenue > 0
            ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : ($revenue > 0 ? 100 : 0);

        $platformRevenueChangePercent = $prevSummary['platform_revenue'] > 0
            ? round((($summary['platform_revenue'] - $prevSummary['platform_revenue']) / $prevSummary['platform_revenue']) * 100, 1)
            : ($summary['platform_revenue'] > 0 ? 100 : 0);

        // Orders + conversion
        $ordersCreatedQuery = Order::query()->whereBetween('created_at', [$from, $to]);
        $totalOrdersCreated = $ordersCreatedQuery->count();

        // Optimized count for paid orders
        $paidOrdersCompleted = Order::query()
            ->where('status_id', $paidStatusId)
            ->whereBetween($dateColumn, [$from, $to])
            ->count();

        $conversionRateByCreatedAt = $totalOrdersCreated > 0
            ? round(($paidOrdersCompleted / $totalOrdersCreated) * 100, 1)
            : 0;

        $failedStatusIds = OrderStatus::query()
            ->where(function ($query) {
                $query->where('name', 'like', '%thất bại%')
                    ->orWhere('name', 'like', '%that bai%')
                    ->orWhere('name', 'like', '%failed%');
            })
            ->pluck('id');

        if ($failedStatusIds->isEmpty()) {
            $failedStatusIds = collect([3]);
        }

        $failedOrdersQuery = Order::query()
            ->whereIn('status_id', $failedStatusIds->values())
            ->whereBetween('created_at', [$from, $to]);
        $failedOrders = $failedOrdersQuery->count();

        $failedRate = $totalOrdersCreated > 0
            ? round(($failedOrders / $totalOrdersCreated) * 100, 1)
            : 0;

        $aov = $paidOrdersCompleted > 0
            ? round($revenue / $paidOrdersCompleted)
            : 0;

        // Students & Teachers
        $newStudentsCount = Student::query()
            ->where('status', 1)
            ->whereNotNull('email_verified_at')
            ->whereBetween('created_at', [$from, $to])
            ->count();
        $totalStudents = Student::query()
            ->where('status', 1)
            ->whereNotNull('email_verified_at')
            ->count();

        $newTeachersCount = Teacher::query()
            ->where('status', 'active')
            ->whereBetween('created_at', [$from, $to])
            ->count();
        $totalTeachers = Teacher::query()
            ->where('status', 'active')
            ->count();

        $coursesCount = Courses::query()->count();
        $topCoursesCount = DB::table('orders_detail as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->where('o.status_id', $paidStatusId)
            ->whereBetween("o.$dateColumn", [$from, $to])
            ->distinct('od.course_id')
            ->count('od.course_id');

        $lessonsCount = Lesson::query()->count();

        // Action Items (Pending)
        $pendingPayoutsCount = class_exists(PayoutRequest::class) ? PayoutRequest::query()->where('status', 'pending')->count() : 0;
        $pendingPayoutsAmount = class_exists(PayoutRequest::class) ? PayoutRequest::query()->where('status', 'pending')->sum('amount') : 0;
        $pendingTeacherApps = class_exists(TeacherApplication::class) ? TeacherApplication::query()->where('status', 'pending')->count() : 0;
        $pendingCourses = Courses::query()->where('status', 0)->count();
        $pendingContacts = \Illuminate\Support\Facades\Schema::hasTable('contacts') ? DB::table('contacts')->where('status', 0)->count() : 0;
        $pendingReports = \Illuminate\Support\Facades\Schema::hasTable('reports') ? DB::table('reports')->where('status', 0)->count() : 0;

        $healthSnapshot = $this->systemHealthService->getSnapshot();
        $healthAlerts = [];
        
        if (($healthSnapshot['disk']['status'] ?? '') === 'critical') {
            $healthAlerts[] = [
                'type' => 'critical',
                'label' => 'Ổ đĩa sắp đầy',
                'desc' => "Dung lượng còn lại rất thấp ({$healthSnapshot['disk']['percent']}%).",
                'icon' => 'fa-solid fa-hard-drive',
                'link' => route('settings.index')
            ];
        }

        if (($healthSnapshot['queue']['status'] ?? '') === 'critical') {
            $healthAlerts[] = [
                'type' => 'critical',
                'label' => 'Hàng đợi (Queue) lỗi',
                'desc' => 'Hệ thống xử lý nền đang bị gián đoạn.',
                'icon' => 'fa-solid fa-bolt-lightning',
                'link' => route('settings.index')
            ];
        }

        $actionItems = [
            'pending_payouts_count' => $pendingPayoutsCount,
            'pending_payouts_amount' => $pendingPayoutsAmount,
            'pending_teachers' => $pendingTeacherApps,
            'pending_courses' => $pendingCourses,
            'pending_contacts' => $pendingContacts,
            'pending_reports' => $pendingReports,
            'health_alerts' => $healthAlerts,
        ];

        $kpi = [
            'gross_revenue' => $summary['gross_amount'],
            'platform_revenue' => $summary['platform_revenue'],
            'teacher_revenue' => $summary['teacher_revenue'],
            'allocated_discount' => $summary['allocated_discount'],
            'revenue_change_percent' => $revenueChangePercent,
            'platform_revenue_change_percent' => $platformRevenueChangePercent,
            'aov' => $aov,
            'orders' => $paidOrdersCompleted,
            'orders_count' => $totalOrdersCreated,
            'conversion_rate' => $conversionRateByCreatedAt,
            'failed_orders' => $failedOrders,
            'failed_rate' => $failedRate,
            'new_students' => $newStudentsCount,
            'total_students' => $totalStudents,
            'new_teachers' => $newTeachersCount,
            'total_teachers' => $totalTeachers,
            'courses_count' => $coursesCount,
            'top_courses_count' => $topCoursesCount,
            'lessons_count' => $lessonsCount,
        ];

        // Revenue line chart data - Optimized with one query
        $period = CarbonPeriod::create($from->toDateString(), $to->toDateString());
        $revenueLabels = collect($period)->map(fn ($dt) => $dt->format('d/m'))->values();

        $dailySummaries = $this->financesRepo->getDailyEarningsSummary($from->toDateTimeString(), $to->toDateTimeString(), $currency);

        $grossData = [];
        $netData = [];
        
        foreach ($period as $dt) {
            $dateString = $dt->toDateString();
            if (isset($dailySummaries[$dateString])) {
                $grossData[] = (int) $dailySummaries[$dateString]['gross_amount'];
                $netData[] = (int) $dailySummaries[$dateString]['platform_revenue'];
            } else {
                $grossData[] = 0;
                $netData[] = 0;
            }
        }

        $revenueData = [
            'gross' => $grossData,
            'net' => $netData
        ];

        // Doughnut by order status
        $statusMap = OrderStatus::query()
            ->orderBy('id')
            ->get(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh']);

        $statusCountsQuery = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status_id, COUNT(*) as c')
            ->groupBy('status_id');
        
        $statusCounts = $statusCountsQuery->pluck('c', 'status_id');

        $orderStatus = [
            'labels' => $statusMap->pluck('name_locale')->values(),
            'data' => $statusMap->pluck('id')->map(fn ($id) => (int) ($statusCounts[$id] ?? 0))->values(),
        ];

        // Top courses by paid orders
        $topCoursesQuery = DB::table('orders_detail as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('courses as c', 'c.id', '=', 'od.course_id')
            ->where('o.status_id', $paidStatusId)
            ->whereBetween("o.$dateColumn", [$from, $to]);
        
        $top = $topCoursesQuery->selectRaw('c.name as course_name, c.slug as course_slug, COUNT(*) as total_buy')
            ->groupBy('c.id', 'c.name', 'c.slug')
            ->orderByDesc('total_buy')
            ->limit(4)
            ->get();

        $topCourses = [
            'labels' => $top->pluck('course_name')->values(),
            'slugs' => $top->pluck('course_slug')->values(),
            'data' => $top->pluck('total_buy')->map(fn ($v) => (int) $v)->values(),
        ];

        // Top Earning Teachers - Filter to only include those with actual course revenue
        $teacherSummaries = $this->financesRepo->getTeacherEarningsSummaries($from->toDateTimeString(), $to->toDateTimeString(), $currency);
        $topTeachers = collect($teacherSummaries)
            ->filter(fn($t) => (float)$t['teacher_revenue'] > 0)
            ->sortByDesc('teacher_revenue')
            ->take(4)
            ->map(function($t) {
                return [
                    'name' => $t['teacher_name'],
                    'slug' => $t['teacher_slug'],
                    'revenue' => $t['teacher_revenue']
                ];
            })->toArray();

        // Top Packages (Upgrade/Registration)
        $topPackagesQuery = DB::table('orders as o')
            ->join('teacher_applications as ta', function($join) {
                $join->on('ta.id', '=', 'o.orderable_id')
                     ->where('o.orderable_type', '=', 'Modules\Teacher\src\Models\TeacherApplication');
            })
            ->join('teacher_packages as tp', 'tp.id', '=', 'ta.package_id')
            ->where('o.status_id', $paidStatusId)
            ->whereBetween("o.$dateColumn", [$from, $to]);
        
        $packages = $topPackagesQuery->selectRaw('tp.name as package_name, COUNT(*) as total_buy')
            ->groupBy('tp.id', 'tp.name')
            ->orderByDesc('total_buy')
            ->limit(4)
            ->get();

        $topPackages = [
            'labels' => $packages->pluck('package_name')->values(),
            'data' => $packages->pluck('total_buy')->map(fn ($v) => (int) $v)->values(),
        ];

        // Recent orders
        $recentOrdersQuery = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->latest('created_at')
            ->limit(12);
        $recentOrders = $recentOrdersQuery->get()
            ->map(function ($od) {
                return [
                    'code' => $od->code,
                    'customer' => $od->customer_name_display ?: ('Học viên #' . $od->student_id),
                    'total' => (int) $od->total,
                    'currency' => $od->currency,
                    'status_id' => (int) $od->status_id,
                    'status' => optional($od->status)->name ?? ('Trạng thái ' . $od->status_id),
                    'created_at' => $od->created_at,
                ];
            });

        return view('dashboard::dashboard', compact(
            'kpi',
            'actionItems',
            'revenueLabels',
            'revenueData',
            'orderStatus',
            'topCourses',
            'topTeachers',
            'topPackages',
            'recentOrders',
            'pageTitle',
            'range',
            'rangeLabel',
            'compareLabel',
            'chartTitle',
            'currencies',
            'currency',
            'from',
            'to'
        ));
    }
}
