@extends('layouts.client')

@section('title', __('clients/errors.403.title'))

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">

                    <h1 class="display-1 fw-bold text-danger">403</h1>

                    <h3 class="mb-3">
                        🚫 {{ __('clients/errors.403.message_1') }}
                    </h3>

                    {{-- 👉 THÔNG BÁO ĐỘNG TỪ abort() --}}
                    <p class="text-muted mb-4">
                        {{ $exception->getMessage() ?: {{ __('clients/errors.403.message_2') }} }}
                    </p>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                            ← {{ __('clients/errors.403.back') }}
                        </a>

                        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary">
                            🏠 T{{ __('common.home') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
