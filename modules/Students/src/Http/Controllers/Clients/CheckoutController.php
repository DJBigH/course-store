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

        $checkoutCountdownMinutes = $this->checkoutCountdownMinutes();

        if ($checkoutCountdownMinutes > 0) {
            $now = strtotime(date('Y-m-d H:i:s'));
            $paymentDate = strtotime($order->payment_date);
            $diff = $now - $paymentDate;
            $checkoutCountdown = $checkoutCountdownMinutes * 60;

            if ($diff > $checkoutCountdown && !$order->payment_date == null) {
                return view('errors.clients.expired');
            }
        }

        $payableAmount = $this->getPayableAmount($order);
        $isFreeOrder = $payableAmount <= 0;

        return view('students::clients.checkout', compact('pageTitle', 'pageName', 'order', 'payableAmount', 'isFreeOrder'));
    }

    public function complete($locale, $orderId)
    {
        $order = $this->orderRepository->getOrder($orderId);

        if (!$order) {
            abort(404);
        }

        if ($this->getPayableAmount($order) > 0) {
            if (!(int) setting('payment_bank_enabled', '1')) {
                return back()->with('msg', 'Phương thức chuyển khoản hiện đang bảo trì. Vui lòng chọn phương thức khác.')
                    ->with('msgType', 'danger');
            }
        }

        $this->setOrderPaymentMethod($order, $this->getPayableAmount($order) <= 0 ? 'free' : 'bank_transfer');
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
        $admins = User::query()->inGroup('super_admin')->get();

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

    public function vnpay($locale, $orderId, Request $request)
    {
        $order = $this->orderRepository->getOrder($orderId);

        if (!$order || $order->status->is_success) {
            abort(404);
        }

        if (!$this->isPaymentGatewayEnabled('vnpay')) {
            return redirect()->route('students.account.checkout', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', 'Cổng thanh toán VNPAY hiện đang bảo trì. Vui lòng chọn phương thức khác.')
                ->with('msgType', 'danger');
        }

        $this->setOrderPaymentMethod($order, 'vnpay');

        $config = $this->getVnpayConfig();
        if (empty($config['tmn_code']) || empty($config['hash_secret']) || empty($config['url'])) {
            return redirect()->route('students.account.checkout', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', __('students::clients/checkout.checkout.vnpay_not_configured'))
                ->with('msgType', 'danger');
        }

        $amount = (int) max($order->total - ($order->discount ?? 0), 0);
        if ($amount <= 0) {
            $this->markOrderAsPaid($order);

            return redirect()->route('students.account.checkout-thankyou', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', __('students::clients/checkout.checkout.free_order_completed'))
                ->with('msgType', 'success');
        }

        $returnUrl = $config['return_url'] ?: route('students.account.checkout-vnpay-return', ['locale' => $locale]);
        $inputData = [
            'vnp_Version' => $config['version'],
            'vnp_TmnCode' => $config['tmn_code'],
            'vnp_Amount' => $amount * 100,
            'vnp_Command' => $config['command'],
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode' => $config['curr_code'],
            'vnp_IpAddr' => $request->ip(),
            'vnp_Locale' => app()->getLocale() === 'vi' ? 'vn' : 'en',
            'vnp_OrderInfo' => __('students::clients/checkout.checkout.vnpay_order_info', ['code' => $order->code]),
            'vnp_OrderType' => $config['order_type'],
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $order->code,
            'vnp_ExpireDate' => now()->addMinutes(max($this->checkoutCountdownMinutes(), 15))->format('YmdHis'),
        ];

        if (!empty($config['bank_code'])) {
            $inputData['vnp_BankCode'] = $config['bank_code'];
        }

        $paymentUrl = $this->buildVnpayUrl($config['url'], $inputData, $config['hash_secret']);

        return redirect()->away($paymentUrl);
    }

    public function vnpayReturn(Request $request, $locale)
    {
        $order = $this->resolveVnpayOrder($request);

        if (!$order) {
            return redirect()->route('students.account.my-order', ['locale' => $locale])
                ->with('msg', __('students::clients/checkout.checkout.vnpay_invalid_return'))
                ->with('msgType', 'danger');
        }

        if (!$this->validateVnpaySignature($request)) {
            return redirect()->route('students.account.order-detail', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', __('students::clients/checkout.checkout.vnpay_invalid_signature'))
                ->with('msgType', 'danger');
        }

        if ($request->input('vnp_ResponseCode') === '00' && $request->input('vnp_TransactionStatus') === '00') {
            $this->markOrderAsPaid($order);

            return redirect()->route('students.account.checkout-thankyou', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', __('students::clients/checkout.checkout.vnpay_payment_success'))
                ->with('msgType', 'success');
        }

        if (!$order->status->is_success) {
            $order->update(['status_id' => 3]);
        }

        return redirect()->route('students.account.order-detail', [
            'locale' => $locale,
            'id' => $order->id,
        ])->with('msg', $this->mapVnpayMessage($request) ?: __('students::clients/checkout.checkout.vnpay_payment_failed'))
            ->with('msgType', 'danger');
    }

    public function vnpayIpn(Request $request, $locale)
    {
        $order = $this->resolveVnpayOrder($request);

        if (!$order) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found'], 404);
        }

        if (!$this->validateVnpaySignature($request)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature'], 400);
        }

        if ($request->input('vnp_ResponseCode') === '00' && $request->input('vnp_TransactionStatus') === '00') {
            $this->markOrderAsPaid($order);

            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        }

        if (!$order->status->is_success) {
            $order->update(['status_id' => 3]);
        }

        return response()->json(['RspCode' => '00', 'Message' => 'Failure recorded']);
    }

    public function momo($locale, $orderId)
    {
        $order = $this->orderRepository->getOrder($orderId);

        if (!$order || $order->status->is_success) {
            abort(404);
        }

        if (!$this->isPaymentGatewayEnabled('momo')) {
            return redirect()->route('students.account.checkout', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', 'Ví điện tử MoMo hiện đang bảo trì. Vui lòng chọn phương thức khác.')
                ->with('msgType', 'danger');
        }

        $this->setOrderPaymentMethod($order, 'momo');

        $config = $this->getMomoConfig();
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
            $this->markOrderAsPaid($order);

            return redirect()->route('students.account.checkout-thankyou', [
                'locale' => $locale,
                'id' => $order->id,
            ])->with('msg', __('students::clients/checkout.checkout.free_order_completed'))
                ->with('msgType', 'success');
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

    private function resolveVnpayOrder(Request $request): ?Order
    {
        $txnRef = $request->input('vnp_TxnRef');
        if (!$txnRef) {
            return null;
        }

        $orderId = Order::where('code', $txnRef)->value('id');

        if (!$orderId) {
            return null;
        }

        return $this->orderRepository->getOrder($orderId);
    }

    private function buildVnpayUrl(string $baseUrl, array $inputData, string $hashSecret): string
    {
        ksort($inputData);

        $pairs = [];
        foreach ($inputData as $key => $value) {
            $pairs[] = urlencode((string) $key) . '=' . urlencode((string) $value);
        }

        $query = implode('&', $pairs);
        $secureHash = hash_hmac('sha512', $query, $hashSecret);

        return $baseUrl . '?' . $query . '&vnp_SecureHash=' . $secureHash;
    }

    private function validateVnpaySignature(Request $request): bool
    {
        $hashSecret = $this->getVnpayConfig()['hash_secret'] ?? null;
        $secureHash = $request->input('vnp_SecureHash');

        if (!$hashSecret || !$secureHash) {
            return false;
        }

        $inputData = $request->except(['vnp_SecureHash', 'vnp_SecureHashType']);
        ksort($inputData);

        $pairs = [];
        foreach ($inputData as $key => $value) {
            $pairs[] = urlencode((string) $key) . '=' . urlencode((string) $value);
        }

        $calculatedHash = hash_hmac('sha512', implode('&', $pairs), $hashSecret);

        return hash_equals($calculatedHash, $secureHash);
    }

    private function mapVnpayMessage(Request $request): string
    {
        $responseCode = $request->input('vnp_ResponseCode');

        return match ($responseCode) {
            '24' => __('students::clients/checkout.checkout.vnpay_payment_cancelled'),
            '51' => __('students::clients/checkout.checkout.vnpay_insufficient_balance'),
            '65' => __('students::clients/checkout.checkout.vnpay_transaction_limit'),
            '75' => __('students::clients/checkout.checkout.vnpay_bank_maintenance'),
            default => $request->input('vnp_OrderInfo', __('students::clients/checkout.checkout.vnpay_payment_failed')),
        };
    }

    private function markOrderAsPaid(Order $order): void
    {
        if ($order->status && $order->status->is_success) {
            return;
        }

        $this->orderRepository->completePayment($order);
        $order->refresh()->loadMissing(['status', 'detail.courses']);

        $admins = User::query()->inGroup('super_admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new OrderPaidNotification($order));
        }

        $student = Student::find($order->student_id);
        if ($student) {
            $this->logPurchasedOrder($student, $order);
        }

        if ($student && $student->email) {
            Mail::to($student->email)->queue(new OrderPaidCustomerMail(
                $order,
                method_exists($student, 'preferredLocale') ? $student->preferredLocale() : app()->getLocale()
            ));
        }
    }

    private function logPurchasedOrder(Student $student, Order $order): void
    {
        $courses = $order->detail
            ->pluck('courses')
            ->filter()
            ->map(function ($course) {
                return $this->buildTranslatedNames($course);
            })
            ->values()
            ->all();

        activity_log(
            'order_purchased',
            $student,
            [
                'order_code' => $order->code,
                'order_status' => $this->buildTranslatedStatus($order),
                'total_paid' => (float) max($order->total - ($order->discount ?? 0), 0),
                'courses' => $courses,
            ],
            'student_order',
            __('students::clients/account.activity_log.order_purchased_desc')
        );
    }

    private function buildTranslatedNames(object $model): array
    {
        return [
            'vi' => (string) ($model->name ?? ''),
            'en' => (string) ($model->name_en ?? $model->name ?? ''),
            'ko' => (string) ($model->name_ko ?? $model->name ?? $model->name_en ?? ''),
            'ja' => (string) ($model->name_ja ?? $model->name ?? $model->name_en ?? ''),
            'zh' => (string) ($model->name_zh ?? $model->name ?? $model->name_en ?? ''),
        ];
    }

    private function buildTranslatedStatus(Order $order): array
    {
        $status = $order->status;

        return [
            'vi' => (string) ($status->name ?? ''),
            'en' => (string) ($status->name_en ?? $status->name ?? ''),
            'ko' => (string) ($status->name_ko ?? $status->name ?? $status->name_en ?? ''),
            'ja' => (string) ($status->name_ja ?? $status->name ?? $status->name_en ?? ''),
            'zh' => (string) ($status->name_zh ?? $status->name ?? $status->name_en ?? ''),
        ];
    }

    private function getPayableAmount(Order $order): float
    {
        return (float) max($order->total - ($order->discount ?? 0), 0);
    }

    private function isPaymentGatewayEnabled(string $gateway): bool
    {
        $default = $gateway === 'momo' ? '0' : '1';

        return (int) setting('payment_' . $gateway . '_enabled', $default) === 1;
    }

    private function getVnpayConfig(): array
    {
        return [
            'url' => config('services.vnpay.url'),
            'tmn_code' => config('services.vnpay.tmn_code'),
            'hash_secret' => config('services.vnpay.hash_secret'),
            'return_url' => config('services.vnpay.return_url'),
            'bank_code' => config('services.vnpay.bank_code'),
            'version' => config('services.vnpay.version'),
            'command' => config('services.vnpay.command'),
            'curr_code' => config('services.vnpay.curr_code'),
            'order_type' => config('services.vnpay.order_type'),
        ];
    }

    private function getMomoConfig(): array
    {
        return [
            'endpoint' => config('services.momo.endpoint'),
            'partner_code' => config('services.momo.partner_code'),
            'access_key' => config('services.momo.access_key'),
            'secret_key' => config('services.momo.secret_key'),
            'request_type' => config('services.momo.request_type'),
        ];
    }

    private function setOrderPaymentMethod(Order $order, string $paymentMethod): void
    {
        if ($order->payment_method === $paymentMethod) {
            return;
        }

        $order->update(['payment_method' => $paymentMethod]);
    }

    private function checkoutCountdownMinutes(): int
    {
        return max(0, (int) setting('checkout_countdown_minutes', config('checkout.checkout_countdown', 0)));
    }
}
