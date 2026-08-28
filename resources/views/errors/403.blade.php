@php
    $isAdmin = request()->is('admin') || request()->is('admin/*');
    $layout = $isAdmin ? 'layouts.backend' : 'layouts.client';
    $homeRoute = $isAdmin ? route('admin.index') : route('home', ['locale' => app()->getLocale()]);
@endphp

@extends($layout)

@section('title', '403 - Truy cập bị từ chối')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh">
        <div class="col-lg-6 text-center">
            <h1 class="display-1 fw-bold text-danger mb-0" style="opacity: 0.2;">403</h1>
            <h2 class="fw-bold mb-3">Truy cập bị từ chối</h2>
            <p class="text-muted mb-5">{{ $exception->getMessage() ?: 'Bạn không có quyền truy cập vào nội dung này.' }}</p>
            <div class="d-flex justify-content-center gap-3">
                <button onclick="window.history.back()" class="btn btn-outline-secondary px-4 rounded-pill">Quay lại</button>
                <a href="{{ $homeRoute }}" class="btn btn-primary px-4 rounded-pill">Về trang chủ</a>
            </div>
        </div>
    </div>
</div>
@endsection
