<div class="teacher-dashboard-tabs mb-4">
    <a href="{{ route('teacher.dashboard.index') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.index') ? 'active' : '' }}">
        {{ __('teacher::dashboard.nav.overview') }}
    </a>
    <a href="{{ route('teacher.dashboard.courses') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.courses') ? 'active' : '' }}">
        {{ __('teacher::dashboard.nav.courses') }}
    </a>
    <a href="{{ route('teacher.dashboard.earnings') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.earnings') ? 'active' : '' }}">
        {{ __('teacher::dashboard.nav.earnings') }}
    </a>
    @if (($teacher ?? null)?->packageHasFeature('can_send_promotions'))
        <a href="{{ route('teacher.dashboard.promotions') }}"
            class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.promotions') ? 'active' : '' }}">
            {{ __('teacher::dashboard.nav.promotions') }}
        </a>
    @endif
    @if (($teacher ?? null)?->packageHasFeature('can_sell_bundles'))
        <a href="{{ route('teacher.dashboard.bundles') }}"
            class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.bundles*') ? 'active' : '' }}">
            {{ __('teacher::dashboard.nav.bundles') }}
        </a>
    @endif
    <a href="{{ route('teacher.dashboard.payouts') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.payouts') ? 'active' : '' }}">
        {{ __('teacher::dashboard.nav.payouts') }}
    </a>
</div>

<style>
    .teacher-dashboard-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .teacher-dashboard-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 0.75rem 1rem;
        border-radius: 999px;
        background: var(--admin-surface-3);
        color: var(--admin-primary);
        font-weight: 600;
        text-decoration: none;
        transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }

    .teacher-dashboard-tab:hover,
    .teacher-dashboard-tab.active {
        background: var(--admin-primary);
        color: #fff;
        transform: translateY(-1px);
    }
</style>
