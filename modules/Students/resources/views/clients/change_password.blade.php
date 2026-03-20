@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page account-password-page py-4">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                {{-- Content --}}
                <div class="col-lg-9">
                    <div class="account-content card shadow-sm border-0 account-password-content">
                        <div class="card-body p-4">
                            <h2 class="mb-2 fw-semibold">{{ __('students::clients/account.change_password.title') }}</h2>
                            <div class="account-form-alerts" data-account-alerts>
                                @if (session('msg'))
                                    <div class="alert alert-{{ session('msgType') }}">{{ session('msg') }}</div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert alert-danger">{{ __('students::clients/account.change_password.error') }}</div>
                                @endif
                            </div>
                            <form action="" method="post" class="js-change-password"
                                data-msg-success="{{ __('students::clients/messages.update-password.success') }}"
                                data-msg-error="{{ __('students::clients/account.change_password.error') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-medium">
                                        {{ __('students::clients/account.change_password.old_password') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" name="old_password" class="form-control"
                                            placeholder="{{ __('students::clients/account.change_password.old_password_placeholder') }}">
                                    </div>
                                    @error('old_password')
                                        <span class="text-danger error error-old_password">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-medium">
                                        {{ __('students::clients/account.change_password.new_password') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-key"></i>
                                        </span>
                                        <input type="password" name="password" class="form-control"
                                            placeholder="{{ __('students::clients/account.change_password.new_password_placeholder') }}">
                                    </div>
                                    @error('password')
                                        <span class="text-danger error error-password">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-medium">
                                        {{ __('students::clients/account.change_password.confirm_password') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-key-fill"></i>
                                        </span>
                                        <input type="password" name="confirm_password" class="form-control"
                                            placeholder="{{ __('students::clients/account.change_password.confirm_password_placeholder') }}">
                                    </div>
                                    @error('confirm_password')
                                        <span class="text-danger error error-confirm_password">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ __('students::clients/account.change_password.submit') }}
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
