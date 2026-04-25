@extends('layouts.client')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center py-5">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="suspended-icon mb-4">
                <i class="fa-solid fa-user-clock text-warning" style="font-size: 5rem;"></i>
            </div>
            <h1 class="fw-bold mb-3">{{ __('teacher::public.suspended_heading') }}</h1>
            <p class="text-muted fs-5 mb-5">
                {!! __('teacher::public.suspended_description', ['name' => $teacher->name_locale]) !!}
            </p>
            
            <div class="alert alert-info border-0 rounded-4 p-4 text-start">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="alert-heading fw-bold">{{ __('teacher::public.suspended_info_title') }}</h5>
                        <ul class="mb-0">
                            <li>{!! __('teacher::public.suspended_info_1') !!}</li>
                            <li>{{ __('teacher::public.suspended_info_2') }}</li>
                            <li>{{ __('teacher::public.suspended_info_3') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <a href="{{ url('/') }}" class="btn btn-primary px-5 py-3 rounded-pill fw-bold">
                    <i class="fa-solid fa-house me-2"></i> Khám phá các giảng viên khác
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('stylesheets')
<style>
    .suspended-icon {
        animation: swing 2s ease-in-out infinite;
    }

    @keyframes swing {
        0% { transform: rotate(0deg); }
        25% { transform: rotate(5deg); }
        75% { transform: rotate(-5deg); }
        100% { transform: rotate(0deg); }
    }
</style>
@endsection
