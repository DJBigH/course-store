@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="account-content shadow-sm">
                        <div class="text-center mb-4">
                            <div class="display-5 text-warning mb-3">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <h2 class="fw-bold mb-3">{{ __('students::clients/account.profile.deactivate_heading') }}</h2>
                            <p class="text-muted mb-2">
                                {{ __('students::clients/account.profile.deactivate_description') }}
                            </p>

                            <p class="mb-2 text-danger fw-semibold">
                                {{ __('students::clients/account.profile.deactivate_note_1') }}
                            </p>
                            <p class="mb-0 text-warning">
                                {{ __('students::clients/account.profile.deactivate_note', ['email' => $student->email]) }}
                            </p>
                        </div>

                        <div class="alert alert-warning border-0 text-center" style="color: red">
                            {{ __('students::clients/account.profile.deactivate_warning') }}
                        </div>

                        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                            <a href="{{ route('students.account.profile', ['locale' => app()->getLocale()]) }}"
                                class="btn btn-outline-secondary px-4">
                                {{ __('students::clients/account.profile.deactivate_back') }}
                            </a>

                            <form
                                id="deactivate-account-form"
                                action="{{ route('students.account.deactivate-submit', ['locale' => app()->getLocale()]) }}"
                                method="POST">
                                @csrf
                                <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal"
                                    data-bs-target="#deactivateConfirmModal">
                                    {{ __('students::clients/account.profile.deactivate_confirm_button') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <div class="modal fade" id="deactivateConfirmModal" tabindex="-1" aria-labelledby="deactivateConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="deactivateConfirmModalLabel">
                        {{ __('students::clients/account.profile.deactivate_modal_title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ __('students::clients/account.profile.deactivate_confirm') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('students::clients/account.profile.deactivate_cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" id="deactivate-confirm-submit">
                        {{ __('students::clients/account.profile.deactivate_confirm_button') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('deactivate-account-form');
            const confirmButton = document.getElementById('deactivate-confirm-submit');

            if (!form || !confirmButton) {
                return;
            }

            confirmButton.addEventListener('click', function() {
                form.submit();
            });
        });
    </script>
@endsection
