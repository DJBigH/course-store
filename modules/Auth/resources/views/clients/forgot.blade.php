@extends('layouts.client')

@section('content')
    <div class="container">
        <div class="home-back">
            {{-- <a href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                <span>
                    <i class="fa-solid fa-arrow-left"></i>
                </span>
                {{ __('auth::clients/auth.forgot.back_home') }}
            </a> --}}
        </div>
        <div class="sign-in">
            <h3>{{ __('auth::clients/auth.forgot.title') }}</h3>
            <p class="mb-3">{{ __('auth::clients/auth.forgot.instruction') }}</p>

            <div class="auth-form-alerts" data-auth-alerts>
                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><strong>{{ __('auth::clients/auth.forgot.error_title') }}</strong>
                            {{ __('auth::clients/auth.forgot.error_message') }}</span>
                    </div>
                @endif

                @if (session('msg'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>{{ __('auth::clients/auth.forgot.success_title') }}</strong> {{ session('msg') }}</span>
                    </div>
                @endif

                @if (session('msg_danger'))
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><strong>{{ __('auth::clients/auth.forgot.error_title') }}</strong>
                            {{ session('msg_danger') }}</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('clients-postforgot', ['locale' => app()->getLocale()]) }}" method="POST"
                class="js-auth-form" data-success-title="{{ __('auth::clients/auth.forgot.success_title') }}"
                data-error-title="{{ __('auth::clients/auth.forgot.error_title') }}">
                <input type="text" name="email" value="{{ old('email') }}"
                    placeholder="{{ __('auth::clients/auth.forgot.email_placeholder') }}" />
                @error('email')
                    <span class="text-start text-danger field-error">{{ $message }}</span>
                @enderror
                <button type="submit" data-submit-label="{{ __('auth::clients/auth.forgot.submit') }}">
                    {{ __('auth::clients/auth.forgot.submit') }}
                </button>
                @csrf
            </form>

            <p class="sign-up register">
                {{ __('auth::clients/auth.forgot.back_login') }}
                <a href="{{ route('clients-login', ['locale' => app()->getLocale()]) }}">
                    {{ __('auth::clients/auth.forgot.login') }}
                </a>
            </p>
        </div>
    </div>
@endsection
