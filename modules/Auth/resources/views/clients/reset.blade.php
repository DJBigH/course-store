@extends('layouts.auth_clients')

@section('content')
    <div class="container">
        <div class="home-back">
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                <span>
                    <i class="fa-solid fa-arrow-left"></i>
                </span>
                {{ __('auth::clients/auth.reset.back_home') }}
            </a>
        </div>
        <div class="sign-up">
            <h3>{{ __('auth::clients/auth.reset.title') }}</h3>

            <div class="auth-form-alerts" data-auth-alerts>
                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><strong>{{ __('auth::clients/auth.reset.error_title') }}</strong>
                            {{ __('auth::clients/auth.reset.error_message') }}</span>
                    </div>
                @endif

                @if (session('msg'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>{{ __('auth::clients/auth.reset.success_title') }}</strong> {{ session('msg') }}</span>
                    </div>
                @endif

                @if (session('msg_danger'))
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><strong>{{ __('auth::clients/auth.reset.error_title') }}</strong>
                            {{ session('msg_danger') }}</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('clients.update.password', ['locale' => app()->getLocale()]) }}" method="POST"
                data-success-title="{{ __('auth::clients/auth.reset.success_title') }}"
                data-error-title="{{ __('auth::clients/auth.reset.error_title') }}">
                @csrf
                <input type="password" name="password"
                    placeholder="{{ __('auth::clients/auth.reset.password_placeholder') }}" />
                @error('password')
                    <span class="text-start text-danger field-error">{{ $message }}</span>
                @enderror

                <input type="password" name="confirm_password"
                    placeholder="{{ __('auth::clients/auth.reset.password_confirm_placeholder') }}" />
                @error('confirm_password')
                    <span class="text-start text-danger field-error">{{ $message }}</span>
                @enderror

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ request()->email }}">

                <button type="submit" data-submit-label="{{ __('auth::clients/auth.reset.submit') }}">
                    <i class="fa-solid fa-user"></i>
                    {{ __('auth::clients/auth.reset.submit') }}
                </button>
            </form>

            <p class="sign-in login">
                {{ __('auth::clients/auth.reset.back_login') }}
                <a href="{{ route('clients-login', ['locale' => app()->getLocale()]) }}">
                    {{ __('auth::clients/auth.reset.login') }}
                </a>
            </p>
        </div>
    </div>
@endsection
