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

                        <h2 class="fw-bold mb-3">{{ __('students::clients/account.profile.delete_success_title') }}</h2>
                        <p class="text-muted mb-2">
                            {{ __('students::clients/account.profile.delete_success_message') }}
                        </p>
                        <p class="text-muted mb-4">
                            {{ __('students::clients/account.profile.delete_success_subtitle') }}
                        </p>

                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary px-4">
                                {{ __('students::clients/account.profile.delete_success_home') }}
                            </a>
                            <a href="{{ route('clients-register', ['locale' => app()->getLocale()]) }}"
                                class="btn btn-outline-secondary px-4">
                                {{ __('students::clients/account.profile.delete_success_register') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
