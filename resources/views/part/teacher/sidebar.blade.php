@php
    $teacherStudent = auth('students')->user();
    $teacherLocale = session('locale', app()->getLocale());
    $teacherApplication = $teacherStudent?->teacherApplications()?->latest('id')->first();
    $teacherProfile = $teacherStudent?->teacher;
    $teacherCurrentPackage = $teacherProfile?->application?->package ?? $teacherApplication?->package;
    $teacherCanManageComments = $teacherProfile?->packageHasFeature('can_manage_comments') ?? false;
    $teacherCanManageCoupons = $teacherProfile?->packageHasFeature('can_manage_coupons') ?? false;
    $teacherCanSendPromotions = $teacherProfile?->packageHasFeature('can_send_promotions') ?? false;
    $teacherCanSellBundles = $teacherProfile?->packageHasFeature('can_sell_bundles') ?? false;
    $teacherCanUseAffiliateLinks = $teacherProfile?->packageHasFeature('can_use_affiliate_links') ?? false;
    $teacherCanIssueCertificates = $teacherProfile?->packageHasFeature('can_issue_certificates') ?? false;
    $teacherCanViewActivityLogs = $teacherProfile?->packageHasFeature('can_view_activity_logs') ?? false;
    $teacherIsActive = $teacherProfile && $teacherProfile->status === 'active';
    $teacherPendingUpgrade = $teacherStudent?->teacherApplications()
        ->where(function ($query) {
            $query->whereIn('status', ['pending_payment', 'pending_review'])
                ->orWhere(function ($approvedQuery) {
                    $approvedQuery->where('status', 'approved')
                        ->whereNotNull('activates_at')
                        ->whereNull('activated_at');
                });
        })
        ->latest('id')
        ->first();
    $teacherStatusText = $teacherIsActive
        ? __('teacher::dashboard.brand.active_channel')
        : ($teacherApplication?->display_status ?? __('teacher::dashboard.brand.not_submitted'));
    $teacherUpgradeUrl = $teacherPendingUpgrade ? route('teacher.dashboard.package.upgrade.status') : route('teacher.dashboard.package.upgrade');
    $teacherSidebarBadge = $teacherProfile?->primary_badge;
    $teacherSidebarOperationsActive = request()->routeIs('teacher.dashboard.index')
        || request()->routeIs('teacher.dashboard.courses*')
        || request()->routeIs('teacher.dashboard.lessons.*')
        || request()->routeIs('teacher.dashboard.earnings')
        || request()->routeIs('teacher.dashboard.payouts*')
        || request()->routeIs('teacher.dashboard.orders*');
    $teacherSidebarGrowthActive = request()->routeIs('teacher.dashboard.promotions*')
        || request()->routeIs('teacher.dashboard.coupons*')
        || request()->routeIs('teacher.dashboard.affiliate-links*');
    $teacherSidebarStudentsActive = request()->routeIs('teacher.dashboard.students*')
        || request()->routeIs('teacher.dashboard.comments*')
        || request()->routeIs('teacher.dashboard.certificates*')
        || request()->routeIs('teacher.dashboard.activity-logs');
    $teacherSidebarAccountActive = request()->routeIs('teacher.dashboard.package.upgrade')
        || request()->routeIs('teacher.dashboard.package.upgrade.*')
        || request()->routeIs('teacher.dashboard.support*');
@endphp

<style>
    #layoutSidenav_nav {
        overflow: hidden;
    }

    #layoutSidenav_nav .sb-sidenav {
        width: 100%;
        overflow-x: hidden;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu {
        padding-bottom: 5rem;
        overflow-x: hidden;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu .nav {
        gap: 0.18rem;
        padding: 0 0.5rem 0.75rem;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-footer {
        min-height: 88px;
        width: 100%;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.35rem;
        line-height: 1.35;
        overflow: hidden;
        border-top: 1px solid rgba(148, 163, 184, 0.14);
        padding: 1rem;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(6px);
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-footer .small {
        font-size: 0.76rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.56);
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-user-name,
    #layoutSidenav_nav .sb-sidenav .sidebar-link-label {
        display: block;
        min-width: 0;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-user-name {
        color: rgba(255, 255, 255, 0.92);
        font-weight: 600;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        max-width: 100%;
        padding: 0.28rem 0.62rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--blue { background: rgba(59, 130, 246, 0.14); color: #bfdbfe; border-color: rgba(96, 165, 250, 0.24); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--gold { background: rgba(245, 158, 11, 0.16); color: #fde68a; border-color: rgba(251, 191, 36, 0.24); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--emerald { background: rgba(16, 185, 129, 0.16); color: #a7f3d0; border-color: rgba(52, 211, 153, 0.24); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--violet { background: rgba(139, 92, 246, 0.16); color: #ddd6fe; border-color: rgba(167, 139, 250, 0.24); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--rose { background: rgba(244, 63, 94, 0.16); color: #fecdd3; border-color: rgba(251, 113, 133, 0.24); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--slate { background: rgba(148, 163, 184, 0.16); color: #e2e8f0; border-color: rgba(148, 163, 184, 0.24); }

    #layoutSidenav_nav .sb-sidenav .sidebar-package-row {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-package-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.24rem 0.55rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.18);
        color: #9cc6ff;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-package-name {
        color: rgba(226, 232, 240, 0.84);
        font-size: 0.84rem;
        font-weight: 600;
    }

    .teacher-status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #4ade80;
        box-shadow: 0 0 0 6px rgba(74, 222, 128, 0.16);
        margin-right: 0.45rem;
    }

    #layoutSidenav_nav .sb-sidenav .nav-link.has-badge,
    #layoutSidenav_nav .sb-sidenav .nav-link.is-locked {
        display: flex;
        width: 100%;
        justify-content: space-between;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: nowrap;
    }

    #layoutSidenav_nav .sb-sidenav .nav-link-main {
        min-width: 0;
        width: 0;
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    #layoutSidenav_nav .sb-sidenav .nav-link-main .sb-nav-link-icon {
        margin-right: 0;
        flex: 0 0 auto;
    }

    #layoutSidenav_nav .sb-sidenav .nav-link-text {
        display: block;
        flex: 1 1 auto;
        min-width: 0;
        line-height: 1.35;
        white-space: normal;
        word-break: normal;
        /* overflow-wrap: anywhere; */
    }

    #layoutSidenav_nav .sb-sidenav .nav-link.is-locked:hover {
        background: rgba(255, 255, 255, 0.08);
    }

    #layoutSidenav_nav .sb-sidenav .nav-link-lock {
        display: inline-flex;
        align-items: center;
        padding: 0.18rem 0.5rem;
        border-radius: 999px;
        background: rgba(251, 191, 36, 0.14);
        color: #fcd34d;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        flex-shrink: 0;
        white-space: nowrap;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group {
        margin-bottom: 0.5rem;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group__toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.55rem 1rem;
        border: 0;
        background: transparent;
        color: rgba(255, 255, 255, 0.56);
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        text-align: left;
        transition: color 0.2s ease;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group__toggle:hover {
        color: rgba(255, 255, 255, 0.82);
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group__icon {
        transition: transform 0.22s ease;
        color: rgba(255, 255, 255, 0.42);
        font-size: 0.78rem;
        flex: 0 0 auto;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group.is-open .sidebar-group__icon {
        transform: rotate(180deg);
        color: rgba(255, 255, 255, 0.72);
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group__content {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows 0.24s ease;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group.is-open .sidebar-group__content {
        grid-template-rows: 1fr;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group__inner {
        overflow: hidden;
    }

    @media (max-width: 767.98px) {
        #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu {
            padding-top: 0.6rem;
        }
    }
</style>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="teacherSidenav">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sidebar-group {{ $teacherSidebarOperationsActive ? 'is-open' : '' }}" data-sidebar-group>
                    <button type="button" class="sidebar-group__toggle" data-sidebar-toggle aria-expanded="{{ $teacherSidebarOperationsActive ? 'true' : 'false' }}">
                        <span>Điều hành</span>
                        <i class="fas fa-chevron-down sidebar-group__icon"></i>
                    </button>
                    <div class="sidebar-group__content">
                        <div class="sidebar-group__inner">
                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.index') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-gauge-high"></i></div>
                                {{ __('teacher::dashboard.nav.overview') }}
                            </a>

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.courses*') || request()->routeIs('teacher.dashboard.lessons.*') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.courses') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                {{ __('teacher::dashboard.nav.courses') }}
                            </a>

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.earnings') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.earnings') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                                {{ __('teacher::dashboard.nav.earnings') }}
                            </a>

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.payouts*') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.payouts') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                                {{ __('teacher::dashboard.nav.payouts') }}
                            </a>

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.orders*') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.orders') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                                {{ __('teacher::dashboard.nav.orders') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="sidebar-group {{ $teacherSidebarGrowthActive ? 'is-open' : '' }}" data-sidebar-group>
                    <button type="button" class="sidebar-group__toggle" data-sidebar-toggle aria-expanded="{{ $teacherSidebarGrowthActive ? 'true' : 'false' }}">
                        <span>Tăng trưởng</span>
                        <i class="fas fa-chevron-down sidebar-group__icon"></i>
                    </button>
                    <div class="sidebar-group__content">
                        <div class="sidebar-group__inner">
                            @if ($teacherCanSendPromotions)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.promotions*') ? 'active' : '' }}"
                                    href="{{ route('teacher.dashboard.promotions') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></div>
                                    {{ __('teacher::dashboard.nav.promotions') }}
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></span>
                                        <span class="nav-link-text">{{ __('teacher::dashboard.nav.promotions') }}</span>
                                    </span>
                                    @include('teacher::clients.dashboard.partials.upgrade_badge', ['class' => 'nav-link-lock'])
                                </a>
                            @endif

                            @if ($teacherCanManageCoupons)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.coupons*') ? 'active' : '' }}"
                                    href="{{ route('teacher.dashboard.coupons.index') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-ticket"></i></div>
                                    {{ __('teacher::coupons.nav.label') }}
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-lock"></i></span>
                                        <span class="nav-link-text">{{ __('teacher::coupons.nav.label') }}</span>
                                    </span>
                                    @include('teacher::clients.dashboard.partials.upgrade_badge', ['class' => 'nav-link-lock'])
                                </a>
                            @endif

                            @if ($teacherCanUseAffiliateLinks)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.affiliate-links*') ? 'active' : '' }}"
                                    href="{{ route('teacher.dashboard.affiliate-links.index') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-link"></i></div>
                                    {{ __('teacher::dashboard.nav.affiliate_links') }}
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-link"></i></span>
                                        <span class="nav-link-text">{{ __('teacher::dashboard.nav.affiliate_links') }}</span>
                                    </span>
                                    @include('teacher::clients.dashboard.partials.upgrade_badge', ['class' => 'nav-link-lock'])
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="sidebar-group {{ $teacherSidebarStudentsActive ? 'is-open' : '' }}" data-sidebar-group>
                    <button type="button" class="sidebar-group__toggle" data-sidebar-toggle aria-expanded="{{ $teacherSidebarStudentsActive ? 'true' : 'false' }}">
                        <span>Học viên</span>
                        <i class="fas fa-chevron-down sidebar-group__icon"></i>
                    </button>
                    <div class="sidebar-group__content">
                        <div class="sidebar-group__inner">
                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.students*') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.students') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-graduate"></i></div>
                                Học viên
                            </a>

                            @if ($teacherIsActive)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.comments*') ? 'active' : '' }} {{ !$teacherCanManageComments ? 'has-badge' : '' }}"
                                    href="{{ route('teacher.dashboard.comments') }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-comments"></i></span>
                                        <span class="nav-link-text">{{ __('teacher::comments.nav.label') }}</span>
                                    </span>
                                    @if (!$teacherCanManageComments)
                                        @include('teacher::clients.dashboard.partials.upgrade_badge', ['class' => 'nav-link-lock'])
                                    @endif
                                </a>
                            @endif

                            @if ($teacherCanIssueCertificates)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.certificates*') ? 'active' : '' }}"
                                    href="{{ route('teacher.dashboard.certificates.index') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-award"></i></div>
                                    {{ __('teacher::dashboard.nav.certificates') }}
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-award"></i></span>
                                        <span class="nav-link-text">{{ __('teacher::dashboard.nav.certificates') }}</span>
                                    </span>
                                    @include('teacher::clients.dashboard.partials.upgrade_badge', ['class' => 'nav-link-lock'])
                                </a>
                            @endif

                            @if ($teacherCanViewActivityLogs)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.activity-logs') ? 'active' : '' }}"
                                    href="{{ route('teacher.dashboard.activity-logs') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-clock-rotate-left"></i></div>
                                    {{ __('teacher::dashboard.nav.activity_logs') }}
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-clock-rotate-left"></i></span>
                                        <span class="nav-link-text">{{ __('teacher::dashboard.nav.activity_logs') }}</span>
                                    </span>
                                    @include('teacher::clients.dashboard.partials.upgrade_badge', ['class' => 'nav-link-lock'])
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="sidebar-group {{ $teacherSidebarAccountActive ? 'is-open' : '' }}" data-sidebar-group>
                    <button type="button" class="sidebar-group__toggle" data-sidebar-toggle aria-expanded="{{ $teacherSidebarAccountActive ? 'true' : 'false' }}">
                        <span>Tài khoản</span>
                        <i class="fas fa-chevron-down sidebar-group__icon"></i>
                    </button>
                    <div class="sidebar-group__content">
                        <div class="sidebar-group__inner">
                            @if ($teacherIsActive)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.package.upgrade') || request()->routeIs('teacher.dashboard.package.upgrade.*') ? 'active' : '' }}"
                                    href="{{ $teacherPendingUpgrade ? route('teacher.dashboard.package.upgrade.status') : route('teacher.dashboard.package.upgrade') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-arrow-trend-up"></i></div>
                                    Gói giảng viên
                                </a>
                            @endif

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.support*') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.support') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-life-ring"></i></div>
                                Góp ý / hỗ trợ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sb-sidenav-footer">
        <div class="small">{{ __('teacher::dashboard.brand.logged_in_as') }}</div>
        <div class="sidebar-user-name">{{ $teacherStudent?->name ?? __('teacher::dashboard.brand.student_fallback') }}</div>
        {{-- @if ($teacherSidebarBadge)
            <span class="sidebar-user-badge sidebar-user-badge--{{ $teacherSidebarBadge['tone'] }}">{{ $teacherSidebarBadge['label'] }}</span>
        @endif --}}
        @if ($teacherCurrentPackage)
            <div class="sidebar-package-row">
                    <span class="sidebar-package-badge">
                        {{ $teacherCurrentPackage->badge_text_locale ?: strtoupper((string) $teacherCurrentPackage->code) }}
                    </span>
                    <span class="sidebar-package-name">
                        {{ $teacherCurrentPackage->name_locale ?: $teacherCurrentPackage->name }}
                    </span>
                </div>
            @endif
            <div class="sidebar-link-label">
                @if ($teacherIsActive)
                    <span class="teacher-status-dot"></span>{{ __('teacher::dashboard.brand.active_channel') }}
                @else
                    {{ $teacherStatusText }}
                @endif
            </div>
        </div>
    </nav>
</div>

<script>
    (() => {
        const storageKey = 'teacher-sidebar-groups';
        const groups = Array.from(document.querySelectorAll('[data-sidebar-group]'));

        if (!groups.length) {
            return;
        }

        const applyState = (group, isOpen) => {
            group.classList.toggle('is-open', isOpen);
            const button = group.querySelector('[data-sidebar-toggle]');
            if (button) {
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
        };

        const saveState = () => {
            const state = groups.reduce((result, group, index) => {
                result[index] = group.classList.contains('is-open');
                return result;
            }, {});

            localStorage.setItem(storageKey, JSON.stringify(state));
        };

        const restoreState = () => {
            try {
                const rawState = localStorage.getItem(storageKey);
                if (!rawState) {
                    groups.forEach((group) => applyState(group, true));
                    saveState();
                    return;
                }

                const parsedState = JSON.parse(rawState);
                groups.forEach((group, index) => {
                    const shouldOpen = parsedState[index] !== false;
                    applyState(group, shouldOpen);
                });
            } catch (error) {
                groups.forEach((group) => applyState(group, true));
                saveState();
            }
        };

        restoreState();

        document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const group = button.closest('[data-sidebar-group]');
                if (!group) {
                    return;
                }

                const isOpen = !group.classList.contains('is-open');
                applyState(group, isOpen);
                saveState();
            });
        });
    })();
</script>
