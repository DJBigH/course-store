@extends('layouts.teacher')

@section('content')
    @php
        $formatBankAccountNumber = static function (?string $value): string {
            $digits = preg_replace('/\D+/', '', (string) $value);
            return $digits === '' ? (string) $value : trim(implode(' ', str_split($digits, 4)));
        };
        $resolveStatusClass = static function (?string $status): string {
            return match ($status) {
                'pending' => 'is-pending',
                'approved' => 'is-approved',
                'rejected' => 'is-rejected',
                'cancelled', 'canceled' => 'is-cancelled',
                default => 'is-default',
            };
        };
    @endphp

    @php
        $maintPackage = $teacher->application?->package;
        $maintPayout = $maintPackage?->isFeatureInMaintenance('can_request_payouts') ?? false;
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

        <!-- Stat Cards (Keep consistent across tabs for smooth feeling) -->
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
                <a href="{{ route('teacher.dashboard.payouts.index') }}" class="teacher-payout-tab" data-payout-nav-link>
                    <i class="fa-solid fa-money-bill-transfer me-2"></i>{{ __('finances::teacher/payouts.tabs.withdraw') }}
                </a>
                <a href="{{ route('teacher.dashboard.payouts.accounts') }}" class="teacher-payout-tab active" data-payout-nav-link>
                    <i class="fa-solid fa-building-columns me-2"></i>{{ __('finances::teacher/payouts.tabs.bank_accounts') }}
                </a>
                <a href="{{ route('teacher.dashboard.payouts.history') }}" class="teacher-payout-tab" data-payout-nav-link>
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>{{ __('finances::teacher/payouts.tabs.history') }}
                </a>
            </div>
        </div>

        <!-- Dynamic Content Area -->
        <div id="payout-content-area" class="position-relative">
            <div class="teacher-panel mb-4 shadow-sm">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
                    <div>
                        <h3 class="h5 fw-bold mb-1">{{ __('finances::teacher/payouts.saved_accounts.title') }}</h3>
                        <p class="text-muted mb-0">
                            {{ __('finances::teacher/payouts.saved_accounts.description', ['count' => $payoutAccountUsage['used'], 'limit' => $payoutAccountUsage['limit_label']]) }}
                        </p>
                    </div>
                    <span class="teacher-chip bg-primary-soft text-primary border-primary-soft">{{ __('finances::teacher/payouts.saved_accounts.limit_chip', ['count' => $payoutAccountUsage['used'], 'limit' => $payoutAccountUsage['limit_label']]) }}</span>
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
                                    <div class="text-muted small">{{ __('finances::teacher/payouts.saved_accounts.account_holder') }}</div>
                                    <div class="fw-semibold">{{ $account->bank_account_name }}</div>
                                </div>
                                <div>
                                    <div class="text-muted small">{{ __('finances::teacher/payouts.saved_accounts.account_number') }}</div>
                                    <div class="fw-bold font-monospace">{{ $formatBankAccountNumber($account->bank_account_number) }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="p-4 text-center border rounded-3 text-muted">
                                <i class="fa-solid fa-ban fs-3 mb-2 d-block"></i>
                                {{ __('finances::teacher/payouts.saved_accounts.empty') }}
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($payoutAccountUsage['can_create'] ?? true)
                    <div class="divider mb-4"></div>
                    <div class="p-4 rounded-4 border bg-opacity-10 mb-5" style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.3) !important;">
                        <h4 class="h6 fw-bold mb-3 text-success text-uppercase"><i class="fa-solid fa-plus-circle me-2"></i>{{ __('finances::teacher/payouts.saved_accounts.add_title') }}</h4>
                        <p class="text-muted small mb-4">{{ __('finances::teacher/payouts.saved_accounts.add_help') }}</p>
                        <form method="POST" action="{{ route('teacher.dashboard.payouts.account.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('finances::teacher/payouts.form.bank_name') }}</label>
                                    <input type="text" class="form-control mb-2" placeholder="{{ __('finances::teacher/payouts.form.search_bank') }}" data-bank-search="payout-add-bank-name">
                                    <select class="form-select" name="bank_name" id="payout-add-bank-name">
                                        <option value="">{{ __('finances::teacher/payouts.form.choose_bank') }}</option>
                                        @foreach ($bankOptions as $bankValue => $bankLabel)
                                            <option value="{{ $bankValue }}" @selected(old('bank_name') === $bankValue)>{{ $bankLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('finances::teacher/payouts.form.bank_account_name') }}</label>
                                    <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="VD: NGUYEN VAN A">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ __('finances::teacher/payouts.form.bank_account_number') }}</label>
                                    <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="{{ __('finances::teacher/payouts.form.bank_account_number') }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success mt-4 py-2 px-4 shadow-sm" @disabled($maintPayout)>
                                <i class="fa-solid fa-save me-2"></i>{{ __('finances::teacher/payouts.saved_accounts.save_button') }}
                                @if($maintPayout) ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }}) @endif
                            </button>
                        </form>
                    </div>
                @endif

                <div class="divider mb-4"></div>

                <h3 class="h5 fw-bold mb-2">{{ __('finances::teacher/payouts.change_requests.title') }}</h3>
                <p class="text-muted mb-4">{{ __('finances::teacher/payouts.change_requests.description') }}</p>

                @if (!($payoutAccountUsage['can_create'] ?? true))
                    <div class="p-4 rounded-4 border bg-opacity-10 mb-4" style="background: rgba(59, 130, 246, 0.05);">
                        <form method="POST" action="{{ route('teacher.dashboard.payouts.account-change.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('finances::teacher/payouts.change_requests.replace_account') }}</label>
                                    <select name="replace_payout_account_id" class="form-select">
                                        <option value="">{{ __('finances::teacher/payouts.change_requests.select_replace_account') }}</option>
                                        @foreach ($payoutAccounts as $account)
                                            <option value="{{ $account->id }}" {{ (string) old('replace_payout_account_id') === (string) $account->id ? 'selected' : '' }}>
                                                {{ $account->bank_name }} - {{ $account->bank_account_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('finances::teacher/payouts.change_requests.new_account_info') }}</label>
                                    <input type="text" class="form-control mb-2" placeholder="{{ __('finances::teacher/payouts.form.search_bank') }}" data-bank-search="payout-change-bank-name">
                                    <select class="form-select" name="bank_name" id="payout-change-bank-name">
                                        <option value="">{{ __('finances::teacher/payouts.form.choose_bank') }}</option>
                                        @foreach ($bankOptions as $bankValue => $bankLabel)
                                            <option value="{{ $bankValue }}" @selected(old('bank_name') === $bankValue)>{{ $bankLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('finances::teacher/payouts.form.bank_account_name') }}</label>
                                    <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="VD: NGUYEN VAN A">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('finances::teacher/payouts.form.bank_account_number') }}</label>
                                    <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="{{ __('finances::teacher/payouts.form.bank_account_number') }}">
                                </div>
                            </div>
                            <button class="btn btn-primary mt-4 py-2 px-4 shadow-sm" @disabled($maintPayout)>
                                <i class="fa-solid fa-paper-plane me-2"></i>{{ __('finances::teacher/payouts.change_requests.submit') }}
                                @if($maintPayout) ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }}) @endif
                            </button>
                        </form>
                    </div>
                @else
                    <div class="alert alert-warning border-0" style="background: rgba(245, 158, 11, 0.1);">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ __('finances::teacher/payouts.change_requests.limit_hint', ['limit' => $payoutAccountUsage['limit_label']]) }}
                    </div>
                @endif

                <div class="table-responsive mt-4">
                    <table class="table align-middle">
                        <thead class="bg-opacity-50" style="background: rgba(148, 163, 184, 0.1);">
                            <tr>
                                <th>{{ __('finances::teacher/payouts.change_requests.table.id') }}</th>
                                <th>{{ __('finances::teacher/payouts.change_requests.table.replace_account') }}</th>
                                <th>{{ __('finances::teacher/payouts.change_requests.table.new_account') }}</th>
                                <th>{{ __('finances::teacher/payouts.change_requests.table.status') }}</th>
                                <th class="text-end">{{ __('finances::teacher/payouts.change_requests.table.submitted_at') }}</th>
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
                                            {{ __('finances::teacher/payouts.change_requests.status.' . $item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end text-muted">{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('finances::teacher/payouts.change_requests.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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

            const initPage = () => {
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
            };

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
                        window.location.href = url;
                    }
                } catch (error) {
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

        .bg-primary-soft { background: rgba(59, 130, 246, 0.1); }
        .divider { height: 1px; background: rgba(148, 163, 184, 0.1); width: 100%; border-top: 1px solid rgba(148,163,184,0.1); }

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
        .teacher-status-badge.is-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .teacher-status-badge.is-cancelled { background: rgba(148, 163, 184, 0.1); color: #64748b; }
        #payout-content-area { transition: opacity 0.2s ease; }
    </style>
@endsection
