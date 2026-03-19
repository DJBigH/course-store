@extends('layouts.client')

@section('content')
    <div class="container">
        <div class="sign-in sign-in--two-factor">
            <h3>{{ __('auth::clients/auth.two_factor.title') }}</h3>
            <p class="mb-3">
                {{ __('auth::clients/auth.two_factor.description', ['purpose' => $purposeLabel]) }}
            </p>
            <p class="mb-3 text-muted">
                {{ __('auth::clients/auth.two_factor.code_sent_to', ['email' => $student->email]) }}
            </p>
            @if ($expiresAt)
                <div class="two-factor-timer" data-two-factor-expire-at="{{ $expiresAt->toIso8601String() }}">
                    <div class="two-factor-timer__label">{{ __('auth::clients/auth.two_factor.expire_label') }}</div>
                    <div class="two-factor-timer__value" id="two-factor-countdown">
                        {{ __('auth::clients/auth.two_factor.calculating') }}
                    </div>
                </div>
            @endif

            <div class="auth-form-alerts" data-auth-alerts>
                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><strong>{{ __('auth::clients/auth.two_factor.error_title') }}</strong>
                            {{ __('auth::clients/auth.two_factor.error_message') }}</span>
                    </div>
                @endif

                @if (session('msg'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>{{ __('auth::clients/auth.two_factor.success_title') }}</strong>
                            {{ session('msg') }}</span>
                    </div>
                @endif

                @if (session('msg_danger'))
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><strong>{{ __('auth::clients/auth.two_factor.error_title') }}</strong>
                            {{ session('msg_danger') }}</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('students.2fa.verify', ['locale' => app()->getLocale()]) }}" method="POST"
                class="js-auth-form" data-success-title="{{ __('auth::clients/auth.two_factor.success_title') }}"
                data-error-title="{{ __('auth::clients/auth.two_factor.error_title') }}">
                @csrf
                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                    placeholder="{{ __('auth::clients/auth.two_factor.code_placeholder') }}" />
                @error('code')
                    <span class="text-start text-danger field-error">{{ $message }}</span>
                @enderror

                <button type="submit" data-submit-label="{{ __('auth::clients/auth.two_factor.submit') }}">
                    {{ __('auth::clients/auth.two_factor.submit') }}
                </button>
            </form>

            <div class="mt-3 text-center">
                <span>{{ __('auth::clients/auth.two_factor.resend_hint') }}</span>
                <form action="{{ route('students.2fa.resend', ['locale' => app()->getLocale()]) }}" method="POST"
                    class="js-auth-form d-inline-block"
                    data-success-title="{{ __('auth::clients/auth.two_factor.success_title') }}"
                    data-error-title="{{ __('auth::clients/auth.two_factor.error_title') }}">
                    @csrf
                    <button type="submit" class="btn-link p-0 border-0 bg-transparent"
                        data-submit-label="{{ __('auth::clients/auth.two_factor.resend') }}">
                        {{ __('auth::clients/auth.two_factor.resend') }}
                    </button>
                </form>
            </div>

            <p class="sign-up register">
                {{ __('auth::clients/auth.two_factor.expire_notice', ['minutes' => ceil(config('auth.student_two_factor_code_expire', 600) / 60)]) }}
            </p>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const timer = document.querySelector('[data-two-factor-expire-at]');
            const countdown = document.getElementById('two-factor-countdown');
            if (!timer || !countdown) return;

            const expireAt = new Date(timer.dataset.twoFactorExpireAt).getTime();
            const expiredText = @json(__('auth::clients/auth.two_factor.expired_inline'));

            const formatTime = (seconds) => {
                const minutes = Math.floor(seconds / 60);
                const remain = seconds % 60;
                return `${String(minutes).padStart(2, '0')}:${String(remain).padStart(2, '0')}`;
            };

            const render = () => {
                const remaining = Math.max(0, Math.floor((expireAt - Date.now()) / 1000));
                if (remaining <= 0) {
                    countdown.textContent = expiredText;
                    timer.classList.add('is-expired');
                    return false;
                }

                countdown.textContent = formatTime(remaining);
                return true;
            };

            if (!render()) return;

            const interval = setInterval(() => {
                if (!render()) {
                    clearInterval(interval);
                }
            }, 1000);
        })();
    </script>
@endsection
