<div class="teacher-dashboard-tabs mb-4">
    <a href="{{ route('teacher.dashboard.index') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.index') ? 'active' : '' }}">
        {{ __('courses::teacher/messages.nav.overview') }}
    </a>
    <a href="{{ route('teacher.dashboard.courses') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.courses') ? 'active' : '' }}">
        {{ __('courses::teacher/messages.nav.courses') }}
    </a>
    <a href="{{ route('teacher.dashboard.earnings') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.earnings') ? 'active' : '' }}">
        {{ __('courses::teacher/messages.nav.earnings') }}
    </a>
    @php($quizLocked = !(($teacher ?? null)?->packageHasFeature('can_manage_quizzes')))
    @if (!$quizLocked)
        <a href="{{ route('teacher.dashboard.courses') }}"
            class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.quizzes*') ? 'active' : '' }}"
            title="{{ __('courses::teacher/messages.nav.quiz.title') }}">
            🧩 {{ __('courses::teacher/messages.nav.quiz.label') }}
        </a>
    @else
        @include('teacher::clients.dashboard.partials.package_feature_notice', [
            'title' => __('courses::teacher/messages.package_features.quiz_management_locked'),
            'message' => __('courses::teacher/messages.package_features.quiz_management_locked_desc'),
            'upgradeUrl' => route('teacher.dashboard.package.upgrade'),
            'showUpgrade' => true,
        ])
        <a href="{{ route('teacher.dashboard.package.upgrade') }}"
            class="teacher-dashboard-tab teacher-dashboard-tab--locked"
            title="{{ __('courses::teacher/messages.nav.quiz.locked_title') }}">
            🧩 {{ __('courses::teacher/messages.nav.quiz.label') }} <span class="teacher-dashboard-tab__lock">{{ __('courses::teacher/messages.nav.quiz.locked_badge') }}</span>
        </a>
    @endif
    @if (($teacher ?? null)?->packageHasFeature('can_send_promotions'))
        <a href="{{ route('teacher.dashboard.promotions') }}"
            class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.promotions') ? 'active' : '' }}">
            {{ __('courses::teacher/messages.nav.promotions') }}
        </a>
    @endif
    @if (($teacher ?? null)?->packageHasFeature('can_sell_bundles'))
        <a href="{{ route('teacher.dashboard.bundles') }}"
            class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.bundles*') ? 'active' : '' }}">
            {{ __('courses::teacher/messages.nav.bundles') }}
        </a>
    @endif
    <a href="{{ route('teacher.dashboard.payouts') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.payouts') ? 'active' : '' }}">
        {{ __('courses::teacher/messages.nav.payouts') }}
    </a>
    <a href="{{ route('teacher.dashboard.cancellation') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.cancellation') ? 'active' : '' }}">
        <i class="fa-solid fa-user-slash me-1"></i>{{ __('courses::teacher/messages.nav.cancellation') }}
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

    .teacher-dashboard-tab--locked {
        opacity: 0.72;
        border: 1px dashed rgba(148, 163, 184, 0.55);
        background: rgba(148, 163, 184, 0.08);
    }

    .teacher-dashboard-tab__lock {
        display: inline-flex;
        align-items: center;
        padding: 0.18rem 0.45rem;
        margin-left: 0.45rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        color: #fff;
        background: #f59e0b;
    }
</style>
