<div class="teacher-dashboard-tabs mb-4">
    <a href="{{ route('teacher.dashboard.index') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.index') ? 'active' : '' }}">
        Tong quan
    </a>
    <a href="{{ route('teacher.dashboard.courses') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.courses') ? 'active' : '' }}">
        Khoa hoc cua toi
    </a>
    <a href="{{ route('teacher.dashboard.earnings') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.earnings') ? 'active' : '' }}">
        Doanh thu
    </a>
    <a href="{{ route('teacher.dashboard.payouts') }}"
        class="teacher-dashboard-tab {{ request()->routeIs('teacher.dashboard.payouts') ? 'active' : '' }}">
        Rut tien
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
