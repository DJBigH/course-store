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

        if ($targetCode !== 'VND') {
            $amountLocale = (float) $request->input('amount');
            // Quy đổi ngược từ ngoại tệ về VND (không tính phí vì amount này là số tiền danh nghĩa muốn trừ từ balance)
            $amountVnd = $currencyService->convert($amountLocale, $targetCode, 'VND', false);
            $request->merge(['amount' => $amountVnd]);
        }

        $request->validate([
            'amount' => 'required|numeric|min:5000',
            'payout_account_id' => 'required',
            'note' => 'nullable|string|max:500',
            'bank_name' => 'required_if:payout_account_id,new',
            'bank_account_name' => 'required_if:payout_account_id,new',
            'bank_account_number' => 'required_if:payout_account_id,new',
        ]);

        $success = $this->financesRepo->createPayoutRequest($teacher->id, $request->all());

        if (!$success) {
            return back()->with('msg_danger', __('finances::teacher/payouts.flash.amount_exceeds_balance'));
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

        $this->financesRepo->createAccountChangeRequest($teacher->id, $request->all());

        return back()->with('msg_success', __('finances::teacher/payouts.flash.change_request_sent'));
    }

    protected function getBankOptions()
    {
        return $this->resolveVietnamBankOptions();
    }
}
