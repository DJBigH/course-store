@extends('layouts.client')

@section('title', __('clients/errors.expired.title'))

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 70vh">
            <div class="col-md-6 text-center">

                {{-- Image --}}
                <img src="{{ asset('clients/assets/expired.png') }}" alt="Payment Expired" class="img-fluid mb-4"
                    style="max-width: 300px; height: auto;">

                {{-- Title --}}
                <h2 class="fw-semibold mb-3 text-danger">
                    {{ __('clients/errors.expired.message_1') }}
                </h2>

                {{-- Message --}}
                <p class="lead text-muted mb-3">
                    {{ __('clients/errors.expired.message_2') }}<br>
                    {{ __('clients/errors.expired.message_3') }}
                </p>

                {{-- Note --}}
                <div class="alert alert-warning d-inline-block mb-4">
                    ⏱ {{ __('clients/errors.expired.hold_time_label') }} <strong>{{ config('checkout.checkout_countdown') }} {{ __('clients/errors.expired.minutes') }}</strong>.
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="#" class="btn btn-warning px-4">
                        <i class="fas fa-redo"></i> {{ __('clients/errors.expired.pay_again') }}
                    </a>

                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-home"></i> {{ __('common.home') }}
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
