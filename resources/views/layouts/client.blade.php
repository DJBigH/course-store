<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <title>{{ $pageTitle ?? __('clients/common.page_not_found') }} - BigK Udemy</title>
    <link rel="shortcut icon" href="{{ asset('clients/assets/LOGO-DSCONS-FAVICON.png') }}" type="image/x-icon">
    <link href="https://vjs.zencdn.net/8.23.4/video-js.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/sass/app.scss'])
    @yield('stylesheets')
</head>

<body>
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
    @include ('part.clients.header')
    <main>
        @yield('content')
    </main>
    @include ('part.clients.footer')
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
<script src="https://vjs.zencdn.net/8.23.4/video.min.js"></script>
@if (request()->routeIs('students.account.checkout'))
    <script>
        let paymentDate = '{{ getCurrentPaymentDate() }}';
        let checkoutCountdown = `{{ config('checkout.checkout_countdown') }}`;
        let orderId = {{ request()->route('id') }};
    </script>
@endif
@vite(['resources/js/app.js'])
@yield('scripts')

</html>
