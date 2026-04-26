<?php

namespace Modules\Settings\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\SystemTestMail;
use App\Support\SystemMailManager;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Home\src\Support\GeminiHealthService;
use Modules\Settings\src\Http\Requests\SettingRequest;
use Modules\Settings\src\Models\Setting;

class SettingController extends Controller
{
    public function __construct(
        protected SystemMailManager $systemMailManager,
        protected GeminiHealthService $geminiHealthService,
    ) {}

    public function index()
    {
        $pageTitle = 'Cấu hình website';
        $pageName = 'Cấu hình website';
        $settings = Setting::pluck('value', 'key')->toArray();
        $currentUser = auth()->user();
        $canUpdateGeneralSettings = $currentUser?->hasPermission('settings.update') ?? false;
        $mailConfigured = $this->systemMailManager->isConfigured();
        $geminiHealth = $this->geminiHealthService->snapshot();

        return view('settings::index', compact(
            'settings',
            'pageName',
            'pageTitle',
            'canUpdateGeneralSettings',
            'mailConfigured',
            'geminiHealth'
        ));
    }

    public function update(SettingRequest $request)
    {
        $currentUser = auth()->user();
        $canUpdateGeneralSettings = $currentUser?->hasPermission('settings.update') ?? false;
        if (!$canUpdateGeneralSettings) {
            abort(403);
        }

        $textInputs = array_merge(
            $request->except('_token', 'banner_slider', 'banner_right', 'banner_full', 'logo'),
            [
                'global_notice_enabled' => $request->boolean('global_notice_enabled') ? '1' : '0',
                'popup_notice_enabled' => $request->boolean('popup_notice_enabled') ? '1' : '0',
                'mail_enabled' => $request->boolean('mail_enabled') ? '1' : '0',
                'chatbot_widget_enabled' => $request->boolean('chatbot_widget_enabled') ? '1' : '0',
                'chatbot_enabled' => $request->boolean('chatbot_enabled') ? '1' : '0',
                'popup_notice_snooze_minutes' => (string) ($request->input('popup_notice_snooze_minutes') ?: '60'),
                'checkout_countdown_minutes' => (string) ($request->input('checkout_countdown_minutes') ?: config('checkout.checkout_countdown', '0')),
                'max_devices' => (string) ($request->input('max_devices') ?: config('auth.max_devices', '1')),
                'chatbot_message_ttl_minutes' => (string) ($request->input('chatbot_message_ttl_minutes') ?: env('CHATBOT_MESSAGE_TTL_MINUTES', '10')),
                'student_two_factor_timeout' => (string) ($request->input('student_two_factor_timeout') ?: config('auth.student_two_factor_timeout', '600')),
                'student_two_factor_code_expire' => (string) ($request->input('student_two_factor_code_expire') ?: config('auth.student_two_factor_code_expire', '600')),
                'student_two_factor_resend_cooldown' => (string) ($request->input('student_two_factor_resend_cooldown') ?: config('auth.student_two_factor_resend_cooldown', '60')),
                'payment_bank_enabled' => $request->boolean('payment_bank_enabled') ? '1' : '0',
                'payment_momo_enabled' => $request->boolean('payment_momo_enabled') ? '1' : '0',
                'payment_vnpay_enabled' => $request->boolean('payment_vnpay_enabled') ? '1' : '0',
            ]
        );

        if (($textInputs['chatbot_widget_enabled'] ?? '0') !== '1') {
            $textInputs['chatbot_enabled'] = '0';
        }

        $generalSettingKeys = [
            'site_name',
            'email',
            'phone',
            'address',
            'facebook',
            'instagram',
            'youtube',
            'tiktok',
            'currency_rate_usd',
            'currency_rate_krw',
            'currency_rate_jpy',
            'currency_rate_cny',
            'currency_conversion_fee',
            'mail_enabled',
            'chatbot_widget_enabled',
            'checkout_countdown_minutes',
            'max_devices',
            'chatbot_enabled',
            'chatbot_message_ttl_minutes',
            'student_two_factor_timeout',
            'student_two_factor_code_expire',
            'student_two_factor_resend_cooldown',
            'global_notice_enabled',
            'global_notice_title',
            'global_notice_title_en',
            'global_notice_title_ko',
            'global_notice_title_ja',
            'global_notice_title_zh',
            'global_notice_content',
            'global_notice_content_en',
            'global_notice_content_ko',
            'global_notice_content_ja',
            'global_notice_content_zh',
            'global_notice_link_label',
            'global_notice_link_label_en',
            'global_notice_link_label_ko',
            'global_notice_link_label_ja',
            'global_notice_link_label_zh',
            'global_notice_link_url',
            'global_notice_link_url_en',
            'global_notice_link_url_ko',
            'global_notice_link_url_ja',
            'global_notice_link_url_zh',
            'popup_notice_enabled',
            'popup_notice_title',
            'popup_notice_title_en',
            'popup_notice_title_ko',
            'popup_notice_title_ja',
            'popup_notice_title_zh',
            'popup_notice_content',
            'popup_notice_content_en',
            'popup_notice_content_ko',
            'popup_notice_content_ja',
            'popup_notice_content_zh',
            'popup_notice_link_label',
            'popup_notice_link_label_en',
            'popup_notice_link_label_ko',
            'popup_notice_link_label_ja',
            'popup_notice_link_label_zh',
            'popup_notice_link_url',
            'popup_notice_link_url_en',
            'popup_notice_link_url_ko',
            'popup_notice_link_url_ja',
            'popup_notice_link_url_zh',
            'popup_notice_snooze_minutes',
            'ai_quiz_enabled',
            'payment_bank_enabled',
            'payment_momo_enabled',
            'payment_vnpay_enabled',
            'bank_transfer_bank_bin',
            'bank_transfer_bank_name',
            'bank_transfer_account_number',
            'bank_transfer_account_name',
            'bank_transfer_note_prefix',
        ];

        $allowedSettingKeys = [];

        if ($canUpdateGeneralSettings) {
            $allowedSettingKeys = array_merge($allowedSettingKeys, $generalSettingKeys);
        }

        $textInputs = Arr::only($textInputs, array_values(array_unique($allowedSettingKeys)));

        $mailConfigKeys = $this->systemMailManager->configKeys();
        $mailKeysToDelete = $canUpdateGeneralSettings ? $mailConfigKeys : [];
        $sensitiveKeys = [];

        $legacyRemovedSettingKeys = [
            'payment_vnpay_url',
            'payment_vnpay_tmn_code',
            'payment_vnpay_hash_secret',
            'payment_vnpay_return_url',
            'payment_vnpay_bank_code',
            'payment_vnpay_version',
            'payment_vnpay_command',
            'payment_vnpay_curr_code',
            'payment_vnpay_order_type',
            'payment_momo_endpoint',
            'payment_momo_partner_code',
            'payment_momo_access_key',
            'payment_momo_secret_key',
            'payment_momo_request_type',
            'captcha_enabled',
            'captcha_site_key',
            'captcha_secret_key',
            'captcha_verify_url',
        ];

        $keysToDelete = array_merge($mailKeysToDelete, $legacyRemovedSettingKeys);
        $keysToDelete = array_values(array_unique($keysToDelete));
        $trackedKeys = array_values(array_unique(array_merge(array_keys($textInputs), $keysToDelete)));

        $old = Setting::whereIn('key', $trackedKeys)
            ->pluck('value', 'key')
            ->toArray();

        foreach ($textInputs as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        \Illuminate\Support\Facades\Cache::forget('currency_rates_base_vnd');

        if (!empty($keysToDelete)) {
            Setting::whereIn('key', $keysToDelete)->delete();
        }

        if ($canUpdateGeneralSettings && $request->hasFile('banner_slider')) {
            $paths = [];

            foreach ($request->file('banner_slider') as $file) {
                $paths[] = $file->store('banners/slider', 'public');
            }

            Setting::updateOrCreate(['key' => 'banner_slider'], ['value' => json_encode($paths)]);
        }

        if ($canUpdateGeneralSettings && $request->hasFile('banner_right')) {
            $paths = [];

            foreach ($request->file('banner_right') as $file) {
                $paths[] = $file->store('banners/right', 'public');
            }

            Setting::updateOrCreate(['key' => 'banner_right'], ['value' => json_encode($paths)]);
        }

        if ($canUpdateGeneralSettings && $request->hasFile('banner_full')) {
            $path = $request->file('banner_full')->store('banners/full', 'public');
            Setting::updateOrCreate(['key' => 'banner_full'], ['value' => $path]);
        }

        if ($canUpdateGeneralSettings && $request->hasFile('logo')) {
            $path = $request->file('logo')->store('banners/logo', 'public');
            Setting::updateOrCreate(['key' => 'logo'], ['value' => $path]);
        }

        $allKeys = array_unique(array_merge($trackedKeys, ['banner_slider', 'banner_right', 'banner_full', 'logo']));
        $newDb = Setting::whereIn('key', $allKeys)->pluck('value', 'key')->toArray();

        $changed = [];
        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $newDb[$key] ?? null;

            if ((string) $oldVal !== (string) $newVal) {
                $changed[] = $key;
            }
        }

        if (!empty($changed)) {
            activity_log(
                action: 'update_settings',
                subject: null,
                properties: [
                    'changed_keys' => $this->formatSettingLogKeys($changed),
                    'old' => $this->formatSettingLogValues(Arr::only($old, $changed), $sensitiveKeys),
                    'new' => $this->formatSettingLogValues(Arr::only($newDb, $changed), $sensitiveKeys),
                ],
                logName: 'Cập nhập',
                description: 'Cập nhập cấu hình'
            );
        }

        return back()->with('msg', 'Cập nhập cấu hình thành công')->with('msgType', 'success');
    }

    public function syncExchangeRates(\Modules\Courses\src\Support\CurrencyService $currencyService)
    {
        abort_unless(auth()->user()?->hasPermission('settings.update'), 403);

        $success = $currencyService->updateRates();

        if ($success) {
            return back()->with('msg', 'Cập nhật tỷ giá quốc tế thành công')->with('msgType', 'success');
        }

        return back()->with('msg', 'Cập nhật tỷ giá thất bại. Vui lòng thử lại sau hoặc kiểm tra Log hệ thống.')->with('msgType', 'danger');
    }

    public function testMail(Request $request)
    {
        abort_unless(auth()->user()?->hasPermission('settings.update'), 403);

        $user = auth()->user();
        $settings = $this->systemMailManager->settings();

        if (!$this->systemMailManager->isEnabled($settings)) {
            return back()->with('msg', 'Mail đang tắt. Hãy bật mail trước khi test')->with('msgType', 'danger');
        }

        if (!$this->systemMailManager->isConfigured($settings)) {
            return back()->with('msg', 'Mail chưa đủ cấu hình!')->with('msgType', 'danger');
        }

        if (!$user || blank($user->email)) {
            return back()->with('msg', 'Tài khoản hiện tại chauw có email để nhận mail test')->with('msgType', 'danger');
        }

        try {
            $this->systemMailManager->apply($settings);

            Mail::to($user->email)->queue(new SystemTestMail(
                $user->name ?: 'Admin',
                now()->format('Y-m-d H:i:s')
            ));
        } catch (\Throwable $exception) {
            return back()
                ->with('msg', 'Gửi email test thất bại: ' . $exception->getMessage())
                ->with('msgType', 'danger');
        }

        return back()
            ->with('msg', 'Đã gửi email tới ' . $user->email)
            ->with('msgType', 'success');
    }

    public function logs(Request $request)
    {
        $pageTitle = 'Lịch sử cấu hình website';

        $query = ActiveLog::query()
            ->where('action', 'update_settings')
            ->withoutGlobalScopes();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%");
            });
        }

        $logs = $query->latest()->paginate(config('paginate.log_limit'))->withQueryString();

        return view('settings::logs', compact('pageTitle', 'logs'));
    }

    private function maskSensitiveSettings(array $settings, array $sensitiveKeys): array
    {
        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $settings) && filled($settings[$key])) {
                $settings[$key] = '***hidden***';
            }
        }

        return $settings;
    }

    private function formatSettingLogKeys(array $keys): array
    {
        return array_values(array_unique(array_map(
            fn ($key) => $this->settingLogLabel($key),
            $keys
        )));
    }

    private function formatSettingLogValues(array $settings, array $sensitiveKeys): array
    {
        $settings = $this->maskSensitiveSettings($settings, $sensitiveKeys);
        $formatted = [];

        foreach ($settings as $key => $value) {
            $formatted[$this->settingLogLabel($key)] = $value;
        }

        return $formatted;
    }

    private function settingLogLabel(string $key): string
    {
        $exactLabels = [
            'site_name' => 'Ten website',
            'email' => 'Email website',
            'phone' => 'So dien thoai',
            'address' => 'Dia chi',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'youtube' => 'Youtube',
            'tiktok' => 'TikTok',
            'mail_enabled' => 'Bat gui mail',
            'chatbot_widget_enabled' => 'Bat chatbot thuong',
            'checkout_countdown_minutes' => 'So phut giu don checkout',
            'chatbot_enabled' => 'Bat chatbot Gemini',
            'chatbot_message_ttl_minutes' => 'Thoi gian nho hoi thoai chatbot',
            'student_two_factor_timeout' => 'Thoi gian xac thuc lai 2FA',
            'student_two_factor_code_expire' => 'Thoi gian het han ma 2FA',
            'student_two_factor_resend_cooldown' => 'Thoi gian cho gui lai ma 2FA',
            'mail_host' => 'SMTP host',
            'mail_port' => 'SMTP port',
            'mail_encryption' => 'Mail encryption',
            'mail_username' => 'Mail username',
            'mail_password' => 'Mail password',
            'mail_from_address' => 'Mail from email',
            'mail_from_name' => 'Mail from name',
            'banner_slider' => 'Banner slider',
            'banner_right' => 'Banner ben phai',
            'banner_full' => 'Banner full',
            'logo' => 'Logo',
            'payment_bank_enabled' => 'Bật chuyển khoản ngân hàng',
            'payment_momo_enabled' => 'Bật thanh toán Momo',
            'payment_vnpay_enabled' => 'Bật thanh toán VNPAY',
            'bank_transfer_bank_bin' => 'Mã BIN ngân hàng',
            'bank_transfer_bank_name' => 'Tên ngân hàng',
            'bank_transfer_account_number' => 'Số tài khoản ngân hàng',
            'bank_transfer_account_name' => 'Tên chủ tài khoản',
            'bank_transfer_note_prefix' => 'Tiền tố nội dung chuyển khoản',
        ];

        if (isset($exactLabels[$key])) {
            return $exactLabels[$key];
        }

        if (str_starts_with($key, 'global_notice_')) {
            return 'Thông báo toàn website';
        }

        if (str_starts_with($key, 'popup_notice_')) {
            return 'Popup thông báo';
        }

        if (str_starts_with($key, 'currency_rate_')) {
            return 'Tỷ giá tiền tệ';
        }

        return ucwords(str_replace('_', ' ', $key));
    }
}

