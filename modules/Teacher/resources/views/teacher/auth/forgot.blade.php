@extends('layouts.client')

@section('content')
    <div class="container py-5">
        <div class="teacher-auth-shell">
            <div class="teacher-auth-panel">
                <span class="teacher-auth-badge">{{ __('teacher::auth.forgot.badge') }}</span>
                <h1>{{ __('teacher::auth.forgot.title') }}</h1>
                <p class="teacher-auth-desc">{{ __('teacher::auth.forgot.description') }}</p>

                @if ($errors->any())
                    <div class="alert alert-danger">{{ __('teacher::auth.forgot.error_message') }}</div>
                @endif

                @if (session('msg'))
                    <div class="alert alert-success">{{ session('msg') }}</div>
                @endif

                @if (session('msg_danger'))
                    <div class="alert alert-danger">{{ session('msg_danger') }}</div>
                @endif

                <form action="{{ route('teacher.auth.post-forgot', ['locale' => app()->getLocale()]) }}" method="POST" class="teacher-auth-form">
                    @csrf
                    <label>{{ __('teacher::auth.forgot.email_label') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('teacher::auth.forgot.email_placeholder') }}" required>
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <button type="submit" class="btn btn-primary w-100">{{ __('teacher::auth.forgot.submit') }}</button>
                </form>

                <p class="teacher-auth-footnote mb-0">
                    {{ __('teacher::auth.forgot.back_login_prefix') }}
                    <a href="{{ route('teacher.auth.login', ['locale' => app()->getLocale()]) }}">{{ __('teacher::auth.forgot.back_login_link') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    @include('teacher::clients.auth.styles')
@endsection
