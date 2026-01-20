<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Error;
use Illuminate\Http\Request;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;
use Modules\Students\src\Repositories\CouponsRepositoryInterface;

class CouponsController extends Controller
{
    private $couponRepository;
    private $orderRepository;

    public function __construct(CouponsRepositoryInterface $couponsRepository, OrdersRepositoryInterface $orderRepository)
    {
        $this->couponRepository = $couponsRepository;
        $this->orderRepository = $orderRepository;
    }

    public function verify(Request $request)
    {
        try {
            $coupon = $request->coupon;
            if (!$coupon) {
                throw new \Exception("Mã giảm giá bắt buộc phải nhập", 400);
            }
            $coupon = $this->couponRepository->verifyCoupon($coupon, $request->orderId);
            if (!$coupon) {
                throw new \Exception("Mã giảm giá không hợp lệ hoặc đã hết hạn", 400);
            }

            //Tính toán mã giảm giá
            $discount = 0;
            $order = $this->orderRepository->getOrder($request->orderId);
            if (
                $coupon->discount_type === 'percent' && $request->orderId && $order
            ) {
                $discount = ($order->total * $coupon->discount_value) / 100;
            }


            if ($coupon->discount_type == 'value') {
                $discount = $coupon->discount_value;
            }
            //Cập nhập mã giảm giá
            $this->orderRepository->updateDiscount($request->orderId, $discount, $coupon->code);
            return response()->json([
                'success' => true,
                'data' => [
                    'discount' => $discount,
                    'total' => $order->total,
                    'total_after_discount' => $order->total - $discount
                ]
            ]);
        } catch (\Exception $exception) {
            $code = $exception->getCode();
            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validated Failed',
                    'errors' => $exception->getMessage()
                ], $code ? $code : 500);
            }
        }
    }

    public function remove(Request $request)
    {
        $orderId = $request->orderId;
        if ($orderId) {
            $status = $this->orderRepository->updateDiscount($orderId, 0, null);
            if (!$status) {
                return response()->json([
                    'success' => false,
                ]);
            }
            $order = $this->orderRepository->getOrder($orderId);
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $order->total
                ]
            ]);
        }
        return response()->json([
            'success' => false,
        ]);
    }
}
