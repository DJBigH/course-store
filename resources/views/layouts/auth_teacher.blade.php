<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>@yield('title', 'Teacher Portal') - BigK Udemy</title>
    <link rel="shortcut icon" href="{{ asset('clients/assets/LOGO-DSCONS-FAVICON.png') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('backend/css/styles.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        :root {
            --auth-bg: #030712;
            --auth-accent: #3b82f6;
            --auth-accent-secondary: #10b981;
            --auth-glass: rgba(17, 24, 39, 0.7);
            --auth-glass-border: rgba(255, 255, 255, 0.08);
            --auth-text: #f9fafb;
            --auth-text-muted: #9ca3af;
            --auth-input-bg: rgba(255, 255, 255, 0.03);
            --auth-input-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--auth-bg);
            color: var(--auth-text);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-background {
            position: fixed;
            inset: 0;
            z-index: -1;
            overflow: hidden;
            background: linear-gradient(135deg, #030712 0%, #111827 100%);
        }

        .auth-blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(80px);
            animation: blob-float 20s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
        }

        .auth-blob-1 { top: -100px; left: -100px; background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, transparent 70%); }
        .auth-blob-2 { bottom: -100px; right: -100px; background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, transparent 70%); animation-delay: -5s; }

        @keyframes blob-float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(100px, 50px) scale(1.1); }
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
            padding: 2rem;
            animation: auth-fade-up 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes auth-fade-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-card {
            background: var(--auth-glass);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--auth-glass-border);
            border-radius: 32px;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .auth-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 2.5rem;
        }

        .auth-logo img {
            height: 56px;
            filter: drop-shadow(0 0 12px rgba(59, 130, 246, 0.3));
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .auth-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--auth-accent);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1rem;
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
            background: linear-gradient(to bottom right, #fff, #9ca3af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .auth-subtitle {
            color: var(--auth-text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--auth-text);
            margin-bottom: 0.5rem;
            padding-left: 0.25rem;
        }

        .form-control-custom {
            width: 100%;
            background: var(--auth-input-bg);
            border: 1px solid var(--auth-input-border);
            border-radius: 16px;
            padding: 0.875rem 1.25rem;
            color: #fff;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.06);
            border-color: var(--auth-accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        .btn-auth-primary {
            width: 100%;
            background: linear-gradient(135deg, var(--auth-accent) 0%, #2563eb 100%);
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .btn-auth-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.4);
            filter: brightness(1.1);
        }

        .btn-auth-primary:active {
            transform: translateY(0);
        }

        .auth-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--auth-text-muted);
        }

        .auth-link {
            color: var(--auth-accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .auth-link:hover {
            color: #60a5fa;
            text-decoration: underline;
        }

        /* Language Switcher */
        .lang-switcher {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 100;
        }

        .lang-dropdown {
            background: var(--auth-glass);
            backdrop-filter: blur(12px);
            border: 1px solid var(--auth-glass-border);
            border-radius: 16px;
            padding: 0.5rem;
            display: flex;
            gap: 0.25rem;
        }

        .lang-item {
            padding: 0.4rem 0.75rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            color: var(--auth-text-muted);
            transition: all 0.2s ease;
            text-transform: uppercase;
        }

        .lang-item.active {
            background: var(--auth-accent);
            color: #fff;
        }

        .lang-item:hover:not(.active) {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        /* Utils */
        .alert-custom {
            padding: 1rem 1.25rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }

        /* Validation errors */
        .invalid-feedback-custom {
            display: block;
            font-size: 0.75rem;
            color: #f87171;
            margin-top: 0.5rem;
            padding-left: 0.25rem;
        }

        .is-invalid-custom {
            border-color: rgba(239, 68, 68, 0.5) !important;
        }
    </style>
    @yield('stylesheets')
</head>

<body>
    <div class="auth-background">
        <div class="auth-blob auth-blob-1"></div>
        <div class="auth-blob auth-blob-2"></div>
    </div>

    <!-- Language Switcher -->
    <div class="lang-switcher">
        <div class="lang-dropdown shadow-lg">
            @foreach(['vi', 'en', 'ko', 'ja', 'zh'] as $locale)
                @php
                    $routeParams = request()->route() ? request()->route()->parameters() : [];
                    $queryParams = request()->query();
                    $allParams = array_merge($routeParams, $queryParams, ['locale' => $locale]);
                @endphp
                <a href="{{ request()->route() ? route(Route::currentRouteName(), $allParams) : url($locale) }}" 
                   class="lang-item {{ app()->getLocale() == $locale ? 'active' : '' }}">
                    {{ $locale }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('clients/assets/LOGO-DSCONS-BLACK.png') }}" alt="Logo" style="filter: brightness(0) invert(1);">
                </a>
            </div>

            @if(session('msg_danger') || session('msg'))
                <div class="alert-custom {{ session('msg_danger') ? 'alert-danger' : 'alert-success' }}">
                    <i class="fa-solid {{ session('msg_danger') ? 'fa-circle-exclamation' : 'fa-circle-check' }}"></i>
                    {{ session('msg_danger') ?? session('msg') }}
                </div>
            @endif

            @yield('content')
        </div>

        <div class="auth-footer">
            <p>&copy; {{ date('Y') }} BigK Udemy - Teacher Portal. All rights reserved.</p>
        </div>
    </div>

    @yield('scripts')
</body>

</html>
