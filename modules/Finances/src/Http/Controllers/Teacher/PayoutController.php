<?php

namespace Modules\Finances\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Finances\src\Repositories\FinancesRepositoryInterface;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Packages\src\Support\PackageLifecycleManager;

class PayoutController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected FinancesRepositoryInterface $financesRepo,
        protected PackageLifecycleManager $packageLifecycleManager
    ) {}

    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $summary = $this->financesRepo->getPayoutSummary($teacher->id);
        $availableBalance = $summary['available'];
        $requestedAmount = $summary['requested'];
        $payoutAccounts = $this->financesRepo->getPayoutAccounts($teacher->id);
        $payoutAccountUsage = $this->financesRepo->getAccountUsage($teacher->id);

        $bankOptions = $this->getBankOptions();
        $pageTitle = __('finances::teacher/payouts.title');

        $exchangeRates = [
            'USD' => (float) \Modules\Settings\src\Models\Setting::getValue('currency_rate_usd') ?: 1,
            'KRW' => (float) \Modules\Settings\src\Models\Setting::getValue('currency_rate_krw') ?: 1,
            'JPY' => (float) \Modules\Settings\src\Models\Setting::getValue('currency_rate_jpy') ?: 1,
            'CNY' => (float) \Modules\Settings\src\Models\Setting::getValue('currency_rate_cny') ?: 1,
        ];

        $conversionFee = (float) \Modules\Settings\src\Models\Setting::getValue('currency_conversion_fee') ?: 0;
        $baseCurrency = 'VND'; // System base

        return view('finances::teacher.payouts.index', compact(
            'pageTitle',
            'teacher',
            'summary',
            'availableBalance',
            'requestedAmount',
            'payoutAccounts',
            'payoutAccountUsage',
            'bankOptions',
            'exchangeRates',
            'conversionFee',
            'baseCurrency'
        ));
    }

    public function accounts(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $summary = $this->financesRepo->getPayoutSummary($teacher->id);
        $availableBalance = $summary['available'];
        $requestedAmount = $summary['requested'];
        $payoutAccounts = $this->financesRepo->getPayoutAccounts($teacher->id);
        $payoutAccountUsage = $this->financesRepo->getAccountUsage($teacher->id);
        $pendingAccountChangeRequests = $this->financesRepo->getPendingAccountChangeRequests($teacher->id);
        
        $bankOptions = $this->getBankOptions();
        $pageTitle = __('finances::teacher/payouts.tabs.bank_accounts');

        $exchangeRates = \Illuminate\Support\Facades\Cache::get('exchange_rates', []);
        $conversionFee = (float) \Modules\Settings\src\Models\Setting::getValue('currency_conversion_fee') ?: 0;

        return view('finances::teacher.payouts.accounts', compact(
            'pageTitle',
            'teacher',
            'summary',
            'availableBalance',
            'requestedAmount',
            'payoutAccounts',
            'payoutAccountUsage',
            'pendingAccountChangeRequests',
            'bankOptions',
            'exchangeRates',
            'conversionFee'
        ));
    }

    public function history(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $summary = $this->financesRepo->getPayoutSummary($teacher->id);
        $availableBalance = $summary['available'];
        $requestedAmount = $summary['requested'];
        $payouts = $this->financesRepo->getPayoutHistory($teacher->id);
        $pageTitle = __('finances::teacher/payouts.tabs.history');

        $exchangeRates = \Illuminate\Support\Facades\Cache::get('exchange_rates', []);
        $conversionFee = (float) \Modules\Settings\src\Models\Setting::getValue('currency_conversion_fee') ?: 0;

        return view('finances::teacher.payouts.history', compact(
            'pageTitle',
            'teacher',
            'summary',
            'availableBalance',
            'requestedAmount',
            'payouts',
            'exchangeRates',
            'conversionFee'
        ));
    }

    public function storePayout(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        // Chuyển đổi số tiền ngược về VND nếu đang dùng locale ngoại tệ
        $currencyService = app(\Modules\Courses\src\Support\CurrencyService::class);
        $locale = app()->getLocale();
        $targetCode = $currencyService->getLocaleCurrency($locale);

        $amountVnd = (float) $request->input('amount');
        if ($targetCode !== 'VND') {
            $amountLocale = (float) $request->input('amount');
            // Quy đổi ngược từ ngoại tệ về VND (không tính phí vì amount này là số tiền danh nghĩa muốn trừ từ balance)
            $amountVnd = $currencyService->convert($amountLocale, $targetCode, 'VND', false);
            $request->merge(['amount' => $amountVnd]);
        }

        $minPayoutAmount = (float) \Modules\Settings\src\Models\Setting::getValue('min_payout_amount') ?: 5000;
        if ($amountVnd < $minPayoutAmount) {
            $minAmountInLocale = $currencyService->convert($minPayoutAmount, 'VND', $targetCode, false);
            $minAmountFormatted = $targetCode === 'VND' 
                ? number_format($minAmountInLocale, 0, ',', '.') . ' đ'
                : $currencyService->getCurrencySymbol($targetCode) . number_format($minAmountInLocale, 2, '.', ',');
            return back()->with('msg_danger', __('finances::teacher/payouts.form.amount_min_hint', ['min' => $minAmountFormatted]));
        }

        $teacher->loadMissing('application.package');
        $package = $teacher->application?->package;

        if ($package && !$package->hasFeature('can_request_payouts')) {
            return back()->with('msg_danger', __('finances::teacher/payouts.flash.feature_locked'));
        }
        
        if ($package && $package->max_payout_per_day !== null) {
            $dailyLimit = (float) $package->max_payout_per_day;
            
            $payoutSum24h = \Modules\Finances\src\Models\PayoutRequest::where('teacher_id', $teacher->id)
                ->whereIn('status', ['requested', 'processing', 'paid'])
                ->where('created_at', '>=', now()->subDay())
                ->sum('amount');
                
            if (($payoutSum24h + $amountVnd) > $dailyLimit) {
                $limitInLocale = $currencyService->convert($dailyLimit, 'VND', $targetCode, false);
                $limitFormatted = $targetCode === 'VND' 
                    ? number_format($limitInLocale, 0, ',', '.') . ' đ'
                    : $currencyService->getCurrencySymbol($targetCode) . number_format($limitInLocale, 2, '.', ',');
                return back()->with('msg_danger', __('finances::teacher/payouts.flash.max_payout_exceeded', ['limit' => $limitFormatted]));
            }
        }

        $request->validate([
            'amount' => 'required|numeric|min:5000',
            'payout_account_id' => 'required',
            'note' => 'nullable|string|max:500',
            'bank_name' => 'required_if:payout_account_id,new',
            'bank_account_name' => 'required_if:payout_account_id,new',
            'bank_account_number' => 'required_if:payout_account_id,new',
        ]);

        $originalAmount = (float) $request->input('amount');
        $exchangeRate = 1.0;
        $feePercentage = 0.0;

        if ($targetCode !== 'VND') {
            $exchangeRate = (float) $currencyService->getRate($targetCode) ?: 1.0;
            $feePercentage = (float) \Modules\Settings\src\Models\Setting::getValue('currency_conversion_fee') ?: 0.0;
        }

        $feeAmountVnd = ($amountVnd * $feePercentage) / 100;

        $request->merge([
            'currency_code' => $targetCode,
            'exchange_rate' => $exchangeRate,
            'original_amount' => $originalAmount,
            'converted_amount_vnd' => $amountVnd,
            'fee_percentage' => $feePercentage,
            'fee_amount_vnd' => $feeAmountVnd,
        ]);

        $success = $this->financesRepo->createPayoutRequest($teacher->id, $request->all());

        if (!$success) {
            return back()->with('msg_danger', __('finances::teacher/payouts.flash.amount_exceeds_balance'));
        }

        // Notify Admins
        $payoutRequest = \Modules\Finances\src\Models\PayoutRequest::where('teacher_id', $teacher->id)->latest()->first();
        if ($payoutRequest) {
            $admins = \Modules\User\src\Models\User::adminPanelUsers()->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\AdminFinanceAlertNotification($payoutRequest, 'payout_request'));
        }

        return redirect()->route('teacher.dashboard.payouts.index')->with('msg_success', __('finances::teacher/payouts.flash.request_sent'));
    }

    public function storePayoutAccount(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $request->validate([
            'bank_name' => 'required|string',
            'bank_account_name' => 'required|string',
            'bank_account_number' => 'required|string',
        ]);

        $success = $this->financesRepo->createPayoutAccount($teacher->id, $request->all());

        if (!$success) {
            return back()->with('msg_danger', __('finances::teacher/payouts.flash.limit_reached_use_change_request', ['limit' => 3]));
        }

        return back()->with('msg_success', __('finances::teacher/payouts.flash.account_saved_success'));
    }

    public function storePayoutAccountChangeRequest(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $request->validate([
            'replace_payout_account_id' => 'required|exists:teacher_payout_accounts,id',
            'bank_name' => 'required|string',
            'bank_account_name' => 'required|string',
            'bank_account_number' => 'required|string',
        ]);

        $changeRequest = $this->financesRepo->createAccountChangeRequest($teacher->id, $request->all());

        if ($changeRequest) {
            $admins = \Modules\User\src\Models\User::adminPanelUsers()->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\AdminFinanceAlertNotification($changeRequest, 'account_change'));
        }

        return back()->with('msg_success', __('finances::teacher/payouts.flash.change_request_sent'));
    }

    protected function getBankOptions()
    {
        return $this->resolveVietnamBankOptions();
    }
}
