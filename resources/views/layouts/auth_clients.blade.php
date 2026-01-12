<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $pageTitle ?? 'Không tìm thấy trang' }} - BigK</title>
    <link rel="shortcut icon" href="{{ asset('clients/assets/LOGO-DSCONS-FAVICON.png') }}" type="image/x-icon">
    @vite(['resources/sass/app.scss'])
    @yield('stylesheets')
</head>

<body>
    @yield('content')
</body>

@vite(['resources/js/app.js'])
@yield('scripts')

</html>

</html>
