@extends('layouts.client')
@section('content')
    <div class="container">
        <div class="home-back">
            {{-- <a href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                <span>
                    <i class="fa-solid fa-arrow-left"></i>
                </span>
                {{ __('auth::clients/auth.register.back_home') }}
            </a> --}}
        </div>
        <div class="sign-up">
            <h3>{{ __('auth::clients/auth.register.title') }}</h3>
            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><strong>{{ __('auth::clients/auth.register.error_title') }}</strong> {{ __('auth::clients/auth.register.error_message') }}</span>
                </div>
            @endif

            @if (session('msg'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><strong>{{ __('auth::clients/auth.register.success_title') }}</strong> {{ session('msg') }}</span>
                </div>
            @endif

            @if (session('msg_danger'))
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><strong>{{ __('auth::clients/auth.register.error_title') }}</strong> {{ session('msg_danger') }}</span>
                </div>
            @endif
            <form action="" method="POST">
                @csrf
                <input type="text" name="name" placeholder="{{ __('auth::clients/auth.register.name_placeholder') }}" />
                @error('name')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="text" name="email" placeholder="{{ __('auth::clients/auth.register.email_placeholder') }}" />
                @error('email')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="text" name="phone" placeholder="{{ __('auth::clients/auth.register.phone_placeholder') }}" />
                @error('phone')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="password" name="password" placeholder="{{ __('auth::clients/auth.register.password_placeholder') }}" />
                @error('password')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="password" name="confirm_password" placeholder="{{ __('auth::clients/auth.register.password_confirm_placeholder') }}" />
                @error('confirm_password')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <button type="submit">
                    <i class="fa-solid fa-user"></i>
                    {{ __('auth::clients/auth.register.submit') }}
                </button>
            </form>
            <p class="sign-in login">
                {{ __('auth::clients/auth.register.has_account') }}
                <a href="{{ route('clients-login',['locale' => app()->getLocale()]) }}">{{ __('auth::clients/auth.register.login_now') }}</a>
            </p>
        </div>
    </div>
@endsection
