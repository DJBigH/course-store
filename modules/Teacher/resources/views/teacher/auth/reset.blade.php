@extends('layouts.auth_clients')

@section('content')
    <div class="container py-5">
        <div class="teacher-auth-shell">
            <div class="teacher-auth-panel">
                <span class="teacher-auth-badge">{{ __('teacher::auth.reset.badge') }}</span>
                <h1>{{ __('teacher::auth.reset.title') }}</h1>
                <p class="teacher-auth-desc">{{ __('teacher::auth.reset.description') }}</p>

                @if ($errors->any())
                    <div class="alert alert-danger">{{ __('teacher::auth.reset.error_message') }}</div>
                @endif

                @if (session('msg'))
                    <div class="alert alert-success">{{ session('msg') }}</div>
                @endif

                @if (session('msg_danger'))
                    <div class="alert alert-danger">{{ session('msg_danger') }}</div>
                @endif

                <form action="{{ route('teacher.auth.password.update', ['locale' => app()->getLocale()]) }}" method="POST" class="teacher-auth-form">
                    @csrf
                    <label>{{ __('teacher::auth.reset.password_label') }}</label>
                    <input type="password" name="password" placeholder="{{ __('teacher::auth.reset.password_placeholder') }}" required>
                    @error('password')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <label>{{ __('teacher::auth.reset.password_confirm_label') }}</label>
                    <input type="password" name="confirm_password" placeholder="{{ __('teacher::auth.reset.password_confirm_placeholder') }}" required>
                    @error('confirm_password')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ request()->email }}">

                    <button type="submit" class="btn btn-primary w-100">{{ __('teacher::auth.reset.submit') }}</button>
                </form>

                <p class="teacher-auth-footnote mb-0">
                    {{ __('teacher::auth.reset.back_login_prefix') }}
                    <a href="{{ route('teacher.auth.login', ['locale' => app()->getLocale()]) }}">{{ __('teacher::auth.reset.back_login_link') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    @include('teacher::clients.auth.styles')
@endsection
