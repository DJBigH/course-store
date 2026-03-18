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
                            data-msg-success="{{ __('students::clients/messages.profile.update_success') }}"
                            data-msg-error="{{ __('students::clients/messages.profile.update_error') }}">
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
                                                placeholder="{{ __('students::clients/account.profile.placeholder_email') }}"
                                                value="{{ $student->email }}" name="email">
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
                                {{ __('students::clients/messages.profile.update_success') }}
                            </p>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        </div>
    </section>
@endsection
@section('scripts')
    <script>
        window.i18n = {
            profile_edit: @json(__('students::clients/account.profile.edit')),
            profile_cancel: @json(__('students::clients/account.profile.cancel')),
        };
    </script>
@endsection
