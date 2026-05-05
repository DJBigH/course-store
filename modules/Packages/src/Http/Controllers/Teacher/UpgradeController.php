<?php

namespace Modules\Packages\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Packages\src\Models\Package;
use Modules\Packages\src\Models\PackageFeature;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Models\OrderStatus;

class UpgradeController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected PackageLifecycleManager $packageLifecycleManager,
        protected TeacherNotificationCenter $notificationCenter,
        protected PackageUsageResolver $packageUsageResolver,
    ) {}

    public function upgradePackage()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        if ($pendingUpgrade) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $currentPackage = $teacher->application?->package;
        $upgradePackages = $this->resolveAvailablePackageChanges($currentPackage);
        if ($upgradePackages->isEmpty()) {
            return redirect()->route('teacher.dashboard.index')
                ->with('msg_danger', __('packages::teacher.flash.no_upgrade_available'));
        }

        $pageTitle = __('packages::teacher.upgrade.upgrade_title');
        $pageName = $pageTitle;

        $features = PackageFeature::query()
            ->whereIn('is_enabled', [
                PackageFeature::STATUS_ACTIVE,
                PackageFeature::STATUS_MAINTENANCE_VISIBLE
            ])
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get();

        $selectedPackageId = (int) request('package_id', $upgradePackages->first()?->id);

        return view('packages::teacher.upgrade', compact('pageTitle', 'pageName', 'teacher', 'currentPackage', 'upgradePackages', 'features', 'selectedPackageId'));
    }

    public function storeUpgradePackage(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        if ($pendingUpgrade) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $data = $request->validate([
            'package_id' => ['required', 'integer'],
            'payment_method' => ['nullable', 'string', 'in:bank_transfer,vnpay,momo'],
        ]);

        $currentPackage = $teacher->application?->package;
        $targetPackage = Package::query()->findOrFail($data['package_id']);

        if (!$this->canChangePackage($currentPackage, $targetPackage)) {
            return back()->with('msg_danger', __('packages::teacher.flash.invalid_upgrade'));
        }

        // Nếu là gói có phí thì bắt buộc chọn phương thức thanh toán
        if ($targetPackage->price > 0 && empty($data['payment_method'])) {
            return back()->withErrors(['payment_method' => __('packages::teacher.form.package.payment_required')]);
        }

        $changeRequest = DB::transaction(function () use ($teacher, $targetPackage, $data) {
            $isFree = $targetPackage->price <= 0;
            
            $application = TeacherApplication::query()->create([
                'teacher_id' => $teacher->id,
                'student_id' => $teacher->student_id,
                'full_name' => $teacher->name,
                'display_name' => $teacher->name,
                'email' => $teacher->student?->email,
                'phone' => $teacher->student?->phone,
                'package_id' => $targetPackage->id,
                'payment_method' => $isFree ? 'free' : $data['payment_method'],
                'status' => $isFree ? 'approved' : 'pending_payment',
                'type' => 'upgrade',
                'submitted_at' => now(),
                'reviewed_at' => $isFree ? now() : null,
                'reviewed_by' => $isFree ? null : null, // System auto-approved
                'note' => __('packages::teacher.upgrade.request_note'),
            ]);

            // Create Order for the upgrade
            Order::query()->create([
                'code' => 'UPG_' . generateUniqueCouponCode(),
                'student_id' => $teacher->student_id,
                'total' => $targetPackage->price,
                'status_id' => $isFree ? 2 : 1, // 2 = Success, 1 = Pending
                'orderable_id' => $application->id,
                'orderable_type' => TeacherApplication::class,
                'type' => 'teacher_upgrade',
                'payment_method' => $application->payment_method,
                'payment_date' => $isFree ? now() : null,
                'payment_complete_date' => $isFree ? now() : null,
            ]);

            if ($isFree) {
                $packageAction = $this->packageLifecycleManager->applyApprovedChange($teacher, $application->fresh(['package']));
                
                activity_log(
                    action: 'teacher_package_upgraded_auto',
                    subject: $teacher,
                    properties: [
                        'application_id' => $application->id,
                        'package' => $targetPackage->name,
                        'action' => $packageAction,
                        'is_free' => true
                    ],
                    logName: __('teacher::admin.logs.approve_title'),
                    description: __('teacher::admin.logs.approve_upgrade_desc')
                );
            }

            return $application;
        });

        if ($targetPackage->price <= 0) {
            $this->activateUpgrade($changeRequest);
            return redirect()->route('teacher.dashboard.package.upgrade.result', ['status' => 'success']);
        }

        if ($data['payment_method'] === 'vnpay') {
            return $this->handleVnpayPayment($changeRequest);
        }

        if ($data['payment_method'] === 'momo') {
            return $this->handleMomoPayment($changeRequest);
        }

        return redirect()->route('teacher.dashboard.package.upgrade.status');
    }

    private function handleVnpayPayment(TeacherApplication $application)
    {
        $config = config('services.vnpay');
        $amount = (int) $application->package->price;

        $inputData = [
            'vnp_Version' => $config['version'],
            'vnp_TmnCode' => $config['tmn_code'],
            'vnp_Amount' => $amount * 100,
            'vnp_Command' => $config['command'],
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode' => $config['curr_code'],
            'vnp_IpAddr' => request()->ip(),
            'vnp_Locale' => app()->getLocale() === 'vi' ? 'vn' : 'en',
            'vnp_OrderInfo' => 'Nang cap goi giao vien: ' . $application->package->name,
            'vnp_OrderType' => $config['order_type'],
            'vnp_ReturnUrl' => route('teacher.dashboard.package.upgrade.vnpay-return'),
            'vnp_TxnRef' => $application->orders()->latest()->value('code'),
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
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $config['url'] . "?" . $query;
        if (isset($config['hash_secret'])) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $config['hash_secret']);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return redirect()->away($vnp_Url);
    }

    private function handleMomoPayment(TeacherApplication $application)
    {
        $config = config('services.momo');
        $amount = (int) $application->package->price;
        $requestId = (string) Str::uuid();
        $orderCode = $application->orders()->latest()->value('code');

        $payload = [
            'partnerCode' => $config['partner_code'],
            'accessKey' => $config['access_key'],
            'requestId' => $requestId,
            'amount' => (string) $amount,
            'orderId' => $orderCode,
            'orderInfo' => 'Nang cap goi giao vien: ' . $application->package->name,
            'redirectUrl' => route('teacher.dashboard.package.upgrade.momo-return'),
            'ipnUrl' => route('teacher.dashboard.package.upgrade.momo-ipn'),
            'extraData' => base64_encode(json_encode(['order_code' => $orderCode])),
            'requestType' => $config['request_type'],
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

        try {
            $response = Http::timeout(20)->post($config['endpoint'], $payload)->json();
            if (!empty($response['payUrl'])) {
                return redirect()->away($response['payUrl']);
            }
            Log::error('MoMo Upgrade Error', ['response' => $response]);
            return back()->with('msg_danger', 'Khong the khoi tao thanh toan MoMo. ' . ($response['message'] ?? ''));
        } catch (\Exception $e) {
            Log::error('MoMo Upgrade Exception', ['message' => $e->getMessage()]);
            return back()->with('msg_danger', 'Loi ket noi cong thanh toan MoMo.');
        }
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = $request->except(['vnp_SecureHash', 'vnp_SecureHashType']);
        ksort($inputData);
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashdata, config('services.vnpay.hash_secret'));

        if ($secureHash == $vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                $orderCode = $request->vnp_TxnRef;
                $order = Order::where('code', $orderCode)->first();

                if ($order && $order->status_id !== 2) {
                    $order->update(['status_id' => 2]);
                    return redirect()->route('teacher.dashboard.package.upgrade.result', ['status' => 'success']);
                }
            }
        }

        $status = ($request->vnp_ResponseCode == '24') ? 'cancelled' : 'failed';
        return redirect()->route('teacher.dashboard.package.upgrade.result', ['status' => $status]);
    }

    public function vnpayIpn(Request $request)
    {
        $inputData = $request->except(['vnp_SecureHash', 'vnp_SecureHashType']);
        ksort($inputData);
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashdata, config('services.vnpay.hash_secret'));

        if ($secureHash == $request->vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                $orderCode = $request->vnp_TxnRef;
                $order = Order::where('code', $orderCode)->first();

                if ($order && $order->status_id !== 2) {
                    $order->update(['status_id' => 2]);
                    return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
                }
            }
            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success (Already processed or failure recorded)']);
        }

        return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
    }

    public function momoReturn(Request $request)
    {
        if ($request->resultCode == 0) {
            $extraData = json_decode(base64_decode($request->extraData), true);
            $orderCode = $extraData['order_code'] ?? null;

            if ($orderCode) {
                $order = Order::where('code', $orderCode)->first();
                if ($order && $order->status_id !== 2) {
                    $order->update(['status_id' => 2]);
                    return redirect()->route('teacher.dashboard.package.upgrade.result', ['status' => 'success']);
                }
            }
        }

        $status = ($request->resultCode == 49) ? 'cancelled' : 'failed';
        return redirect()->route('teacher.dashboard.package.upgrade.result', ['status' => $status]);
    }

    public function momoIpn(Request $request)
    {
        if ($request->resultCode == 0) {
            $extraData = json_decode(base64_decode($request->extraData), true);
            $orderCode = $extraData['order_code'] ?? null;

            if ($orderCode) {
                $order = Order::where('code', $orderCode)->first();
                if ($order && $order->status_id !== 2) {
                    $order->update(['status_id' => 2]);
                }
            }
        }
        return response()->json(['resultCode' => 0, 'message' => 'Success']);
    }

    public function upgradeResult()
    {
        $status = request('status', 'failed');
        $pageTitle = __('packages::teacher.result.page_title');
        $pageName = $pageTitle;

        return view('packages::teacher.result', compact('pageTitle', 'pageName', 'status'));
    }

    public function repayPackage(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $application = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$application) {
            return redirect()->route('teacher.dashboard.package.upgrade')->with('msg_danger', __('packages::teacher.result.failed_desc'));
        }

        if ($application->payment_method === 'vnpay') {
            return $this->handleVnpayPayment($application);
        }

        if ($application->payment_method === 'momo') {
            return $this->handleMomoPayment($application);
        }

        return back()->with('msg_danger', __('packages::teacher.common.payment_under_maintenance', ['gateway' => strtoupper($application->payment_method)]));
    }

    private function activateUpgrade(TeacherApplication $application)
    {
        // This method is now effectively replaced by Order observer logic, 
        // but we'll keep it as a wrapper calling lifecycle manager if needed, 
        // or just let the observer handle it.
        // Actually, to ensure backward compatibility or manual triggers, let's call the centralized method.
        
        $lifecycleManager = app(\Modules\Packages\src\Support\PackageLifecycleManager::class);
        $lifecycleManager->activateTeacherUpgrade($application);
    }

    public function upgradePackageStatus()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.package.upgrade');
        }

        $pageTitle = __('packages::teacher.upgrade.status_title');
        $pageName = $pageTitle;

        $currentPackage = $teacher->application?->package;
        return view('packages::teacher.upgrade_status', compact('pageTitle', 'pageName', 'teacher', 'upgradeRequest', 'currentPackage'));
    }

    public function markUpgradePaid()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.index');
        }

        if (!in_array($upgradeRequest->status, ['pending_payment', 'pending_review'], true)) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $upgradeRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        $packageAction = $this->packageLifecycleManager->applyApprovedChange($teacher, $upgradeRequest->fresh(['package']));

        // Also update the associated order to Success
        $order = $upgradeRequest->orders()->latest()->first();
        if ($order && $order->status_id !== 2) {
            $order->update(['status_id' => 2]);
        }
        
        activity_log(
            action: 'teacher_package_upgraded_auto_test',
            subject: $teacher,
            properties: [
                'application_id' => $upgradeRequest->id,
                'package' => $upgradeRequest->package?->name,
                'action' => $packageAction,
                'is_test_auto' => true
            ],
            logName: __('teacher::admin.logs.approve_title'),
            description: __('teacher::admin.logs.approve_upgrade_desc')
        );

        return redirect()->route('teacher.dashboard.index')
            ->with('msg_success', __('packages::teacher.flash.auto_activated'));
    }

    public function cancelUpgradePackage()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest || $upgradeRequest->status !== 'pending_payment') {
            return redirect()->route('teacher.dashboard.index');
        }

        $upgradeRequest->update(['status' => 'cancelled']);
        
        // Also cancel the associated order
        $order = $upgradeRequest->orders()->latest()->first();
        if ($order && $order->status_id !== 4) {
            $order->update(['status_id' => 4]);
        }

        return redirect()->route('teacher.dashboard.index')
            ->with('msg_success', __('packages::teacher.flash.cancelled'));
    }
}
