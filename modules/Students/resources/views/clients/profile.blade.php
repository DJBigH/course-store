@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-4">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                {{-- Content --}}
                <div class="col-lg-9 account-profile">
                    <div class="account-content">
                        <div class="auth-form-alerts mb-3" data-auth-alerts>
                            @if (session('msg') || session('msg_success'))
                                <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>{{ session('msg_success') ?? session('msg') }}</span>
                                </div>
                            @endif
                            @if (session('msg_danger'))
                                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    <span>{{ session('msg_danger') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="fw-semibold mb-0">{{ __('students::clients/account.profile.title') }}</h2>
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <button class="btn btn-warning js-profile-btn" type="button">
                                    {{ __('students::clients/account.profile.edit') }}
                                </button>
                                <a class="btn btn-outline-danger"
                                    href="{{ route('students.account.deactivate', ['locale' => app()->getLocale()]) }}">
                                    {{ __('students::clients/account.profile.deactivate') }}
                                </a>
                            </div>
                        </div>

                        <table class="js-profile profile-item table table-bordered table-profile active">
                            <tbody>
                                <tr>
                                    <th>{{ __('students::clients/account.profile.full_name') }}</th>
                                    <td>{{ $student->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('students::clients/account.profile.email') }}</th>
                                    <td>{{ $student->email }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('students::clients/account.profile.phone') }}</th>
                                    <td>{{ $student->phone }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('students::clients/account.profile.address') }}</th>
                                    <td>{{ $student->address ?? 'Chưa cập nhật' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('students::clients/account.profile.status') }}</th>
                                    <td>
                                        @if ($student->email_verified_at)
                                            <span
                                                class="badge bg-success">{{ __('students::clients/account.core.active') }}</span>
                                        @else
                                            <span
                                                class="badge bg-warning text-dark">{{ __('students::clients/account.profile.not_activated') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('students::clients/account.profile.registered_at') }}</th>
                                    <td>{{ Carbon\Carbon::parse($student->created_at)->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('students::clients/account.profile.activated_at') }}</th>
                                    <td>
                                        @if ($student->email_verified_at)
                                            {{ Carbon\Carbon::parse($student->email_verified_at)->format('d/m/Y H:i:s') }}
                                        @else
                                            {{ __('students::clients/account.profile.not_activated') }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>


                        <form
                            action="{{ route('students.account.client-updateprofile', ['locale' => app()->getLocale()]) }}"
                            class="js-profile profile-item profile-form" method="post"
                            data-msg-success="{{ __('students::clients/messages.profile.update.success') }}"
                            data-msg-error="{{ __('students::clients/messages.profile.update.error') }}">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light fw-bold">
                                    {{ __('students::clients/account.profile.update_title') }}
                                    @if (session('msg'))
                                        <div class="alert alert-success">{{ session('msg') }}</div>
                                    @endif
                                </div>

                                <div class="card-body">
                                    <div class="row mb-3">
                                        <label
                                            class="col-md-4 col-form-label">{{ __('students::clients/account.profile.full_name') }}</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('students::clients/account.profile.placeholder_full_name') }}"
                                                value="{{ $student->name }}" name="name">
                                            <span class="error error-name text-danger"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label
                                            class="col-md-4 col-form-label">{{ __('students::clients/account.profile.email') }}</label>
                                        <div class="col-md-8">
                                            <input type="email" class="form-control"
                                                value="{{ $student->email }}" name="email" readonly>
                                            <small class="text-muted d-block mt-1">Email hiện không thể thay đổi tại đây.</small>
                                            <span class="error error-email text-danger"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label
                                            class="col-md-4 col-form-label">{{ __('students::clients/account.profile.phone') }}</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('students::clients/account.profile.placeholder_phone') }}"
                                                value="{{ $student->phone }}" name="phone">
                                            <span class="error error-phone text-danger"></span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label
                                            class="col-md-4 col-form-label">{{ __('students::clients/account.profile.address') }}</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('students::clients/account.profile.placeholder_address') }}"
                                                value="{{ $student->address }}" name="address">
                                            <span class="error error-address text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <button class="btn btn-primary px-4">
                                        {{ __('students::clients/account.profile.save') }}
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted fst-italic mt-2">
                                {{ __('students::clients/messages.profile.update.success') }}
                            </p>
                        </form>

                        <div class="card shadow-sm mt-4 two-factor-card">
                            <div
                                class="card-header bg-light fw-bold d-flex justify-content-between align-items-center two-factor-card__header">
                                <span>{{ __('students::clients/account.two_factor.title') }}</span>
                                <span
                                    class="badge {{ $student->two_factor_email_enabled ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $student->two_factor_email_enabled
                                        ? __('students::clients/account.two_factor.enabled')
                                        : __('students::clients/account.two_factor.disabled') }}
                                </span>
                            </div>
                            <div class="card-body two-factor-card__body">
                                <div class="two-factor-card__intro">
                                    <div class="two-factor-card__icon">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                    <div>
                                        <p class="mb-2">{{ __('students::clients/account.two_factor.description') }}</p>
                                        <p class="text-muted mb-0">{{ __('students::clients/account.two_factor.scope_hint') }}</p>
                                    </div>
                                </div>

                                @if ($student->two_factor_email_enabled && $student->two_factor_email_enabled_at)
                                    <p class="mb-3 mt-3 two-factor-card__meta">
                                        <strong>{{ __('students::clients/account.two_factor.enabled_at') }}</strong>
                                        {{ $student->two_factor_email_enabled_at->format('d/m/Y H:i:s') }}
                                    </p>
                                @endif

                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    @if ($student->two_factor_email_enabled)
                                        <form
                                            action="{{ route('students.account.two-factor.disable', ['locale' => app()->getLocale()]) }}"
                                            method="POST" id="two-factor-disable-form">
                                            @csrf
                                            <button type="button" class="btn btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#twoFactorConfirmModal"
                                                data-two-factor-submit="#two-factor-disable-form"
                                                data-two-factor-title="{{ __('students::clients/account.two_factor.disable_button') }}"
                                                data-two-factor-text="{{ __('students::clients/account.two_factor.disable_confirm') }}"
                                                data-two-factor-button="{{ __('students::clients/account.two_factor.disable_button') }}"
                                                data-two-factor-button-class="btn-outline-danger">
                                                {{ __('students::clients/account.two_factor.disable_button') }}
                                            </button>
                                        </form>
                                    @else
                                        <form
                                            action="{{ route('students.account.two-factor.enable', ['locale' => app()->getLocale()]) }}"
                                            method="POST" id="two-factor-enable-form">
                                            @csrf
                                            <button type="button" class="btn btn-primary"
                                                data-bs-toggle="modal" data-bs-target="#twoFactorConfirmModal"
                                                data-two-factor-submit="#two-factor-enable-form"
                                                data-two-factor-title="{{ __('students::clients/account.two_factor.enable_button') }}"
                                                data-two-factor-text="{{ __('students::clients/account.two_factor.enable_confirm') }}"
                                                data-two-factor-button="{{ __('students::clients/account.two_factor.enable_button') }}"
                                                data-two-factor-button-class="btn-primary">
                                                {{ __('students::clients/account.two_factor.enable_button') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection

@section('modals')
    <div class="modal fade" id="twoFactorConfirmModal" tabindex="-1" aria-labelledby="twoFactorConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="twoFactorConfirmModalLabel">
                        {{ __('students::clients/account.two_factor.modal_title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('students::clients/account.profile.deactivate_cancel') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="twoFactorConfirmModalText"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('students::clients/account.profile.deactivate_cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="twoFactorConfirmSubmit">
                        {{ __('students::clients/account.two_factor.modal_submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.i18n = {
            profile_edit: @json(__('students::clients/account.profile.edit')),
            profile_cancel: @json(__('students::clients/account.profile.cancel')),
        };

        (() => {
            const modal = document.getElementById('twoFactorConfirmModal');
            if (!modal) return;

            let targetSelector = null;
            const titleEl = modal.querySelector('.modal-title');
            const textEl = document.getElementById('twoFactorConfirmModalText');
            const submitButton = document.getElementById('twoFactorConfirmSubmit');

            modal.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                targetSelector = trigger.dataset.twoFactorSubmit;
                titleEl.textContent = trigger.dataset.twoFactorTitle ||
                    @json(__('students::clients/account.two_factor.modal_title'));
                textEl.textContent = trigger.dataset.twoFactorText || '';
                submitButton.textContent = trigger.dataset.twoFactorButton ||
                    @json(__('students::clients/account.two_factor.modal_submit'));
                submitButton.className = `btn ${trigger.dataset.twoFactorButtonClass || 'btn-primary'}`;
            });

            submitButton.addEventListener('click', () => {
                if (!targetSelector) return;
                const form = document.querySelector(targetSelector);
                if (form) {
                    form.submit();
                }
            });
        })();
    </script>
@endsection
