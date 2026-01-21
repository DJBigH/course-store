<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <title>{{ $pageTitle ?? 'Không tìm thấy trang' }} - BigK Udemy</title>
    <link rel="shortcut icon" href="{{ asset('clients/assets/LOGO-DSCONS-FAVICON.png') }}" type="image/x-icon">
    <link href="https://vjs.zencdn.net/8.23.4/video-js.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/sass/app.scss'])
    @yield('stylesheets')
</head>

<body>
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
@if (\Request::route()->getName() === 'students.account.checkout')
    <script>
        let paymentDate = '{{ getCurrentPaymentDate() }}';
        let checkoutCountdown = `{{ config('checkout.checkout_countdown') }}`;
        let orderId = {{ request()->route()->id }};
    </script>
@endif
@vite(['resources/js/app.js'])
@yield('scripts')

</html>

</html>
