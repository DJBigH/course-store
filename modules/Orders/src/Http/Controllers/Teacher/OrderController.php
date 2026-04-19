<?php

namespace Modules\Orders\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Finances\src\Support\FinanceCalculator as TeacherFinanceCalculator;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\Teacher\src\Support\TeacherPackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderController extends Controller
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

        $directory = $this->buildTeacherOrderDirectory($teacher, $request);
        $groupedOrders = $directory['orders'];

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $orders = new LengthAwarePaginator(
            $groupedOrders->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $groupedOrders->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $pageTitle = __('orders::teacher/orders.pages.index');
        $pageName = $pageTitle;

        return view('orders::teacher.orders.index', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'orders',
            'directory'
        ));
    }

    public function export(Request $request, string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export')) {
            return $featureRedirect;
        }

        $directory = $this->buildTeacherOrderDirectory($teacher, $request);
        $orders = $directory['orders'];

        if ($format === 'excel') {
            $filename = 'teacher-orders-' . now()->format('Ymd-His') . '.xls';
            $html = view('orders::teacher.orders.exports.excel', compact('orders'))->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $filename = 'teacher-orders-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                __('orders::teacher/orders.export.order'),
                __('orders::teacher/orders.export.student'),
                __('orders::teacher/orders.export.email'),
                __('orders::teacher/orders.export.phone'),
                __('orders::teacher/orders.export.payment_method'),
                __('orders::teacher/orders.export.paid_at'),
                __('orders::teacher/orders.export.course_count'),
                __('orders::teacher/orders.export.courses'),
                __('orders::teacher/orders.export.gross'),
                __('orders::teacher/orders.export.discount'),
                __('orders::teacher/orders.export.net'),
                __('orders::teacher/orders.export.revenue'),
            ]);

            foreach ($orders as $item) {
                fputcsv($handle, [
                    $item->order?->code ?: $item->order?->id,
                    $item->order?->customer_name_display ?: '',
                    $item->order?->customer_email_display ?: '',
                    $item->order?->customer_phone_display ?: '',
                    $item->order?->payment_method_label ?: '',
                    optional($item->payment_at)->format('Y-m-d H:i:s'),
                    $item->item_count,
                    $item->details->pluck(fn ($detail) => $detail->courses?->name_locale ?: $detail->courses?->name ?: '')->implode(' | '),
                    $item->gross_amount,
                    $item->allocated_discount,
                    $item->net_revenue,
                    $item->teacher_revenue,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show(int $orderId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $details = $this->paidOrderDetailsQuery($teacher)
            ->where('order_id', $orderId)
            ->get();

        if ($details->isEmpty()) {
            abort(404);
        }

        $details = TeacherFinanceCalculator::decorate(
            $details,
            fn() => $this->resolveEffectiveCommissionRate($teacher)
        );

        $order = $details->first()?->order;
        if (!$order) {
            abort(404);
        }

        $summary = [
            'gross_amount' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
            'allocated_discount' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.allocated_discount', 0)),
            'net_revenue' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.net_revenue', 0)),
            'teacher_revenue' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
            'item_count' => $details->count(),
        ];

        $pageTitle = __('orders::teacher/orders.pages.show', ['code' => $order->code ?: $order->id]);
        $pageName = $pageTitle;

        return view('orders::teacher.orders.show', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'order',
            'details',
            'summary'
        ));
    }
}
