<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta name="color-scheme" content="light dark" />
    <title>{{ $pageTitle ?? 'Không tìm thấy trang' }} - BigK Udemy</title>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.3.5/datatables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="{{ asset('backend/css/styles.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --admin-bg: #f4f7fb;
            --admin-surface: #ffffff;
            --admin-border: #dbe4f0;
            --admin-text: #0f172a;
            --admin-muted: #64748b;
            --admin-primary: #2563eb;
            --admin-topnav-bg: rgba(15, 23, 42, 0.96);
            --admin-topnav-border: rgba(148, 163, 184, 0.14);
            --admin-sidebar-gradient:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.28), transparent 28%),
                linear-gradient(180deg, #0f172a 0%, #172554 100%);
            --admin-card-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
            --admin-dropdown-shadow: 0 18px 35px rgba(15, 23, 42, 0.12);
            --admin-form-bg: rgba(255, 255, 255, 0.96);
            --admin-subtle-bg: #f8fafc;
            --admin-notification-unread: #f8fbff;
            --admin-hover-bg: #f8fafc;
            --admin-input-bg: #ffffff;
            --admin-link: #0f172a;
            --admin-link-muted: #475569;
            --admin-surface-2: #f8fafc;
            --admin-surface-3: #eef4fb;
            --admin-overlay: rgba(15, 23, 42, 0.55);
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
        }

        html[data-theme="dark"] {
            --admin-bg: #0b1220;
            --admin-surface: #111827;
            --admin-border: #2b3b53;
            --admin-text: #e2e8f0;
            --admin-muted: #a8b6c9;
            --admin-primary: #60a5fa;
            --admin-topnav-bg: rgba(8, 15, 31, 0.96);
            --admin-topnav-border: rgba(71, 85, 105, 0.35);
            --admin-sidebar-gradient:
                radial-gradient(circle at top left, rgba(96, 165, 250, 0.2), transparent 28%),
                linear-gradient(180deg, #020617 0%, #0f172a 100%);
            --admin-card-shadow: 0 18px 40px rgba(2, 6, 23, 0.35);
            --admin-dropdown-shadow: 0 18px 35px rgba(2, 6, 23, 0.45);
            --admin-form-bg: rgba(15, 23, 42, 0.92);
            --admin-subtle-bg: #172235;
            --admin-notification-unread: #132033;
            --admin-hover-bg: #1a2940;
            --admin-input-bg: #0b1324;
            --admin-link: #e2e8f0;
            --admin-link-muted: #cbd5e1;
            --admin-surface-2: #162033;
            --admin-surface-3: #1a2740;
            --admin-overlay: rgba(2, 6, 23, 0.76);
            --admin-success-bg: rgba(34, 197, 94, 0.16);
            --admin-success-text: #86efac;
            --admin-success-border: rgba(34, 197, 94, 0.34);
            --admin-danger-bg: rgba(239, 68, 68, 0.16);
            --admin-danger-text: #fca5a5;
            --admin-danger-border: rgba(239, 68, 68, 0.34);
            --admin-warning-bg: rgba(245, 158, 11, 0.16);
            --admin-warning-text: #fcd34d;
            --admin-warning-border: rgba(245, 158, 11, 0.34);
            --admin-info-bg: rgba(59, 130, 246, 0.16);
            --admin-info-text: #93c5fd;
            --admin-info-border: rgba(59, 130, 246, 0.34);
        }

        body.sb-nav-fixed {
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.08), transparent 22%),
                linear-gradient(180deg, var(--admin-surface-2) 0%, var(--admin-bg) 100%);
            color: var(--admin-text);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        #layoutSidenav,
        #layoutSidenav_content,
        #layoutSidenav_content main {
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.08), transparent 22%),
                linear-gradient(180deg, var(--admin-surface-2) 0%, var(--admin-bg) 100%);
        }

        #layoutSidenav_content {
            min-height: calc(100vh - 72px);
        }

        #layoutSidenav_content main {
            min-height: 100%;
            padding-bottom: 2rem;
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
        }

        .sb-topnav .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .sb-sidenav {
            background: var(--admin-sidebar-gradient);
        }

        .sb-sidenav .sb-sidenav-menu .nav .nav-link {
            margin: 0.18rem 0.75rem;
            padding: 0.82rem 1rem;
            border-radius: 14px;
            color: rgba(255, 255, 255, 0.78);
            transition: 0.2s ease;
        }

        .sb-sidenav .sb-sidenav-menu .nav .nav-link:hover,
        .sb-sidenav .sb-sidenav-menu .nav .nav-link.active,
        .sb-sidenav .sb-sidenav-menu .nav .nav-link.is-active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .sb-sidenav .sb-sidenav-menu .nav .sb-sidenav-menu-heading {
            padding: 1rem 1.5rem 0.65rem;
            color: rgba(255, 255, 255, 0.48);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .sb-sidenav-menu-nested {
            gap: 0.3rem;
            padding: 0.25rem 0 0.7rem;
        }

        .sb-sidenav-menu-nested .nav-link {
            margin-left: 1.25rem !important;
            margin-right: 0.75rem !important;
            padding-left: 2.85rem !important;
            position: relative;
            font-size: 0.94rem;
        }

        .sb-sidenav-menu-nested .nav-link::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.34);
            position: absolute;
            left: 1.4rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .sb-sidenav-menu-nested .nav-link.active::before {
            background: #93c5fd;
        }

        .breadcrumb-item,
        .breadcrumb-item.active {
            color: var(--admin-muted);
        }

        .card {
            background: var(--admin-surface);
            border: 1px solid color-mix(in srgb, var(--admin-border) 85%, transparent);
            box-shadow: var(--admin-card-shadow) !important;
            color: var(--admin-text);
        }

        .card-header,
        .card-footer {
            background: var(--admin-surface) !important;
            border-color: var(--admin-border);
            color: var(--admin-text);
        }

        .card-body {
            color: var(--admin-text);
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

        html[data-theme="dark"] .table-light,
        html[data-theme="dark"] .table-light > th,
        html[data-theme="dark"] .table-light > td,
        html[data-theme="dark"] .table > :not(caption) > * > .table-light {
            --bs-table-bg: var(--admin-subtle-bg);
            --bs-table-striped-bg: var(--admin-surface-2);
            --bs-table-striped-color: var(--admin-text);
            --bs-table-active-bg: var(--admin-hover-bg);
            --bs-table-active-color: var(--admin-text);
            --bs-table-hover-bg: var(--admin-hover-bg);
            --bs-table-hover-color: var(--admin-text);
            color: var(--admin-text);
            border-color: var(--admin-border);
        }

        html[data-theme="dark"] .table-hover > tbody > tr:hover > * {
            --bs-table-accent-bg: var(--admin-hover-bg);
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* {
            --bs-table-accent-bg: rgba(255, 255, 255, 0.02);
            color: var(--admin-text);
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select,
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

        .form-control::placeholder {
            color: var(--admin-muted);
        }

        html[data-theme="dark"] .form-control[type="file"] {
            color: var(--admin-muted);
            background: var(--admin-input-bg);
        }

        html[data-theme="dark"] .form-control[type="file"]::file-selector-button {
            margin: -0.375rem 0.75rem -0.375rem -0.75rem;
            padding: 0.75rem 1rem;
            border: 0;
            border-right: 1px solid var(--admin-border);
            background: var(--admin-surface-2);
            color: var(--admin-text);
            transition: 0.2s ease;
        }

        html[data-theme="dark"] .form-control[type="file"]:hover::file-selector-button {
            background: var(--admin-hover-bg);
        }

        html[data-theme="dark"] .form-control[type="file"]::-webkit-file-upload-button {
            margin: -0.375rem 0.75rem -0.375rem -0.75rem;
            padding: 0.75rem 1rem;
            border: 0;
            border-right: 1px solid var(--admin-border);
            background: var(--admin-surface-2);
            color: var(--admin-text);
            transition: 0.2s ease;
        }

        html[data-theme="dark"] .form-control[type="file"]:hover::-webkit-file-upload-button {
            background: var(--admin-hover-bg);
        }

        .form-check-input {
            background-color: var(--admin-input-bg);
            border-color: var(--admin-border);
        }

        .form-check-input:checked {
            background-color: var(--admin-primary);
            border-color: var(--admin-primary);
        }

        html[data-theme="dark"] .form-switch .form-check-input {
            background-color: rgba(15, 23, 42, 0.9);
            border-color: rgba(96, 165, 250, 0.65);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23f8fafc'/%3e%3c/svg%3e");
        }

        html[data-theme="dark"] .form-switch .form-check-input:focus {
            border-color: rgba(96, 165, 250, 0.85);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.16);
        }

        html[data-theme="dark"] .form-switch .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        html[data-theme="dark"] .form-switch .form-check-input:disabled {
            opacity: 0.65;
        }

        .form-check-label,
        .col-form-label,
        .form-text {
            color: var(--admin-muted);
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
        }

        html[data-theme="dark"] .btn-light,
        html[data-theme="dark"] .btn-outline-secondary,
        html[data-theme="dark"] .btn-outline-dark {
            background: var(--admin-surface-2);
            border-color: var(--admin-border);
            color: var(--admin-text);
        }

        html[data-theme="dark"] .btn-light:hover,
        html[data-theme="dark"] .btn-outline-secondary:hover,
        html[data-theme="dark"] .btn-outline-dark:hover {
            background: var(--admin-hover-bg);
            border-color: color-mix(in srgb, var(--admin-primary) 45%, var(--admin-border));
            color: #fff;
        }

        .dropdown-menu {
            background: var(--admin-surface);
            border: 1px solid color-mix(in srgb, var(--admin-border) 95%, transparent);
            border-radius: 16px;
            box-shadow: var(--admin-dropdown-shadow);
            color: var(--admin-text);
        }

        .dropdown-item {
            color: var(--admin-text);
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            color: var(--admin-text);
            background: var(--admin-hover-bg);
        }

        .dropdown-divider,
        hr {
            border-color: var(--admin-border);
            opacity: 1;
        }

        .notification-list {
            max-height: 420px;
            overflow: auto;
        }

        .notification-dropdown-menu {
            width: min(360px, calc(100vw - 2rem));
            max-width: calc(100vw - 2rem);
        }

        .notification-item {
            color: var(--admin-text);
            text-decoration: none;
            border-bottom: 1px solid color-mix(in srgb, var(--admin-border) 80%, transparent);
        }

        .notification-item .min-w-0 {
            min-width: 0;
        }

        .notification-text {
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .notification-time .badge {
            max-width: 100%;
            white-space: normal;
            text-align: center;
        }

        .notification-item.unread {
            background: var(--admin-notification-unread);
        }

        .notification-item:hover {
            background: var(--admin-hover-bg);
        }

        .admin-page-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .admin-form {
            background: var(--admin-form-bg);
            border: 1px solid color-mix(in srgb, var(--admin-border) 90%, transparent);
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: var(--admin-card-shadow);
        }

        .admin-form__header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--admin-border);
        }

        .admin-form__footer {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--admin-border);
        }

        .admin-form label,
        .admin-form .form-label {
            font-weight: 600;
            color: var(--admin-text);
            margin-bottom: 0.45rem;
        }

        .admin-form .btn-group .btn {
            border-radius: 12px !important;
        }

        .admin-form .list-categories {
            border-radius: 16px;
            border: 1px solid var(--admin-border) !important;
            background: var(--admin-subtle-bg);
            padding: 1rem;
        }

        .theme-toggle-admin {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            min-height: 40px;
            padding: 0.4rem 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
            transition: 0.2s ease;
        }

        .theme-toggle-admin:hover {
            color: #fff;
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

        .bg-white,
        .bg-light,
        .modal-content,
        .list-group-item,
        .accordion-item {
            background-color: var(--admin-surface) !important;
            color: var(--admin-text);
            border-color: var(--admin-border);
        }

        .accordion-button {
            background: var(--admin-surface);
            color: var(--admin-text);
            border-color: var(--admin-border);
        }

        .accordion-button:not(.collapsed) {
            background: var(--admin-subtle-bg);
            color: var(--admin-text);
            box-shadow: inset 0 -1px 0 var(--admin-border);
        }

        .accordion-button::after {
            filter: invert(0.45);
        }

        html[data-theme="dark"] .accordion-button::after {
            filter: invert(0.9);
        }

        .nav-tabs {
            border-bottom-color: var(--admin-border);
        }

        .nav-tabs .nav-link {
            color: var(--admin-link-muted);
            border-color: transparent;
        }

        .nav-tabs .nav-link:hover,
        .nav-tabs .nav-link:focus {
            color: var(--admin-text);
            border-color: var(--admin-border) var(--admin-border) transparent;
            background: var(--admin-surface-2);
        }

        .nav-tabs .nav-link.active,
        .nav-tabs .nav-item.show .nav-link {
            background: var(--admin-surface);
            color: var(--admin-text);
            border-color: var(--admin-border) var(--admin-border) var(--admin-surface);
        }

        .text-muted,
        .small.text-muted,
        .text-secondary {
            color: var(--admin-muted) !important;
        }

        .text-dark,
        .text-body,
        .text-black,
        .text-reset,
        .text-gray-800,
        .text-gray-900 {
            color: var(--admin-text) !important;
        }

        .badge.bg-secondary {
            background: var(--admin-surface-3) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] .badge.bg-warning {
            color: #1f1300 !important;
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

        .modal-header,
        .modal-footer {
            border-color: var(--admin-border);
        }

        .btn-close {
            filter: none;
        }

        html[data-theme="dark"] .btn-close {
            filter: invert(1) grayscale(1);
        }

        html[data-theme="dark"] .page-link {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            color: var(--admin-text);
        }

        html[data-theme="dark"] .pagination .page-link {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            color: var(--admin-text);
        }

        html[data-theme="dark"] .page-item.active .page-link {
            background: var(--admin-primary);
            border-color: var(--admin-primary);
            color: #08111f;
        }

        html[data-theme="dark"] .dataTables_wrapper .dataTables_info,
        html[data-theme="dark"] .dataTables_wrapper .dataTables_length,
        html[data-theme="dark"] .dataTables_wrapper .dataTables_filter,
        html[data-theme="dark"] .dataTables_wrapper .dataTables_paginate {
            color: var(--admin-muted) !important;
        }

        html[data-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] .select2-container--default .select2-selection--single,
        html[data-theme="dark"] .select2-container--default .select2-selection--multiple {
            background: var(--admin-input-bg);
            border-color: var(--admin-border);
            color: var(--admin-text);
        }

        html[data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered,
        html[data-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__rendered,
        html[data-theme="dark"] .select2-container--default .select2-search--inline .select2-search__field {
            color: var(--admin-text);
        }

        html[data-theme="dark"] .select2-dropdown {
            background: var(--admin-surface);
            border-color: var(--admin-border);
        }

        html[data-theme="dark"] .select2-container--default .select2-results__option {
            color: var(--admin-text);
        }

        html[data-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--admin-primary);
            color: #08111f;
        }

        html[data-theme="dark"] .ck.ck-editor__main>.ck-editor__editable,
        html[data-theme="dark"] .ck.ck-toolbar,
        html[data-theme="dark"] .ck.ck-reset_all,
        html[data-theme="dark"] .ck.ck-content {
            background: var(--admin-surface);
            color: var(--admin-text);
            border-color: var(--admin-border);
        }

        @media (max-width: 1199.98px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-responsive > .table,
            .table-responsive > table {
                width: max-content !important;
                min-width: 100%;
                table-layout: auto;
            }

            .table-responsive th,
            .table-responsive td {
                vertical-align: top;
            }
        }

        @media (max-width: 767.98px) {
            .dataTables_wrapper .row,
            div.dt-container div.dt-layout-row {
                display: flex;
                flex-direction: column;
                gap: 0.85rem;
                margin-left: 0;
                margin-right: 0;
            }

            .dataTables_wrapper .row > *,
            div.dt-container div.dt-layout-cell {
                width: 100%;
                max-width: 100%;
                padding-left: 0;
                padding-right: 0;
            }

            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate,
            div.dt-container .dt-length,
            div.dt-container .dt-search,
            div.dt-container .dt-info,
            div.dt-container .dt-paging {
                width: 100%;
                text-align: left !important;
                margin: 0;
            }

            .dataTables_wrapper .dataTables_length label,
            .dataTables_wrapper .dataTables_filter label,
            div.dt-container .dt-length label,
            div.dt-container .dt-search label {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
                width: 100%;
            }

            .dataTables_wrapper .dataTables_filter input,
            .dataTables_wrapper .dataTables_length select,
            div.dt-container .dt-search input,
            div.dt-container .dt-length select {
                width: 100% !important;
                max-width: 100%;
                margin-left: 0 !important;
            }

            .dataTables_wrapper .dataTables_paginate .pagination,
            div.dt-container .dt-paging .pagination {
                justify-content: flex-start;
                flex-wrap: wrap;
                gap: 0.35rem;
            }
        }

        html[data-theme="dark"] .cke {
            border: 1px solid var(--admin-border) !important;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.24);
        }

        html[data-theme="dark"] .cke_top,
        html[data-theme="dark"] .cke_bottom {
            background: var(--admin-surface-2) !important;
            border-color: var(--admin-border) !important;
            box-shadow: none !important;
        }

        html[data-theme="dark"] .cke_contents {
            background: var(--admin-input-bg) !important;
            border-color: var(--admin-border) !important;
        }

        html[data-theme="dark"] .cke_toolgroup,
        html[data-theme="dark"] .cke_combo_button,
        html[data-theme="dark"] a.cke_button,
        html[data-theme="dark"] .cke_button_off,
        html[data-theme="dark"] .cke_button_on,
        html[data-theme="dark"] .cke_button_disabled:hover,
        html[data-theme="dark"] .cke_button_disabled:focus,
        html[data-theme="dark"] .cke_button_disabled:active {
            background: transparent !important;
            border-color: transparent !important;
            box-shadow: none !important;
        }

        html[data-theme="dark"] .cke_toolgroup {
            border-color: var(--admin-border) !important;
            background: rgba(255, 255, 255, 0.02) !important;
        }

        html[data-theme="dark"] a.cke_button:hover,
        html[data-theme="dark"] a.cke_button:focus,
        html[data-theme="dark"] a.cke_button:active,
        html[data-theme="dark"] .cke_combo_button:hover,
        html[data-theme="dark"] .cke_combo_button:focus,
        html[data-theme="dark"] .cke_combo_button:active {
            background: var(--admin-hover-bg) !important;
            border-color: var(--admin-border) !important;
        }

        html[data-theme="dark"] .cke_combo_text,
        html[data-theme="dark"] .cke_combo_open,
        html[data-theme="dark"] .cke_label,
        html[data-theme="dark"] .cke_button_label {
            color: var(--admin-text) !important;
            text-shadow: none !important;
        }

        html[data-theme="dark"] .cke_button_arrow,
        html[data-theme="dark"] .cke_combo_arrow {
            border-top-color: var(--admin-muted) !important;
        }

        html[data-theme="dark"] .cke_button_icon,
        html[data-theme="dark"] .cke_combo_button .cke_button_icon,
        html[data-theme="dark"] .cke_menuitem .cke_button_icon {
            filter: brightness(0) invert(0.88);
        }

        html[data-theme="dark"] .cke_path_item,
        html[data-theme="dark"] .cke_path_empty {
            color: var(--admin-muted) !important;
        }

        html[data-theme="dark"] .cke_resizer {
            border-color: transparent var(--admin-muted) var(--admin-muted) transparent !important;
        }

        html[data-theme="dark"] .cke_dialog {
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.55) !important;
        }

        html[data-theme="dark"] .cke_dialog_body,
        html[data-theme="dark"] .cke_dialog_contents,
        html[data-theme="dark"] .cke_dialog_footer,
        html[data-theme="dark"] .cke_dialog_title {
            background: var(--admin-surface) !important;
            color: var(--admin-text) !important;
            border-color: var(--admin-border) !important;
            text-shadow: none !important;
        }

        html[data-theme="dark"] .cke_dialog_title {
            background: var(--admin-surface-2) !important;
        }

        html[data-theme="dark"] a.cke_dialog_tab {
            background: var(--admin-surface-2) !important;
            border-color: var(--admin-border) !important;
            color: var(--admin-muted) !important;
        }

        html[data-theme="dark"] a.cke_dialog_tab:hover,
        html[data-theme="dark"] a.cke_dialog_tab:focus,
        html[data-theme="dark"] a.cke_dialog_tab_selected {
            background: var(--admin-surface) !important;
            color: var(--admin-text) !important;
            border-color: var(--admin-border) !important;
        }

        html[data-theme="dark"] .cke_dialog_body label,
        html[data-theme="dark"] .cke_dialog_ui_labeled_label,
        html[data-theme="dark"] .cke_dialog_ui_text,
        html[data-theme="dark"] .cke_dialog_ui_html,
        html[data-theme="dark"] .cke_dialog_contents_body,
        html[data-theme="dark"] .cke_dialog_contents_body legend {
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] input.cke_dialog_ui_input_text,
        html[data-theme="dark"] input.cke_dialog_ui_input_password,
        html[data-theme="dark"] input.cke_dialog_ui_input_tel,
        html[data-theme="dark"] textarea.cke_dialog_ui_input_textarea,
        html[data-theme="dark"] select.cke_dialog_ui_input_select,
        html[data-theme="dark"] .cke_dialog iframe.cke_pasteframe {
            background: var(--admin-input-bg) !important;
            border-color: var(--admin-border) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] .cke_dialog fieldset,
        html[data-theme="dark"] .cke_dialog .ImagePreviewBox,
        html[data-theme="dark"] .cke_dialog_contents_body .cke_tpl_list {
            background: var(--admin-surface-2) !important;
            border-color: var(--admin-border) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] .cke_dialog .ImagePreviewLoader {
            background: rgba(15, 23, 42, 0.92) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] a.cke_dialog_ui_button {
            background: var(--admin-surface-2) !important;
            border-color: var(--admin-border) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] a.cke_dialog_ui_button:hover,
        html[data-theme="dark"] a.cke_dialog_ui_button:focus,
        html[data-theme="dark"] a.cke_dialog_ui_button:active {
            background: var(--admin-hover-bg) !important;
            border-color: color-mix(in srgb, var(--admin-primary) 45%, var(--admin-border)) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] a.cke_dialog_ui_button_ok {
            background: #15803d !important;
            border-color: #15803d !important;
            color: #f8fafc !important;
        }

        html[data-theme="dark"] a.cke_dialog_ui_button_ok:hover,
        html[data-theme="dark"] a.cke_dialog_ui_button_ok:focus,
        html[data-theme="dark"] a.cke_dialog_ui_button_ok:active {
            background: #16a34a !important;
            border-color: #16a34a !important;
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .cke_panel,
        html[data-theme="dark"] .cke_panel_frame,
        html[data-theme="dark"] .cke_panel_container,
        html[data-theme="dark"] .cke_menu_panel {
            background: var(--admin-surface) !important;
            border-color: var(--admin-border) !important;
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] .cke_panel_listItem a,
        html[data-theme="dark"] .cke_panel_grouptitle,
        html[data-theme="dark"] .cke_colorblock,
        html[data-theme="dark"] .cke_colorblock a,
        html[data-theme="dark"] a.cke_colorauto,
        html[data-theme="dark"] a.cke_colormore,
        html[data-theme="dark"] .cke_panel_listItem p,
        html[data-theme="dark"] .cke_panel_listItem h1,
        html[data-theme="dark"] .cke_panel_listItem h2,
        html[data-theme="dark"] .cke_panel_listItem h3,
        html[data-theme="dark"] .cke_panel_listItem h4,
        html[data-theme="dark"] .cke_panel_listItem h5,
        html[data-theme="dark"] .cke_panel_listItem h6,
        html[data-theme="dark"] .cke_panel_listItem pre {
            background: transparent !important;
            color: var(--admin-text) !important;
        }

        html[data-theme="dark"] .cke_panel_grouptitle {
            background: var(--admin-surface-2) !important;
            border-color: var(--admin-border) !important;
        }

        html[data-theme="dark"] .cke_panel_listItem.cke_selected a,
        html[data-theme="dark"] .cke_panel_listItem a:hover,
        html[data-theme="dark"] .cke_panel_listItem a:focus,
        html[data-theme="dark"] .cke_panel_listItem a:active,
        html[data-theme="dark"] a:hover.cke_colorauto,
        html[data-theme="dark"] a:hover.cke_colormore,
        html[data-theme="dark"] a:focus.cke_colorauto,
        html[data-theme="dark"] a:focus.cke_colormore,
        html[data-theme="dark"] a:active.cke_colorauto,
        html[data-theme="dark"] a:active.cke_colormore {
            background: var(--admin-hover-bg) !important;
        }

        html[data-theme="dark"] span.cke_colorbox[style*="#ffffff"],
        html[data-theme="dark"] span.cke_colorbox[style*="#FFFFFF"],
        html[data-theme="dark"] span.cke_colorbox[style="background-color:#fff"],
        html[data-theme="dark"] span.cke_colorbox[style="background-color:#FFF"],
        html[data-theme="dark"] span.cke_colorbox[style*="rgb(255,255,255)"],
        html[data-theme="dark"] span.cke_colorbox[style*="rgb(255, 255, 255)"] {
            border-color: var(--admin-border) !important;
        }

        html[data-theme="dark"] .cke_dialog_close_button,
        html[data-theme="dark"] .cke_dialog a.cke_btn_reset,
        html[data-theme="dark"] .cke_dialog a.cke_btn_locked,
        html[data-theme="dark"] .cke_dialog a.cke_btn_unlocked {
            filter: brightness(0) invert(0.88);
        }

        html[data-theme="dark"] .swal2-popup {
            background: var(--admin-surface);
            color: var(--admin-text);
        }

        html[data-theme="dark"] .swal2-html-container,
        html[data-theme="dark"] .swal2-title {
            color: var(--admin-text);
        }

        .offcanvas-backdrop,
        .modal-backdrop {
            background: var(--admin-overlay);
        }

        @media (max-width: 991.98px) {
            .container-fluid.px-4 {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .admin-form {
                padding: 1rem;
                border-radius: 20px;
            }

            .theme-toggle-admin span {
                display: none;
            }

            .theme-toggle-admin {
                padding-inline: 0.75rem;
            }
        }

        @media (max-width: 575.98px) {
            .notification-dropdown-menu {
                width: calc(100vw - 1rem);
                max-width: calc(100vw - 1rem);
                margin-top: 0.5rem;
            }

            .notification-list {
                max-height: min(60vh, 420px);
            }

            .notification-item {
                align-items: flex-start;
            }

            .notification-dropdown-footer {
                flex-direction: column;
                align-items: stretch !important;
            }

            .notification-dropdown-footer > * {
                width: 100%;
                text-align: left;
            }
        }
    </style>
    @yield('stylesheets')
</head>

<body class="sb-nav-fixed">
    @include('part.backend.header')
    <div id="layoutSidenav">
        @include('part.backend.sidebar')
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="https://cdn.datatables.net/v/bs5/dt-2.3.5/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('backend/plugins/ckeditor/ckeditor.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>
    <script src="{{ asset('backend/js/scripts.js') }}"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        (() => {
            const getCkEditorContentCss = (dark) => dark
                ? `
                    html, body {
                        background: #0b1324 !important;
                        color: #e2e8f0 !important;
                    }
                    body {
                        margin: 0;
                        padding: 12px 14px;
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                    }
                    a { color: #93c5fd !important; }
                    table, td, th { border-color: #334155 !important; }
                    blockquote {
                        border-left: 4px solid #334155;
                        color: #cbd5e1;
                        background: rgba(255,255,255,0.03);
                        padding: 0.75rem 1rem;
                    }
                    pre, code {
                        background: #111827;
                        color: #e2e8f0;
                    }
                    img { opacity: 0.98; }
                `
                : `
                    html, body {
                        background: #ffffff !important;
                        color: #111827 !important;
                    }
                    body {
                        margin: 0;
                        padding: 12px 14px;
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                    }
                    a { color: #2563eb !important; }
                    table, td, th { border-color: #dbe4f0 !important; }
                    blockquote {
                        border-left: 4px solid #cbd5e1;
                        color: #334155;
                        background: #f8fafc;
                        padding: 0.75rem 1rem;
                    }
                    pre, code {
                        background: #f8fafc;
                        color: #111827;
                    }
                `;

            const syncCkEditorInstanceTheme = (editor) => {
                if (!editor || editor.status === 'destroyed') {
                    return;
                }

                const dark = document.documentElement.dataset.theme === 'dark';
                const container = editor.container;

                if (container) {
                    if (dark) {
                        container.addClass('cke_admin_dark');
                    } else {
                        container.removeClass('cke_admin_dark');
                    }
                }

                if (editor.document && editor.document.getHead()) {
                    const head = editor.document.getHead();
                    const existingStyle = head.findOne('style[data-admin-cke-theme]');

                    if (existingStyle) {
                        existingStyle.remove();
                    }

                    const style = new CKEDITOR.dom.element('style');
                    style.setAttribute('type', 'text/css');
                    style.setAttribute('data-admin-cke-theme', '1');
                    style.setHtml(getCkEditorContentCss(dark));
                    head.append(style);

                    const body = editor.document.getBody();
                    if (body) {
                        body.setStyle('background', dark ? '#0b1324' : '#ffffff');
                        body.setStyle('color', dark ? '#e2e8f0' : '#111827');
                    }
                }
            };

            const syncAllCkEditorThemes = () => {
                if (typeof CKEDITOR === 'undefined' || !CKEDITOR.instances) {
                    return;
                }

                Object.values(CKEDITOR.instances).forEach(syncCkEditorInstanceTheme);
            };

            window.syncAllCkEditorThemes = syncAllCkEditorThemes;

            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.on('instanceReady', (event) => {
                    syncCkEditorInstanceTheme(event.editor);
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                window.setTimeout(syncAllCkEditorThemes, 0);
            });
        })();
    </script>
    <script>
        $('#lfm').filemanager('image');
        $('#lfm-file').filemanager('file');
        $('#lfm-video').filemanager('video');
        $('#lfm-document').filemanager('document');
    </script>
    <script>
        window.AdminSlug = {
            vi(title) {
                let slug = (title || '').toLowerCase();
                slug = slug.replace(/Ã¡|Ã |áº£|áº¡|Ã£|Äƒ|áº¯|áº±|áº³|áºµ|áº·|Ã¢|áº¥|áº§|áº©|áº«|áº­/gi, 'a');
                slug = slug.replace(/Ã©|Ã¨|áº»|áº½|áº¹|Ãª|áº¿|á»|á»ƒ|á»…|á»‡/gi, 'e');
                slug = slug.replace(/Ã­|Ã¬|á»‰|Ä©|á»‹/gi, 'i');
                slug = slug.replace(/Ã³|Ã²|á»|Ãµ|á»|Ã´|á»‘|á»“|á»•|á»—|á»™|Æ¡|á»›|á»|á»Ÿ|á»¡|á»£/gi, 'o');
                slug = slug.replace(/Ãº|Ã¹|á»§|Å©|á»¥|Æ°|á»©|á»«|á»­|á»¯|á»±/gi, 'u');
                slug = slug.replace(/Ã½|á»³|á»·|á»¹|á»µ/gi, 'y');
                slug = slug.replace(/Ä‘/gi, 'd');
                slug = slug.replace(/[^\p{L}\p{N}\s-]/gu, '');
                return slug.replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-+|-+$/g, '');
            },
            intl(title) {
                return (title || '').toLowerCase().trim().replace(/[^\p{L}\p{N}\s-]/gu, '').replace(/\s+/g, '-')
                    .replace(/-+/g, '-').replace(/^-+|-+$/g, '');
            },
            byLocale(title, locale = 'vi') {
                return (locale || 'vi').toLowerCase() === 'vi' ? this.vi(title) : this.intl(title);
            },
            bindAuto(titleSelector, slugSelector, locale = 'vi') {
                const titleEl = document.querySelector(titleSelector);
                const slugEl = document.querySelector(slugSelector);
                if (!titleEl || !slugEl) return;

                titleEl.addEventListener('input', (event) => {
                    if (!slugEl.dataset.manual) {
                        slugEl.value = this.byLocale(event.target.value, locale);
                    }
                });

                slugEl.addEventListener('input', () => {
                    slugEl.dataset.manual = '1';
                });
            },
            bindIfEmpty(titleSelector, slugSelector, locale = 'vi') {
                const titleEl = document.querySelector(titleSelector);
                const slugEl = document.querySelector(slugSelector);
                if (!titleEl || !slugEl) return;

                titleEl.addEventListener('input', (event) => {
                    if (slugEl.value.trim() === '') {
                        slugEl.value = this.byLocale(event.target.value, locale);
                    }
                });
            }
        };

        window.getSlugByLocale = function(title, locale) {
            return window.AdminSlug.byLocale(title, locale);
        };
    </script>
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
                    const nextThemeLabel = isDark ? 'Light mode' : 'Dark mode';
                    const nextThemeTitle = isDark ? 'Switch to light mode' : 'Switch to dark mode';
                    const label = button.querySelector('[data-admin-theme-label]');

                    button.setAttribute('aria-pressed', String(isDark));
                    button.setAttribute('title', nextThemeTitle);
                    button.setAttribute('aria-label', nextThemeTitle);

                    if (label) {
                        label.textContent = nextThemeLabel;
                    }
                });

                if (typeof window.syncAllCkEditorThemes === 'function') {
                    window.syncAllCkEditorThemes();
                }
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
    @yield('scripts')
    @stack('scripts')
</body>

</html>
