<?php

namespace Modules\DashBoard\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Courses\src\Models\Courses;
use Modules\Lessons\src\Models\Lesson;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Models\OrderStatus;
use Modules\Students\src\Models\Student;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Tổng quan';

        // Config
        $paidStatusId = (int) (OrderStatus::query()->where('is_success', true)->value('id') ?? 2);
        $dateColumn = 'payment_complete_date';

        // Range filter
        $range = $request->query('range', 'today');
        $to = now()->endOfDay();

        [$from, $rangeLabel] = match ($range) {
            'today' => [now()->startOfDay(), 'Hôm nay'],
            '7d'    => [now()->subDays(6)->startOfDay(), '1 tuần'],
            '14d'   => [now()->subDays(13)->startOfDay(), '2 tuần'],
            '1m'    => [now()->subMonth()->addDay()->startOfDay(), '1 tháng'],
            '1y'    => [now()->subYear()->addDay()->startOfDay(), '1 năm'],
            default => [now()->startOfDay(), 'Hôm nay'],
        };

        $compareLabel = match ($range) {
            'today' => 'hôm qua',
            '7d'    => '7 ngày trước',
            '14d'   => '14 ngày trước',
            '1m'    => 'tháng trước',
            '1y'    => 'năm trước',
            default => 'hôm qua',
        };

        $chartTitle = "Doanh thu {$rangeLabel}";

        // Revenue
        $revenue = Order::query()
            ->where('status_id', $paidStatusId)
            ->whereNotNull($dateColumn)
            ->whereBetween($dateColumn, [$from, $to])
            ->sum('total');

        // Previous period for comparison
        if (in_array($range, ['today', '7d', '14d'])) {
            $days = $range === 'today' ? 1 : ($range === '7d' ? 7 : 14);
            $prevFrom = (clone $from)->subDays($days);
            $prevTo = (clone $to)->subDays($days);
        } elseif ($range === '1m') {
            $prevFrom = (clone $from)->subMonth();
            $prevTo = (clone $to)->subMonth();
        } else {
            $prevFrom = (clone $from)->subYear();
            $prevTo = (clone $to)->subYear();
        }

        $prevRevenue = Order::query()
            ->where('status_id', $paidStatusId)
            ->whereNotNull($dateColumn)
            ->whereBetween($dateColumn, [$prevFrom, $prevTo])
            ->sum('total');

        $revenueChangePercent = $prevRevenue > 0
            ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : ($revenue > 0 ? 100 : 0);

        // Orders + conversion
        $totalOrdersCreated = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $paidOrdersCreated = Order::query()
            ->where('status_id', $paidStatusId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $paymentStartedOrders = Order::query()
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [$from, $to])
            ->count();

        $paidOrdersCompleted = Order::query()
            ->where('status_id', $paidStatusId)
            ->whereNotNull($dateColumn)
            ->whereBetween($dateColumn, [$from, $to])
            ->count();

        $conversionRateByCreatedAt = $totalOrdersCreated > 0
            ? round(($paidOrdersCreated / $totalOrdersCreated) * 100, 1)
            : 0;

        $conversionRateByPaymentComplete = $paymentStartedOrders > 0
            ? round(($paidOrdersCompleted / $paymentStartedOrders) * 100, 1)
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

        $failedOrders = Order::query()
            ->whereIn('status_id', $failedStatusIds->values())
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $failedRate = $totalOrdersCreated > 0
            ? round(($failedOrders / $totalOrdersCreated) * 100, 1)
            : 0;

        $aov = $paidOrdersCompleted > 0
            ? (int) round($revenue / $paidOrdersCompleted)
            : 0;

        // Students
        $studentCount = Student::query()
            ->where('status', 1)
            ->whereNotNull('email_verified_at')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $studentTotal = Student::query()
            ->where('status', 1)
            ->whereNotNull('email_verified_at')
            ->count();

        $coursesCount = Courses::query()->count();
        $lessonsCount = Lesson::query()->count();

        $kpi = [
            'revenue' => $revenue,
            'revenue_change_percent' => $revenueChangePercent,
            'aov' => $aov,
            'orders' => $paidOrdersCreated,
            'orders_count' => $totalOrdersCreated,
            'paid_orders_completed' => $paidOrdersCompleted,
            'payment_started_orders' => $paymentStartedOrders,
            'conversion_rate' => $conversionRateByCreatedAt,
            'conversion_rate_by_created_at' => $conversionRateByCreatedAt,
            'conversion_rate_by_payment_complete_date' => $conversionRateByPaymentComplete,
            'failed_orders' => $failedOrders,
            'failed_rate' => $failedRate,
            'new_students' => $studentCount,
            'student' => $studentTotal,
            'courses_count' => $coursesCount,
            'lessons_count' => $lessonsCount,
        ];

        // Revenue line chart
        $rows = Order::query()
            ->selectRaw("DATE($dateColumn) as d, SUM(total) as revenue")
            ->where('status_id', $paidStatusId)
            ->whereNotNull($dateColumn)
            ->whereBetween($dateColumn, [$from, $to])
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $period = CarbonPeriod::create($from->toDateString(), $to->toDateString());

        $revenueLabels = collect($period)->map(fn ($dt) => $dt->format('d/m'))->values();

        $revenueData = collect($period)->map(function ($dt) use ($rows) {
            $key = $dt->toDateString();
            return (int) ($rows[$key]->revenue ?? 0);
        })->values();

        // Doughnut by order status
        $statusMap = OrderStatus::query()
            ->orderBy('id')
            ->get(['id', 'name']);

        $statusCounts = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status_id, COUNT(*) as c')
            ->groupBy('status_id')
            ->pluck('c', 'status_id');

        $orderStatus = [
            'labels' => $statusMap->pluck('name')->values(),
            'data' => $statusMap->pluck('id')->map(fn ($id) => (int) ($statusCounts[$id] ?? 0))->values(),
        ];

        // Top courses by paid orders
        $top = DB::table('orders_detail as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('courses as c', 'c.id', '=', 'od.course_id')
            ->where('o.status_id', $paidStatusId)
            ->whereNotNull("o.$dateColumn")
            ->whereBetween("o.$dateColumn", [$from, $to])
            ->selectRaw('c.name as course_name, COUNT(*) as total_buy')
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('total_buy')
            ->limit(3)
            ->get();

        $topCourses = [
            'labels' => $top->pluck('course_name')->values(),
            'data' => $top->pluck('total_buy')->map(fn ($v) => (int) $v)->values(),
        ];

        $totalBuys = DB::table('orders_detail as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->where('o.status_id', $paidStatusId)
            ->whereNotNull("o.$dateColumn")
            ->whereBetween("o.$dateColumn", [$from, $to])
            ->count();

        // Recent orders
        $recentOrders = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->map(function ($od) {
                return [
                    'code' => $od->code,
                    'customer' => $od->customer_name_display ?: ('Student #' . $od->student_id),
                    'total' => (int) $od->total,
                    'status_id' => (int) $od->status_id,
                    'status' => optional($od->status)->name ?? ('Status ' . $od->status_id),
                    'created_at' => $od->created_at,
                ];
            });

        return view('dashboard::dashboard', compact(
            'kpi',
            'revenueLabels',
            'revenueData',
            'orderStatus',
            'topCourses',
            'recentOrders',
            'pageTitle',
            'totalBuys',
            'range',
            'rangeLabel',
            'compareLabel',
            'chartTitle'
        ));
    }
}

