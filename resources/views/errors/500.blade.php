@php
    $isAdmin = request()->is('admin') || request()->is('admin/*');
    $layout = $isAdmin ? 'layouts.backend' : 'layouts.client';
    $homeRoute = $isAdmin ? route('admin.index') : route('home', ['locale' => app()->getLocale()]);
@endphp

@extends($layout)

@section('title', '500 - Lỗi hệ thống')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh">
        <div class="col-lg-6 text-center">
            <h1 class="display-1 fw-bold text-secondary mb-0" style="opacity: 0.2;">500</h1>
            <h2 class="fw-bold mb-3">Lỗi hệ thống</h2>
            <p class="text-muted mb-5">Đã có lỗi xảy ra từ phía máy chủ. Vui lòng thử lại sau.</p>
            <div class="d-flex justify-content-center gap-3">
                <button onclick="window.location.reload()" class="btn btn-outline-secondary px-4 rounded-pill">Tải lại trang</button>
                <a href="{{ $homeRoute }}" class="btn btn-primary px-4 rounded-pill">Về trang chủ</a>
            </div>
        </div>
    </div>
</div>
@endsection
