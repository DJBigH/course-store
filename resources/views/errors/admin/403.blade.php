@extends('layouts.backend')

@section('title', '403 - Truy cập bị từ chối')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 75vh">
            <div class="col-xl-6 col-lg-8 text-center">
                <div class="error-visual mb-5">
                    <div class="display-1 fw-bold text-danger mb-0" style="font-size: 8rem; line-height: 1; opacity: 0.1; position: absolute; left: 50%; transform: translateX(-50%); z-index: -1;">403</div>
                    <i class="bi bi-shield-lock text-danger" style="font-size: 5rem;"></i>
                </div>
                
                <h1 class="fw-bold text-dark mb-3">Truy cập bị giới hạn</h1>
                <p class="text-muted mb-5 fs-5 px-md-5">
                    {{ $message ?? 'Bạn không có đủ quyền hạn để truy cập vào khu vực này. Nếu bạn tin rằng đây là một lỗi, vui lòng liên hệ với quản trị viên cấp cao.' }}
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <button onclick="window.history.back()" class="btn btn-light btn-lg px-4 rounded-pill shadow-sm border">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </button>
                    <a href="{{ route('admin.index') }}" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm">
                        <i class="bi bi-house-door me-2"></i>Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
<style>
    .error-visual {
        position: relative;
        animation: pulse-lock 2s ease-in-out infinite;
    }
    @keyframes pulse-lock {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>
@endsection
