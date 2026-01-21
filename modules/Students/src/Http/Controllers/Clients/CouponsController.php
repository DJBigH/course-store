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
            $order = $this->orderRepository->getOrder($request->orderId);
            $coupon = $this->couponRepository->verifyCoupon($coupon, $order);
            if (!$coupon) {
                throw new \Exception("Mã giảm giá không hợp lệ hoặc đã hết hạn", 400);
            }

            //Tính toán mã giảm giá
            $discount = 0;

            if (
                $coupon->discount_type === 'percent' && $request->orderId && $order
            ) {
                $discount = ($order->total * $coupon->discount_value) / 100;
            }


            if ($coupon->discount_type == 'value') {
                $discount = $coupon->discount_value;
            }
            if ($this->couponRepository->isCourseCoupon($coupon)) {
                $courses = $this->couponRepository->getCourses($coupon, $request->orderId)->pluck('id')->toArray();
                if ($coupon->discount_type === 'percent') {
                    $discount = $order->detail()->whereIn('course_id', $courses)->sum('price') * $coupon->discount_value / 100;
                }

                if ($coupon->discount_type === 'value') {
                    $discount = $order->detail()->whereIn('course_id', $courses)->sum('price') * $coupon->discount_value / 100;
                }
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

    public function pollingCoupon(Request $request)
    {
        set_time_limit(0);
        ignore_user_abort(enable: false);
        while (true) {
            echo '\n';
            ob_flush();
            flush();
            if (connection_aborted()) {
                return;
            }
            $data =  $this->verify($request);
            $data = json_decode($data->getContent());
            if (!$data->success) {
                break;
            }
            sleep(1);
        }
        return response()->json([
            'success' => false,
        ], 500);
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
