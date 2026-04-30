@php
    $isAdmin = request()->is('admin') || request()->is('admin/*');
    $layout = $isAdmin ? 'layouts.backend' : 'layouts.client';
    $homeRoute = $isAdmin ? route('admin.index') : route('home', ['locale' => app()->getLocale()]);
@endphp

@extends($layout)

@section('title', '404 - Không tìm thấy trang')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh">
        <div class="col-lg-6 text-center">
            <h1 class="display-1 fw-bold text-primary mb-0" style="opacity: 0.2;">404</h1>
            <h2 class="fw-bold mb-3">Không tìm thấy trang</h2>
            <p class="text-muted mb-5">Đường dẫn bạn truy cập không tồn tại hoặc đã bị xóa.</p>
            <div class="d-flex justify-content-center gap-3">
                <button onclick="window.history.back()" class="btn btn-outline-secondary px-4 rounded-pill">Quay lại</button>
                <a href="{{ $homeRoute }}" class="btn btn-primary px-4 rounded-pill">Về trang chủ</a>
            </div>
        </div>
    </div>
</div>
@endsection
