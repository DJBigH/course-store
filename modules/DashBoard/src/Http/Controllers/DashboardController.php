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

class DashboardController extends Controller
{
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

        // Orders Query Helper
        $buildOrderQuery = function($from, $to) use ($paidStatusId, $dateColumn, $currency) {
            $q = Order::query()
                ->with(['detail.courses.teacher'])
                ->where('status_id', $paidStatusId)
                ->whereNotNull($dateColumn)
                ->whereBetween($dateColumn, [$from, $to]);
                
            if ($currency !== 'ALL') {
                $q->where('currency', $currency);
            }
            return $q;
        };

        // Revenue Calculation
        $orders = $buildOrderQuery($from, $to)->get();
        $summary = $this->calculateRevenueSummary($orders, $currency);

        $prevOrders = $buildOrderQuery($prevFrom, $prevTo)->get();
        $prevSummary = $this->calculateRevenueSummary($prevOrders, $currency);

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
        if ($currency !== 'ALL') $ordersCreatedQuery->where('currency', $currency);
        $totalOrdersCreated = $ordersCreatedQuery->count();

        $paidOrdersCompleted = $orders->count();

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
        if ($currency !== 'ALL') $failedOrdersQuery->where('currency', $currency);
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
        $lessonsCount = Lesson::query()->count();

        // Action Items (Pending)
        $pendingPayoutsCount = class_exists(PayoutRequest::class) ? PayoutRequest::query()->where('status', 'pending')->count() : 0;
        $pendingPayoutsAmount = class_exists(PayoutRequest::class) ? PayoutRequest::query()->where('status', 'pending')->sum('amount') : 0;
        $pendingTeacherApps = class_exists(TeacherApplication::class) ? TeacherApplication::query()->where('status', 'pending')->count() : 0;
        $pendingCourses = Courses::query()->where('status', 0)->count();
        $pendingContacts = \Illuminate\Support\Facades\Schema::hasTable('contacts') ? DB::table('contacts')->where('status', 0)->count() : 0;
        $pendingReports = \Illuminate\Support\Facades\Schema::hasTable('reports') ? DB::table('reports')->where('status', 0)->count() : 0;

        $actionItems = [
            'pending_payouts_count' => $pendingPayoutsCount,
            'pending_payouts_amount' => $pendingPayoutsAmount,
            'pending_teachers' => $pendingTeacherApps,
            'pending_courses' => $pendingCourses,
            'pending_contacts' => $pendingContacts,
            'pending_reports' => $pendingReports,
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
            'lessons_count' => $lessonsCount,
        ];

        // Revenue line chart data
        $period = CarbonPeriod::create($from->toDateString(), $to->toDateString());
        $revenueLabels = collect($period)->map(fn ($dt) => $dt->format('d/m'))->values();

        $groupedOrders = $orders->groupBy(function($order) use ($dateColumn) {
            return Carbon::parse($order->{$dateColumn})->toDateString();
        });

        $grossData = [];
        $netData = [];
        
        foreach ($period as $dt) {
            $dateString = $dt->toDateString();
            if ($groupedOrders->has($dateString)) {
                $daySummary = $this->calculateRevenueSummary($groupedOrders->get($dateString), $currency);
                $grossData[] = (int) $daySummary['gross_amount'];
                $netData[] = (int) $daySummary['platform_revenue'];
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
            
        if ($currency !== 'ALL') {
            $statusCountsQuery->where('currency', $currency);
        }
        
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
            ->whereNotNull("o.$dateColumn")
            ->whereBetween("o.$dateColumn", [$from, $to]);
            
        if ($currency !== 'ALL') {
            $topCoursesQuery->where('o.currency', $currency);
        }
        
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

        // Top Earning Teachers
        $teacherEarnings = [];
        foreach ($orders as $order) {
            $exRate = $currency === 'ALL' ? (float) ($order->exchange_rate ?: 1) : 1;
            $orderTotal = (float) ($order->total ?? 0);
            $orderDiscount = (float) ($order->discount ?? 0);

            foreach ($order->detail as $detail) {
                if (!$detail->courses || !$detail->courses->teacher_id) continue;
                $teacherId = $detail->courses->teacher_id;
                $teacherName = $detail->courses->teacher->name ?? 'Giảng viên #' . $teacherId;
                $teacherSlug = $detail->courses->teacher->slug ?? '';
                
                $grossAmount = ((float) ($detail->price ?? 0)) * $exRate;
                $allocatedDiscount = 0.0;
                if ($orderTotal > 0 && $orderDiscount > 0 && $grossAmount > 0) {
                    $ratio = ((float) ($detail->price ?? 0)) / $orderTotal;
                    $allocatedDiscount = min($grossAmount, ($orderDiscount * $exRate) * $ratio);
                }
                $netRevenue = max($grossAmount - $allocatedDiscount, 0);
                $commissionRate = (float) ($detail->courses->teacher->commission_rate ?? 0);
                $teacherRevenue = $netRevenue * max(min($commissionRate, 100), 0) / 100;

                if (!isset($teacherEarnings[$teacherId])) {
                    $teacherEarnings[$teacherId] = ['name' => $teacherName, 'slug' => $teacherSlug, 'revenue' => 0];
                }
                $teacherEarnings[$teacherId]['revenue'] += $teacherRevenue;
            }
        }
        usort($teacherEarnings, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        $topTeachers = array_slice($teacherEarnings, 0, 4);

        // Recent orders
        $recentOrdersQuery = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->latest('created_at')
            ->limit(12);
        if ($currency !== 'ALL') {
            $recentOrdersQuery->where('currency', $currency);
        }
        $recentOrders = $recentOrdersQuery->get()
            ->map(function ($od) {
                return [
                    'code' => $od->code,
                    'customer' => $od->customer_name_display ?: ('Student #' . $od->student_id),
                    'total' => (int) $od->total,
                    'currency' => $od->currency,
                    'status_id' => (int) $od->status_id,
                    'status' => optional($od->status)->name ?? ('Status ' . $od->status_id),
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

    private function calculateRevenueSummary($orders, $currency)
    {
        $summary = [
            'gross_amount' => 0.0,
            'allocated_discount' => 0.0,
            'net_revenue' => 0.0,
            'teacher_revenue' => 0.0,
            'platform_revenue' => 0.0,
        ];

        foreach ($orders as $order) {
            $exRate = $currency === 'ALL' ? (float) ($order->exchange_rate ?: 1) : 1;
            $orderTotal = (float) ($order->total ?? 0);
            $orderDiscount = (float) ($order->discount ?? 0);

            foreach ($order->detail as $detail) {
                $grossAmount = ((float) ($detail->price ?? 0)) * $exRate;
                
                $allocatedDiscount = 0.0;
                if ($orderTotal > 0 && $orderDiscount > 0 && $grossAmount > 0) {
                    $ratio = ((float) ($detail->price ?? 0)) / $orderTotal;
                    $allocatedDiscount = min($grossAmount, ($orderDiscount * $exRate) * $ratio);
                }

                $netRevenue = max($grossAmount - $allocatedDiscount, 0);
                
                $commissionRate = (float) ($detail->courses?->teacher?->commission_rate ?? 0);
                $teacherRevenue = $netRevenue * max(min($commissionRate, 100), 0) / 100;
                $platformRevenue = max($netRevenue - $teacherRevenue, 0);

                $summary['gross_amount'] += $grossAmount;
                $summary['allocated_discount'] += $allocatedDiscount;
                $summary['net_revenue'] += $netRevenue;
                $summary['teacher_revenue'] += $teacherRevenue;
                $summary['platform_revenue'] += $platformRevenue;
            }
        }

        return $summary;
    }
}
