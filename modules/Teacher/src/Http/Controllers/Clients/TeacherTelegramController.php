<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Models\TelegramPackage;
use Modules\Teacher\src\Models\TeacherTelegramSubscription;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Orders\src\Models\Order;
use Modules\Finances\src\Models\PayoutRequest as TeacherPayoutRequest;

class TeacherTelegramController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected PackageLifecycleManager $packageLifecycleManager,
        protected PackageUsageResolver $packageUsageResolver,
        protected TeacherNotificationCenter $notificationCenter,
    ) {}

    /**
     * Hiển thị danh sách các gói Telegram và trạng thái hiện tại
     */
    public function index()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = 'Cấu hình & Mua gói Telegram';
        $pageName = 'Telegram';
        
        $packages = TelegramPackage::where('is_active', true)->orderBy('sort_order')->get();
        $subscriptionStatus = $teacher->getTelegramPackageStatus();
        $availableBalance = $this->resolveAvailableBalance($teacher);
        
        $paymentSettings = \Modules\Settings\src\Models\Setting::whereIn('key', [
            'payment_bank_enabled',
            'payment_momo_enabled',
            'payment_vnpay_enabled',
            'payment_wallet_enabled',
            'bank_transfer_bank_bin',
            'bank_transfer_bank_name',
            'bank_transfer_account_number',
            'bank_transfer_account_name',
            'bank_transfer_note_prefix'
        ])->pluck('value', 'key')->toArray();

        $history = TeacherTelegramSubscription::where('teacher_id', $teacher->id)
            ->with('package')
            ->orderBy('id', 'desc')
            ->get();

        return view('teacher::teacher.telegram.index', compact(
            'pageTitle', 'pageName', 'teacher', 'packages', 
            'subscriptionStatus', 'availableBalance', 'paymentSettings', 'history'
        ));
    }

    public function cancelPending(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) return redirect()->route('teacher.dashboard.index');

        // 1. Xóa các bản ghi trong bảng teacher_telegram_subscriptions có trạng thái pending
        $pendingIds = DB::table('teacher_telegram_subscriptions')
            ->where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->pluck('id');

        if ($pendingIds->isNotEmpty()) {
            // 2. Xóa các đơn hàng liên quan
            DB::table('orders')
                ->whereIn('orderable_id', $pendingIds)
                ->where('orderable_type', TeacherTelegramSubscription::class)
                ->delete();

            // 3. Xóa chính các bản ghi subscription
            DB::table('teacher_telegram_subscriptions')
                ->whereIn('id', $pendingIds)
                ->delete();
        }

        return redirect()->route('teacher.dashboard.telegram.index')
            ->with('msg_success', 'Đã hủy yêu cầu thành công. Bạn có thể chọn gói mới ngay bây giờ.');
    }

    /**
     * Xử lý mua/gia hạn gói Telegram
     */
    public function purchase(Request $request, $packageId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $package = TelegramPackage::findOrFail($packageId);
        if (!$package->is_active) {
            if ($request->ajax()) return response()->json(['message' => 'Gói này hiện không còn khả dụng.'], 422);
            return back()->with('msg_danger', 'Gói này hiện không còn khả dụng.');
        }

        $paymentMethod = $request->input('payment_method', 'bank_transfer');

        DB::beginTransaction();
        try {
            $priceVnd = $package->sale_price ?? $package->price;
            $statusId = 1; // Mặc định Pending
            $isPaid = false;

            if ($paymentMethod === 'wallet') {
                $availableBalance = $this->resolveAvailableBalance($teacher);
                if ($availableBalance < $priceVnd) {
                    if ($request->ajax()) {
                        return response()->json(['message' => 'Số dư ví không đủ để thực hiện thanh toán.'], 422);
                    }
                    return back()->with('msg_danger', 'Số dư ví không đủ để thực hiện thanh toán.');
                }
            }

            $statusId = 2; // Success
            $isPaid = true;

            // Sử dụng logic cộng dồn từ Model nếu đã thanh toán
            $newExpiry = null;
            if ($isPaid) {
                $newExpiry = $teacher->addTelegramDuration($package->duration_value, $package->duration_unit);
            }

            // Tạo bản ghi lịch sử đăng ký
            $subscription = TeacherTelegramSubscription::create([
                'teacher_id' => $teacher->id,
                'telegram_package_id' => $package->id,
                'amount' => $priceVnd,
                'started_at' => now(),
                'expires_at' => $newExpiry,
                'status' => $isPaid ? 'active' : 'pending'
            ]);

            // Tạo Đơn hàng để hệ thống ghi nhận doanh thu
            $order = Order::query()->create([
                'code' => 'TEL_' . strtoupper(Str::random(10)),
                'student_id' => $teacher->student_id,
                'total' => $priceVnd,
                'currency' => 'VND',
                'exchange_rate' => 1.0,
                'base_total' => $priceVnd,
                'status_id' => $statusId,
                'orderable_id' => $subscription->id,
                'orderable_type' => TeacherTelegramSubscription::class,
                'type' => 'telegram_package',
                'payment_method' => $paymentMethod,
                'payment_date' => $isPaid ? now() : null,
                'payment_complete_date' => $isPaid ? now() : null,
            ]);

            // Tạo chi tiết đơn hàng (để xuất hiện trong báo cáo doanh thu)
            \Modules\Orders\src\Models\OrderDetail::create([
                'order_id' => $order->id,
                'course_id' => null, // null cho các gói hệ thống
                'price' => $priceVnd,
                'total_amount' => $priceVnd,
                'type' => 'telegram_package'
            ]);

            // Log hoạt động nếu đã thanh toán
            if ($isPaid) {
                activity_log(
                    action: 'telegram_package_purchased',
                    subject: $teacher,
                    properties: [
                        'package_id' => $package->id,
                        'price' => $priceVnd,
                        'payment_method' => $paymentMethod,
                        'new_expiry' => $newExpiry?->toDateTimeString()
                    ],
                    logName: 'teacher_telegram_management',
                    description: "Đã mua gói Telegram: {$package->name} qua {$paymentMethod}"
                );
            }

            if ($paymentMethod === 'vnpay') {
                DB::commit();
                return $this->handleVnpayPayment($order, $package);
            }

            if ($paymentMethod === 'momo') {
                DB::commit();
                return $this->handleMomoPayment($order, $package);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $isPaid ? 'Thanh toán thành công!' : 'Đơn hàng đã được tạo. Vui lòng thanh toán để kích hoạt gói.',
                    'redirect' => route('teacher.dashboard.telegram.index')
                ]);
            }

            $msg = $isPaid ? 'Mua gói thành công! Thời hạn sử dụng đã được cập nhật.' : 'Đơn hàng đã được tạo. Vui lòng thanh toán để kích hoạt gói.';
            return redirect()->route('teacher.dashboard.telegram.index')->with('msg_success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
            }
            return back()->with('msg_danger', 'Có lỗi xảy ra trong quá trình thanh toán: ' . $e->getMessage());
        }
    }

    private function handleVnpayPayment(Order $order, TelegramPackage $package)
    {
        $config = config('services.vnpay');
        $amount = (int) $order->total;

        $inputData = [
            'vnp_Version' => $config['version'] ?? '2.1.0',
            'vnp_TmnCode' => $config['tmn_code'],
            'vnp_Amount' => $amount * 100,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => request()->ip(),
            'vnp_Locale' => app()->getLocale() === 'vi' ? 'vn' : 'en',
            'vnp_OrderInfo' => 'Thanh toan goi Telegram: ' . $package->name,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => route('teacher.dashboard.telegram.purchase.vnpay-return'),
            'vnp_TxnRef' => $order->code,
        ];

        if (!empty($config['bank_code'])) {
            $inputData['vnp_BankCode'] = $config['bank_code'];
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashdata .= urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
            $query .= urlencode((string)$key) . "=" . urlencode((string)$value) . '&';
        }

        $vnp_Url = $config['url'] . "?" . $query;
        if (isset($config['hash_secret'])) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $config['hash_secret']);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        if (request()->ajax()) {
            return response()->json(['success' => true, 'redirect' => $vnp_Url]);
        }
        return redirect()->away($vnp_Url);
    }

    private function handleMomoPayment(Order $order, TelegramPackage $package)
    {
        $config = config('services.momo');
        $amount = (int) $order->total;
        $requestId = (string) Str::uuid();

        $payload = [
            'partnerCode' => $config['partner_code'],
            'accessKey' => $config['access_key'],
            'requestId' => $requestId,
            'amount' => (string) $amount,
            'orderId' => $order->code,
            'orderInfo' => 'Thanh toan goi Telegram: ' . $package->name,
            'redirectUrl' => route('teacher.dashboard.telegram.purchase.momo-return'),
            'ipnUrl' => route('teacher.dashboard.telegram.purchase.momo-ipn'),
            'extraData' => '',
            'requestType' => $config['request_type'] ?? 'captureWallet',
            'lang' => app()->getLocale() === 'vi' ? 'vi' : 'en',
            'autoCapture' => true,
        ];

        $rawSignature = sprintf(
            'accessKey=%s&amount=%s&extraData=%s&ipnUrl=%s&orderId=%s&orderInfo=%s&partnerCode=%s&redirectUrl=%s&requestId=%s&requestType=%s',
            $payload['accessKey'], $payload['amount'], $payload['extraData'], $payload['ipnUrl'],
            $payload['orderId'], $payload['orderInfo'], $payload['partnerCode'], $payload['redirectUrl'],
            $payload['requestId'], $payload['requestType']
        );

        $payload['signature'] = hash_hmac('sha256', $rawSignature, $config['secret_key']);

        $client = new \GuzzleHttp\Client();
        $response = $client->post($config['url'], [
            'json' => $payload
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        if (isset($result['payUrl'])) {
            if (request()->ajax()) {
                return response()->json(['success' => true, 'redirect' => $result['payUrl']]);
            }
            return redirect()->away($result['payUrl']);
        }

        throw new \Exception('Không thể kết nối đến cổng thanh toán MoMo.');
    }

    public function vnpayReturn(Request $request)
    {
        $orderCode = $request->vnp_TxnRef;
        $vnp_ResponseCode = $request->vnp_ResponseCode;
        $order = Order::where('code', $orderCode)->firstOrFail();

        if ($vnp_ResponseCode === '00') {
            $this->completeOrder($order);
            return redirect()->route('teacher.dashboard.telegram.index')->with('msg_success', 'Thanh toán VNPay thành công!');
        }

        return redirect()->route('teacher.dashboard.telegram.index')->with('msg_danger', 'Thanh toán VNPay không thành công hoặc bị hủy.');
    }

    public function momoReturn(Request $request)
    {
        $orderCode = $request->orderId;
        $resultCode = $request->resultCode;
        $order = Order::where('code', $orderCode)->firstOrFail();

        if ($resultCode == 0) {
            $this->completeOrder($order);
            return redirect()->route('teacher.dashboard.telegram.index')->with('msg_success', 'Thanh toán MoMo thành công!');
        }

        return redirect()->route('teacher.dashboard.telegram.index')->with('msg_danger', 'Thanh toán MoMo không thành công hoặc bị hủy.');
    }

    public function momoIpn(Request $request)
    {
        $orderCode = $request->orderId;
        $resultCode = $request->resultCode;
        $order = Order::where('code', $orderCode)->first();

        if ($order && $resultCode == 0) {
            $this->completeOrder($order);
        }

        return response()->json(['message' => 'IPN Received']);
    }

    private function completeOrder(Order $order)
    {
        if ($order->status_id == 2) return;

        DB::transaction(function () use ($order) {
            $order->update([
                'status_id' => 2,
                'payment_complete_date' => now(),
            ]);

            $subscription = $order->orderable;
            if ($subscription && $subscription instanceof TeacherTelegramSubscription) {
                $package = $subscription->package;
                $teacher = $subscription->teacher;

                $newExpiry = $teacher->addTelegramDuration($package->duration_value, $package->duration_unit);
                
                $subscription->update([
                    'status' => 'active',
                    'started_at' => now(),
                    'expires_at' => $newExpiry,
                ]);

                activity_log(
                    action: 'telegram_package_purchased',
                    subject: $teacher,
                    properties: [
                        'package_id' => $package->id,
                        'price' => $order->total,
                        'payment_method' => $order->payment_method,
                        'new_expiry' => $newExpiry?->toDateTimeString()
                    ],
                    logName: 'teacher_telegram_management',
                );

                // Gửi thông báo qua Telegram nếu giảng viên có cấu hình
                if ($teacher->telegram_chat_id && $teacher->is_telegram_notifications_enabled) {
                    $packageName = $package->name;
                    $expiryDate = $newExpiry ? $newExpiry->format('H:i d/m/Y') : 'Không thời hạn';
                    
                    $text = "✅ <b>KÍCH HOẠT GÓI THÀNH CÔNG</b>\n\n";
                    $text .= "Chúc mừng <b>{$teacher->name}</b>,\n";
                    $text .= "Bạn đã kích hoạt thành công gói: <b>{$packageName}</b>\n";
                    $text .= "🗓️ <b>Hạn dùng:</b> {$expiryDate}\n\n";
                    $text .= "Hệ thống sẽ bắt đầu gửi các thông báo quan trọng cho bạn tại đây.";

                    \App\Jobs\SendTelegramTeacherNotification::dispatch($teacher, $text);
                }
            }
        });
    }

    /**
     * Tính toán ngày hết hạn dựa trên đơn vị
     */
    protected function calculateExpiry(Carbon $baseDate, int $value, string $unit): Carbon
    {
        $date = $baseDate->copy();
        switch ($unit) {
            case 'minute': return $date->addMinutes($value);
            case 'hour': return $date->addHours($value);
            case 'day': return $date->addDays($value);
            case 'month': return $date->addMonths($value);
            case 'year': return $date->addYears($value);
            default: return $date;
        }
    }

    /**
     * Cập nhật cấu hình Telegram (Chat ID, Bật/Tắt)
     */
    public function updateSettings(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $request->validate([
            'telegram_chat_id' => 'nullable|string',
            'is_telegram_notifications_enabled' => 'boolean'
        ]);

        $teacher->update([
            'telegram_chat_id' => $request->telegram_chat_id,
            'is_telegram_notifications_enabled' => $request->has('is_telegram_notifications_enabled')
        ]);

        return back()->with('msg_success', 'Đã cập nhật cấu hình Telegram.');
    }

    /**
     * Gửi tin nhắn test Telegram
     */
    public function testConnection(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher || !$teacher->telegram_chat_id) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng cấu hình Chat ID trước.'], 400);
        }

        $status = $teacher->getTelegramPackageStatus();
        if ($status['status'] !== 'active') {
            return response()->json(['status' => 'error', 'message' => 'Bạn cần sở hữu gói Telegram còn hạn để sử dụng tính năng này.'], 403);
        }

        $message = "🔔 <b>KẾT NỐI THÀNH CÔNG</b>\n\n";
        $message .= "Chào <b>{$teacher->name}</b>,\n";
        $message .= "Đây là tin nhắn thử nghiệm từ hệ thống. Bạn sẽ nhận được thông báo về đơn hàng và các hoạt động khác tại đây.";

        try {
            \App\Jobs\SendTelegramTeacherNotification::dispatch($teacher, $message);
            return response()->json(['status' => 'success', 'message' => 'Tin nhắn thử nghiệm đã được gửi đi!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Không thể gửi tin nhắn: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hiển thị trang nhận quà Telegram
     */
    public function showClaim($locale, $token)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) return redirect()->route('clients-login');

        $subscription = TeacherTelegramSubscription::where('claim_token', $token)
            ->where('status', 'pending_claim')
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $pageTitle = 'Nhận quà tặng Telegram';
        $package = $subscription->package;

        return view('teacher::teacher.telegram.claim', compact('pageTitle', 'subscription', 'package', 'teacher'));
    }

    /**
     * Xử lý kích hoạt quà tặng
     */
    public function claim($locale, $token)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) return response()->json(['message' => 'Unauthorized'], 403);

        $subscription = TeacherTelegramSubscription::where('claim_token', $token)
            ->where('status', 'pending_claim')
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $package = $subscription->package;
            $newExpiry = $teacher->addTelegramDuration($package->duration_value, $package->duration_unit);

            $subscription->update([
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => $newExpiry,
                'claimed_at' => now(),
                'claim_token' => null, // One-time use
            ]);

            activity_log(
                action: 'telegram_gift_claimed',
                subject: $teacher,
                properties: [
                    'package_id' => $package->id,
                    'subscription_id' => $subscription->id,
                    'expires_at' => $newExpiry->toDateTimeString()
                ],
                logName: 'teacher_telegram_management',
                description: "Giáo viên đã nhận quà tặng gói Telegram: {$package->name}"
            );

            DB::commit();

            // Notify via Telegram if teacher has feature (now they definitely have it since it's activated)
            if ($teacher->hasTelegramFeature()) {
                $msg = "✅ <b>KÍCH HOẠT QUÀ TẶNG THÀNH CÔNG!</b>\n\n";
                $msg .= "Bạn đã nhận quà tặng gói: <b>" . ($package->name_locale ?: $package->name) . "</b>\n";
                $msg .= "📅 <b>Hạn dùng mới:</b> " . $newExpiry->format('d/m/Y');
                
                dispatch(new \App\Jobs\SendTelegramTeacherNotification($teacher, $msg));
            }

            return response()->json([
                'success' => true, 
                'message' => 'Kích hoạt gói quà tặng thành công!',
                'redirect' => route('teacher.dashboard.telegram.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}
