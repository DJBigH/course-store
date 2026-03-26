<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>{{ $pageTitle ?? 'Không tìm thấy trang' }} - BigK Udemy</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.3.5/datatables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
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
        }

        body.sb-nav-fixed {
            background: linear-gradient(180deg, #eef4fb 0%, #f8fafc 100%);
            color: var(--admin-text);
        }

        .container-fluid {
            max-width: 1600px;
        }

        .sb-topnav {
            min-height: 72px;
            background: rgba(15, 23, 42, 0.96) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
        }

        .sb-topnav .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .sb-sidenav {
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.28), transparent 28%),
                linear-gradient(180deg, #0f172a 0%, #172554 100%);
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
            border: 1px solid rgba(219, 228, 240, 0.85);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06) !important;
        }

        .table thead th {
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--admin-muted);
            background: #f8fafc;
            border-bottom: 1px solid var(--admin-border);
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select,
        .form-control,
        .form-select {
            border-radius: 12px;
            border-color: var(--admin-border);
            min-height: 44px;
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
        }

        .dropdown-menu {
            border: 1px solid rgba(219, 228, 240, 0.95);
            border-radius: 16px;
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.12);
        }

        .notification-list {
            max-height: 420px;
            overflow: auto;
        }

        .notification-item {
            color: var(--admin-text);
            text-decoration: none;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .notification-item.unread {
            background: #f8fbff;
        }

        .notification-item:hover {
            background: #f8fafc;
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
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(219, 228, 240, 0.9);
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
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
            color: #334155;
            margin-bottom: 0.45rem;
        }

        .admin-form .btn-group .btn {
            border-radius: 12px !important;
        }

        .admin-form .list-categories {
            border-radius: 16px;
            border: 1px solid var(--admin-border) !important;
            background: #f8fafc;
            padding: 1rem;
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
        $('#lfm').filemanager('image');
        $('#lfm-file').filemanager('file');
        $('#lfm-video').filemanager('video');
        $('#lfm-document').filemanager('document');
    </script>
    <script>
        window.AdminSlug = {
            vi(title) {
                let slug = (title || '').toLowerCase();
                slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
                slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
                slug = slug.replace(/í|ì|ỉ|ĩ|ị/gi, 'i');
                slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
                slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
                slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
                slug = slug.replace(/đ/gi, 'd');
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
    @yield('scripts')
    @stack('scripts')
</body>

</html>
