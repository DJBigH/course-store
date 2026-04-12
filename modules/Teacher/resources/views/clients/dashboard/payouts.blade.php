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

        <div class="teacher-panel mb-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h3 class="h5 fw-bold mb-1">{{ __('teacher::dashboard.payouts.saved_accounts.title') }}</h3>
                    <p class="text-muted mb-0">
                        {{ __('teacher::dashboard.payouts.saved_accounts.description', ['count' => $payoutAccountUsage['used'], 'limit' => $payoutAccountUsage['limit_label']]) }}
                    </p>
                </div>
                <span class="teacher-chip">{{ $payoutAccountUsage['used'] }}/{{ $payoutAccountUsage['limit_label'] }}</span>
            </div>

            <div class="row g-3">
                @forelse ($payoutAccounts as $account)
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 teacher-payout-account-card">
                            <div class="fw-semibold teacher-payout-account-card__bank">{{ $account->bank_name }}</div>
                            <div class="teacher-payout-account-card__meta">
                                <span>Chủ tài khoản</span>
                                <strong>{{ $account->bank_account_name }}</strong>
                            </div>
                            <div class="teacher-payout-account-card__meta">
                                <span>Số tài khoản</span>
                                <strong>{{ $formatBankAccountNumber($account->bank_account_number) }}</strong>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="border rounded-3 p-3 text-muted">{{ __('teacher::dashboard.payouts.saved_accounts.empty') }}</div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="teacher-panel mb-4">
            <h3 class="h5 fw-bold mb-2">{{ __('teacher::dashboard.payouts.form.title') }}</h3>
            <p class="text-muted mb-3">{{ __('teacher::dashboard.payouts.form.help') }}</p>

            <form method="POST" action="{{ route('teacher.dashboard.payouts.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.amount') }}</label>
                        <input type="hidden" name="amount" id="teacher-payout-amount" value="{{ old('amount') }}">
                        <div class="teacher-money-input">
                            <input
                                type="text"
                                inputmode="numeric"
                                class="form-control"
                                id="teacher-payout-amount-display"
                                value="{{ old('amount') }}"
                                data-money-input
                                data-money-target="teacher-payout-amount"
                                placeholder="0">
                            <span class="teacher-money-input__unit">đ</span>
                        </div>
                    </div>

                    @if ($payoutAccounts->isNotEmpty())
                        <div class="col-md-8">
                            <label class="form-label d-block">{{ __('teacher::dashboard.payouts.form.account_mode') }}</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="account_mode" id="account-mode-saved" value="saved"
                                    {{ old('account_mode', 'saved') === 'saved' ? 'checked' : '' }}>
                                <label class="form-check-label" for="account-mode-saved">{{ __('teacher::dashboard.payouts.form.use_saved_account') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="account_mode" id="account-mode-new" value="new"
                                    {{ old('account_mode') === 'new' ? 'checked' : '' }}>
                                <label class="form-check-label" for="account-mode-new">{{ __('teacher::dashboard.payouts.form.use_new_account') }}</label>
                            </div>
                        </div>
                        <div class="col-12" data-payout-mode-group="saved">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.saved_account') }}</label>
                            <select name="payout_account_id" class="form-select" id="teacher-payout-saved-account-select">
                                <option value="">{{ __('teacher::dashboard.payouts.form.select_saved_account') }}</option>
                                @foreach ($payoutAccounts as $account)
                                    <option value="{{ $account->id }}" {{ (string) old('payout_account_id') === (string) $account->id ? 'selected' : '' }}>
                                        {{ $account->bank_name }} - {{ $account->bank_account_name }} - ****{{ substr($account->bank_account_number, -4) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="teacher-payout-saved-preview mt-3" id="teacher-payout-saved-preview">
                                <div class="teacher-payout-saved-preview__empty">Chọn một tài khoản đã lưu để xem rõ ngân hàng, chủ tài khoản và số tài khoản.</div>
                                @foreach ($payoutAccounts as $account)
                                    <div
                                        class="teacher-payout-saved-preview__card d-none"
                                        data-saved-account-preview="{{ $account->id }}">
                                        <div class="teacher-payout-saved-preview__row">
                                            <span>Ngân hàng</span>
                                            <strong>{{ $account->bank_name }}</strong>
                                        </div>
                                        <div class="teacher-payout-saved-preview__row">
                                            <span>Chủ tài khoản</span>
                                            <strong>{{ $account->bank_account_name }}</strong>
                                        </div>
                                        <div class="teacher-payout-saved-preview__row">
                                            <span>Số tài khoản</span>
                                            <strong>{{ $formatBankAccountNumber($account->bank_account_number) }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="account_mode" value="new">
                    @endif

                    <div class="col-md-4" data-payout-mode-group="new">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_name') }}</label>
                        <input
                            type="text"
                            class="form-control mb-2"
                            placeholder="Tìm nhanh ngân hàng..."
                            data-bank-search="payout-create-bank-name">
                        <select class="form-select" name="bank_name" id="payout-create-bank-name">
                            <option value="">Chọn ngân hàng tại Việt Nam</option>
                            @foreach ($bankOptions as $bankValue => $bankLabel)
                                <option value="{{ $bankValue }}" @selected(old('bank_name') === $bankValue)>{{ $bankLabel }}</option>
                            @endforeach
                            @if (old('bank_name') && !array_key_exists(old('bank_name'), $bankOptions))
                                <option value="{{ old('bank_name') }}" selected>{{ old('bank_name') }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4" data-payout-mode-group="new">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_name') }}</label>
                        <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}">
                    </div>
                    <div class="col-md-4" data-payout-mode-group="new">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_number') }}</label>
                        <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.note') }}</label>
                        <input type="text" class="form-control" name="note" value="{{ old('note') }}">
                    </div>
                </div>
                <button class="btn btn-primary mt-3">{{ __('teacher::dashboard.payouts.form.submit') }}</button>
            </form>
        </div>

        <div class="teacher-panel mb-4">
            <h3 class="h5 fw-bold mb-2">{{ __('teacher::dashboard.payouts.change_requests.title') }}</h3>
            <p class="text-muted mb-3">{{ __('teacher::dashboard.payouts.change_requests.description') }}</p>

            @if (!($payoutAccountUsage['can_create'] ?? true))
                <form method="POST" action="{{ route('teacher.dashboard.payouts.account-change.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.change_requests.replace_account') }}</label>
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
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_name') }}</label>
                            <input
                                type="text"
                                class="form-control mb-2"
                                placeholder="Tìm nhanh ngân hàng..."
                                data-bank-search="payout-change-bank-name">
                            <select class="form-select" name="bank_name" id="payout-change-bank-name">
                                <option value="">Chọn ngân hàng tại Việt Nam</option>
                                @foreach ($bankOptions as $bankValue => $bankLabel)
                                    <option value="{{ $bankValue }}" @selected(old('bank_name') === $bankValue)>{{ $bankLabel }}</option>
                                @endforeach
                                @if (old('bank_name') && !array_key_exists(old('bank_name'), $bankOptions))
                                    <option value="{{ old('bank_name') }}" selected>{{ old('bank_name') }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_name') }}</label>
                            <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_number') }}</label>
                            <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.note') }}</label>
                            <input type="text" class="form-control" name="note" value="{{ old('note') }}">
                        </div>
                    </div>
                    <button class="btn btn-outline-primary mt-3">{{ __('teacher::dashboard.payouts.change_requests.submit') }}</button>
                </form>
            @else
                <div class="border rounded-3 p-3 text-muted">{{ __('teacher::dashboard.payouts.change_requests.limit_hint', ['limit' => $payoutAccountUsage['limit_label']]) }}</div>
            @endif

            <div class="table-responsive mt-4">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.id') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.replace_account') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.new_account') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.status') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.submitted_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingAccountChangeRequests as $item)
                            <tr>
                                <td>#{{ $item->id }}</td>
                                <td>
                                    @if ($item->replace_bank_name)
                                        {{ $item->replace_bank_name }}<br>
                                        <small class="text-muted">{{ $item->replace_bank_account_name }} - {{ $item->replace_bank_account_number }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ $item->bank_name }}<br>
                                    <small class="text-muted">{{ $item->bank_account_name }} - {{ $item->bank_account_number }}</small>
                                </td>
                                <td>
                                    <span class="teacher-status-badge {{ $resolveStatusClass($item->status) }}">
                                        {{ __('teacher::dashboard.payouts.change_requests.status.' . $item->status) }}
                                    </span>
                                    @if ($item->admin_note)
                                        <div class="small text-muted mt-1">{{ $item->admin_note }}</div>
                                    @endif
                                </td>
                                <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('teacher::dashboard.payouts.change_requests.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="teacher-panel">
            <h3 class="h5 fw-bold mb-3">{{ __('teacher::dashboard.payouts.history_title') }}</h3>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('teacher::dashboard.payouts.table.id') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.table.amount') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.table.bank') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.table.status') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.table.submitted_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payouts as $payout)
                            <tr>
                                <td>#{{ $payout->id }}</td>
                                <td>{{ money($payout->amount, 'đ', '0 đ') }}</td>
                                <td>{{ $payout->bank_name }}<br><small class="text-muted">{{ $payout->bank_account_number }}</small></td>
                                <td>
                                    <span class="teacher-status-badge {{ $resolveStatusClass($payout->status) }}">
                                        {{ __('teacher::dashboard.payouts.status.' . $payout->status) }}
                                    </span>
                                </td>
                                <td>{{ optional($payout->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('teacher::dashboard.payouts.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $payouts->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const savedModeInput = document.getElementById('account-mode-saved');
            const newModeInput = document.getElementById('account-mode-new');
            const savedGroups = document.querySelectorAll('[data-payout-mode-group="saved"]');
            const newGroups = document.querySelectorAll('[data-payout-mode-group="new"]');
            const savedAccountSelect = document.getElementById('teacher-payout-saved-account-select');
            const previewEmpty = document.querySelector('.teacher-payout-saved-preview__empty');
            const previewCards = document.querySelectorAll('[data-saved-account-preview]');
            const bankSearchInputs = document.querySelectorAll('[data-bank-search]');

            const toggleGroup = (elements, shouldShow) => {
                elements.forEach((element) => {
                    element.classList.toggle('d-none', !shouldShow);

                    element.querySelectorAll('input, select, textarea').forEach((field) => {
                        if (field.name === 'note' || field.type === 'hidden') {
                            return;
                        }

                        field.disabled = !shouldShow;
                    });
                });
            };

            const syncSavedPreview = () => {
                if (!savedAccountSelect) {
                    return;
                }

                const selectedId = savedAccountSelect.value;
                let hasVisibleCard = false;

                previewCards.forEach((card) => {
                    const shouldShow = selectedId !== '' && card.getAttribute('data-saved-account-preview') === selectedId;
                    card.classList.toggle('d-none', !shouldShow);
                    hasVisibleCard = hasVisibleCard || shouldShow;
                });

                if (previewEmpty) {
                    previewEmpty.classList.toggle('d-none', hasVisibleCard);
                }
            };

            const syncMode = () => {
                const mode = newModeInput?.checked ? 'new' : 'saved';
                toggleGroup(savedGroups, mode === 'saved');
                toggleGroup(newGroups, mode === 'new');
                syncSavedPreview();
            };

            const syncBankSearch = (searchInput) => {
                const selectId = searchInput.getAttribute('data-bank-search');
                const select = document.getElementById(selectId);
                if (!select) {
                    return;
                }

                const keyword = (searchInput.value || '').trim().toLowerCase();
                const currentValue = select.value;
                let firstMatchedValue = '';

                Array.from(select.options).forEach((option, index) => {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    const matched = keyword === '' || option.text.toLowerCase().includes(keyword);
                    option.hidden = !matched;

                    if (matched && !firstMatchedValue) {
                        firstMatchedValue = option.value;
                    }
                });

                if (keyword !== '' && currentValue) {
                    const selectedOption = Array.from(select.options).find((option) => option.value === currentValue);
                    if (selectedOption && selectedOption.hidden) {
                        select.value = firstMatchedValue || '';
                    }
                }
            };

            if (!savedModeInput && !newModeInput) {
                syncSavedPreview();
            } else {
                savedModeInput?.addEventListener('change', syncMode);
                newModeInput?.addEventListener('change', syncMode);
                savedAccountSelect?.addEventListener('change', syncSavedPreview);
                syncMode();
            };

            bankSearchInputs.forEach((input) => {
                input.addEventListener('input', () => syncBankSearch(input));
                syncBankSearch(input);
            });
        })();
    </script>
@endsection

@section('stylesheets')
    <style>
        .teacher-money-input {
            position: relative;
        }

        .teacher-money-input .form-control {
            padding-right: 2.75rem;
        }

        .teacher-money-input__unit {
            position: absolute;
            top: 50%;
            right: 0.95rem;
            transform: translateY(-50%);
            color: var(--admin-muted);
            font-weight: 700;
            pointer-events: none;
        }

        .teacher-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            line-height: 1;
            border: 1px solid transparent;
        }

        .teacher-status-badge.is-pending {
            background: rgba(245, 158, 11, 0.16);
            border-color: rgba(245, 158, 11, 0.28);
            color: #fcd34d;
        }

        .teacher-status-badge.is-approved {
            background: rgba(59, 130, 246, 0.16);
            border-color: rgba(96, 165, 250, 0.28);
            color: #93c5fd;
        }

        .teacher-status-badge.is-paid {
            background: rgba(34, 197, 94, 0.16);
            border-color: rgba(74, 222, 128, 0.28);
            color: #86efac;
        }

        .teacher-status-badge.is-rejected {
            background: rgba(244, 63, 94, 0.16);
            border-color: rgba(251, 113, 133, 0.28);
            color: #fda4af;
        }

        .teacher-status-badge.is-cancelled {
            background: rgba(148, 163, 184, 0.16);
            border-color: rgba(148, 163, 184, 0.28);
            color: #cbd5e1;
        }

        .teacher-status-badge.is-default {
            background: rgba(125, 211, 252, 0.16);
            border-color: rgba(125, 211, 252, 0.28);
            color: #bae6fd;
        }

        html[data-theme="light"] .teacher-status-badge.is-pending {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.22);
            color: #b45309;
        }

        html[data-theme="light"] .teacher-status-badge.is-approved {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.2);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-status-badge.is-paid {
            background: rgba(34, 197, 94, 0.12);
            border-color: rgba(34, 197, 94, 0.22);
            color: #15803d;
        }

        html[data-theme="light"] .teacher-status-badge.is-rejected {
            background: rgba(244, 63, 94, 0.1);
            border-color: rgba(244, 63, 94, 0.18);
            color: #be123c;
        }

        html[data-theme="light"] .teacher-status-badge.is-cancelled {
            background: rgba(148, 163, 184, 0.12);
            border-color: rgba(148, 163, 184, 0.2);
            color: #475569;
        }

        html[data-theme="light"] .teacher-status-badge.is-default {
            background: rgba(14, 165, 233, 0.1);
            border-color: rgba(14, 165, 233, 0.18);
            color: #0369a1;
        }
    </style>
@endsection
