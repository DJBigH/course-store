@php
    $teacherStudent = auth('students')->user();
    $teacherLocale = session('locale', 'vi');
    if (!in_array($teacherLocale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
        $teacherLocale = 'vi';
    }
    $teacherNotificationSummary = app(\Modules\Teacher\src\Support\TeacherNotificationCenter::class)->summary($teacherStudent, 6);
    $teacherUnreadCount = $teacherNotificationSummary['count'] ?? 0;
    $teacherNotifications = $teacherNotificationSummary['items'] ?? collect();
    $teacherLocaleOptions = [
        'vi' => ['short' => 'VI', 'label' => __('teacher::dashboard.header.locales.vi'), 'icon' => 'VN'],
        'en' => ['short' => 'EN', 'label' => __('teacher::dashboard.header.locales.en'), 'icon' => 'EN'],
        'ko' => ['short' => 'KO', 'label' => __('teacher::dashboard.header.locales.ko'), 'icon' => 'KO'],
        'ja' => ['short' => 'JA', 'label' => __('teacher::dashboard.header.locales.ja'), 'icon' => 'JA'],
        'zh' => ['short' => 'ZH', 'label' => __('teacher::dashboard.header.locales.zh'), 'icon' => 'ZH'],
    ];
    $teacherCurrentUrl = url()->current();
    if (request()->getQueryString()) {
        $teacherCurrentUrl .= '?' . request()->getQueryString();
    }
@endphp

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3 d-flex align-items-center gap-3 teacher-topnav-brand" href="{{ route('teacher.dashboard.index') }}">
        <span class="teacher-brand-mark">
            <i class="fas fa-chalkboard-user"></i>
        </span>
        <span class="teacher-brand-copy">
            <strong class="mb-1">{{ setting('site_name', 'BigK Udemy') }}</strong>
            <small>{{ __('teacher::dashboard.brand.studio') }}</small>
        </span>
    </a>

    <button class="btn btn-link btn-sm order-1 order-lg-0 me-3 me-lg-0 teacher-sidebar-toggle" id="sidebarToggle" type="button"
        aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="d-none d-md-flex align-items-center ms-auto me-0 me-md-3 my-2 my-md-0 teacher-header-meta">
        <div class="dropdown">
            <button class="teacher-header-link dropdown-toggle teacher-header-link--dropdown" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-globe"></i>
                <span class="teacher-header-locale-badge">{{ $teacherLocaleOptions[$teacherLocale]['icon'] }}</span>
                <span>{{ $teacherLocaleOptions[$teacherLocale]['short'] }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end teacher-header-locale-menu">
                @foreach ($teacherLocaleOptions as $locale => $option)
                    <li>
                        <a class="dropdown-item d-flex align-items-center justify-content-between gap-3 {{ $teacherLocale === $locale ? 'active' : '' }}"
                            href="{{ route('teacher.dashboard.locale', ['locale' => $locale, 'redirect' => $teacherCurrentUrl]) }}">
                            <span class="d-inline-flex align-items-center gap-2">
                                @if ($teacherLocale === $locale)
                                    <i class="fas fa-check teacher-header-locale-check" aria-hidden="true"></i>
                                @else
                                    <span class="teacher-header-locale-check teacher-header-locale-check--placeholder" aria-hidden="true"></span>
                                @endif
                                <span class="teacher-header-locale-badge">{{ $option['icon'] }}</span>
                                <span>{{ $option['label'] }}</span>
                            </span>
                            <small>{{ $option['short'] }}</small>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <a href="{{ route('home', ['locale' => $teacherLocale]) }}" class="teacher-header-link"
            target="_blank" rel="noopener noreferrer">
            {{ __('teacher::dashboard.header.view_site') }}
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
            <div class="dropdown">
                <a class="nav-link position-relative" href="#" id="teacherNotificationDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    @if ($teacherUnreadCount)
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                            {{ $teacherUnreadCount }}
                        </span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end teacher-notification-menu" aria-labelledby="teacherNotificationDropdown">
                    <div class="teacher-notification-menu__header">
                        <strong>{{ __('teacher::dashboard.notifications.title') }}</strong>
                        <a href="{{ route('teacher.dashboard.notifications') }}">{{ __('teacher::dashboard.notifications.view_all_cta') }}</a>
                    </div>
                    <div class="teacher-notification-menu__list">
                        @forelse ($teacherNotifications as $notification)
                            @php
                                $severityClass = match ($notification['severity'] ?? 'secondary') {
                                    'success' => 'success',
                                    'warning' => 'warning',
                                    'danger', 'error', 'critical' => 'danger',
                                    'info', 'primary' => 'primary',
                                    default => 'secondary',
                                };
                            @endphp
                            <a class="teacher-notification-menu__item teacher-notification-menu__item--{{ $severityClass }}"
                                href="{{ $notification['url'] ?? route('teacher.dashboard.notifications') }}">
                                <span class="teacher-notification-menu__icon">
                                    <i class="{{ $notification['icon'] ?? 'fas fa-bell' }}"></i>
                                </span>
                                <span class="teacher-notification-menu__body">
                                    <span class="teacher-notification-menu__title">{{ $notification['title'] ?? __('teacher::dashboard.notifications.types.system') }}</span>
                                    <span class="teacher-notification-menu__message">{{ $notification['message'] ?? '' }}</span>
                                    <span class="teacher-notification-menu__time">{{ optional($notification['created_at'] ?? null)->diffForHumans() }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="teacher-notification-menu__empty">
                                {{ __('teacher::dashboard.notifications.empty') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="teacherNavbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="teacherNavbarDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('teacher.dashboard.profile') }}">
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
    .teacher-topnav-brand {
        flex: 0 1 auto;
        min-width: 0;
        max-width: calc(100% - 4.5rem);
        margin-right: 0.5rem;
    }

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
        min-width: 0;
    }

    .teacher-brand-copy strong {
        font-size: 0.98rem;
        letter-spacing: 0.02em;
        white-space: nowrap;
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

    .teacher-sidebar-toggle {
        position: relative;
        z-index: 1042;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        color: rgba(255, 255, 255, 0.82);
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        text-decoration: none;
    }

    .teacher-sidebar-toggle:hover,
    .teacher-sidebar-toggle:focus {
        color: #fff;
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.18);
        box-shadow: none;
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

    .teacher-header-link--dropdown::after {
        margin-left: 0.35rem;
    }

    .teacher-header-locale-menu {
        border-radius: 16px;
        padding: 0.45rem;
        min-width: 220px;
        border: 1px solid var(--admin-topnav-border);
        background: var(--admin-surface);
        box-shadow: var(--admin-dropdown-shadow);
    }

    .teacher-header-locale-menu .dropdown-item {
        border-radius: 12px;
        color: var(--admin-text);
        padding: 0.65rem 0.8rem;
    }

    .teacher-header-locale-menu .dropdown-item small {
        color: var(--admin-muted);
        font-weight: 700;
    }

    .teacher-header-locale-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 0.45rem;
        border-radius: 999px;
        background: var(--admin-surface-3);
        border: 1px solid var(--admin-border);
        color: var(--admin-primary);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .teacher-header-locale-check {
        color: var(--admin-primary);
        font-size: 0.8rem;
        width: 14px;
        text-align: center;
    }

    .teacher-header-locale-check--placeholder {
        display: inline-block;
    }

    .teacher-header-locale-menu .dropdown-item.active,
    .teacher-header-locale-menu .dropdown-item:active,
    .teacher-header-locale-menu .dropdown-item:hover {
        background: var(--admin-surface-3);
        color: var(--admin-primary);
    }

    .teacher-header-locale-menu .dropdown-item.active .teacher-header-locale-badge,
    .teacher-header-locale-menu .dropdown-item:hover .teacher-header-locale-badge {
        background: color-mix(in srgb, var(--admin-primary) 16%, var(--admin-surface));
        border-color: color-mix(in srgb, var(--admin-primary) 36%, var(--admin-border));
    }

    .teacher-notification-menu {
        width: min(380px, calc(100vw - 2rem));
        padding: 0;
        overflow: hidden;
        border-radius: 20px;
        border: 1px solid var(--admin-topnav-border);
        background: var(--admin-surface);
        box-shadow: var(--admin-dropdown-shadow);
    }

    .teacher-notification-menu__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.95rem 1rem;
        border-bottom: 1px solid var(--admin-border);
    }

    .teacher-notification-menu__header strong {
        color: var(--admin-text);
    }

    .teacher-notification-menu__header a {
        color: var(--admin-primary);
        font-size: 0.86rem;
        font-weight: 700;
        text-decoration: none;
    }

    .teacher-notification-menu__list {
        max-height: 430px;
        overflow-y: auto;
    }

    .teacher-notification-menu__item {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 0.85rem;
        padding: 0.9rem 1rem;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid color-mix(in srgb, var(--admin-border) 70%, transparent);
    }

    .teacher-notification-menu__item:last-child {
        border-bottom: 0;
    }

    .teacher-notification-menu__item:hover {
        background: var(--admin-hover-bg);
    }

    .teacher-notification-menu__icon {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-size: 1rem;
    }

    .teacher-notification-menu__item--primary .teacher-notification-menu__icon,
    .teacher-notification-menu__item--secondary .teacher-notification-menu__icon {
        color: #2563eb;
        background: rgba(37, 99, 235, 0.12);
    }

    .teacher-notification-menu__item--success .teacher-notification-menu__icon {
        color: #15803d;
        background: rgba(34, 197, 94, 0.14);
    }

    .teacher-notification-menu__item--warning .teacher-notification-menu__icon {
        color: #b45309;
        background: rgba(245, 158, 11, 0.14);
    }

    .teacher-notification-menu__item--danger .teacher-notification-menu__icon {
        color: #dc2626;
        background: rgba(239, 68, 68, 0.14);
    }

    .teacher-notification-menu__body {
        min-width: 0;
    }

    .teacher-notification-menu__title,
    .teacher-notification-menu__message,
    .teacher-notification-menu__time {
        display: block;
    }

    .teacher-notification-menu__title {
        color: var(--admin-text);
        font-weight: 700;
        margin-bottom: 0.15rem;
    }

    .teacher-notification-menu__message {
        color: var(--admin-link-muted);
        font-size: 0.88rem;
        line-height: 1.45;
    }

    .teacher-notification-menu__time {
        color: var(--admin-muted);
        font-size: 0.8rem;
        margin-top: 0.35rem;
    }

    .teacher-notification-menu__empty {
        padding: 1rem;
        color: var(--admin-muted);
    }

    @media (max-width: 767.98px) {
        .teacher-topnav-brand {
            gap: 0.75rem !important;
            max-width: calc(100% - 4rem);
            padding-left: 1rem !important;
            margin-right: 0.25rem;
        }

        .teacher-brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
        }

        .teacher-brand-copy strong {
            font-size: 0.8rem;
        }

        .teacher-brand-copy small {
            font-size: 0.66rem;
            letter-spacing: 0.08em;
        }

        .teacher-sidebar-toggle {
            width: 40px;
            height: 40px;
            margin-right: 0.75rem !important;
        }
    }
</style>
