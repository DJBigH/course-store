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

    // Kiem tra Bao tri he thong (Global)
    $teacherMaintCourse = $teacherCurrentPackage?->isFeatureInMaintenance('course_limit') ?? false;
    $teacherMaintComments = $teacherCurrentPackage?->isFeatureInMaintenance('can_manage_comments') ?? false;
    $teacherMaintCoupons = $teacherCurrentPackage?->isFeatureInMaintenance('can_manage_coupons') ?? false;
    $teacherMaintPromotions = $teacherCurrentPackage?->isFeatureInMaintenance('can_send_promotions') ?? false;
    $teacherMaintBundles = $teacherCurrentPackage?->isFeatureInMaintenance('can_sell_bundles') ?? false;
    $teacherMaintAffiliate = $teacherCurrentPackage?->isFeatureInMaintenance('can_use_affiliate_links') ?? false;
    $teacherMaintCertificates = $teacherCurrentPackage?->isFeatureInMaintenance('can_issue_certificates') ?? false;
    $teacherMaintActivityLogs = $teacherCurrentPackage?->isFeatureInMaintenance('can_view_activity_logs') ?? false;
    $teacherMaintStudents = $teacherCurrentPackage?->isFeatureInMaintenance('can_manage_students') ?? false;
    $teacherMaintQuizzes = $teacherCurrentPackage?->isFeatureInMaintenance('can_manage_quizzes') ?? false;

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
        ? __('teacher/sidebar.brand.active_channel')
        : ($teacherApplication?->display_status ?? __('teacher/sidebar.brand.not_submitted'));
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
    
    $teacherUsedCoursesCount = $teacherProfile?->courses()->count() ?? 0;
    $teacherCourseLimit = $teacherCurrentPackage?->effective_course_limit;
    $teacherCourseProgress = $teacherCourseLimit ? min(100, ($teacherUsedCoursesCount / $teacherCourseLimit) * 100) : 0;
@endphp

@php
    if (!function_exists('hexToRgb')) {
        function hexToRgb($hex) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) == 3) {
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }
            return "$r, $g, $b";
        }
    }
    $packageTone = $teacherCurrentPackage->badge_tone ?? '#2563eb';
    $packageToneRgb = hexToRgb($packageTone);
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

    nav.sb-sidenav {
        background: var(--admin-sidebar-bg) !important;
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
        border-top: 1px solid var(--admin-sidebar-border);
        padding: 1rem;
        background: var(--admin-surface-3);
        backdrop-filter: blur(6px);
    }

    html[data-theme="dark"] #layoutSidenav_nav .sb-sidenav .sb-sidenav-footer {
        background: rgba(15, 23, 42, 0.4);
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-footer .small {
        font-size: 0.76rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--admin-muted);
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-user-name,
    #layoutSidenav_nav .sb-sidenav .sidebar-link-label {
        display: block;
        min-width: 0;
        line-height: 1.4;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-user-name {
        color: var(--admin-text);
        font-weight: 700;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-link-label {
        color: var(--admin-muted);
        font-size: 0.82rem;
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

    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--blue { background: rgba(59, 130, 246, 0.1); color: #2563eb; border-color: rgba(59, 130, 246, 0.2); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--gold { background: rgba(245, 158, 11, 0.12); color: #b45309; border-color: rgba(245, 158, 11, 0.2); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--emerald { background: rgba(16, 185, 129, 0.12); color: #059669; border-color: rgba(16, 185, 129, 0.2); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--violet { background: rgba(139, 92, 246, 0.12); color: #7c3aed; border-color: rgba(139, 92, 246, 0.2); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--rose { background: rgba(244, 63, 94, 0.12); color: #e11d48; border-color: rgba(244, 63, 94, 0.2); }
    #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--slate { background: rgba(148, 163, 184, 0.12); color: #475569; border-color: rgba(148, 163, 184, 0.2); }

    html[data-theme="dark"] #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--blue { background: rgba(59, 130, 246, 0.14); color: #bfdbfe; border-color: rgba(96, 165, 250, 0.24); }
    html[data-theme="dark"] #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--gold { background: rgba(245, 158, 11, 0.16); color: #fde68a; border-color: rgba(251, 191, 36, 0.24); }
    html[data-theme="dark"] #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--emerald { background: rgba(16, 185, 129, 0.16); color: #a7f3d0; border-color: rgba(52, 211, 153, 0.24); }
    html[data-theme="dark"] #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--violet { background: rgba(139, 92, 246, 0.16); color: #ddd6fe; border-color: rgba(167, 139, 250, 0.24); }
    html[data-theme="dark"] #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--rose { background: rgba(244, 63, 94, 0.16); color: #fecdd3; border-color: rgba(251, 113, 133, 0.24); }
    html[data-theme="dark"] #layoutSidenav_nav .sb-sidenav .sidebar-user-badge--slate { background: rgba(148, 163, 184, 0.16); color: #e2e8f0; border-color: rgba(148, 163, 184, 0.24); }

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
        background: rgba(37, 99, 235, 0.12);
        color: var(--admin-primary);
        font-size: 0.72rem;
        font-weight: 800;
        border: 1px solid rgba(37, 99, 235, 0.2);
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-package-name {
        color: var(--admin-text);
        font-size: 0.82rem;
        font-weight: 700;
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

    .sidebar-package-card {
        margin: 0.85rem;
        padding: 1.15rem;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(var(--package-tone-rgb, 37, 99, 235), 0.12), rgba(var(--package-tone-rgb, 37, 99, 235), 0.06));
        border: 1px solid rgba(var(--package-tone-rgb, 37, 99, 235), 0.15);
        display: flex;
        flex-direction: column;
        gap: 1rem;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(8px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
    }

    html[data-theme="dark"] .sidebar-package-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.9));
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .sidebar-package-card::before {
        content: "";
        position: absolute;
        inset: -100%;
        background: radial-gradient(circle at 20% 20%, rgba(14, 165, 233, 0.1), transparent 40%);
        pointer-events: none;
    }

    .sidebar-package-card__profile {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .sidebar-package-card__avatar {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    html[data-theme="dark"] .sidebar-package-card__avatar {
        border-color: rgba(255, 255, 255, 0.1);
    }

    .sidebar-package-card__user {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .sidebar-package-card__user-name {
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--admin-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }

    .sidebar-package-card__plan-badge {
        display: inline-flex;
        width: fit-content;
        margin-top: 0.25rem;
        padding: 0.18rem 0.6rem;
        border-radius: 999px;
        background: var(--package-tone, var(--teacher-accent));
        color: #fff;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .sidebar-package-card__stats {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .sidebar-package-card__stats-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--admin-muted);
    }

    .sidebar-package-card__progress {
        height: 5px;
        background: rgba(0, 0, 0, 0.08);
        border-radius: 999px;
        overflow: hidden;
    }

    html[data-theme="dark"] .sidebar-package-card__progress {
        background: rgba(255, 255, 255, 0.08);
    }

    .sidebar-package-card__progress-bar {
        height: 100%;
        background: var(--premium-gradient);
        border-radius: 999px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-package-card__actions {
        margin-top: 0.25rem;
    }

    .sidebar-package-card__upgrade {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.55rem 1rem;
        border-radius: 12px;
        background: var(--admin-text);
        color: var(--admin-surface) !important;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
    }

    .sidebar-package-card__upgrade:hover {
        transform: translateY(-1px);
        opacity: 0.9;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
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

    .nav-link.is-maintenance {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
        filter: grayscale(0.5);
    }

    .nav-link-lock.is-maintenance {
        background: rgba(245, 158, 11, 0.25) !important;
        color: #f59e0b !important;
        border: 1px solid rgba(245, 158, 11, 0.4);
        box-shadow: 0 0 10px rgba(245, 158, 11, 0.1);
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
        padding: 0.65rem 1rem;
        border: 0;
        background: transparent;
        color: var(--admin-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        text-align: left;
        transition: color 0.2s ease;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group__toggle:hover {
        color: var(--admin-text);
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group__icon {
        transition: transform 0.22s ease;
        color: var(--admin-muted);
        opacity: 0.6;
        font-size: 0.78rem;
        flex: 0 0 auto;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-group.is-open .sidebar-group__icon {
        transform: rotate(180deg);
        color: var(--admin-primary);
        opacity: 1;
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
            @if ($teacherCurrentPackage)
                <div class="sidebar-package-card" style="--package-tone: {{ $packageTone }}; --package-tone-rgb: {{ $packageToneRgb }};">
                    <div class="sidebar-package-card__profile">
                        <img src="{{ $teacherProfile->image ? asset($teacherProfile->image) : asset('clients/assets/LOGO-DSCONS-FAVICON.png') }}" 
                            alt="{{ $teacherProfile->getNameLocaleAttribute() }}" class="sidebar-package-card__avatar">
                        <div class="sidebar-package-card__user">
                            <span class="sidebar-package-card__user-name">{{ $teacherProfile->getNameLocaleAttribute() }}</span>
                            <span class="sidebar-package-card__plan-badge">
                                {{ $teacherCurrentPackage->badge_text_locale ?: $teacherCurrentPackage->name_locale }}
                            </span>
                        </div>
                    </div>

                    {{-- <div class="sidebar-package-card__stats">
                        <div class="sidebar-package-card__stats-label">
                            <span>{{ __('courses::teacher/messages.courses.title') }}</span>
                            <span>{{ $teacherUsedCoursesCount }}{{ $teacherCourseLimit ? '/' . $teacherCourseLimit : '' }}</span>
                        </div>
                        <div class="sidebar-package-card__progress">
                            <div class="sidebar-package-card__progress-bar" style="width: {{ $teacherCourseProgress }}%"></div>
                        </div>
                    </div> --}}

                    @if ($teacherIsActive && !$teacherPendingUpgrade)
                        <div class="sidebar-package-card__actions">
                            <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="sidebar-package-card__upgrade">
                                <i class="fas fa-arrow-trend-up me-2"></i>
                                <span>{{ __('packages::teacher.upgrade.upgrade_cta_short') ?? 'Nâng cấp' }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <div class="nav">
                <div class="sidebar-group {{ $teacherSidebarOperationsActive ? 'is-open' : '' }}" data-sidebar-group>
                    <button type="button" class="sidebar-group__toggle" data-sidebar-toggle aria-expanded="{{ $teacherSidebarOperationsActive ? 'true' : 'false' }}">
                        <span>{{ __('teacher/sidebar.group.management') }}</span>
                        <i class="fas fa-chevron-down sidebar-group__icon"></i>
                    </button>
                    <div class="sidebar-group__content">
                        <div class="sidebar-group__inner">
                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.index') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-gauge-high"></i></div>
                                {{ __('teacher/sidebar.nav.overview') }}
                            </a>

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.courses*') || request()->routeIs('teacher.dashboard.lessons.*') ? 'active' : '' }} {{ $teacherMaintCourse ? 'is-maintenance' : '' }}"
                                href="{{ route('teacher.dashboard.courses') }}">
                                <span class="nav-link-main">
                                    <span class="sb-nav-link-icon"><i class="fas fa-book-open"></i></span>
                                    <span class="nav-link-text">{{ __('teacher/sidebar.nav.courses') }}</span>
                                </span>
                                @if($teacherMaintCourse)
                                    <span class="nav-link-lock is-maintenance">BAO TRI</span>
                                @endif
                            </a>

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.earnings') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.earnings') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                                {{ __('teacher/sidebar.nav.earnings') }}
                            </a>

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.payouts*') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.payouts.index') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                                {{ __('teacher/sidebar.nav.payouts') }}
                            </a>

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.orders*') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.orders') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                                {{ __('teacher/sidebar.nav.orders') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="sidebar-group {{ $teacherSidebarGrowthActive ? 'is-open' : '' }}" data-sidebar-group>
                    <button type="button" class="sidebar-group__toggle" data-sidebar-toggle aria-expanded="{{ $teacherSidebarGrowthActive ? 'true' : 'false' }}">
                        <span>{{ __('teacher/sidebar.group.growth') }}</span>
                        <i class="fas fa-chevron-down sidebar-group__icon"></i>
                    </button>
                    <div class="sidebar-group__content">
                        <div class="sidebar-group__inner">
                            {{-- Promotions --}}
                            @if ($teacherCanSendPromotions || $teacherMaintPromotions)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.promotions*') ? 'active' : '' }} {{ $teacherMaintPromotions ? 'is-maintenance' : '' }}"
                                    href="{{ route('teacher.dashboard.promotions') }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.promotions') }}</span>
                                    </span>
                                    @if ($teacherMaintPromotions)
                                        <span class="nav-link-lock is-maintenance">BAO TRI</span>
                                    @endif
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.promotions') }}</span>
                                    </span>
                                    @include('packages::partials.upgrade_badge', [
                                        'class' => 'nav-link-lock',
                                        'label' => '<i class="fas fa-lock me-1"></i> PRO'
                                    ])
                                </a>
                            @endif

                            {{-- Coupons --}}
                            @if ($teacherCanManageCoupons || $teacherMaintCoupons)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.coupons*') ? 'active' : '' }} {{ $teacherMaintCoupons ? 'is-maintenance' : '' }}"
                                    href="{{ route('teacher.dashboard.coupons.index') }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-ticket"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.coupons') }}</span>
                                    </span>
                                    @if ($teacherMaintCoupons)
                                        <span class="nav-link-lock is-maintenance">BAO TRI</span>
                                    @endif
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-lock"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.coupons') }}</span>
                                    </span>
                                    @include('packages::partials.upgrade_badge', [
                                        'class' => 'nav-link-lock',
                                        'label' => '<i class="fas fa-lock me-1"></i> PRO'
                                    ])
                                </a>
                            @endif

                            {{-- Affiliate --}}
                            @if ($teacherCanUseAffiliateLinks || $teacherMaintAffiliate)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.affiliate-links*') ? 'active' : '' }} {{ $teacherMaintAffiliate ? 'is-maintenance' : '' }}"
                                    href="{{ route('teacher.dashboard.affiliate-links.index') }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-link"></i></span>
                                        <span class="nav-link-text">{{ __('finances::teacher/affiliate_links.nav.affiliate_links') }}</span>
                                    </span>
                                    @if ($teacherMaintAffiliate)
                                        <span class="nav-link-lock is-maintenance">BAO TRI</span>
                                    @endif
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-link"></i></span>
                                        <span class="nav-link-text">{{ __('finances::teacher/affiliate_links.nav.affiliate_links') }}</span>
                                    </span>
                                    @include('packages::partials.upgrade_badge', [
                                        'class' => 'nav-link-lock',
                                        'label' => '<i class="fas fa-lock me-1"></i> PRO'
                                    ])
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="sidebar-group {{ $teacherSidebarStudentsActive ? 'is-open' : '' }}" data-sidebar-group>
                    <button type="button" class="sidebar-group__toggle" data-sidebar-toggle aria-expanded="{{ $teacherSidebarStudentsActive ? 'true' : 'false' }}">
                        <span>{{ __('teacher/sidebar.group.trainees') }}</span>
                        <i class="fas fa-chevron-down sidebar-group__icon"></i>
                    </button>
                    <div class="sidebar-group__content">
                        <div class="sidebar-group__inner">
                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.students*') ? 'active' : '' }} {{ $teacherMaintStudents ? 'is-maintenance' : '' }}"
                                href="{{ route('teacher.dashboard.students') }}">
                                <span class="nav-link-main">
                                    <span class="sb-nav-link-icon"><i class="fas fa-user-graduate"></i></span>
                                    <span class="nav-link-text">{{ __('teacher/sidebar.nav.students') }}</span>
                                </span>
                                @if($teacherMaintStudents)
                                    <span class="nav-link-lock is-maintenance">BAO TRI</span>
                                @endif
                            </a>

                            @if ($teacherCanManageComments || $teacherMaintComments)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.comments*') ? 'active' : '' }} {{ $teacherMaintComments ? 'is-maintenance' : '' }}"
                                    href="{{ route('teacher.dashboard.comments') }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-comments"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.comments') }}</span>
                                    </span>
                                    @if ($teacherMaintComments)
                                        <span class="nav-link-lock is-maintenance">BAO TRI</span>
                                    @endif
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-comments"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.comments') }}</span>
                                    </span>
                                    @include('packages::partials.upgrade_badge', [
                                        'class' => 'nav-link-lock',
                                        'label' => '<i class="fas fa-lock me-1"></i> PRO'
                                    ])
                                </a>
                            @endif

                            @if ($teacherCanIssueCertificates || $teacherMaintCertificates)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.certificates*') ? 'active' : '' }} {{ $teacherMaintCertificates ? 'is-maintenance' : '' }}"
                                    href="{{ route('teacher.dashboard.certificates.index') }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-award"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.certificates') }}</span>
                                    </span>
                                    @if ($teacherMaintCertificates)
                                        <span class="nav-link-lock is-maintenance">BAO TRI</span>
                                    @endif
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-award"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.certificates') }}</span>
                                    </span>
                                    @include('packages::partials.upgrade_badge', [
                                        'class' => 'nav-link-lock',
                                        'label' => '<i class="fas fa-lock me-1"></i> PRO'
                                    ])
                                </a>
                            @endif

                            @if ($teacherCanViewActivityLogs || $teacherMaintActivityLogs)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.activity-logs') ? 'active' : '' }} {{ $teacherMaintActivityLogs ? 'is-maintenance' : '' }}"
                                    href="{{ route('teacher.dashboard.activity-logs') }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-clock-rotate-left"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.activity_logs') }}</span>
                                    </span>
                                    @if ($teacherMaintActivityLogs)
                                        <span class="nav-link-lock is-maintenance">BAO TRI</span>
                                    @endif
                                </a>
                            @elseif ($teacherIsActive)
                                <a class="nav-link is-locked" href="{{ $teacherUpgradeUrl }}">
                                    <span class="nav-link-main">
                                        <span class="sb-nav-link-icon"><i class="fas fa-clock-rotate-left"></i></span>
                                        <span class="nav-link-text">{{ __('teacher/sidebar.nav.activity_logs') }}</span>
                                    </span>
                                    @include('packages::partials.upgrade_badge', [
                                        'class' => 'nav-link-lock',
                                        'label' => '<i class="fas fa-lock me-1"></i> PRO'
                                    ])
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="sidebar-group {{ $teacherSidebarAccountActive ? 'is-open' : '' }}" data-sidebar-group>
                    <button type="button" class="sidebar-group__toggle" data-sidebar-toggle aria-expanded="{{ $teacherSidebarAccountActive ? 'true' : 'false' }}">
                        <span>{{ __('teacher/sidebar.group.account') }}</span>
                        <i class="fas fa-chevron-down sidebar-group__icon"></i>
                    </button>
                    <div class="sidebar-group__content">
                        <div class="sidebar-group__inner">
                            @if ($teacherIsActive)
                                <a class="nav-link {{ request()->routeIs('teacher.dashboard.package.upgrade') || request()->routeIs('teacher.dashboard.package.upgrade.*') ? 'active' : '' }}"
                                    href="{{ $teacherPendingUpgrade ? route('teacher.dashboard.package.upgrade.status') : route('teacher.dashboard.package.upgrade') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-arrow-trend-up"></i></div>
                                    {{ __('teacher/sidebar.nav.package') }}
                                </a>
                            @endif

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.support*') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.support') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-life-ring"></i></div>
                                {{ __('teacher/sidebar.nav.support') }}
                            </a>

                            <a class="nav-link {{ request()->routeIs('teacher.dashboard.cancellation') ? 'active' : '' }}"
                                href="{{ route('teacher.dashboard.cancellation') }}">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-slash"></i></div>
                                {{ __('teacher/sidebar.nav.cancellation') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sb-sidenav-footer">
            <div class="small">{{ __('teacher/sidebar.brand.logged_in_as') }}</div>
            <div class="sidebar-user-name">{{ $teacherStudent?->name ?? __('teacher/sidebar.brand.student_fallback') }}</div>
            <div class="sidebar-link-label">
                {{ $teacherStudent?->email }}
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
