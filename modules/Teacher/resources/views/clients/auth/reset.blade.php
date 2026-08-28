@extends('layouts.auth_teacher')

@section('title', __('teacher::auth.reset.page_title'))

@section('content')
    <div class="auth-header">
        <span class="auth-badge">
            <i class="fa-solid fa-shield-halved me-2"></i>
            {{ __('teacher::auth.reset.badge') }}
        </span>
        <h1 class="auth-title">{{ __('teacher::auth.reset.title') }}</h1>
        <p class="auth-subtitle">{{ __('teacher::auth.reset.description') }}</p>
    </div>

    <form action="{{ route('teacher.auth.password.update', ['locale' => app()->getLocale()]) }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        
        <div class="form-group">
            <label class="form-label">{{ __('teacher::auth.reset.email_label') ?? 'Email' }}</label>
            <input type="email" name="email" 
                   class="form-control-custom @error('email') is-invalid-custom @enderror" 
                   placeholder="{{ __('teacher::auth.reset.email_placeholder') ?? 'Nhập email' }}" 
                   value="{{ request()->email ?? old('email') }}" required>
            @error('email')
                <span class="invalid-feedback-custom">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('teacher::auth.reset.password_label') }}</label>
            <input type="password" name="password" 
                   class="form-control-custom @error('password') is-invalid-custom @enderror" 
                   placeholder="{{ __('teacher::auth.reset.password_placeholder') }}" required autofocus>
            @error('password')
                <span class="invalid-feedback-custom">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group mb-4">
            <label class="form-label">{{ __('teacher::auth.reset.password_confirm_label') }}</label>
            <input type="password" name="confirm_password" 
                   class="form-control-custom @error('confirm_password') is-invalid-custom @enderror" 
                   placeholder="{{ __('teacher::auth.reset.password_confirm_placeholder') }}" required>
            @error('confirm_password')
                <span class="invalid-feedback-custom">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn-auth-primary">
            {{ __('teacher::auth.reset.submit') }}
            <i class="fa-solid fa-rotate"></i>
        </button>
    </form>

    <div class="auth-footer mt-5">
        <p class="mb-0">
            {{ __('teacher::auth.reset.back_login_prefix') }}
            <a href="{{ route('teacher.auth.login', ['locale' => app()->getLocale()]) }}" class="auth-link">
                {{ __('teacher::auth.reset.back_login_link') }}
            </a>
        </p>
    </div>
@endsection
