<?php

namespace Modules\Orders\src\Repositories;

use App\Repositories\RepositoryInterface;

interface OrdersRepositoryInterface extends RepositoryInterface
{
    public function getOrdersByStudent($studentId, $filters = [], $limit);
    public function getOrder($orderId);
    public function updatePaymentDate($orderId, $atributes = []);
    public function updateDiscount($orderId, $disscount, $coupon);
}
