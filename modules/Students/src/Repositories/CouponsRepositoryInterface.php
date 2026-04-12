<?php

namespace Modules\Students\src\Repositories;

use App\Repositories\RepositoryInterface;

interface CouponsRepositoryInterface extends RepositoryInterface
{
    public function verifyCoupon($code, $order);
    public function isCourseCoupon($coupon);
    public function isBundleCoupon($coupon);
    public function getCourses($coupon,$orderId);
    public function getBundles($coupon, $orderId);
    public function getAllCoupons();
}
