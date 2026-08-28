@php
    // Chỉ dùng layout backend nếu là Admin đã đăng nhập thật sự
    $isAdmin = (request()->is('admin') || request()->is('admin/*')) && auth()->check() && auth()->user()->hasPermission('admin.access');
    
    // Nếu là admin thì dùng layout backend, nếu không thì dùng layout standalone (không sidebar)
    $layout = $isAdmin ? 'layouts.backend' : 'layouts.errors';
    
    $homeRoute = $isAdmin ? route('admin.index') : route('home', ['locale' => app()->getLocale()]);
@endphp

@extends($layout)

@section('title', '404 - Không tìm thấy trang')

@section('content')
<div class="error-card">
    <div class="mb-4">
        <h1 class="display-1 fw-bold mb-0" style="color: #3b82f6; opacity: 0.8;">404</h1>
    </div>
    <h2 class="fw-bold mb-3">Ối! Trang không tồn tại</h2>
    <p class="mb-5" style="color: #94a3b8;">
        Đường dẫn bạn đang tìm kiếm có thể đã bị di chuyển, xóa hoặc chưa từng tồn tại.
    </p>
    <div class="d-flex justify-content-center gap-3">
        <button onclick="window.history.back()" class="btn btn-outline-secondary px-4 rounded-pill">
            <i class="fa fa-arrow-left me-2"></i> Quay lại
        </button>
        <a href="{{ $homeRoute }}" class="btn btn-primary px-4 rounded-pill">
             Trang chủ
        </a>
    </div>
</div>
@endsection
