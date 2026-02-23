@extends('layouts.client')

@section('content')
    <div class="container">
        <div class="home-back">
            {{-- <a href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                <span>
                    <i class="fa-solid fa-arrow-left"></i>
                </span>
                {{ __('auth::clients/auth.login.back_home') }}
            </a> --}}
        </div>
        <div class="sign-in">
            <h3>{{ __('auth::clients/auth.login.title') }}</h3>
            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><strong>{{ __('auth::clients/auth.login.error_title') }}</strong>
                        {{ __('auth::clients/auth.login.error_message') }}</span>
                </div>
            @endif

            @if (session('msg'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><strong>{{ __('auth::clients/auth.login.success_title') }}</strong> {{ session('msg') }}</span>
                </div>
            @endif

            @if (session('msg_danger'))
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><strong>{{ __('auth::clients/auth.login.error_title') }}</strong>
                        {{ session('msg_danger') }}</span>
                </div>
            @endif

            <form action="" method="POST">
                <input type="text" name="email" placeholder="{{ __('auth::clients/auth.login.email_placeholder') }}" />
                @error('email')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="password" name="password"
                    placeholder="{{ __('auth::clients/auth.login.password_placeholder') }}" />
                @error('password')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <div class="checker">
                    <input type="checkbox" name="remember" value="1" />
                    <span>{{ __('auth::clients/auth.login.remember_me') }}</span>
                </div>
                <p class="forgot-password"><a href="{{ route('clients-forgot', ['locale' => app()->getLocale()]) }}"
                        style="color: rgb(81, 81, 81); font-weight: normal; ">{{ __('auth::clients/auth.login.forgot_password') }}</a>
                </p>
                <button type="submit">{{ __('auth::clients/auth.login.submit') }}</button>
                @csrf
            </form>
            <p class="sign-up register">
                {{ __('auth::clients/auth.login.no_account') }}
                <a
                    href="{{ route('clients-register', ['locale' => app()->getLocale()]) }}">{{ __('auth::clients/auth.login.register_now') }}</a>
            </p>
        </div>
    </div>
@endsection
