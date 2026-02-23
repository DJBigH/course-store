<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Notifications\OrderPaidNotification;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;
use Modules\User\src\Models\User;

class CheckoutController extends Controller
{

    private $orderRepository;

    public function __construct(OrdersRepositoryInterface $ordersRepository)
    {
        $this->orderRepository = $ordersRepository;
    }

    public function index($locale, $id)
    {
        $pageTitle = __('students::clients/checkout.checkout.page_title');
        $pageName = __('students::clients/checkout.checkout.page_name');
        $order = $this->orderRepository->getOrder($id);
        if (!$order || $order->status->is_success == 1) {
            abort(404);
        }
        $this->orderRepository->updateDiscount($id, 0, null);
        $order->discount = 0;
        $order->coupon = null;
        $this->orderRepository->updatePaymentDate($id);
        if (config('checkout.checkout_countdown') > 0) {

            $now = strtotime(date('Y-m-d H:i:s'));
            $paymentDate = strtotime($order->payment_date);
            $diff = $now - $paymentDate;
            $checkoutCountdown = config('checkout.checkout_countdown') * 60;
            if ($diff > $checkoutCountdown && !$order->payment_date == null) {
                return view('errors.clients.expired');
            }
        }
        return view('students::clients.checkout', compact('pageTitle', 'pageName', 'order'));
    }

    public function complete($locale, $orderId)
    {
        $order = $this->orderRepository->getOrder($orderId);

        if (!$order) {
            abort(404);
        }

        $this->orderRepository->completePayment($order);
        $admins = User::where('group_id', 1)->get();

        foreach ($admins as $admin) {
            $admin->notify(new OrderPaidNotification($order));
        }
        return redirect()->route('students.account.checkout-thankyou', [
            'locale' => app()->getLocale(),
            'id' => $order->id
        ]);
    }

    public function cancel($locale, $id)
    {
        $order = $this->orderRepository->getOrder($id);

        if (!$order || $order->status->is_success) {
            abort(404);
        }

        $this->orderRepository->cancelOrder($order);
        $admins = User::where('group_id', 1)->get();

        foreach ($admins as $admin) {
            $admin->notify(new OrderPaidNotification($order));
        }
        return redirect()
            ->route('students.account.order-detail', [
                'locale' => app()->getLocale(),
                'id' => $order->id
            ]);
    }



    public function thankyou($locale, $orderId)
    {
        $order = $this->orderRepository->getOrder($orderId);
        $pageTitle = __('students::clients/thankyou.page_title');
        $pageName = __('students::clients/thankyou.page_title');
        return view('students::clients.thanksyou', compact('pageTitle', 'pageName', 'order'));
    }
}
