<?php

namespace Modules\Orders\src\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $orders = $this->orderRepository->getCategories();

        return DataTables::of($orders)
            ->editColumn('status_id', function ($orders) {
                $name = $orders->status->name;
                $color = $orders->status->color;
                return '<button class="btn btn-' . $color . '">' . $name . '</button>';
            })
            ->addColumn('total', function ($order) {
                if (!empty($order->discount) && $order->discount > 0) {
                    return number_format($order->total - $order->discount);
                }
                return number_format($order->total);
            })
            ->addColumn('created_at', function ($order) {
                return $order->created_at
                    ? date('d/m/Y H:i:s', strtotime($order->created_at))
                    : '';
            })
            ->addColumn('detail', function ($order) {
                return '<a href="' . route('orders.show', $order->id) . '" class="btn btn-primary btn-sm">Xem</a>';
            })
            ->addColumn('delete', function ($order) {
                return '<a href="' . route('orders.delete', $order->id) . '" class="btn btn-danger btn-sm delete-action">Xóa</a>';
            })
            ->rawColumns(['detail', 'delete', 'status_id'])
            ->make(true);
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
                'message' => 'Đơn hàng không tồn tại'
            ], 404);
        }

        $this->orderRepository->delete($orderId);

        return back()->with('msg', __('orders::messages.delete.success'));
    }
}
