@extends('layouts.teacher')

@section('content')
    @php
        $resolveStatusClass = static function (?string $status): string {
            return match ($status) {
                'requested' => 'is-pending',
                'processing' => 'is-approved',
                'paid' => 'is-paid',
                'rejected' => 'is-rejected',
                'cancelled', 'canceled' => 'is-cancelled',
                default => 'is-default',
            };
        };
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

        <!-- Stat Cards -->
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
                <a href="{{ route('teacher.dashboard.payouts.accounts') }}" class="teacher-payout-tab" data-payout-nav-link>
                    <i class="fa-solid fa-building-columns me-2"></i>{{ __('finances::teacher/payouts.tabs.bank_accounts') }}
                </a>
                <a href="{{ route('teacher.dashboard.payouts.history') }}" class="teacher-payout-tab active" data-payout-nav-link>
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>{{ __('finances::teacher/payouts.tabs.history') }}
                </a>
            </div>
        </div>

        <!-- Dynamic Content Area -->
        <div id="payout-content-area" class="position-relative">
            <div class="teacher-panel shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="bg-opacity-50" style="background: rgba(148, 163, 184, 0.1);">
                            <tr>
                                <th>{{ __('finances::teacher/payouts.table.id') }}</th>
                                <th>{{ __('finances::teacher/payouts.table.amount') }}</th>
                                <th>{{ __('finances::teacher/payouts.table.bank') }}</th>
                                <th>{{ __('finances::teacher/payouts.table.status') }}</th>
                                <th class="text-end">{{ __('finances::teacher/payouts.table.submitted_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payouts as $payout)
                                <tr>
                                    <td>#{{ $payout->id }}</td>
                                    <td class="fw-bold text-primary">{{ moneyLocale($payout->amount, null, true) }}</td>
                                    <td>{{ $payout->bank_name }}<br><small class="text-muted">{{ $payout->bank_account_number }}</small></td>
                                    <td>
                                        <span class="teacher-status-badge {{ $resolveStatusClass($payout->status) }}">
                                            {{ __('finances::teacher/payouts.status.' . $payout->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end text-muted">{{ optional($payout->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('finances::teacher/payouts.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 payout-pagination-container">
                    {{ $payouts->links() }}
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
                // Pagination AJAX logic
                const paginationLinks = document.querySelectorAll('.payout-pagination-container a');
                paginationLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        loadTab(link.href, true);
                    });
                });
            };

            const contentArea = document.getElementById('payout-content-area');
            const navLinks = document.querySelectorAll('[data-payout-nav-link]');

            const setActiveTab = (url) => {
                navLinks.forEach(link => {
                    const linkPath = new URL(link.href).pathname;
                    const urlPath = new URL(url).pathname;
                    link.classList.toggle('active', linkPath === urlPath);
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
        .teacher-status-badge.is-cancelled { background: rgba(148, 163, 184, 0.1); color: #64748b; }
        #payout-content-area { transition: opacity 0.2s ease; }
    </style>
@endsection
