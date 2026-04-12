@extends('layouts.client')

@section('title', __('clients/errors.affiliate_link_expired.title'))

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 70vh">
            <div class="col-md-6 text-center">
                <img src="{{ asset('clients/assets/expired.png') }}" alt="Affiliate Link Expired" class="img-fluid mb-4"
                    style="max-width: 280px; height: auto;">

                <h2 class="fw-semibold mb-3 text-warning">
                    {{ __('clients/errors.affiliate_link_expired.message_1') }}
                </h2>

                <p class="lead text-muted mb-4">
                    {{ __('clients/errors.affiliate_link_expired.message_2') }}<br>
                    {{ __('clients/errors.affiliate_link_expired.message_3') }}
                </p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary px-4">
                        <i class="fas fa-home"></i> {{ __('common.home') }}
                    </a>

                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-arrow-left"></i> {{ __('clients/errors.404.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
