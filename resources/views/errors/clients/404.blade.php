@extends('layouts.client')

@section('title', __('clients/errors.404.title'))

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 70vh">
            <div class="col-md-6 text-center">

                {{-- Image --}}
                <img src="{{ asset('backend/assets/img/404.gif') }}" alt="404 Not Found" class="img-fluid mb-4"
                    style="max-width: 330px; height: auto;">

                {{-- Message --}}
                <p class="lead text-muted mb-2">
                    {{ __('clients/errors.404.message_1') }}<br>
                    {{ __('clients/errors.404.message_2') }}
                </p>

                {{-- Actions --}}
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('clients/errors.404.back') }}
                    </a>

                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary">
                        <i class="fas fa-home"></i> {{ __('common.home') }}
                    </a>

                </div>

            </div>
        </div>
    </div>
@endsection
