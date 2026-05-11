<?php

namespace Modules\Orders\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;
use Modules\ActiveLogs\src\Models\ActiveLog;

class OrderController extends Controller
{
    protected $orderRepository;

    public function __construct(OrdersRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function index()
    {
        $pageTitle = 'Quản lý đơn hàng';
        return view('orders::lists', compact('pageTitle'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác đơn hàng';

        return view('orders::trash', compact('pageTitle'));
    }

    public function data()
    {
        $canView = auth()->user()?->hasPermission('orders.view');
        $canDelete = auth()->user()?->canAnyPermission(['orders.soft_delete', 'orders.delete']);
        $orders = $this->orderRepository
            ->getCategories()
            ->when(request()->filled('payment_method_filter'), function ($query) {
                $query->where('payment_method', request()->input('payment_method_filter'));
            })
            ->when(request()->filled('order_code_filter'), function ($query) {
                $query->where('code', 'like', '%' . request()->input('order_code_filter') . '%');
            })
            ->when(request()->input('search.value'), function ($query, $search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('code', 'like', '%' . $search . '%')
                        ->orWhere('customer_name_snapshot', 'like', '%' . $search . '%')
                        ->orWhere('customer_email_snapshot', 'like', '%' . $search . '%')
                        ->orWhereHas('students', function ($stQuery) use ($search) {
                            $stQuery->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            });

        return DataTables::of($orders)
            ->addColumn('select', function ($order) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $order->id . '"></div>';
            })
            ->addColumn('order_info', function ($order) {
                $studentName = e($order->students?->name ?: $order->customer_name_snapshot ?: 'Khách vãng lai');
                $studentEmail = e($order->students?->email ?: $order->customer_email_snapshot ?: '-');
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=f1f5f9&color=64748b';

                return '
                    <div class="d-flex align-items-center gap-3">
                        <img src="' . $avatar . '" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-bold text-dark">#' . e($order->code) . '</span>
                                ' . ($order->type === 'telegram_package'
                                    ? '<span class="badge bg-primary text-white border border-primary" style="font-size: 10px;">Telegram</span>'
                                    : ($order->type === 'teacher_upgrade' 
                                        ? '<span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 10px;">Gói giảng viên</span>' 
                                        : ($order->bundle_id 
                                            ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">Combo</span>' 
                                            : '<span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 10px;">Khóa học</span>'))) . '
                            </div>
                            <div class="text-secondary small">' . $studentName . ' (' . $studentEmail . ')</div>
                        </div>
                    </div>';
            })
            ->addColumn('financial_info', function ($order) {
                $currency = $order->currency ?: 'VND';
                $symbol = $currency === 'VND' ? 'đ' : $currency;
                
                $finalTotal = $order->total;
                if (!empty($order->discount) && $order->discount > 0) {
                    $finalTotal = $order->total - $order->discount;
                }
                
                $discountBadge = (!empty($order->discount) && $order->discount > 0)
                    ? '<div class="text-muted small text-decoration-line-through">' . number_format($order->total, $currency === 'VND' ? 0 : 2) . ' ' . $symbol . '</div>'
                    : '';

                $paymentBadge = '<span class="badge rounded-pill mt-1" style="' . e($order->payment_method_badge_style) . '; font-size: 11px;">' . e($order->payment_method_label) . '</span>';

                $priceDisplay = '<div class="fw-bold text-primary">' . number_format($finalTotal, $currency === 'VND' ? 0 : 2) . ' ' . $symbol . '</div>';
                
                if ($currency !== 'VND' && $order->base_total > 0) {
                    $priceDisplay .= '<div class="text-muted" style="font-size: 10px;">(' . number_format($order->base_total) . ' đ)</div>';
                }

                return '
                    <div>
                        ' . $discountBadge . '
                        ' . $priceDisplay . '
                        ' . $paymentBadge . '
                    </div>';
            })
            ->editColumn('status_id', function ($order) {
                $name = $order->status->name_locale ?? 'Chưa rõ';
                $color = $order->status->color ?? 'secondary';

                return '<span class="badge bg-' . $color . '-subtle text-' . $color . ' px-2 py-1"><i class="fa-solid fa-circle me-1 small"></i>' . $name . '</span>';
            })
            ->editColumn('created_at', function ($order) {
                return '<div class="small text-muted">' . ($order->created_at ? date('d/m/Y H:i', strtotime($order->created_at)) : '') . '</div>';
            })
            ->addColumn('actions', function ($order) use ($canView, $canDelete) {
                $btn = '<div class="dropdown">
                            <button class="btn btn-light btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">';

                if ($canView) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('orders.show', $order->id) . '"><i class="fa-solid fa-file-invoice text-info me-2"></i>Xem chi tiết</a></li>';
                }

                if ($canDelete) {
                    $btn .= '<li><hr class="dropdown-divider my-1"></li>';
                    $btn .= '<li><a class="dropdown-item py-2 text-danger delete-action" href="' . route('orders.delete', $order->id) . '"><i class="fa-solid fa-trash me-2"></i>Xóa đơn hàng</a></li>';
                }

                $btn .= '</ul></div>';
                return $btn;
            })
            ->rawColumns(['select', 'order_info', 'financial_info', 'status_id', 'created_at', 'actions'])
            ->make(true);
    }

    public function trashData()
    {
        $canRestore = auth()->user()?->canAnyPermission(['orders.soft_delete', 'orders.delete']);
        $canForceDelete = auth()->user()?->hasPermission('orders.force_delete');
        $orders = Order::query()->onlyTrashed()->with('status')->latest('deleted_at');

        return DataTables::of($orders)
            ->addColumn('select', fn($order) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $order->id . '"></div>')
            ->addColumn('total', function ($order) {
                if (!empty($order->discount) && $order->discount > 0) {
                    return number_format($order->total - $order->discount);
                }

                return number_format($order->total);
            })
            ->addColumn('payment_method', fn($order) => '<span class="badge rounded-pill" style="' . e($order->payment_method_badge_style) . '">' . e($order->payment_method_label) . '</span>')
            ->addColumn('status_id', function ($order) {
                $name = $order->status->name_locale ?? '-';
                $color = $order->status->color ?? 'secondary';

                return '<button class="btn btn-' . $color . '">' . e($name) . '</button>';
            })
            ->addColumn('deleted_at', fn($order) => Carbon::parse($order->deleted_at)->format('d/m/Y H:i:s'))
            ->addColumn('restore', function ($order) use ($canRestore) {
                if (!$canRestore) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('orders.restore', $order->id) . '" class="d-inline-block">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-success btn-sm">Khôi phục</button>'
                    . '</form>';
            })
            ->addColumn('force_delete', function ($order) use ($canForceDelete) {
                if (!$canForceDelete) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('orders.force-delete', $order->id) . '" class="d-inline-block" onsubmit="return confirm(\'Xóa vĩnh viễn đơn hàng này?\');">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="submit" class="btn btn-outline-danger btn-sm">Xóa vĩnh viễn</button>'
                    . '</form>';
            })
            ->rawColumns(['select', 'payment_method', 'status_id', 'restore', 'force_delete'])
            ->toJson();
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_action' => 'Vui lòng chọn ít nhất một đơn hàng.',
            ]);
        }

        $orders = Order::query()->whereIn('id', $selectedIds)->get();

        if ($orders->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy đơn hàng để xử lý.');
        }

        if ($action === 'cancel') {
            $affectedOrders = Order::query()
                ->whereIn('id', $selectedIds)
                ->where('status_id', '!=', 2)
                ->get();

            Order::query()
                ->whereIn('id', $selectedIds)
                ->where('status_id', '!=', 2)
                ->update(['status_id' => 4]);

            foreach ($affectedOrders as $order) {
                activity_log(
                    action: 'cancel',
                    subject: $order,
                    properties: [
                        'old' => ['status_id' => $order->status_id],
                        'new' => ['status_id' => 4],
                    ],
                    logName: 'Hủy đơn hàng',
                    description: 'Hủy đơn hàng chưa thanh toán'
                );

                // Notify Student
                if ($order->students) {
                    $order->students->notify(new \App\Notifications\OrderStatusNotification($order, 'cancelled'));
                }
            }

            return back()->with('msg', 'Đã hủy ' . $affectedOrders->count() . ' đơn hàng chưa thanh toán.');
        }

        if ($action === 'delete') {
            foreach ($orders as $order) {
                $snapshot = $order->toArray();
                $this->orderRepository->delete($order->id);

                activity_log(
                    action: 'delete',
                    subject: $order,
                    properties: ['data' => $snapshot],
                    logName: 'Xóa hàng loạt',
                    description: 'Xóa đơn hàng'
                );
            }

            return back()->with('msg', 'Đã xóa ' . $orders->count() . ' đơn hàng.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    public function trashBulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_action' => 'Vui lòng chọn ít nhất một đơn hàng trong thùng rác.',
            ]);
        }

        $orders = Order::query()->onlyTrashed()->whereIn('id', $selectedIds)->get();

        if ($orders->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy đơn hàng hợp lệ trong thùng rác.');
        }

        if ($action === 'restore') {
            foreach ($orders as $order) {
                $order->restore();

                activity_log(
                    action: 'restore',
                    subject: $order->fresh(),
                    properties: ['restored_from_trash' => true],
                    logName: 'Khôi phục hàng loạt',
                    description: 'Khôi phục đơn hàng'
                );
            }

            return back()->with('msg', 'Đã khôi phục ' . $orders->count() . ' đơn hàng.');
        }

        if ($action === 'force_delete') {
            foreach ($orders as $order) {
                $snapshot = $order->toArray();
                $order->forceDelete();

                activity_log(
                    action: 'force_delete',
                    subject: $order,
                    properties: [
                        'data' => $snapshot,
                        'deleted_permanently' => true,
                    ],
                    logName: 'Xóa vĩnh viễn hàng loạt',
                    description: 'Xóa vĩnh viễn đơn hàng'
                );
            }

            return back()->with('msg', 'Đã xóa vĩnh viễn ' . $orders->count() . ' đơn hàng.');
        }

        return back()->with('msg_danger', 'Thao tác trong thùng rác không hợp lệ.');
    }

    public function show($orderId)
    {
        $order = $this->orderRepository->getOrder($orderId);
        if (!$order) {
            abort(404);
        }

        $pageTitle = 'Chi tiết đơn hàng #' . $order->code;
        return view('orders::show', compact('pageTitle', 'order'));
    }

    public function delete($orderId)
    {
        $order = $this->orderRepository->getOrder($orderId);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đơn hàng không tồn tại',
            ], 404);
        }

        $snapshot = $order->toArray();
        $this->orderRepository->delete($orderId);

        activity_log(
            action: 'delete',
            subject: $order,
            properties: ['data' => $snapshot],
            logName: 'Xóa',
            description: 'Xóa đơn hàng'
        );

        return back()->with('msg', __('orders::messages.delete.success'));
    }

    public function restore($orderId)
    {
        $order = Order::query()->onlyTrashed()->find($orderId);

        if (!$order) {
            abort(404);
        }

        $order->restore();

        activity_log(
            action: 'restore',
            subject: $order->fresh(),
            properties: ['restored_from_trash' => true],
            logName: 'Khôi phục',
            description: 'Khôi phục đơn hàng thành công.'
        );

        return back()->with('msg', 'Khôi phục đơn hàng thành công.');
    }

    public function forceDelete($orderId)
    {
        $order = Order::query()->onlyTrashed()->find($orderId);

        if (!$order) {
            abort(404);
        }

        $snapshot = $order->toArray();
        $order->forceDelete();

        activity_log(
            action: 'force_delete',
            subject: $order,
            properties: [
                'data' => $snapshot,
                'deleted_permanently' => true,
            ],
            logName: 'Xóa vĩnh viễn',
            description: 'Xóa vĩnh viễn đơn hàng'
        );

        return back()->with('msg', 'Đã xóa vĩnh viễn đơn hàng.');
    }
}
