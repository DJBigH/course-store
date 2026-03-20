@extends('layouts.auth_clients')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 88vh">
        <div class="card border-0 shadow-lg text-center p-5 rounded-4" style="max-width: 540px; width: 100%;">
            <div class="mb-4">
                <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10"
                    style="width: 96px; height: 96px;">
                    <i class="fa-solid fa-envelope-circle-check text-danger fs-1"></i>
                </div>
            </div>

            <div class="auth-form-alerts" data-auth-alerts>
                @if (session('msg'))
                    <div class="alert alert-success">{{ session('msg') }}</div>
                @endif
            </div>

            <h2 class="fw-bold text-danger mb-3">
                {{ __('auth::clients/auth.verify.title') }}
            </h2>

            <p class="text-muted mb-4 lh-lg">
                {{ __('auth::clients/auth.verify.message_sent') }}<br>
                {{ __('auth::clients/auth.verify.instruction_1') }}<br>
                {{ __('auth::clients/auth.verify.instruction_2') }}
            </p>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-2">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary px-4">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    {{ __('auth::clients/auth.verify.home') }}
                </a>

                <a href="https://mail.google.com/" target="_blank" class="btn btn-danger px-4">
                    <i class="fa-solid fa-envelope me-1"></i>
                    {{ __('auth::clients/auth.verify.open_gmail') }}
                </a>
            </div>

            <div class="d-flex align-items-center my-4">
                <div class="flex-grow-1 border-top"></div>
                <span class="px-3 text-muted small">{{ __('auth::clients/auth.verify.or') }}</span>
                <div class="flex-grow-1 border-top"></div>
            </div>

            <form method="POST" action="{{ route('verification.send', ['locale' => app()->getLocale()]) }}"
                id="resend-form" class="js-auth-form js-verify-resend-form"
                data-success-title="{{ __('auth::clients/auth.verify.title') }}"
                data-error-title="{{ __('auth::clients/auth.login.error_title') }}">
                @csrf
                <button type="submit" id="resend-btn" class="btn btn-link text-decoration-none text-danger fw-semibold"
                    data-submit-label="{{ __('auth::clients/auth.verify.resend_email') }}">
                    <i class="fa-solid fa-rotate-right me-1"></i>
                    {{ __('auth::clients/auth.verify.resend_email') }}
                </button>
            </form>

            <div id="countdown" class="mt-3 small text-muted d-none">
                {{ __('auth::clients/auth.verify.resend_after') }}
                <span id="timer" class="fw-bold">60</span> {{ __('auth::clients/auth.verify.seconds') }}
            </div>

            <div class="mt-3 small text-muted">
                {{ __('auth::clients/auth.verify.not_received') }} <b>{{ __('auth::clients/auth.verify.spam') }}</b>
                {{ __('auth::clients/auth.verify.or') }} <b>{{ __('auth::clients/auth.verify.promotions') }}</b>.
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resendBtn = document.getElementById('resend-btn');
            const countdown = document.getElementById('countdown');
            const timerEl = document.getElementById('timer');

            const startCountdown = (seconds = 60) => {
                let timeLeft = seconds;

                resendBtn.classList.add('d-none');
                countdown.classList.remove('d-none');
                timerEl.textContent = timeLeft;

                const interval = setInterval(() => {
                    timeLeft--;
                    timerEl.textContent = timeLeft;

                    if (timeLeft <= 0) {
                        clearInterval(interval);
                        resendBtn.classList.remove('d-none');
                        countdown.classList.add('d-none');
                    }
                }, 1000);
            };

            window.startVerifyCountdown = startCountdown;

            @if (session('resent'))
                startCountdown();
            @endif
        });
    </script>
@endsection
