@extends('layouts.teacher')

@section('content')
    @php
        $formatBankAccountNumber = static function (?string $value): string {
            $digits = preg_replace('/\D+/', '', (string) $value);

            if ($digits === '') {
                return (string) $value;
            }

            return trim(implode(' ', str_split($digits, 4)));
        };
        $resolveStatusClass = static function (?string $status): string {
            return match ($status) {
                'pending' => 'is-pending',
                'approved' => 'is-approved',
                'paid' => 'is-paid',
                'rejected' => 'is-rejected',
                'cancelled', 'canceled' => 'is-cancelled',
                default => 'is-default',
            };
        };
    @endphp
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.payouts.title') }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::dashboard.payouts.description') }}</p>
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
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.payouts.summary.teacher_revenue') }}</div>
                    <div class="teacher-stat-card__value">{{ money($summary['teacher_revenue'], 'đ', '0 đ') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.payouts.summary.requested') }}</div>
                    <div class="teacher-stat-card__value">{{ money($requestedAmount, 'đ', '0 đ') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.payouts.summary.available') }}</div>
                    <div class="teacher-stat-card__value">{{ money($availableBalance, 'đ', '0 đ') }}</div>
                </div>
            </div>
        </div>

        <!-- Sub-tabs System -->
        <div class="teacher-payout-tabs mb-4">
            <button type="button" class="teacher-payout-tab active" data-tab-target="tab-payout-request">
                <i class="fa-solid fa-money-bill-transfer me-2"></i>Rút tiền
            </button>
            <button type="button" class="teacher-payout-tab" data-tab-target="tab-bank-accounts">
                <i class="fa-solid fa-building-columns me-2"></i>Tài khoản ngân hàng
            </button>
            <button type="button" class="teacher-payout-tab" data-tab-target="tab-history">
                <i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử giao dịch
            </button>
        </div>

        <!-- Tab: Payout Request -->
        <div class="teacher-tab-content active" id="tab-payout-request">
            <div class="teacher-panel shadow-sm border-0 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05) !important;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="h5 fw-bold mb-1">{{ __('teacher::dashboard.payouts.form.title') }}</h3>
                        <p class="text-muted mb-0 small">{{ __('teacher::dashboard.payouts.form.help') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('teacher.dashboard.payouts.store') }}" id="payout-submission-form">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold mb-2">1. Chọn tài khoản nhận tiền</label>
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
                                                        <div class="fw-semibold">Dùng tài khoản mới</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <input type="hidden" name="account_mode" id="account-mode-hidden" value="{{ old('account_mode', 'saved') }}">
                            @else
                                <div class="alert alert-info py-3 shadow-none border-0" style="background: rgba(59, 130, 246, 0.1);">
                                    <i class="fa-solid fa-circle-info me-2"></i>Bạn chưa lưu tài khoản ngân hàng nào. Vui lòng nhập thông tin bên dưới để rút tiền.
                                </div>
                                <input type="hidden" name="account_mode" value="new">
                                <input type="hidden" name="payout_account_id" value="new">
                            @endif
                        </div>

                        <!-- New Account Fields (Only for Payout context) -->
                        <div class="col-12 {{ ($payoutAccounts->isNotEmpty() && old('payout_account_id') !== 'new') ? 'd-none' : '' }}" id="new-account-fields">
                            <div class="p-3 rounded-3" style="background: rgba(148, 163, 184, 0.05); border: 1px dashed rgba(148, 163, 184, 0.2);">
                                <div class="fw-semibold mb-3 small text-muted text-uppercase">Thông tin tài khoản nhận tiền mới</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_name') }}</label>
                                        <input type="text" class="form-control mb-2" placeholder="Tìm ngân hàng..." data-bank-search="payout-create-bank-name">
                                        <select class="form-select" name="bank_name" id="payout-create-bank-name">
                                            <option value="">Chọn ngân hàng</option>
                                            @foreach ($bankOptions as $bankValue => $bankLabel)
                                                <option value="{{ $bankValue }}" @selected(old('bank_name') === $bankValue)>{{ $bankLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_name') }}</label>
                                        <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="VD: NGUYEN VAN A">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_number') }}</label>
                                        <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Nhập số tài khoản">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-2">2. Số tiền muốn rút (Min 5.000đ)</label>
                            <input type="hidden" name="amount" id="teacher-payout-amount" value="{{ old('amount') }}">
                            <div class="teacher-money-input-wrapper">
                                <div class="teacher-money-input">
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        class="form-control form-control-lg"
                                        id="teacher-payout-amount-display"
                                        value="{{ old('amount') }}"
                                        data-money-input
                                        data-money-target="teacher-payout-amount"
                                        placeholder="0"
                                        {{ (old('payout_account_id') || $payoutAccounts->isEmpty()) ? '' : 'disabled' }}>
                                    <span class="teacher-money-input__unit">đ</span>
                                </div>
                                <div class="mt-2 small text-muted" id="amount-validation-hint">
                                    @if(!old('payout_account_id') && $payoutAccounts->isNotEmpty())
                                        <span class="text-warning"><i class="fa-solid fa-lock me-1"></i>Vui lòng chọn ngân hàng trước khi nhập số tiền</span>
                                    @else
                                        <span>Rút tối thiểu 5.000đ</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-2">{{ __('teacher::dashboard.payouts.form.note') }}</label>
                            <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú thêm (nếu có)">{{ old('note') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm" id="payout-submit-btn" {{ (old('payout_account_id') || $payoutAccounts->isEmpty()) ? '' : 'disabled' }}>
                            <i class="fa-solid fa-paper-plane me-2"></i>{{ __('teacher::dashboard.payouts.form.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab: Bank Accounts -->
        <div class="teacher-tab-content" id="tab-bank-accounts">
            <div class="teacher-panel mb-4 shadow-sm">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
                    <div>
                        <h3 class="h5 fw-bold mb-1">{{ __('teacher::dashboard.payouts.saved_accounts.title') }}</h3>
                        <p class="text-muted mb-0">
                            {{ __('teacher::dashboard.payouts.saved_accounts.description', ['count' => $payoutAccountUsage['used'], 'limit' => $payoutAccountUsage['limit_label']]) }}
                        </p>
                    </div>
                    <span class="teacher-chip bg-primary-soft text-primary border-primary-soft">Hạn mức: {{ $payoutAccountUsage['used'] }}/{{ $payoutAccountUsage['limit_label'] }}</span>
                </div>

                <div class="row g-3 mb-5">
                    @forelse ($payoutAccounts as $account)
                        <div class="col-md-4">
                            <div class="teacher-stat-card border h-100 p-4" style="background: rgba(255,255,255,0.01);">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="p-2 rounded bg-primary-soft text-primary me-3">
                                        <i class="fa-solid fa-building-columns fs-5"></i>
                                    </div>
                                    <div class="fw-bold truncate-1">{{ $account->bank_name }}</div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-muted small">Chủ tài khoản</div>
                                    <div class="fw-semibold">{{ $account->bank_account_name }}</div>
                                </div>
                                <div>
                                    <div class="text-muted small">Số tài khoản</div>
                                    <div class="fw-bold font-monospace">{{ $formatBankAccountNumber($account->bank_account_number) }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="p-4 text-center border rounded-3 text-muted">
                                <i class="fa-solid fa-ban fs-3 mb-2 d-block"></i>
                                {{ __('teacher::dashboard.payouts.saved_accounts.empty') }}
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($payoutAccountUsage['can_create'] ?? true)
                    <div class="divider mb-4"></div>
                    <div class="p-4 rounded-4 border bg-opacity-10 mb-5" style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.3) !important;">
                        <h4 class="h6 fw-bold mb-3 text-success text-uppercase"><i class="fa-solid fa-plus-circle me-2"></i>Thêm tài khoản ngân hàng mới</h4>
                        <p class="text-muted small mb-4">Lưu trước thông tin tài khoản để rút tiền nhanh hơn trong tương lai.</p>
                        <form method="POST" action="{{ route('teacher.dashboard.payouts.account.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Tên ngân hàng</label>
                                    <input type="text" class="form-control mb-2" placeholder="Tìm ngân hàng..." data-bank-search="payout-add-bank-name">
                                    <select class="form-select" name="bank_name" id="payout-add-bank-name">
                                        <option value="">Chọn ngân hàng</option>
                                        @foreach ($bankOptions as $bankValue => $bankLabel)
                                            <option value="{{ $bankValue }}" @selected(old('bank_name') === $bankValue)>{{ $bankLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('teacher::dashboard.payouts.form.bank_account_name') }}</label>
                                    <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="VD: NGUYEN VAN A">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('teacher::dashboard.payouts.form.bank_account_number') }}</label>
                                    <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Số tài khoản">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success mt-4 py-2 px-4 shadow-sm">
                                <i class="fa-solid fa-save me-2"></i>Lưu tài khoản
                            </button>
                        </form>
                    </div>
                @endif

                <div class="divider mb-4"></div>

                <h3 class="h5 fw-bold mb-2">{{ __('teacher::dashboard.payouts.change_requests.title') }}</h3>
                <p class="text-muted mb-4">{{ __('teacher::dashboard.payouts.change_requests.description') }}</p>

                @if (!($payoutAccountUsage['can_create'] ?? true))
                    <div class="p-4 rounded-4 border bg-opacity-10 mb-4" style="background: rgba(59, 130, 246, 0.05);">
                        <form method="POST" action="{{ route('teacher.dashboard.payouts.account-change.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('teacher::dashboard.payouts.change_requests.replace_account') }}</label>
                                    <select name="replace_payout_account_id" class="form-select">
                                        <option value="">{{ __('teacher::dashboard.payouts.change_requests.select_replace_account') }}</option>
                                        @foreach ($payoutAccounts as $account)
                                            <option value="{{ $account->id }}" {{ (string) old('replace_payout_account_id') === (string) $account->id ? 'selected' : '' }}>
                                                {{ $account->bank_name }} - {{ $account->bank_account_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Thông tin ngân hàng mới</label>
                                    <input type="text" class="form-control mb-2" placeholder="Tìm ngân hàng..." data-bank-search="payout-change-bank-name">
                                    <select class="form-select" name="bank_name" id="payout-change-bank-name">
                                        <option value="">Chọn ngân hàng</option>
                                        @foreach ($bankOptions as $bankValue => $bankLabel)
                                            <option value="{{ $bankValue }}" @selected(old('bank_name') === $bankValue)>{{ $bankLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('teacher::dashboard.payouts.form.bank_account_name') }}</label>
                                    <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="VD: NGUYEN VAN A">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('teacher::dashboard.payouts.form.bank_account_number') }}</label>
                                    <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Số tài khoản mới">
                                </div>
                            </div>
                            <button class="btn btn-primary mt-4 py-2 px-4 shadow-sm">
                                <i class="fa-solid fa-paper-plane me-2"></i>Gửi yêu cầu thay đổi
                            </button>
                        </form>
                    </div>
                @else
                    <div class="alert alert-warning border-0" style="background: rgba(245, 158, 11, 0.1);">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ __('teacher::dashboard.payouts.change_requests.limit_hint', ['limit' => $payoutAccountUsage['limit_label']]) }}
                    </div>
                @endif

                <div class="table-responsive mt-4">
                    <table class="table align-middle">
                        <thead class="bg-opacity-50" style="background: rgba(148, 163, 184, 0.1);">
                            <tr>
                                <th>{{ __('teacher::dashboard.payouts.change_requests.table.id') }}</th>
                                <th>{{ __('teacher::dashboard.payouts.change_requests.table.replace_account') }}</th>
                                <th>{{ __('teacher::dashboard.payouts.change_requests.table.new_account') }}</th>
                                <th>{{ __('teacher::dashboard.payouts.change_requests.table.status') }}</th>
                                <th class="text-end">{{ __('teacher::dashboard.payouts.change_requests.table.submitted_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingAccountChangeRequests as $item)
                                <tr>
                                    <td>#{{ $item->id }}</td>
                                    <td>{{ $item->replace_bank_name ?: '-' }}<br><small class="text-muted">{{ $item->replace_bank_account_number }}</small></td>
                                    <td>{{ $item->bank_name }}<br><small class="text-muted">{{ $item->bank_account_name }} - {{ $item->bank_account_number }}</small></td>
                                    <td>
                                        <span class="teacher-status-badge {{ $resolveStatusClass($item->status) }}">
                                            {{ __('teacher::dashboard.payouts.change_requests.status.' . $item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end text-muted">{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Chưa có yêu cầu thay đổi tài khoản nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: History -->
        <div class="teacher-tab-content" id="tab-history">
            <div class="teacher-panel shadow-sm">
                <h3 class="h5 fw-bold mb-4">{{ __('teacher::dashboard.payouts.history_title') }}</h3>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="bg-opacity-50" style="background: rgba(148, 163, 184, 0.1);">
                            <tr>
                                <th>{{ __('teacher::dashboard.payouts.table.id') }}</th>
                                <th>{{ __('teacher::dashboard.payouts.table.amount') }}</th>
                                <th>{{ __('teacher::dashboard.payouts.table.bank') }}</th>
                                <th>{{ __('teacher::dashboard.payouts.table.status') }}</th>
                                <th class="text-end">{{ __('teacher::dashboard.payouts.table.submitted_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payouts as $payout)
                                <tr>
                                    <td>#{{ $payout->id }}</td>
                                    <td class="fw-bold text-primary">{{ money($payout->amount, 'đ', '0 đ') }}</td>
                                    <td>{{ $payout->bank_name }}<br><small class="text-muted">{{ $payout->bank_account_number }}</small></td>
                                    <td>
                                        <span class="teacher-status-badge {{ $resolveStatusClass($payout->status) }}">
                                            {{ __('teacher::dashboard.payouts.status.' . $payout->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end text-muted">{{ optional($payout->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('teacher::dashboard.payouts.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $payouts->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            // Tab switching logic
            const tabBtns = document.querySelectorAll('.teacher-payout-tab');
            const tabContents = document.querySelectorAll('.teacher-tab-content');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const target = btn.getAttribute('data-tab-target');
                    
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    btn.classList.add('active');
                    document.getElementById(target).classList.add('active');
                    
                    history.replaceState(null, null, '#' + target);
                });
            });

            if (window.location.hash) {
                const activeTab = document.querySelector(`[data-tab-target="${window.location.hash.substring(1)}"]`);
                if (activeTab) activeTab.click();
            }

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
                    amountInput.disabled = false;
                    submitBtn.disabled = false;
                    validationHint.innerHTML = '<span class="text-success"><i class="fa-solid fa-unlock me-1"></i>Sẵn sàng. Rút tối thiểu 5.000đ</span>';
                    
                    const isNew = selectedRadio.value === 'new';
                    if (modeHidden) modeHidden.value = isNew ? 'new' : 'saved';
                    if (newAccFields) newAccFields.classList.toggle('d-none', !isNew);
                } else {
                    // Force enable if NO accounts exist (handled by blade logic but as fallback here)
                    const accountsExist = payoutAccRadios.length > 0;
                    if (!accountsExist) {
                        amountInput.disabled = false;
                        submitBtn.disabled = false;
                    } else {
                        amountInput.disabled = true;
                        submitBtn.disabled = true;
                        validationHint.innerHTML = '<span class="text-warning"><i class="fa-solid fa-lock me-1"></i>Vui lòng chọn ngân hàng trước khi nhập số tiền</span>';
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
        })();
    </script>
@endsection

@section('stylesheets')
    <style>
        /* Tabs Styling */
        .teacher-payout-tabs {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid rgba(148, 163, 184, 0.1);
            padding-bottom: 2px;
        }
        .teacher-payout-tab {
            background: none;
            border: none;
            padding: 12px 20px;
            color: var(--admin-text-soft);
            font-weight: 600;
            position: relative;
            transition: all 0.2s;
            cursor: pointer;
        }
        .teacher-payout-tab:hover {
            color: var(--admin-primary);
        }
        .teacher-payout-tab.active {
            color: var(--admin-primary);
        }
        .teacher-payout-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--admin-primary);
        }
        .teacher-tab-content {
            display: none;
        }
        .teacher-tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        /* Account Selection Styling */
        .teacher-payout-account-label {
            display: block;
            padding: 16px;
            border: 2px solid rgba(148, 163, 184, 0.1);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: rgba(255,255,255,0.01);
        }
        .teacher-payout-account-option .btn-check:checked + .teacher-payout-account-label {
            border-color: var(--admin-primary);
            background: rgba(var(--admin-primary-rgb), 0.05);
            box-shadow: 0 4px 12px rgba(var(--admin-primary-rgb), 0.1);
        }

        /* Money Input Wrapper */
        .teacher-money-input-wrapper .form-control-lg {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--admin-primary);
            padding: 12px 60px 12px 20px;
        }
        .teacher-money-input {
            position: relative;
        }
        .teacher-money-input__unit {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--admin-text-soft);
        }

        /* Chips & Badges */
        .bg-primary-soft { background: rgba(59, 130, 246, 0.1); }
        .divider { height: 1px; background: rgba(148, 163, 184, 0.1); width: 100%; border-top: 1px solid rgba(148,163,184,0.1); }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .teacher-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .teacher-status-badge.is-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .teacher-status-badge.is-approved { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .teacher-status-badge.is-paid { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .teacher-status-badge.is-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    </style>
@endsection
