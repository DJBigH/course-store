@extends('layouts.teacher')

@section('content')
    @php
        $formatBankAccountNumber = static function (?string $value): string {
            $digits = preg_replace('/\D+/', '', (string) $value);
            return $digits === '' ? (string) $value : trim(implode(' ', str_split($digits, 4)));
        };
    @endphp

    @php
        $maintPackage = $teacher->application?->package;
        $maintPayout = $maintPackage?->isFeatureInMaintenance('can_request_payouts') ?? false;
        
        $payoutSum24h = 0;
        if ($maintPackage && $maintPackage->max_payout_per_day !== null) {
            $payoutSum24h = \Modules\Finances\src\Models\PayoutRequest::where('teacher_id', $teacher->id)
                ->whereIn('status', ['requested', 'processing', 'paid'])
                ->where('created_at', '>=', now()->subDay())
                ->sum('amount');
        }
        
        $currencyService = app(\Modules\Courses\src\Support\CurrencyService::class);
        $currentLocale = app()->getLocale();
        $userCurrency = $currencyService->getLocaleCurrency($currentLocale);
        $minPayoutVnd = (float) \Modules\Settings\src\Models\Setting::getValue('min_payout_amount') ?: 5000;
        $minPayoutLocale = $currencyService->convert($minPayoutVnd, 'VND', $userCurrency, false);
        $minPayoutFormatted = $userCurrency === 'VND' 
            ? number_format($minPayoutLocale, 0, ',', '.') . ' đ'
            : $currencyService->getCurrencySymbol($userCurrency) . number_format($minPayoutLocale, 2, '.', ',');
    @endphp
    <div class="teacher-panel" id="payout-main-container">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('finances::teacher/payouts.title') }}</h3>
                <p class="text-muted mb-0">{{ __('finances::teacher/payouts.description') }}</p>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('finances::teacher/payouts.summary.teacher_revenue') }}</div>
                    <div class="teacher-stat-card__value">{{ moneyLocale($summary['teacher_revenue'], null, true) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('finances::teacher/payouts.summary.requested') }}</div>
                    <div class="teacher-stat-card__value">{{ moneyLocale($requestedAmount, null, true) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('finances::teacher/payouts.summary.available') }}</div>
                    <div class="teacher-stat-card__value">{{ moneyLocale($availableBalance, null, true) }}</div>
                </div>
            </div>
        </div>



        <!-- Sub-navigation Systems -->
        <div class="teacher-payout-tabs-container mb-4">
            <div class="teacher-payout-tabs" data-payout-tabs>
                <a href="{{ route('teacher.dashboard.payouts.index') }}" class="teacher-payout-tab active" data-payout-nav-link>
                    <i class="fa-solid fa-money-bill-transfer me-2"></i>{{ __('finances::teacher/payouts.tabs.withdraw') }}
                </a>
                <a href="{{ route('teacher.dashboard.payouts.accounts') }}" class="teacher-payout-tab" data-payout-nav-link>
                    <i class="fa-solid fa-building-columns me-2"></i>{{ __('finances::teacher/payouts.tabs.bank_accounts') }}
                </a>
                <a href="{{ route('teacher.dashboard.payouts.history') }}" class="teacher-payout-tab" data-payout-nav-link>
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>{{ __('finances::teacher/payouts.tabs.history') }}
                </a>
            </div>
        </div>

        <!-- Dynamic Content Area -->
        <div id="payout-content-area" class="position-relative">
            <!-- Main Content: Payout Request -->
            <div class="teacher-panel shadow-sm border-0 mb-5" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05) !important;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="h5 fw-bold mb-1">{{ __('finances::teacher/payouts.form.title') }}</h3>
                        <p class="text-muted mb-0 small">{{ __('finances::teacher/payouts.form.help') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('teacher.dashboard.payouts.store') }}" id="payout-submission-form">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold mb-2">{{ __('finances::teacher/payouts.form.select_account') }}</label>
                            @if ($payoutAccounts->isNotEmpty())
                                <div class="row g-3">
                                    @foreach ($payoutAccounts as $account)
                                        <div class="col-md-4">
                                            <div class="teacher-payout-account-option">
                                                <input type="radio" name="payout_account_id" value="{{ $account->id }}" id="acc-{{ $account->id }}" class="btn-check" {{ (string) old('payout_account_id') === (string) $account->id ? 'checked' : '' }}>
                                                <label class="teacher-payout-account-label h-100" for="acc-{{ $account->id }}">
                                                    <div class="fw-bold text-primary mb-1">{{ $account->bank_name }}</div>
                                                    <div class="small fw-semibold mb-1">{{ $account->bank_account_name }}</div>
                                                    <div class="small text-muted">{{ $formatBankAccountNumber($account->bank_account_number) }}</div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if ($payoutAccountUsage['can_create'] ?? true)
                                        <div class="col-md-4">
                                            <div class="teacher-payout-account-option">
                                                <input type="radio" name="payout_account_id" value="new" id="acc-new" class="btn-check" {{ old('payout_account_id') === 'new' ? 'checked' : '' }}>
                                                <label class="teacher-payout-account-label h-100 d-flex align-items-center justify-content-center" for="acc-new">
                                                    <div class="text-center">
                                                        <i class="fa-solid fa-plus-circle fs-4 mb-2"></i>
                                                        <div class="fw-semibold">{{ __('finances::teacher/payouts.form.use_new_account') }}</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <input type="hidden" name="account_mode" id="account-mode-hidden" value="{{ old('account_mode', 'saved') }}">
                            @else
                                <div class="alert alert-info py-3 shadow-none border-0" style="background: rgba(59, 130, 246, 0.1);">
                                    <i class="fa-solid fa-circle-info me-2"></i>{{ __('finances::teacher/payouts.form.no_accounts_hint') }}
                                </div>
                                <input type="hidden" name="account_mode" value="new">
                                <input type="hidden" name="payout_account_id" value="new">
                            @endif
                        </div>

                        <!-- New Account Fields -->
                        <div class="col-12 {{ ($payoutAccounts->isNotEmpty() && old('payout_account_id') !== 'new') ? 'd-none' : '' }}" id="new-account-fields">
                            <div class="p-3 rounded-3" style="background: rgba(148, 163, 184, 0.05); border: 1px dashed rgba(148, 163, 184, 0.2);">
                                <div class="fw-semibold mb-3 small text-muted text-uppercase">{{ __('finances::teacher/payouts.form.new_account_title') }}</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('finances::teacher/payouts.form.bank_name') }}</label>
                                        <input type="text" class="form-control mb-2" placeholder="{{ __('finances::teacher/payouts.form.search_bank') }}" data-bank-search="payout-create-bank-name">
                                        <select class="form-select" name="bank_name" id="payout-create-bank-name">
                                            <option value="">{{ __('finances::teacher/payouts.form.choose_bank') }}</option>
                                            @foreach ($bankOptions as $bankValue => $bankLabel)
                                                <option value="{{ $bankValue }}" @selected(old('bank_name') === $bankValue)>{{ $bankLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('finances::teacher/payouts.form.bank_account_name') }}</label>
                                        <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="VD: NGUYEN VAN A">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('finances::teacher/payouts.form.bank_account_number') }}</label>
                                        <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="{{ __('finances::teacher/payouts.form.bank_account_number') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="row g-4 align-items-start">
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold mb-2">{{ __('finances::teacher/payouts.form.amount_label', ['min' => $minPayoutFormatted]) }}</label>
                                    <input type="hidden" name="amount" id="teacher-payout-amount" value="{{ old('amount') }}">
                                    <div class="teacher-money-input-wrapper">
                                        <div class="teacher-money-input mb-2">
                                            <input
                                                type="text"
                                                inputmode="numeric"
                                                class="form-control form-control-lg"
                                                id="teacher-payout-amount-display"
                                                value="{{ old('amount') }}"
                                                data-money-input
                                                data-money-target="teacher-payout-amount"
                                                placeholder="{{ __('finances::teacher/payouts.form.amount_placeholder') }}"
                                                {{ (old('payout_account_id') || $payoutAccounts->isEmpty()) ? '' : 'disabled' }}>
                                            <span class="teacher-money-input__unit">{{ __('finances::teacher/payouts.common.currency_symbol') }}</span>
                                        </div>
                                        
                                        <!-- Real-time Conversion Preview -->
                                        <div id="payout-conversion-preview" class="p-3 rounded-3 mb-3 d-none" style="background: rgba(var(--admin-primary-rgb), 0.08); border: 1px solid rgba(var(--admin-primary-rgb), 0.2);">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="small text-muted">{{ __('finances::teacher/payouts.calculator.fee_label', ['fee' => $conversionFee]) }}</div>
                                                <div class="text-end fw-bold text-danger small" id="preview-fee-amount">- 0 ₫</div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="small text-muted">
                                                    <i class="fa-solid fa-calculator me-1"></i> {{ __('finances::teacher/payouts.calculator.expected_amount') }}
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold text-primary fs-5" id="preview-final-amount">0 ₫</div>
                                                    <div class="small text-muted" id="preview-conversion-info" style="font-size: 0.7rem; opacity: 0.8;"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-2 small text-muted" id="amount-validation-hint">
                                            @if(!old('payout_account_id') && $payoutAccounts->isNotEmpty())
                                                <span class="text-warning"><i class="fa-solid fa-lock me-1"></i>{{ __('finances::teacher/payouts.form.amount_lock_hint') }}</span>
                                            @else
                                                <span>{{ __('finances::teacher/payouts.form.amount_min_hint', ['min' => $minPayoutFormatted]) }}</span>
                                            @endif

                                            @if($maintPackage && $maintPackage->max_payout_per_day !== null)
                                                @php
                                                    $remainingLimitVnd = max(0, $maintPackage->max_payout_per_day - $payoutSum24h);
                                                    $remainingInLocale = $currencyService->convert($remainingLimitVnd, 'VND', $userCurrency, false);
                                                    $limitFormatted = $userCurrency === 'VND' 
                                                        ? number_format($remainingInLocale, 0, ',', '.') . ' đ'
                                                        : $currencyService->getCurrencySymbol($userCurrency) . number_format($remainingInLocale, 2, '.', ',');
                                                @endphp
                                                <div class="text-info mt-1" id="package-limit-hint" data-remaining-limit="{{ $remainingLimitVnd }}" data-target-code="{{ $userCurrency }}">
                                                    <i class="fa-solid fa-circle-info me-1"></i> Gói của bạn còn hạn mức rút {{ $limitFormatted }} trong hôm nay.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold mb-2">{{ __('finances::teacher/payouts.form.note') }}</label>
                                    <textarea class="form-control" name="note" rows="3" style="height: 80px;" placeholder="{{ __('finances::teacher/payouts.form.note_placeholder') }}">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm" id="payout-submit-btn" {{ (old('payout_account_id') || $payoutAccounts->isEmpty()) ? '' : 'disabled' }} @disabled($maintPayout)>
                            <i class="fa-solid fa-paper-plane me-2"></i>{{ __('finances::teacher/payouts.form.submit') }}
                            @if($maintPayout) ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }}) @endif
                        </button>
                    </div>
                </form>
            </div>

            <div class="divider mb-5" style="height: 1px; background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);"></div>

            <!-- Exchange Rates & Conversion Calculator -->
            <div class="row g-4 mb-4">
                <div class="col-lg-7">
                    <div class="teacher-payout-rates-card p-4 rounded-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded-3 bg-primary text-white me-3 shadow-sm">
                                <i class="fa-solid fa-chart-line fs-5"></i>
                            </div>
                            <div>
                                <h4 class="h6 fw-bold mb-1 text-primary">{{ __('finances::teacher/payouts.calculator.rates_title') }}</h4>
                                <p class="text-muted small mb-0">{{ __('finances::teacher/payouts.calculator.rates_subtitle') }}</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            @php
                                $displayCurrencies = ['USD', 'KRW', 'JPY', 'CNY'];
                                $hasRates = false;
                            @endphp
                            @foreach ($displayCurrencies as $currencyCode)
                                @if (isset($exchangeRates[$currencyCode]))
                                    @php $hasRates = true; @endphp
                                    <div class="col-sm-6">
                                        <div class="currency-rate-item d-flex align-items-center justify-content-between rounded-3 p-3 shadow-sm border">
                                            <div class="d-flex align-items-center">
                                                <span class="currency-flag-{{ strtolower($currencyCode) }} me-2"></span>
                                                <span class="fw-bold currency-label">1 {{ $currencyCode }}</span>
                                            </div>
                                            <div class="text-end">
                                                <span class="fw-bold text-primary">{{ number_format($exchangeRates[$currencyCode], 0, ',', '.') }} ₫</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if (!$hasRates)
                                <div class="col-12">
                                    <div class="alert alert-warning border-0 shadow-none mb-0">
                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                        {{ __('finances::teacher/payouts.calculator.no_rates') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="teacher-payout-calculator-card p-4 rounded-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded-3 bg-success text-white me-3 shadow-sm">
                                <i class="fa-solid fa-calculator fs-5"></i>
                            </div>
                            <div>
                                <h4 class="h6 fw-bold mb-1 text-success">{{ __('finances::teacher/payouts.calculator.calc_title') }}</h4>
                                <p class="text-muted small mb-0">{{ __('finances::teacher/payouts.calculator.calc_subtitle') }}</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">{{ __('finances::teacher/payouts.calculator.target_currency') }}</label>
                            <select class="form-select border-0 shadow-sm custom-payout-select" id="calc-target-currency">
                                <option value="VND" selected>{{ __('finances::teacher/payouts.calculator.default_vnd') }}</option>
                                <option value="USD">{{ __('finances::teacher/payouts.calculator.currencies.USD') }}</option>
                                <option value="JPY">{{ __('finances::teacher/payouts.calculator.currencies.JPY') }}</option>
                                <option value="KRW">{{ __('finances::teacher/payouts.calculator.currencies.KRW') }}</option>
                                <option value="CNY">{{ __('finances::teacher/payouts.calculator.currencies.CNY') }}</option>
                            </select>
                        </div>

                        <div class="teacher-calc-result-box p-3 rounded-3 shadow-sm">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">{{ __('finances::teacher/payouts.calculator.fee_label', ['fee' => $conversionFee]) }}</span>
                                <span class="fw-bold text-danger" id="calc-fee-display">- 0 ₫</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="text-muted small">{{ __('finances::teacher/payouts.calculator.estimate_label') }}</span>
                                <div class="text-end">
                                    <h4 class="mb-0 fw-bold text-success" id="calc-result-display">0 ₫</h4>
                                    <small class="text-muted" id="calc-rate-hint"></small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 small text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i> {{ __('finances::teacher/payouts.calculator.calculator_info') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const container = document.getElementById('payout-main-container');
            if (!container) return;

            // Initialize Page Functions
            const initPage = () => {
                // Payout Form Logic
                const payoutAccRadios = document.querySelectorAll('input[name="payout_account_id"]');
                const amountInput = document.getElementById('teacher-payout-amount-display');
                const submitBtn = document.getElementById('payout-submit-btn');
                const validationHint = document.getElementById('amount-validation-hint');
                const modeHidden = document.getElementById('account-mode-hidden');
                const newAccFields = document.getElementById('new-account-fields');

                const updatePayoutFormState = () => {
                    let selectedRadio = null;
                    payoutAccRadios.forEach(r => { if(r.checked) selectedRadio = r; });

                    if (selectedRadio) {
                        if (amountInput) amountInput.disabled = false;
                        if (submitBtn) submitBtn.disabled = false;
                        if (validationHint) validationHint.innerHTML = `<span class="text-success"><i class="fa-solid fa-unlock me-1"></i>${@json(__('finances::teacher/payouts.form.amount_unlock_hint', ['min' => $minPayoutFormatted]))}</span>`;
                        
                        const isNew = selectedRadio.value === 'new';
                        if (modeHidden) modeHidden.value = isNew ? 'new' : 'saved';
                        if (newAccFields) newAccFields.classList.toggle('d-none', !isNew);
                    } else {
                        const accountsExist = payoutAccRadios.length > 0;
                        if (!accountsExist) {
                            if (amountInput) amountInput.disabled = false;
                            if (submitBtn) submitBtn.disabled = false;
                        } else {
                            if (amountInput) amountInput.disabled = true;
                            if (submitBtn) submitBtn.disabled = true;
                            if (validationHint) validationHint.innerHTML = `<span class="text-warning"><i class="fa-solid fa-lock me-1"></i>${@json(__('finances::teacher/payouts.form.amount_lock_hint'))}</span>`;
                        }
                    }
                };

                if (payoutAccRadios.length > 0) {
                    payoutAccRadios.forEach(r => r.addEventListener('change', updatePayoutFormState));
                    updatePayoutFormState();
                }

                // Bank Search Logic
                const bankSearchInputs = document.querySelectorAll('[data-bank-search]');
                const syncBankSearch = (searchInput) => {
                    const selectId = searchInput.getAttribute('data-bank-search');
                    const select = document.getElementById(selectId);
                    if (!select) return;

                    const keyword = (searchInput.value || '').trim().toLowerCase();
                    Array.from(select.options).forEach((option, index) => {
                        if (index === 0) return;
                        option.hidden = keyword !== '' && !option.text.toLowerCase().includes(keyword);
                    });
                };

                bankSearchInputs.forEach(input => {
                    input.addEventListener('input', () => syncBankSearch(input));
                });

                // Re-init global money inputs if any
                if (window.initMoneyInputs) window.initMoneyInputs();

                // Currency Conversion Logic
                const currentCurrency = @json($userCurrency);
                const exchangeRates = @json($exchangeRates);
                const conversionFee = @json($conversionFee);
                const targetCurrencySelect = document.getElementById('calc-target-currency');
                const resultDisplay = document.getElementById('calc-result-display');
                const feeDisplay = document.getElementById('calc-fee-display');
                const rateHint = document.getElementById('calc-rate-hint');
                const amountHidden = document.getElementById('teacher-payout-amount');

                const formatResult = (value, currency) => {
                    if (currency === 'VND' || currency === 'JPY' || currency === 'KRW') {
                        return new Intl.NumberFormat('vi-VN').format(Math.round(value)) + ' ' + getSymbol(currency);
                    }
                    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' ' + getSymbol(currency);
                };

                const getSymbol = (currency) => {
                    const symbols = { 'VND': '₫', 'USD': '$', 'JPY': '¥', 'KRW': '₩', 'CNY': '元' };
                    return symbols[currency] || currency;
                };

                const previewBox = document.getElementById('payout-conversion-preview');
                const previewFinal = document.getElementById('preview-final-amount');
                const previewFee = document.getElementById('preview-fee-amount');
                const previewInfo = document.getElementById('preview-conversion-info');
                
                const labels = {
                    feeDeducted: @json(__('finances::teacher/payouts.calculator.system_fee_deducted')),
                    rateHint: @json(__('finances::teacher/payouts.calculator.rate_hint'))
                };

                const updateConversion = () => {
                    if (!resultDisplay) return;
                    
                    const enteredAmount = parseFloat(amountHidden.value) || 0;
                    const feePct = conversionFee / 100;

                    // Convert entered amount to VND for calculation
                    let amountInVnd = enteredAmount;
                    if (currentCurrency !== 'VND' && exchangeRates[currentCurrency]) {
                        amountInVnd = enteredAmount * exchangeRates[currentCurrency];
                    }

                    const limitHintNode = document.getElementById('package-limit-hint');
                    if (limitHintNode) {
                        const remainingLimitVnd = parseFloat(limitHintNode.getAttribute('data-remaining-limit')) || 0;
                        if (amountInVnd > remainingLimitVnd) {
                            limitHintNode.className = 'text-danger fw-bold mt-1';
                            if (submitBtn) submitBtn.disabled = true;
                        } else {
                            limitHintNode.className = 'text-info mt-1';
                            if (submitBtn) submitBtn.disabled = false;
                        }
                    }
                    
                    const amountInVndAfterFee = amountInVnd * (1 - feePct);
                    const feeAmountVnd = amountInVnd * feePct;
                    const feeTextVnd = '- ' + formatResult(feeAmountVnd, 'VND');

                    const target = targetCurrencySelect.value;
                    let finalResultText = '';
                    let infoText = '';

                    if (target === 'VND') {
                        finalResultText = formatResult(amountInVndAfterFee, 'VND');
                        feeDisplay.innerText = feeTextVnd;
                        resultDisplay.innerText = finalResultText;
                        rateHint.innerText = '';
                        infoText = labels.feeDeducted;
                    } else if (exchangeRates[target]) {
                        const rate = exchangeRates[target]; 
                        const finalTargetAmount = amountInVndAfterFee / rate;
                        finalResultText = formatResult(finalTargetAmount, target);
                        
                        resultDisplay.innerText = finalResultText;
                        feeDisplay.innerText = feeTextVnd;
                        
                        const formattedRate = new Intl.NumberFormat('vi-VN').format(rate);
                        rateHint.innerText = labels.rateHint.replace(':target', target).replace(':rate', formattedRate);
                        infoText = labels.rateHint.replace(':target', target).replace(':rate', formattedRate);
                    }

                    // Update Preview Box
                    if (previewBox) {
                        if (enteredAmount > 0) {
                            previewBox.classList.remove('d-none');
                            if (previewFinal) previewFinal.innerText = finalResultText;
                            if (previewFee) previewFee.innerText = feeTextVnd;
                            if (previewInfo) previewInfo.innerText = infoText;
                        } else {
                            previewBox.classList.add('d-none');
                        }
                    }
                };

                if (targetCurrencySelect && amountHidden) {
                    targetCurrencySelect.addEventListener('change', updateConversion);
                    
                    const observer = new MutationObserver(updateConversion);
                    observer.observe(amountHidden, { attributes: true });
                    
                    if (amountInput) {
                        amountInput.addEventListener('input', () => {
                            setTimeout(updateConversion, 10);
                        });
                    }

                    updateConversion();
                }
            };

            // AJAX Tab Switch Logic
            const contentArea = document.getElementById('payout-content-area');
            const navLinks = document.querySelectorAll('[data-payout-nav-link]');

            const setActiveTab = (url) => {
                navLinks.forEach(link => {
                    link.classList.toggle('active', link.href === url);
                });
            };

            const loadTab = async (url, pushState = true) => {
                if (contentArea) contentArea.style.opacity = '0.5';
                
                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('payout-content-area');

                    if (newContent && contentArea) {
                        contentArea.innerHTML = newContent.innerHTML;
                        contentArea.style.opacity = '1';
                        setActiveTab(url);
                        if (pushState) history.pushState({ url }, '', url);
                        initPage();
                    } else {
                        // Fallback if content area not found
                        window.location.href = url;
                    }
                } catch (error) {
                    console.error('Failed to load tab:', error);
                    window.location.href = url;
                }
            };

            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (link.classList.contains('active')) return;
                    loadTab(link.href);
                });
            });

            window.addEventListener('popstate', (e) => {
                if (e.state && e.state.url) {
                    loadTab(e.state.url, false);
                }
            });

            initPage();
        })();
    </script>
@endsection

@section('stylesheets')
    <style>
        /* Modern Payout Design System */
        .teacher-payout-rates-card {
            background: var(--admin-glass-bg);
            border: 1px solid rgba(37, 99, 235, 0.15);
            backdrop-filter: blur(10px);
        }
        .teacher-payout-calculator-card {
            background: var(--admin-glass-bg);
            border: 1px solid rgba(16, 185, 129, 0.15);
            backdrop-filter: blur(10px);
        }
        
        .currency-rate-item {
            background: rgba(var(--admin-primary-rgb), 0.03);
            border-color: rgba(148, 163, 184, 0.1) !important;
        }
        .currency-label {
            color: var(--admin-text-main);
        }
        
        .custom-payout-select {
            background-color: var(--admin-glass-bg) !important;
            color: var(--admin-text-main) !important;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
        }
        .custom-payout-select option {
            background-color: var(--admin-bg-body) !important;
            color: var(--admin-text-main) !important;
        }
        
        .teacher-calc-result-box {
            background: rgba(var(--admin-primary-rgb), 0.05);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(148, 163, 184, 0.1), transparent);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .teacher-payout-account-label { padding: 12px; }
        }

        .teacher-payout-tabs-container {
            width: 100%;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .teacher-payout-tabs-container::-webkit-scrollbar { display: none; }
        .teacher-payout-tabs {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid rgba(148, 163, 184, 0.1);
            padding-bottom: 2px;
            min-width: max-content;
        }
        .teacher-payout-tab {
            text-decoration: none;
            padding: 12px 20px;
            color: var(--admin-text-soft);
            font-weight: 600;
            position: relative;
            transition: all 0.2s;
        }
        .teacher-payout-tab:hover { color: var(--admin-primary); }
        .teacher-payout-tab.active { color: var(--admin-primary); }
        .teacher-payout-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--admin-primary);
        }

        .teacher-payout-account-label {
            display: block;
            padding: 16px;
            border: 2px solid rgba(148, 163, 184, 0.1);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--admin-glass-bg);
            backdrop-filter: blur(8px);
        }
        .teacher-payout-account-option .btn-check:checked + .teacher-payout-account-label {
            border-color: var(--admin-primary);
            background: rgba(37, 99, 235, 0.05);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .teacher-money-input-wrapper .form-control-lg {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--admin-primary);
            padding: 12px 60px 12px 20px;
        }
        .teacher-money-input { position: relative; }
        .teacher-money-input__unit {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--admin-text-soft);
        }

        /* Currency Flags & Calculator Styles */
        .currency-flag-usd::before { content: "🇺🇸"; }
        .currency-flag-vnd::before { content: "🇻🇳"; }
        .currency-flag-jpy::before { content: "🇯🇵"; }
        .currency-flag-krw::before { content: "🇰🇷"; }
        .currency-flag-cny::before { content: "🇨🇳"; }
        
        .teacher-payout-calculator .form-select {
            background-color: #fff;
            border: 1px solid rgba(16, 185, 129, 0.2) !important;
            color: #065f46;
            font-weight: 600;
        }

        .teacher-calc-result {
            transition: all 0.3s ease;
        }
        
        .bg-primary-soft { background: rgba(59, 130, 246, 0.1); }
        .divider { height: 1px; background: rgba(148, 163, 184, 0.1); width: 100%; border-top: 1px solid rgba(148,163,184,0.1); }
        
        #payout-content-area { transition: opacity 0.2s ease; }
    </style>
@endsection
