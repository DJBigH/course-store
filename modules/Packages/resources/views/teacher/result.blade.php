@extends('layouts.backend')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center py-5">
        <div class="col-md-6 text-center">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden animate__animated animate__fadeInUp">
                <div class="card-body p-5">
                    @if($status === 'success')
                        <div class="mb-4">
                            <div class="display-1 text-success mb-3 animate__animated animate__bounceIn">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h2 class="fw-bold text-dark">{{ __('packages::teacher.result.success_title') }}</h2>
                            <p class="text-muted fs-5">{{ __('packages::teacher.result.success_desc') }}</p>
                        </div>
                        <div class="d-grid gap-2 mt-5">
                            <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-primary btn-lg rounded-pill shadow-sm py-3">
                                <i class="fa-solid fa-house me-2"></i> {{ __('packages::teacher.result.back_to_dashboard') }}
                            </a>
                        </div>
                    @elseif($status === 'cancelled')
                        <div class="mb-4">
                            <div class="display-1 text-warning mb-3 animate__animated animate__shakeX">
                                <i class="fa-solid fa-circle-exclamation"></i>
                            </div>
                            <h2 class="fw-bold text-dark">{{ __('packages::teacher.result.cancelled_title') }}</h2>
                            <p class="text-muted fs-5">{{ __('packages::teacher.result.cancelled_desc') }}</p>
                        </div>
                        <div class="d-grid gap-2 mt-5">
                            <a href="{{ route('teacher.dashboard.package.upgrade.status') }}" class="btn btn-warning btn-lg rounded-pill shadow-sm py-3 text-white">
                                <i class="fa-solid fa-rotate-left me-2"></i> {{ __('packages::teacher.result.back_to_status') }}
                            </a>
                            <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-link text-muted">{{ __('packages::teacher.upgrade.back') }}</a>
                        </div>
                    @else
                        <div class="mb-4">
                            <div class="display-1 text-danger mb-3 animate__animated animate__headShake">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                            <h2 class="fw-bold text-dark">{{ __('packages::teacher.result.failed_title') }}</h2>
                            <p class="text-muted fs-5">{{ __('packages::teacher.result.failed_desc') }}</p>
                        </div>
                        <div class="d-grid gap-2 mt-5">
                            <a href="{{ route('teacher.dashboard.package.upgrade.status') }}" class="btn btn-danger btn-lg rounded-pill shadow-sm py-3">
                                <i class="fa-solid fa-rotate-right me-2"></i> {{ __('packages::teacher.result.try_again') }}
                            </a>
                            <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-link text-muted">{{ __('packages::teacher.upgrade.back') }}</a>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-4 text-muted animate__animated animate__fadeIn animate__delay-1s">
                <p>{!! __('packages::teacher.result.support_note', ['support' => '<a href="mailto:support@example.com" class="text-primary text-decoration-none fw-bold">'.__('packages::teacher.result.support_link').'</a>']) !!}</p>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-4 { border-radius: 1.5rem !important; }
    .animate__delay-1s { animation-delay: 0.8s; }
</style>
@endsection
