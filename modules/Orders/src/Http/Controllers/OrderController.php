<?php

namespace Modules\Orders\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

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

    public function data()
    {
        $canView = auth()->user()?->hasPermission('orders.view');
        $canDelete = auth()->user()?->hasPermission('orders.delete');
        $orders = $this->orderRepository
            ->getCategories()
            ->when(request()->filled('payment_method_filter'), function ($query) {
                $query->where('payment_method', request()->input('payment_method_filter'));
            });

        return DataTables::of($orders)
            ->addColumn('select', function ($order) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $order->id . '"></div>';
            })
            ->editColumn('status_id', function ($order) {
                $name = $order->status->name_locale;
                $color = $order->status->color;

                return '<button class="btn btn-' . $color . '">' . $name . '</button>';
            })
            ->addColumn('total', function ($order) {
                if (!empty($order->discount) && $order->discount > 0) {
                    return number_format($order->total - $order->discount);
                }

                return number_format($order->total);
            })
            ->addColumn('payment_method', function ($order) {
                return '<span class="badge rounded-pill" style="' . e($order->payment_method_badge_style) . '">' . e($order->payment_method_label) . '</span>';
            })
            ->addColumn('created_at', function ($order) {
                return $order->created_at
                    ? date('d/m/Y H:i:s', strtotime($order->created_at))
                    : '';
            })
            ->addColumn('detail', function ($order) use ($canView) {
                return $canView
                    ? '<a href="' . route('orders.show', $order->id) . '" class="btn btn-primary btn-sm">Xem</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            })
            ->addColumn('delete', function ($order) use ($canDelete) {
                if (!$canDelete) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<a href="' . route('orders.delete', $order->id) . '" class="btn btn-outline-danger btn-sm delete-action">Xóa</a>';
            })
            ->rawColumns(['select', 'detail', 'delete', 'status_id', 'payment_method'])
            ->make(true);
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
            $affected = Order::query()
                ->whereIn('id', $selectedIds)
                ->where('status_id', '!=', 2)
                ->update(['status_id' => 4]);

            return back()->with('msg', 'Đã hủy ' . $affected . ' đơn hàng chưa thanh toán.');
        }

        if ($action === 'delete') {
            foreach ($orders as $order) {
                $this->orderRepository->delete($order->id);
            }

            return back()->with('msg', 'Đã xóa ' . $orders->count() . ' đơn hàng.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
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

        $this->orderRepository->delete($orderId);

        return back()->with('msg', __('orders::messages.delete.success'));
    }
}
