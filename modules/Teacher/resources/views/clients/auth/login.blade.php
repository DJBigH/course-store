@extends('layouts.client')

@section('content')
    <div class="container py-5">
        <div class="teacher-auth-shell">
            <div class="teacher-auth-panel">
                <span class="teacher-auth-badge">{{ __('teacher::auth.login.badge') }}</span>
                <h1>{{ __('teacher::auth.login.title') }}</h1>
                <p class="teacher-auth-desc">{{ __('teacher::auth.login.description') }}</p>

                @if ($errors->any())
                    <div class="alert alert-danger">{{ __('teacher::auth.login.error_message') }}</div>
                @endif

                @if (session('msg'))
                    <div class="alert alert-success">{{ session('msg') }}</div>
                @endif

                @if (session('msg_danger'))
                    <div class="alert alert-danger teacher-auth-alert">
                        <div class="fw-semibold mb-1">{{ session('msg_danger') }}</div>
                        <div class="small">{{ __('teacher::auth.access.not_teacher_help') }}</div>
                    </div>
                @endif

                <form action="{{ route('teacher.auth.post-login', ['locale' => app()->getLocale()]) }}" method="POST" class="teacher-auth-form">
                    @csrf
                    <label>{{ __('teacher::auth.login.email_label') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('teacher::auth.login.email_placeholder') }}" required>
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <label>{{ __('teacher::auth.login.password_label') }}</label>
                    <input type="password" name="password" placeholder="{{ __('teacher::auth.login.password_placeholder') }}" required>
                    @error('password')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <label class="teacher-auth-check">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember') == 1)>
                        <span>{{ __('teacher::auth.login.remember_me') }}</span>
                    </label>

                    <div class="teacher-auth-links">
                        <a href="{{ route('teacher.auth.forgot', ['locale' => app()->getLocale()]) }}">{{ __('teacher::auth.login.forgot_password') }}</a>
                        <a href="{{ route('clients-login', ['locale' => app()->getLocale()]) }}">{{ __('teacher::auth.login.student_login') }}</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">{{ __('teacher::auth.login.submit') }}</button>
                </form>

                <div class="teacher-auth-switcher">
                    <div class="teacher-auth-switcher__card">
                        <h2>{{ __('teacher::auth.switcher.student_title') }}</h2>
                        <p>{{ __('teacher::auth.login.student_login_hint') }}</p>
                        <a class="btn btn-outline-primary w-100" href="{{ route('clients-login', ['locale' => app()->getLocale()]) }}">
                            {{ __('teacher::auth.login.student_login_cta') }}
                        </a>
                    </div>
                    <div class="teacher-auth-switcher__card">
                        <h2>{{ __('teacher::auth.switcher.teacher_title') }}</h2>
                        <p>{{ __('teacher::auth.switcher.teacher_desc') }}</p>
                        <a class="btn btn-outline-secondary w-100" href="{{ route('teacher.portal.index', ['locale' => app()->getLocale()]) }}">
                            {{ __('teacher::auth.login.apply_cta') }}
                        </a>
                    </div>
                </div>

                <p class="teacher-auth-footnote mb-0">
                    {{ __('teacher::auth.login.hint_prefix') }}
                    <a href="{{ route('teacher.portal.index', ['locale' => app()->getLocale()]) }}">{{ __('teacher::auth.login.hint_link') }}</a>
                    {{ __('teacher::auth.login.hint_suffix') }}
                </p>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    @include('teacher::clients.auth.styles')
@endsection
