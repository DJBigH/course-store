@extends('layouts.client')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh">
        <div class="card shadow-sm border-0 text-center p-4" style="max-width: 520px; width: 100%;">

            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10"
                    style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-lock text-danger fs-1"></i>
                </div>
            </div>

            <h2 class="fw-bold text-danger mb-2">
            {{ __('auth::clients/auth.block.title') }}
            </h2>

            <p class="text-muted mb-4">
                {{ __('auth::clients/auth.block.message_1') }}<br>
                {{ __('auth::clients/auth.block.message_2') }} <strong>{{ __('auth::clients/auth.block.support') }}</strong> {{ __('auth::clients/auth.block.message_3') }}
            </p>

            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    {{ __('auth::clients/auth.block.back_home') }}
                </a>

                <a href="#" class="btn btn-danger">
                    <i class="fa-solid fa-headset me-1"></i>
                    {{ __('auth::clients/auth.block.contact_support') }}
                </a>
            </div>
        </div>
    </div>
@endsection
