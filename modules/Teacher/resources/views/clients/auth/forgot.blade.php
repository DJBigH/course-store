@extends('layouts.auth_teacher')

@section('title', __('teacher::auth.forgot.page_title'))

@section('content')
    <div class="auth-header">
        <span class="auth-badge">
            <i class="fa-solid fa-key me-2"></i>
            {{ __('teacher::auth.forgot.badge') }}
        </span>
        <h1 class="auth-title">{{ __('teacher::auth.forgot.title') }}</h1>
        <p class="auth-subtitle">{{ __('teacher::auth.forgot.description') }}</p>
    </div>

    <form action="{{ route('teacher.auth.post-forgot', ['locale' => app()->getLocale()]) }}" method="POST">
        @csrf
        <div class="form-group mb-4">
            <label class="form-label">{{ __('teacher::auth.forgot.email_label') }}</label>
            <input type="email" name="email" 
                   class="form-control-custom @error('email') is-invalid-custom @enderror" 
                   placeholder="{{ __('teacher::auth.forgot.email_placeholder') }}" 
                   value="{{ old('email') }}" required autofocus>
            @error('email')
                <span class="invalid-feedback-custom">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn-auth-primary">
            {{ __('teacher::auth.forgot.submit') }}
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </form>

    <div class="auth-footer mt-5">
        <p class="mb-0">
            {{ __('teacher::auth.forgot.back_login_prefix') }}
            <a href="{{ route('teacher.auth.login', ['locale' => app()->getLocale()]) }}" class="auth-link">
                {{ __('teacher::auth.forgot.back_login_link') }}
            </a>
        </p>
    </div>
@endsection
