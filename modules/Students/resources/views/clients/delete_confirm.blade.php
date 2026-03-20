@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="account-content shadow-sm">
                        <div class="text-center mb-4">
                            <div class="display-5 text-danger mb-3">
                                <i class="fa-solid fa-trash-can"></i>
                            </div>
                            <h2 class="fw-bold mb-3">{{ __('students::clients/account.profile.delete_heading') }}</h2>
                            <p class="text-muted mb-3">
                                {{ __('students::clients/account.profile.delete_description') }}
                            </p>

                            <p class="mb-2 text-danger fw-semibold">
                                {{ __('students::clients/account.profile.delete_note_1') }}
                            </p>
                            <p class="mb-0 text-warning">
                                {{ __('students::clients/account.profile.delete_note_2', ['email' => $student->email]) }}
                            </p>
                        </div>

                        <div class="alert alert-danger border-0">
                            <strong>{{ __('students::clients/account.profile.delete_warning_title') }}</strong><br>
                            {{ __('students::clients/account.profile.delete_warning') }}
                        </div>

                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item">{{ __('students::clients/account.profile.delete_consequence_1') }}</li>
                            <li class="list-group-item">{{ __('students::clients/account.profile.delete_consequence_2') }}</li>
                            <li class="list-group-item">{{ __('students::clients/account.profile.delete_consequence_3') }}</li>
                            <li class="list-group-item">{{ __('students::clients/account.profile.delete_consequence_4') }}</li>
                        </ul>

                        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                            <a href="{{ route('students.account.profile', ['locale' => app()->getLocale()]) }}"
                                class="btn btn-outline-secondary px-4">
                                {{ __('students::clients/account.profile.delete_back') }}
                            </a>

                            <form id="delete-account-form"
                                action="{{ route('students.account.delete-start-2fa', ['locale' => app()->getLocale()]) }}"
                                method="POST">
                                @csrf
                                <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal"
                                    data-bs-target="#deleteConfirmModal">
                                    {{ __('students::clients/account.profile.delete_confirm_button') }}
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
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">
                        {{ __('students::clients/account.profile.delete_modal_title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ __('students::clients/account.profile.delete_confirm') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('students::clients/account.profile.delete_cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" id="delete-confirm-submit">
                        {{ __('students::clients/account.profile.delete_confirm_button') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('delete-account-form');
            const confirmButton = document.getElementById('delete-confirm-submit');

            if (!form || !confirmButton) {
                return;
            }

            confirmButton.addEventListener('click', function() {
                form.submit();
            });
        });
    </script>
@endsection
