<?php

namespace Modules\Orders\src\Repositories;

use App\Repositories\RepositoryInterface;
use Modules\Orders\src\Models\Order;

interface OrdersRepositoryInterface extends RepositoryInterface
{
    public function getOrdersByStudent($studentId, $filters = [], $limit);
    public function getOrder($orderId);
    public function updatePaymentDate($orderId, $atributes = []);
    public function updateDiscount($orderId, $disscount, $coupon);
    public function createOrder($data = []);
    public function createOrderWithDetail(array $orderData, array $detailData);
    public function completePayment(Order $order);
    public function cancelOrder(Order $order);
}
