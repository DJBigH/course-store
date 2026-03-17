@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="account-content shadow-sm text-center">
                        <div class="display-4 text-success mb-3">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <h2 class="fw-bold mb-3">{{ __('students::clients/account.profile.deactivate_success_title') }}</h2>
                        <p class="text-muted mb-2">
                            {{ __('students::clients/account.profile.deactivate_success_message') }}
                        </p>
                        <p class="text-muted mb-4">
                            {{ __('students::clients/account.profile.deactivate_success_reactivate') }}
                        </p>

                        <div class="alert alert-success border-0">
                            {{ __('students::clients/account.profile.deactivate_success_logout', ['seconds' => 5]) }}
                            <strong id="deactivate-countdown">5</strong>s
                        </div>

                        <form id="deactivate-logout-form"
                            action="{{ route('clients-logout', ['locale' => app()->getLocale()]) }}" method="POST">
                            @csrf
                        </form>

                        <button type="button" class="btn btn-primary px-4" id="deactivate-home-button">
                            {{ __('students::clients/account.profile.deactivate_success_home') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('deactivate-logout-form');
            const button = document.getElementById('deactivate-home-button');
            const countdownEl = document.getElementById('deactivate-countdown');
            let seconds = 5;

            const logoutNow = () => {
                if (form) {
                    form.submit();
                }
            };

            const timer = window.setInterval(() => {
                seconds -= 1;

                if (countdownEl) {
                    countdownEl.textContent = seconds;
                }

                if (seconds <= 0) {
                    window.clearInterval(timer);
                    logoutNow();
                }
            }, 1000);

            if (button) {
                button.addEventListener('click', logoutNow);
            }
        });
    </script>
@endsection
