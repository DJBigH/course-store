<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Mail\OrderPaidCustomerMail;
use App\Notifications\OrderPaidNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;
use Modules\Students\src\Models\Student;
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

        $this->markOrderAsPaid($order);

        return redirect()->route('students.account.checkout-thankyou', [
            'locale' => app()->getLocale(),
            'id' => $order->id,
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

        return redirect()->route('students.account.order-detail', [
            'locale' => app()->getLocale(),
            'id' => $order->id,
        ]);
    }

    public function thankyou($locale, $orderId)
    {
        $order = $this->orderRepository->getOrder($orderId);
        $pageTitle = __('students::clients/thankyou.page_title');
        $pageName = __('students::clients/thankyou.page_title');

        return view('students::clients.thanksyou', compact('pageTitle', 'pageName', 'order'));
    }

    public function momo($locale, $orderId)
    {
        $order = $this->orderRepository->getOrder($orderId);

        if (!$order || $order->status->is_success) {
            abort(404);
        }

        $config = config('services.momo');
        if (
            empty($config['partner_code']) ||
            empty($config['access_key']) ||
            empty($config['secret_key'])
        ) {
            return redirect()->route('students.account.checkout', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', __('students::clients/checkout.checkout.momo_not_configured'))
              ->with('msgType', 'danger');
        }

        $amount = (int) max($order->total - ($order->discount ?? 0), 0);
        if ($amount <= 0) {
            return redirect()->route('students.account.checkout', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', __('students::clients/checkout.checkout.momo_invalid_amount'))
              ->with('msgType', 'danger');
        }

        $requestId = (string) Str::uuid();
        $redirectUrl = route('students.account.checkout-momo-return', ['locale' => $locale]);
        $ipnUrl = route('students.account.checkout-momo-ipn', ['locale' => $locale]);
        $extraData = base64_encode(json_encode([
            'order_id' => $order->id,
            'student_id' => $order->student_id,
        ]));

        $payload = [
            'partnerCode' => $config['partner_code'],
            'accessKey' => $config['access_key'],
            'requestId' => $requestId,
            'amount' => (string) $amount,
            'orderId' => (string) $order->code,
            'orderInfo' => __('students::clients/checkout.checkout.momo_order_info', ['code' => $order->code]),
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'extraData' => $extraData,
            'requestType' => $config['request_type'],
            'lang' => app()->getLocale() === 'vi' ? 'vi' : 'en',
            'autoCapture' => true,
        ];

        $rawSignature = sprintf(
            'accessKey=%s&amount=%s&extraData=%s&ipnUrl=%s&orderId=%s&orderInfo=%s&partnerCode=%s&redirectUrl=%s&requestId=%s&requestType=%s',
            $payload['accessKey'],
            $payload['amount'],
            $payload['extraData'],
            $payload['ipnUrl'],
            $payload['orderId'],
            $payload['orderInfo'],
            $payload['partnerCode'],
            $payload['redirectUrl'],
            $payload['requestId'],
            $payload['requestType'],
        );

        $payload['signature'] = hash_hmac('sha256', $rawSignature, $config['secret_key']);

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->post($config['endpoint'], $payload)
                ->throw()
                ->json();
        } catch (\Throwable $e) {
            Log::error('MoMo create payment failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('students.account.checkout', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', __('students::clients/checkout.checkout.momo_create_failed'))
              ->with('msgType', 'danger');
        }

        if (!empty($response['payUrl'])) {
            return redirect()->away($response['payUrl']);
        }

        Log::warning('MoMo create payment response missing payUrl', [
            'order_id' => $order->id,
            'response' => $response,
        ]);

        return redirect()->route('students.account.checkout', [
            'locale' => $locale,
            'id' => $order->id,
        ])->with('msg', $response['message'] ?? __('students::clients/checkout.checkout.momo_create_failed'))
          ->with('msgType', 'danger');
    }

    public function momoReturn(Request $request, $locale)
    {
        $order = $this->resolveMomoOrder($request);

        if (!$order) {
            return redirect()->route('students.account.my-order', ['locale' => $locale])
                ->with('msg', __('students::clients/checkout.checkout.momo_invalid_return'))
                ->with('msgType', 'danger');
        }

        if ((int) $request->integer('resultCode') === 0) {
            $this->markOrderAsPaid($order);

            return redirect()->route('students.account.checkout-thankyou', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', __('students::clients/checkout.checkout.momo_payment_success'))
              ->with('msgType', 'success');
        }

        if (!$order->status->is_success) {
            $order->update(['status_id' => 3]);
        }

        return redirect()->route('students.account.order-detail', [
            'locale' => $locale,
            'id' => $order->id,
        ])->with('msg', $request->string('message')->toString() ?: __('students::clients/checkout.checkout.momo_payment_failed'))
          ->with('msgType', 'danger');
    }

    public function momoIpn(Request $request, $locale)
    {
        $order = $this->resolveMomoOrder($request);

        if (!$order) {
            return response()->json(['resultCode' => 1, 'message' => 'Order not found'], 404);
        }

        if ((int) $request->integer('resultCode') === 0) {
            $this->markOrderAsPaid($order);

            return response()->json(['resultCode' => 0, 'message' => 'Success']);
        }

        if (!$order->status->is_success) {
            $order->update(['status_id' => 3]);
        }

        return response()->json(['resultCode' => 0, 'message' => 'Failure recorded']);
    }

    private function resolveMomoOrder(Request $request): ?Order
    {
        $extraData = $request->input('extraData');
        $decoded = null;

        if (!empty($extraData)) {
            $decoded = json_decode(base64_decode($extraData, true) ?: '', true);
        }

        $orderId = $decoded['order_id'] ?? null;

        if (!$orderId && $request->filled('orderId')) {
            $orderId = Order::where('code', $request->input('orderId'))->value('id');
        }

        if (!$orderId) {
            return null;
        }

        return $this->orderRepository->getOrder($orderId);
    }

    private function markOrderAsPaid(Order $order): void
    {
        if ($order->status && $order->status->is_success) {
            return;
        }

        $this->orderRepository->completePayment($order);

        $admins = User::where('group_id', 1)->get();
        foreach ($admins as $admin) {
            $admin->notify(new OrderPaidNotification($order));
        }

        $student = Student::find($order->student_id);
        if ($student && $student->email) {
            Mail::to($student->email)->queue(new OrderPaidCustomerMail(
                $order,
                method_exists($student, 'preferredLocale') ? $student->preferredLocale() : app()->getLocale()
            ));
        }
    }
}
