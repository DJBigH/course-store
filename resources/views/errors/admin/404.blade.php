@extends('layouts.backend')

@section('title', '404 - Không tìm thấy trang')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 75vh">
            <div class="col-xl-6 col-lg-8 text-center">
                <div class="error-visual mb-5">
                    <div class="display-1 fw-bold text-primary mb-0" style="font-size: 8rem; line-height: 1; opacity: 0.1; position: absolute; left: 50%; transform: translateX(-50%); z-index: -1;">404</div>
                    <i class="bi bi-search text-warning" style="font-size: 5rem;"></i>
                </div>
                
                <h1 class="fw-bold text-dark mb-3">Ối! Trang không tồn tại</h1>
                <p class="text-muted mb-5 fs-5 px-md-5">
                    Trang bạn đang tìm kiếm có thể đã bị di chuyển, xóa hoặc chưa từng tồn tại. 
                    Vui lòng kiểm tra lại đường dẫn hoặc quay lại trang quản trị.
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <button onclick="window.history.back()" class="btn btn-light btn-lg px-4 rounded-pill shadow-sm border">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </button>
                    <a href="{{ route('admin.index') }}" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm">
                        <i class="bi bi-house-door me-2"></i>Trang quản trị
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
        animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>
@endsection
