@extends('layouts.teacher')

@php $pageTitle = __('teacher::teacher/errors.500.title'); @endphp

@section('stylesheets')
<style>
    /* Hide default layout page title and breadcrumbs for error pages */
    #layoutSidenav_content main .container-fluid > h2,
    #layoutSidenav_content main .container-fluid > ol.breadcrumb {
        display: none !important;
    }
    
    .display-1 { line-height: 1; }
    .teacher-panel {
        background: var(--admin-surface);
        background-image: radial-gradient(circle at top right, rgba(239, 68, 68, 0.05), transparent 40%);
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="text-center p-5 teacher-panel shadow-lg" style="max-width: 600px; border: 1px solid var(--admin-border);">
        <div class="mb-4 position-relative">
            <h1 class="display-1 fw-bold text-danger opacity-10" style="font-size: 8rem; letter-spacing: -5px;">500</h1>
            <div class="position-absolute top-50 start-50 translate-middle w-100">
                <i class="fa-solid fa-triangle-exclamation text-danger display-4 mb-3"></i>
                <h2 class="fw-800 mb-0">{{ __('teacher::teacher/errors.500.title') }}</h2>
            </div>
        </div>
        
        <p class="text-muted fs-5 mb-4">
            {{ __('teacher::teacher/errors.500.message') }}<br>
            <span class="small opacity-75">{{ __('teacher::teacher/errors.500.description') }}</span>
        </p>

        <div class="d-flex gap-3 justify-content-center mt-5">
            <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-primary btn-lg px-4 py-3 shadow-sm" style="border-radius: 16px;">
                <i class="fa-solid fa-house me-2"></i> {{ __('teacher::teacher/errors.500.button') }}
            </a>
            <button onclick="window.location.reload()" class="btn btn-outline-secondary btn-lg px-4 py-3" style="border-radius: 16px;">
                <i class="fa-solid fa-rotate me-2"></i> {{ __('teacher::teacher/common.retry') ?? 'Thử lại' }}
            </button>
        </div>
    </div>
</div>
@endsection
