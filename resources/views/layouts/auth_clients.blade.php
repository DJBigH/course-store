<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light dark" />
    <title>{{ $pageTitle ?? 'Khong tim thay trang' }} - BigK</title>
    <link rel="shortcut icon" href="{{ asset('clients/assets/LOGO-DSCONS-FAVICON.png') }}" type="image/x-icon">
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

<body>
    @yield('content')
</body>

@vite(['resources/js/app.js'])
@yield('scripts')

</html>
