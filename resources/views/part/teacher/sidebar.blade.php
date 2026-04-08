@php
    $teacherStudent = auth('students')->user();
    $teacherLocale = session('locale', app()->getLocale());
    $teacherApplication = $teacherStudent?->teacherApplications()?->latest('id')->first();
    $teacherProfile = $teacherStudent?->teacher;
    $teacherCurrentPackage = $teacherProfile?->application?->package ?? $teacherApplication?->package;
    $teacherCanManageComments = $teacherProfile?->packageHasFeature('can_manage_comments') ?? false;
    $teacherCanManageCoupons = $teacherProfile?->packageHasFeature('can_manage_coupons') ?? false;
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
                <div class="sb-sidenav-menu-heading mt-2">{{ __('teacher::dashboard.brand.portal') }}</div>

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

                @if ($teacherIsActive)
                    <a class="nav-link {{ request()->routeIs('teacher.dashboard.comments*') ? 'active' : '' }} {{ !$teacherCanManageComments ? 'has-badge' : '' }}"
                        href="{{ route('teacher.dashboard.comments') }}">
                        <span class="nav-link-main">
                            <span class="sb-nav-link-icon"><i class="fas fa-comments"></i></span>
                            <span class="nav-link-text">{{ __('teacher::comments.nav.label') }}</span>
                        </span>
                        @if (!$teacherCanManageComments)
                            <span class="nav-link-lock">{{ __('teacher::dashboard.package_features.upgrade_badge') }}</span>
                        @endif
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
                        <span class="nav-link-lock">{{ __('teacher::dashboard.package_features.upgrade_badge') }}</span>
                    </a>
                @endif

                <a class="nav-link {{ request()->routeIs('teacher.dashboard.students*') ? 'active' : '' }}"
                    href="{{ route('teacher.dashboard.students') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-user-graduate"></i></div>
                    Học viên của tôi
                </a>

                <a class="nav-link {{ request()->routeIs('teacher.dashboard.orders*') ? 'active' : '' }}"
                    href="{{ route('teacher.dashboard.orders') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                    {{ __('teacher::dashboard.nav.orders') }}
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

                <a class="nav-link {{ request()->routeIs('teacher.dashboard.support*') ? 'active' : '' }}"
                    href="{{ route('teacher.dashboard.support') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-life-ring"></i></div>
                    {{ __('teacher::dashboard.nav.support') }}
                </a>

                @if ($teacherIsActive)
                    <a class="nav-link {{ request()->routeIs('teacher.dashboard.package.upgrade') || request()->routeIs('teacher.dashboard.package.upgrade.*') ? 'active' : '' }}"
                        href="{{ $teacherPendingUpgrade ? route('teacher.dashboard.package.upgrade.status') : route('teacher.dashboard.package.upgrade') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-arrow-trend-up"></i></div>
                        Đổi gói
                    </a>
                @endif
            </div>
        </div>

        <div class="sb-sidenav-footer">
            <div class="small">{{ __('teacher::dashboard.brand.logged_in_as') }}</div>
            <div class="sidebar-user-name">{{ $teacherStudent?->name ?? __('teacher::dashboard.brand.student_fallback') }}</div>
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


