<?php

use Modules\Orders\src\Repositories\OrdersRepositoryInterface;

function getCurrentPaymentDate()
{
    $orderId = request()->route()->id;
    $orderRepository = app(OrdersRepositoryInterface::class);

    $order = $orderRepository->getOrder($orderId);
    if (!$order) {
        abort(404);
    }
    return $order->payment_date;
}

function generateCouponCode(int $length = 10): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $code;
}


function generateUniqueCouponCode(int $length = 10): string
{
    do {
        $code = generateCouponCode($length);
    } while (\DB::table('coupons')->where('code', $code)->exists());

    return $code;
}
