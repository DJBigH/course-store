@php
    $teacherStudent = auth('students')->user();
    $teacherLocale = session('locale', 'vi');
    if (!in_array($teacherLocale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
        $teacherLocale = 'vi';
    }
    $teacherUnreadCount = $teacherStudent?->unreadNotifications()->count() ?? 0;
@endphp

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3 d-flex align-items-center gap-3" href="{{ route('teacher.dashboard.index') }}">
        <span class="teacher-brand-mark">
            <i class="fas fa-chalkboard-user"></i>
        </span>
        <span class="teacher-brand-copy">
            <strong>{{ setting('site_name', 'BigK Udemy') }}</strong>
            <small>{{ __('teacher::dashboard.brand.studio') }}</small>
        </span>
    </a>

    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" type="button">
        <i class="fas fa-bars"></i>
    </button>

    <div class="d-none d-md-flex align-items-center ms-auto me-0 me-md-3 my-2 my-md-0 teacher-header-meta">
        <span class="teacher-header-pill">
            <i class="fas fa-globe"></i>
            {{ strtoupper($teacherLocale) }}
        </span>
        <a href="{{ route('home', ['locale' => $teacherLocale]) }}" class="teacher-header-link">
            {{ __('teacher::dashboard.header.view_site') }}
        </a>
        <a href="{{ route('students.account.index', ['locale' => $teacherLocale]) }}" class="teacher-header-link">
            {{ __('teacher::dashboard.header.student_account') }}
        </a>
    </div>

    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4 align-items-center">
        <li class="nav-item me-2">
            <button type="button" class="theme-toggle-admin" data-admin-theme-toggle
                title="{{ __('teacher::dashboard.header.switch_to_dark') }}"
                aria-label="{{ __('teacher::dashboard.header.switch_to_dark') }}" aria-pressed="false">
                <i class="fas fa-moon theme-toggle-admin__icon-dark" aria-hidden="true"></i>
                <i class="fas fa-sun theme-toggle-admin__icon-light" aria-hidden="true"></i>
                <span data-admin-theme-label>{{ __('teacher::dashboard.header.dark_mode') }}</span>
            </button>
        </li>

        <li class="nav-item me-2">
            <a class="nav-link position-relative" href="{{ route('students.notifications.index', ['locale' => $teacherLocale]) }}">
                <i class="fas fa-bell"></i>
                @if ($teacherUnreadCount)
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                        {{ $teacherUnreadCount }}
                    </span>
                @endif
            </a>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="teacherNavbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="teacherNavbarDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('students.account.index', ['locale' => $teacherLocale]) }}">
                        {{ __('teacher::dashboard.header.student_account') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('teacher.account.status', ['locale' => $teacherLocale]) }}">
                        {{ __('teacher::dashboard.header.teacher_profile') }}
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider" />
                </li>
                <li>
                    <form action="{{ route('clients-logout', ['locale' => $teacherLocale]) }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            {{ __('teacher::dashboard.header.logout') }}
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<style>
    .teacher-brand-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.95), rgba(34, 197, 94, 0.85));
        color: #fff;
        box-shadow: 0 14px 26px rgba(14, 165, 233, 0.18);
    }

    .teacher-brand-copy {
        display: flex;
        flex-direction: column;
        line-height: 1.05;
        color: #fff;
    }

    .teacher-brand-copy strong {
        font-size: 0.98rem;
        letter-spacing: 0.02em;
    }

    .teacher-brand-copy small {
        color: rgba(255, 255, 255, 0.66);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    .teacher-header-meta {
        gap: 0.65rem;
    }

    .teacher-header-pill,
    .teacher-header-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 38px;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        text-decoration: none;
        color: rgba(255, 255, 255, 0.88);
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: 0.18s ease;
    }

    .teacher-header-link:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.14);
        border-color: rgba(255, 255, 255, 0.18);
    }
</style>

