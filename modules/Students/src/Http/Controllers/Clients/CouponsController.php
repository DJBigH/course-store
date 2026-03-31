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

    public function verify(Request $request, $locale)
    {
        $coupon = null;

        try {
            $coupon = $request->coupon;
            if (!$coupon) {
                throw new \Exception(__('students::clients/messages.verify_coupons.coupon_required'), 400);
            }
            $order = $this->orderRepository->getOrder($request->orderId);
            if (!$request->orderId || !$order) {
                throw new \Exception(__('students::clients/messages.verify_coupons.coupon_exp'), 400);
            }
            $coupon = $this->couponRepository->verifyCoupon($coupon, $order);
            if (!$coupon) {
                throw new \Exception(__('students::clients/messages.verify_coupons.coupon_exp'), 400);
            }

            //Tính toán mã giảm giá
            $discount = 0;
            $discountableTotal = (float) $order->total;

            if (
                $coupon->discount_type === 'percent' && $request->orderId && $order
            ) {
                $discount = ($discountableTotal * $coupon->discount_value) / 100;
            }


            if ($coupon->discount_type == 'value') {
                $discount = $coupon->discount_value;
            }
            if ($this->couponRepository->isCourseCoupon($coupon)) {
                $courses = $this->couponRepository->getCourses($coupon, $request->orderId)->pluck('id')->toArray();
                $discountableTotal = (float) $order->detail()->whereIn('course_id', $courses)->sum('price');
                if ($coupon->discount_type === 'percent') {
                    $discount = $discountableTotal * $coupon->discount_value / 100;
                }

                if ($coupon->discount_type === 'value') {
                    $discount = $coupon->discount_value;
                }
            }
            //Cập nhập mã giảm giá
            $discount = min((float) $discount, $discountableTotal);
            $totalAfterDiscount = max((float) $order->total - $discount, 0);
            $this->orderRepository->updateDiscount($request->orderId, $discount, $coupon->code);
            return response()->json([
                'success' => true,
                'data' => [
                    'discount' => $discount,
                    'total' => $order->total,
                    'total_after_discount' => $totalAfterDiscount
                ]
            ]);
        } catch (\Exception $exception) {
            $code = $exception->getCode();
            return response()->json([
                'success' => false,
                'message' => 'Validated Failed',
                'errors' => $exception->getMessage()
            ], $code ? $code : 500);
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

    public function remove(Request $request, $locale)
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
