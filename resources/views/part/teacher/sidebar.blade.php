@php
    $teacherStudent = auth('students')->user();
    $teacherApplication = $teacherStudent?->teacherApplications()?->latest('id')->first();
    $teacherProfile = $teacherStudent?->teacher;
    $teacherIsActive = $teacherProfile && $teacherProfile->status === 'active';
    $teacherStatusText = $teacherIsActive ? 'Kenh da kich hoat' : ($teacherApplication?->display_status ?? 'Chua gui ho so');
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

    .teacher-sidebar-brand {
        margin: 0.9rem 0.85rem 1rem;
        padding: 1rem;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(34, 197, 94, 0.14));
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #fff;
    }

    .teacher-sidebar-brand__eyebrow {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(255, 255, 255, 0.62);
    }

    .teacher-sidebar-brand__title {
        margin-top: 0.45rem;
        font-weight: 800;
        font-size: 1.05rem;
    }

    .teacher-sidebar-brand__meta {
        margin-top: 0.55rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.92);
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

    .teacher-status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #4ade80;
        box-shadow: 0 0 0 6px rgba(74, 222, 128, 0.16);
        margin-right: 0.45rem;
    }
</style>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="teacherSidenav">
        <div class="sb-sidenav-menu">
            <div class="teacher-sidebar-brand">
                <div class="teacher-sidebar-brand__eyebrow">Instructor workspace</div>
                <div class="teacher-sidebar-brand__title">Kenh giang vien</div>
                <div class="teacher-sidebar-brand__meta">
                    <i class="fas fa-signal"></i>
                    {{ $teacherStatusText }}
                </div>
            </div>

            <div class="nav">
                <div class="sb-sidenav-menu-heading mt-2">Teacher Portal</div>

                <a class="nav-link {{ request()->routeIs('teacher.dashboard.index') ? 'active' : '' }}"
                    href="{{ route('teacher.dashboard.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-gauge-high"></i></div>
                    Tong quan
                </a>

                <a class="nav-link {{ request()->routeIs('teacher.account.*') ? 'active' : '' }}"
                    href="{{ $teacherApplication ? route('teacher.account.status', ['locale' => $teacherLocale]) : route('teacher.account.apply', ['locale' => $teacherLocale]) }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-id-card"></i></div>
                    Ho so giang vien
                </a>

                @if ($teacherIsActive)
                    <a class="nav-link {{ request()->routeIs('teacher.dashboard.courses') ? 'active' : '' }}"
                        href="{{ route('teacher.dashboard.courses') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                        Khoa hoc cua toi
                    </a>
                    <a class="nav-link {{ request()->routeIs('teacher.dashboard.earnings') ? 'active' : '' }}"
                        href="{{ route('teacher.dashboard.earnings') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                        Doanh thu
                    </a>
                    <a class="nav-link {{ request()->routeIs('teacher.dashboard.payouts') ? 'active' : '' }}"
                        href="{{ route('teacher.dashboard.payouts') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                        Rut tien
                    </a>
                @endif
            </div>
        </div>

        <div class="sb-sidenav-footer">
            <div class="small">Dang nhap voi</div>
            <div class="sidebar-user-name">{{ $teacherStudent?->name ?? 'Hoc vien' }}</div>
            <div class="sidebar-link-label">
                @if ($teacherIsActive)
                    <span class="teacher-status-dot"></span>Kenh da kich hoat
                @else
                    {{ $teacherStatusText }}
                @endif
            </div>
        </div>
    </nav>
</div>

