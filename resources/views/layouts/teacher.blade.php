<!DOCTYPE html>
@php
    $teacherLocale = session('locale', app()->getLocale());
    if (!in_array($teacherLocale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
        $teacherLocale = 'vi';
    }
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta name="color-scheme" content="light dark" />
    <title>{{ $pageTitle ?? 'Teacher Portal' }} - BigK Udemy</title>
    <link rel="shortcut icon" href="{{ asset('clients/assets/LOGO-DSCONS-FAVICON.png') }}" type="image/x-icon">
    <script>
        (() => {
            const storageKey = 'admin-theme';
            const root = document.documentElement;
            const savedTheme = localStorage.getItem(storageKey);
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme === 'light' || savedTheme === 'dark'
                ? savedTheme
                : (systemPrefersDark ? 'dark' : 'light');

            root.dataset.theme = theme;
            root.style.colorScheme = theme;
        })();
    </script>
    <link href="{{ asset('backend/css/styles.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --admin-bg: #f5f8fc;
            --admin-surface: #ffffff;
            --admin-border: #dbe4f0;
            --admin-text: #0f172a;
            --admin-muted: #64748b;
            --admin-primary: #2563eb;
            --teacher-accent: #0ea5e9;
            --teacher-accent-2: #22c55e;
            --teacher-glow: rgba(14, 165, 233, 0.12);
            --teacher-warm: #f59e0b;
            --admin-topnav-bg: rgba(255, 255, 255, 0.94);
            --admin-topnav-border: rgba(148, 163, 184, 0.12);
            --admin-sidebar-gradient:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.22), transparent 28%),
                linear-gradient(180deg, #0f172a 0%, #172554 100%);
            --admin-card-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
            --admin-dropdown-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            --admin-form-bg: #ffffff;
            --admin-subtle-bg: #f8fafc;
            --admin-hover-bg: #f1f5f9;
            --admin-input-bg: #ffffff;
            --admin-link: #0f172a;
            --admin-link-muted: #475569;
            --admin-surface-2: #f8fafc;
            --admin-surface-3: #eef4fb;
            --admin-glass-bg: rgba(255, 255, 255, 0.75);
            --admin-glass-border: rgba(255, 255, 255, 0.5);
            --admin-success-bg: #dcfce7;
            --admin-success-text: #166534;
            --admin-success-border: #86efac;
            --admin-danger-bg: #fee2e2;
            --admin-danger-text: #991b1b;
            --admin-danger-border: #fca5a5;
            --admin-warning-bg: #fef3c7;
            --admin-warning-text: #92400e;
            --admin-warning-border: #fcd34d;
            --admin-info-bg: #dbeafe;
            --admin-info-text: #1d4ed8;
            --admin-info-border: #93c5fd;
            --admin-sidebar-bg: #ffffff;
            --admin-sidebar-text: #0f172a;
            --admin-sidebar-hover: #f1f5f9;
            --admin-sidebar-border: #e2e8f0;
            --admin-sidebar-icon: #475569;
            --admin-history-bg: #f8fafc;
            --admin-history-text: #1e293b;
        }

        html[data-theme="dark"] {
            --admin-bg: #070d19;
            --admin-surface: #0f172a;
            --admin-border: #1e293b;
            --admin-text: #f1f5f9;
            --admin-muted: #94a3b8;
            --admin-primary: #3b82f6;
            --teacher-accent: #0ea5e9;
            --teacher-accent-2: #10b981;
            --teacher-glow: rgba(14, 165, 233, 0.15);
            --teacher-warm: #f59e0b;
            --admin-topnav-bg: rgba(8, 15, 31, 0.88);
            --admin-topnav-border: rgba(30, 41, 59, 0.8);
            --admin-sidebar-gradient:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 28%),
                linear-gradient(180deg, #020617 0%, #0f172a 100%);
            --admin-card-shadow: 0 14px 30px rgba(2, 6, 23, 0.6);
            --admin-dropdown-shadow: 0 18px 35px rgba(2, 6, 23, 0.75);
            --admin-form-bg: #111827;
            --admin-subtle-bg: #1e293b;
            --admin-hover-bg: #1e293b;
            --admin-input-bg: #0f172a;
            --admin-link: #f1f5f9;
            --admin-link-muted: #94a3b8;
            --admin-surface-2: #0f172a;
            --admin-surface-3: #1e293b;
            --admin-glass-bg: rgba(15, 23, 42, 0.65);
            --admin-glass-border: rgba(255, 255, 255, 0.08);
            --admin-success-bg: rgba(16, 185, 129, 0.15);
            --admin-success-text: #34d399;
            --admin-success-border: rgba(16, 185, 129, 0.25);
            --admin-danger-bg: rgba(239, 68, 68, 0.15);
            --admin-danger-text: #f87171;
            --admin-danger-border: rgba(239, 68, 68, 0.25);
            --admin-warning-bg: rgba(245, 158, 11, 0.15);
            --admin-warning-text: #fbbf24;
            --admin-warning-border: rgba(245, 158, 11, 0.25);
            --admin-info-bg: rgba(59, 130, 246, 0.15);
            --admin-info-text: #60a5fa;
            --admin-info-border: rgba(59, 130, 246, 0.25);
            --admin-sidebar-bg: #0f172a;
            --admin-sidebar-text: rgba(255, 255, 255, 0.78);
            --admin-sidebar-hover: rgba(255, 255, 255, 0.1);
            --admin-sidebar-border: rgba(255, 255, 255, 0.08);
            --admin-sidebar-icon: rgba(255, 255, 255, 0.6);
            --admin-history-bg: rgba(15, 23, 42, 0.58);
            --admin-history-text: #f8fbff;
        }

        body.sb-nav-fixed {
            background:
                radial-gradient(circle at top, var(--teacher-glow), transparent 22%),
                radial-gradient(circle at right top, rgba(34, 197, 94, 0.06), transparent 24%),
                linear-gradient(180deg, var(--admin-surface-2) 0%, var(--admin-bg) 100%);
            color: var(--admin-text);
        }

        #layoutSidenav,
        #layoutSidenav_content,
        #layoutSidenav_content main {
            background:
                radial-gradient(circle at top, var(--teacher-glow), transparent 22%),
                radial-gradient(circle at right top, rgba(34, 197, 94, 0.06), transparent 24%),
                linear-gradient(180deg, var(--admin-surface-2) 0%, var(--admin-bg) 100%);
        }

        #layoutSidenav_content {
            min-height: calc(100vh - 72px);
        }

        #layoutSidenav_content main {
            min-height: 100%;
            padding-bottom: 2rem;
        }

        .sb-nav-fixed #layoutSidenav #layoutSidenav_nav .sb-sidenav {
            padding-top: 72px;
        }

        .sb-nav-fixed #layoutSidenav #layoutSidenav_content {
            padding-left: 225px;
            top: 72px;
            transition: padding-left 0.15s ease-in-out, margin 0.15s ease-in-out;
        }

        @media (min-width: 992px) {
            .sb-sidenav-toggled #layoutSidenav #layoutSidenav_content {
                padding-left: 0;
                margin-left: 0;
            }
        }

        a {
            color: var(--admin-link);
        }

        a:hover {
            color: var(--admin-primary);
        }

        .container-fluid {
            max-width: 1600px;
        }

        .sb-topnav {
            min-height: 72px;
            background: var(--admin-topnav-bg) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--admin-topnav-border);
            position: sticky;
            top: 0;
            z-index: 1035;
        }

        .sb-topnav::after {
            content: "";
            position: absolute;
            inset: auto 0 0;
            height: 2px;
            background: linear-gradient(90deg, var(--teacher-accent), var(--teacher-accent-2), var(--teacher-warm));
        }

        .sb-sidenav {
            background: var(--admin-sidebar-bg);
            border-right: 1px solid var(--admin-sidebar-border);
        }

        html[data-theme="dark"] .sb-sidenav {
            background: var(--admin-sidebar-gradient);
            border-right: 0;
        }

        .sb-sidenav .sb-sidenav-menu .nav .nav-link {
            margin: 0.18rem 0.75rem;
            padding: 0.82rem 1rem;
            border-radius: 14px;
            color: var(--admin-sidebar-text);
            transition: 0.2s ease;
        }

        .sb-sidenav .sb-sidenav-menu .nav .nav-link:hover,
        .sb-sidenav .sb-sidenav-menu .nav .nav-link.active,
        .sb-sidenav .sb-sidenav-menu .nav .nav-link.is-active {
            background: var(--admin-sidebar-hover);
            color: var(--admin-primary);
        }

        html[data-theme="dark"] .sb-sidenav .sb-sidenav-menu .nav .nav-link:hover,
        html[data-theme="dark"] .sb-sidenav .sb-sidenav-menu .nav .nav-link.active,
        html[data-theme="dark"] .sb-sidenav .sb-sidenav-menu .nav .nav-link.is-active {
            color: #fff;
        }

        .sb-nav-link-icon {
            color: var(--admin-sidebar-icon) !important;
            transition: color 0.2s ease;
        }

        .active .sb-nav-link-icon,
        .is-active .sb-nav-link-icon,
        .nav-link:hover .sb-nav-link-icon {
            color: var(--admin-primary) !important;
        }

        html[data-theme="dark"] .active .sb-nav-link-icon,
        html[data-theme="dark"] .is-active .sb-nav-link-icon,
        html[data-theme="dark"] .nav-link:hover .sb-nav-link-icon {
            color: #fff !important;
        }

        .sb-sidenav .sb-sidenav-menu .nav .sb-sidenav-menu-heading {
            padding: 1rem 1.5rem 0.65rem;
            color: var(--admin-muted);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .card,
        .teacher-panel {
            background: var(--admin-surface);
            border: 1px solid color-mix(in srgb, var(--admin-border) 85%, transparent);
            box-shadow: var(--admin-card-shadow) !important;
            color: var(--admin-text);
            border-radius: 24px;
        }

        .teacher-panel {
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .teacher-panel::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 1px;
            background: linear-gradient(90deg, transparent, color-mix(in srgb, var(--teacher-accent) 55%, transparent), transparent);
            opacity: 0.95;
        }

        .teacher-stat-card {
            padding: 1.35rem;
            border-radius: 22px;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-card-shadow);
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .teacher-stat-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, var(--teacher-accent), var(--teacher-accent-2));
        }

        .teacher-stat-card:hover {
            transform: translateY(-2px);
            border-color: color-mix(in srgb, var(--teacher-accent) 35%, var(--admin-border));
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.1);
        }

        .teacher-stat-card__label {
            color: var(--admin-muted);
            font-size: 0.92rem;
            margin-bottom: 0.45rem;
        }

        .teacher-stat-card__value {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--admin-text);
        }

        .teacher-subtle-card {
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            background: var(--admin-subtle-bg);
            padding: 1rem 1.1rem;
        }

        .teacher-hero {
            position: relative;
            overflow: hidden;
            padding: 1.6rem;
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(34, 197, 94, 0.18), transparent 24%),
                radial-gradient(circle at left center, rgba(14, 165, 233, 0.18), transparent 28%),
                linear-gradient(135deg, #081526 0%, #0f2742 58%, #11355b 100%);
            color: #fff;
            box-shadow: 0 24px 54px rgba(8, 21, 38, 0.22);
        }

        .teacher-hero--dashboard {
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(260px, 0.8fr);
            gap: 1rem;
            align-items: stretch;
        }

        .teacher-hero__content,
        .teacher-hero__rail {
            position: relative;
            z-index: 1;
        }

        .teacher-hero__rail {
            display: grid;
            gap: 0.85rem;
            align-content: end;
        }

        .teacher-hero__mini {
            padding: 1rem 1rem 1.05rem;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
        }

        .teacher-hero__mini span {
            display: block;
            font-size: 0.86rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.35rem;
        }

        .teacher-hero__mini strong {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
        }

        .teacher-hero__mini--glass {
            background: rgba(14, 165, 233, 0.14);
        }

        .teacher-hero::after {
            content: "";
            position: absolute;
            inset: auto -80px -90px auto;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18), transparent 70%);
            pointer-events: none;
        }

        .teacher-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .teacher-hero__title {
            margin: 1rem 0 0.65rem;
            font-size: clamp(1.8rem, 2.8vw, 2.45rem);
            font-weight: 800;
            line-height: 1.1;
        }

        .teacher-hero__desc {
            max-width: 720px;
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.78);
        }

        .teacher-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .teacher-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 40px;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--admin-surface) 84%, transparent);
            border: 1px solid color-mix(in srgb, var(--admin-border) 90%, transparent);
            color: var(--admin-text);
            font-weight: 600;
        }

        .teacher-chip--dark {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.16);
            color: #fff;
        }

        .teacher-section-title {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 0.9rem;
            margin-bottom: 1.1rem;
        }

        .teacher-section-title h3,
        .teacher-section-title h4 {
            margin: 0;
            font-weight: 800;
        }

        .teacher-status-badge {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            padding: 0.5rem 0.9rem;
            border-radius: 999px;
            background: var(--admin-success-bg);
            border: 1px solid var(--admin-success-border);
            color: var(--admin-success-text);
            font-weight: 700;
        }

        .teacher-soft-link {
            color: var(--teacher-accent);
            font-weight: 700;
            text-decoration: none;
        }

        .teacher-soft-link:hover {
            color: var(--admin-primary);
        }

        .teacher-page-shell {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .teacher-course-card strong {
            font-size: 1.05rem;
        }

        .teacher-panel .pagination {
            --bs-pagination-bg: var(--admin-surface);
            --bs-pagination-color: var(--admin-text);
            --bs-pagination-border-color: var(--admin-border);
            --bs-pagination-hover-bg: var(--admin-surface-3);
            --bs-pagination-hover-color: var(--admin-primary);
            --bs-pagination-hover-border-color: var(--admin-border);
            --bs-pagination-focus-bg: var(--admin-surface-3);
            --bs-pagination-focus-color: var(--admin-primary);
            --bs-pagination-focus-box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--admin-primary) 18%, transparent);
            --bs-pagination-active-bg: var(--admin-primary);
            --bs-pagination-active-border-color: var(--admin-primary);
            --bs-pagination-active-color: #fff;
            --bs-pagination-disabled-bg: var(--admin-subtle-bg);
            --bs-pagination-disabled-color: var(--admin-muted);
        }

        .app-shell-footer {
            background: linear-gradient(180deg, color-mix(in srgb, var(--admin-surface) 96%, transparent), var(--admin-surface-2));
            border-top: 1px solid var(--admin-border);
            color: var(--admin-muted);
        }

        .app-shell-footer a {
            color: var(--admin-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .app-shell-footer a:hover {
            color: var(--teacher-accent);
        }

        .table {
            color: var(--admin-text);
        }

        .table thead th {
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--admin-muted);
            background: var(--admin-subtle-bg);
            border-bottom: 1px solid var(--admin-border);
        }

        .table > :not(caption) > * > * {
            background-color: transparent;
            border-color: var(--admin-border);
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border-color: var(--admin-border);
            min-height: 44px;
            background: var(--admin-input-bg);
            color: var(--admin-text);
        }

        .form-control:focus,
        .form-select:focus {
            background: var(--admin-input-bg);
            color: var(--admin-text);
            border-color: color-mix(in srgb, var(--admin-primary) 60%, var(--admin-border));
            box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--admin-primary) 18%, transparent);
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
        }

        .text-muted,
        .small.text-muted,
        .text-secondary {
            color: var(--admin-muted) !important;
        }

        .text-dark,
        .text-body,
        .text-black,
        .text-reset {
            color: var(--admin-text) !important;
        }

        .alert {
            border-width: 1px;
            border-style: solid;
        }

        .alert-success {
            background: var(--admin-success-bg);
            color: var(--admin-success-text);
            border-color: var(--admin-success-border);
        }

        .alert-danger {
            background: var(--admin-danger-bg);
            color: var(--admin-danger-text);
            border-color: var(--admin-danger-border);
        }

        .alert-warning {
            background: var(--admin-warning-bg);
            color: var(--admin-warning-text);
            border-color: var(--admin-warning-border);
        }

        .alert-info {
            background: var(--admin-info-bg);
            color: var(--admin-info-text);
            border-color: var(--admin-info-border);
        }

        .theme-toggle-admin {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            min-height: 40px;
            padding: 0.4rem 0.9rem;
            border: 1px solid var(--admin-border);
            border-radius: 999px;
            color: var(--admin-text);
            background: var(--admin-surface-3);
            transition: 0.2s ease;
        }

        .theme-toggle-admin:hover {
            color: var(--admin-primary);
            background: var(--admin-hover-bg);
            border-color: var(--admin-primary);
        }

        html[data-theme="dark"] .theme-toggle-admin {
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
        }

        html[data-theme="dark"] .theme-toggle-admin:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.28);
        }

        .theme-toggle-admin__icon-light {
            display: none;
        }

        html[data-theme="dark"] .theme-toggle-admin__icon-dark {
            display: none;
        }

        html[data-theme="dark"] .theme-toggle-admin__icon-light {
            display: inline-block;
        }

        .teacher-shell-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            color: var(--admin-muted);
            font-size: 0.9rem;
        }

        @media (max-width: 991.98px) {
            .teacher-panel {
                padding: 1rem;
            }

            .teacher-hero--dashboard {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @yield('stylesheets')
</head>

<body class="sb-nav-fixed">
    @include('part.teacher.header')
    <div id="layoutSidenav">
        @include('part.teacher.sidebar')
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    @include('part.backend.page_title')
                    @yield('content')
                </div>
            </main>
            @include('part.backend.footer')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('backend/js/scripts.js') }}"></script>
    <script>
        (() => {
            const storageKey = 'admin-theme';
            const root = document.documentElement;
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

            const applyTheme = (theme) => {
                root.dataset.theme = theme;
                root.style.colorScheme = theme;

                document.querySelectorAll('[data-admin-theme-toggle]').forEach((button) => {
                    const isDark = theme === 'dark';
                    const nextThemeLabel = isDark ? @json(__('teacher/header.light_mode')) : @json(__('teacher/header.dark_mode'));
                    const nextThemeTitle = isDark ? @json(__('teacher/header.switch_to_light')) : @json(__('teacher/header.switch_to_dark'));
                    const label = button.querySelector('[data-admin-theme-label]');

                    button.setAttribute('aria-pressed', String(isDark));
                    button.setAttribute('title', nextThemeTitle);
                    button.setAttribute('aria-label', nextThemeTitle);

                    if (label) {
                        label.textContent = nextThemeLabel;
                    }
                });
            };

            const persistTheme = (theme) => {
                localStorage.setItem(storageKey, theme);
                applyTheme(theme);
            };

            document.addEventListener('click', (event) => {
                const toggle = event.target.closest('[data-admin-theme-toggle]');
                if (!toggle) {
                    return;
                }

                const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                persistTheme(nextTheme);
            });

            const syncSystemTheme = (event) => {
                const savedTheme = localStorage.getItem(storageKey);
                if (savedTheme === 'light' || savedTheme === 'dark') {
                    return;
                }

                applyTheme(event.matches ? 'dark' : 'light');
            };

            if (typeof mediaQuery.addEventListener === 'function') {
                mediaQuery.addEventListener('change', syncSystemTheme);
            } else if (typeof mediaQuery.addListener === 'function') {
                mediaQuery.addListener(syncSystemTheme);
            }

            applyTheme(root.dataset.theme || 'light');
        })();
    </script>
    <script>
        (() => {
            const sanitizeDigits = (value) => String(value ?? '').replace(/[^\d]/g, '');
            const formatDigits = (value) => {
                const digits = sanitizeDigits(value);

                if (!digits) {
                    return '';
                }

                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            };

            const initMoneyInput = (displayEl) => {
                if (!displayEl || displayEl.dataset.moneyInputBound === 'true') {
                    return null;
                }

                const targetId = displayEl.getAttribute('data-money-target');
                const hiddenEl = targetId ? document.getElementById(targetId) : null;

                const sync = () => {
                    const digits = sanitizeDigits(displayEl.value);

                    if (hiddenEl) {
                        hiddenEl.value = digits;
                    }

                    displayEl.value = formatDigits(digits);
                    displayEl.dispatchEvent(new CustomEvent('teacher:money-input-sync', {
                        bubbles: true,
                        detail: {
                            digits,
                            numericValue: Number(digits || 0),
                            hiddenElement: hiddenEl,
                        },
                    }));
                };

                displayEl.addEventListener('input', sync);
                displayEl.addEventListener('blur', sync);
                displayEl.dataset.moneyInputBound = 'true';
                sync();

                return {
                    displayEl,
                    hiddenEl,
                    sync,
                    getNumericValue: () => Number((hiddenEl?.value ?? sanitizeDigits(displayEl.value)) || 0),
                };
            };

            const initMoneyInputs = (scope = document) => {
                return Array.from(scope.querySelectorAll('[data-money-input]'))
                    .map((element) => initMoneyInput(element))
                    .filter(Boolean);
            };

            window.TeacherMoneyInput = {
                sanitizeDigits,
                formatDigits,
                init: initMoneyInput,
                initAll: initMoneyInputs,
            };

            document.addEventListener('DOMContentLoaded', () => {
                initMoneyInputs(document);
            });
        })();
    </script>
    @yield('scripts')
</body>

</html>
