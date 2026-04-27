@extends('layouts.auth_teacher')

@section('title', __('teacher::auth.login.page_title'))

@section('content')
    <div class="auth-header">
        <span class="auth-badge">
            <i class="fa-solid fa-chalkboard-user me-2"></i>
            {{ __('teacher::auth.login.badge') }}
        </span>
        <h1 class="auth-title">{{ __('teacher::auth.login.title') }}</h1>
        <p class="auth-subtitle">{{ __('teacher::auth.login.description') }}</p>
    </div>

    <form action="{{ route('teacher.auth.post-login', ['locale' => app()->getLocale()]) }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('teacher::auth.login.email_label') }}</label>
            <input type="email" name="email" 
                   class="form-control-custom @error('email') is-invalid-custom @enderror" 
                   placeholder="{{ __('teacher::auth.login.email_placeholder') }}" 
                   value="{{ old('email') }}" required autofocus>
            @error('email')
                <span class="invalid-feedback-custom">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">{{ __('teacher::auth.login.password_label') }}</label>
                <a href="{{ route('teacher.auth.forgot', ['locale' => app()->getLocale()]) }}" class="auth-link small">
                    {{ __('teacher::auth.login.forgot_password') }}
                </a>
            </div>
            <input type="password" name="password" 
                   class="form-control-custom @error('password') is-invalid-custom @enderror" 
                   placeholder="{{ __('teacher::auth.login.password_placeholder') }}" required>
            @error('password')
                <span class="invalid-feedback-custom">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group mb-4">
            <div class="form-check custom-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label text-muted small" for="remember">
                    {{ __('teacher::auth.login.remember_me') }}
                </label>
            </div>
        </div>

        <button type="submit" class="btn-auth-primary">
            {{ __('teacher::auth.login.submit') }}
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
        </button>
    </form>

    <div class="auth-footer mt-5">
        <p class="mb-2">
            {{ __('teacher::auth.login.student_login_hint') }}
        </p>
        <a href="{{ route('clients-login', ['locale' => app()->getLocale()]) }}" class="auth-link">
            <i class="fa-solid fa-user-graduate me-1"></i>
            {{ __('teacher::auth.login.student_login_cta') }}
        </a>
        
        <div class="mt-4 pt-4 border-top border-secondary opacity-25"></div>
        
        <p class="mb-0">
            {{ __('teacher::auth.login.hint_prefix') }}
            <a href="{{ route('teacher.portal.index', ['locale' => app()->getLocale()]) }}" class="auth-link">
                {{ __('teacher::auth.login.hint_link') }}
            </a>
        </p>
    </div>
@endsection

@section('stylesheets')
<style>
    .custom-check .form-check-input {
        background-color: var(--auth-input-bg);
        border-color: var(--auth-input-border);
    }
    .custom-check .form-check-input:checked {
        background-color: var(--auth-accent);
        border-color: var(--auth-accent);
    }
</style>
@endsection
