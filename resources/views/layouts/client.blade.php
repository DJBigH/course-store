<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <meta name="color-scheme" content="light dark" />
    <title>{{ $pageTitle ?? __('clients/common.page_not_found') }} - BigK Udemy</title>
    @if(!empty(setting('favicon')))
        <link rel="shortcut icon" href="{{ asset('storage/' . setting('favicon')) }}" type="image/x-icon">
    @else
        <link rel="shortcut icon" href="{{ asset('clients/assets/LOGO-DSCONS-FAVICON.png') }}" type="image/x-icon">
    @endif

    <style>
        :root {
            --bs-primary: {{ setting('theme_primary_color', '#0d6efd') }} !important;
            --primary-color: {{ setting('theme_primary_color', '#0d6efd') }} !important;
        }
        .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .text-primary {
            color: var(--primary-color) !important;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        (() => {
            const storageKey = 'client-theme';
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
    @vite(['resources/sass/app.scss'])
    @yield('stylesheets')
</head>

<body data-locale-switch-loading="{{ __('clients/common.loading_subtitle') }}">
    @if(session()->has('admin_impersonator'))
        <div class="impersonate-banner">
            <div class="container-fluid d-flex justify-content-between align-items-center py-2">
                <div>
                    <i class="fa-solid fa-user-secret me-2"></i>
                    Bạn đang đăng nhập hộ tài khoản: <strong>{{ auth('students')->user()->name }}</strong> (Học viên)
                </div>
                <a href="{{ route('students.stop-impersonate') }}" class="btn btn-sm btn-light text-dark fw-bold border-0 shadow-sm">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Quay lại Admin
                </a>
            </div>
        </div>
        <style>
            .impersonate-banner {
                background: linear-gradient(90deg, #dc2626, #991b1b);
                color: #fff;
                font-size: 0.9rem;
                position: sticky;
                top: 0;
                z-index: 9999;
                box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
            }
            .impersonate-banner .btn-light:hover {
                background: #f8fafc;
                transform: translateY(-1px);
            }
        </style>
    @endif
    <div id="page-loader" class="page-loader" aria-hidden="true">
        <div class="page-loader__panel">
            <div class="page-loader__brand">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <p class="page-loader__title">{{ __('clients/common.loading_title') }}</p>
            <p class="page-loader__subtitle">{{ __('clients/common.loading_subtitle') }}</p>
            <div class="page-loader__track">
                <div class="page-loader__bar"></div>
            </div>
            <div class="page-loader__dots" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <script>
        (() => {
            const loader = document.getElementById('page-loader');

            if (!loader) {
                return;
            }

            let hidden = false;
            const hideLoader = () => {
                if (hidden) {
                    return;
                }

                hidden = true;
                loader.classList.add('is-hidden');

                window.setTimeout(() => {
                    if (loader && loader.parentNode) {
                        loader.remove();
                    }
                }, 320);
            };

            if (document.readyState === 'interactive' || document.readyState === 'complete') {
                window.requestAnimationFrame(hideLoader);
            } else {
                document.addEventListener('DOMContentLoaded', hideLoader, { once: true });
            }

            window.addEventListener('load', hideLoader, { once: true });
            window.addEventListener('pageshow', hideLoader, { once: true });
            window.setTimeout(hideLoader, 1200);
        })();
    </script>
    @include ('part.clients.header')
    @include('part.clients.site-announcement')
    <main>
        @yield('content')
    </main>
    @include ('part.clients.footer')
    @if ((int) setting('chatbot_widget_enabled', '1') === 1)
        @include('part.clients.sales-chatbot')
    @endif
    <div id="page-modals">
        @yield('modals')
    </div>
    <div class="modal fade" id="modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="examplemodalLabel"></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
</body>
@if (request()->routeIs('students.account.checkout'))
    <script>
        let paymentDate = '{{ getCurrentPaymentDate() }}';
        let checkoutCountdown = `{{ config('checkout.checkout_countdown') }}`;
        let orderId = {{ request()->route('id') }};
    </script>
@endif
@vite(['resources/js/app.js'])
<div id="page-inline-scripts" hidden>
    @yield('scripts')
</div>

</html>
