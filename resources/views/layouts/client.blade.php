<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $pageTitle ?? 'Không tìm thấy trang' }} - BigK</title>
    <link rel="stylesheet" href="{{ asset('clients/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('clients/css/slick.css') }}" />
    <link rel="stylesheet" href="{{ asset('clients/css/all.min.css') }}" />
    <link rel="shortcut icon" href="{{ asset('clients/assets/LOGO-DSCONS-FAVICON.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('clients/css/reset.css') }}" />
    <link rel="stylesheet" href="{{ asset('clients/css/header.css') }}" />
    <link rel="stylesheet" href="{{ asset('clients/css/home.css') }}" />
    <link rel="stylesheet" href="{{ asset('clients/css/course.css') }}" />
    <link rel="stylesheet" href="{{ asset('clients/css/course-detail.css') }}" />
    <link rel="stylesheet" href="{{ asset('clients/css/video-detail.css') }}" />
    <link rel="stylesheet" href="{{ asset('clients/css/footer.css') }}" />
    <link href="{{ asset('clients/css/video-js.css') }}" rel="stylesheet" />
    @yield('stylesheets')
</head>

<body>
    @include ('part.clients.header')
    <main>
        @yield('content')
    </main>
    @include ('part.clients.footer')
    @yield('scripts')
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
<script src="{{ asset('clients/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('clients/js/jquery.min.js') }}"></script>
<script src="{{ asset('clients/js/jquery-migrate-1.2.1.min.js') }}"></script>
<script src="{{ asset('clients/js/slick.min.js') }}"></script>
<script src="{{ asset('clients/js/slider-home.js') }}"></script>
<script src="{{ asset('clients/js/accordion.js') }}"></script>
<script src="{{ asset('clients/js/home.js') }}"></script>
<script src="{{ asset('clients/js/tab.js') }}"></script>
<script src="{{ asset('clients/js/video.min.js') }}"></script>

</html>

</html>
