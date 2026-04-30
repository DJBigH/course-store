@extends('layouts.backend')

@section('title', '500 - Lỗi hệ thống')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 75vh">
            <div class="col-xl-6 col-lg-8 text-center">
                <div class="error-visual mb-5">
                    <div class="display-1 fw-bold text-secondary mb-0" style="font-size: 8rem; line-height: 1; opacity: 0.1; position: absolute; left: 50%; transform: translateX(-50%); z-index: -1;">500</div>
                    <i class="bi bi-cpu text-info" style="font-size: 5rem;"></i>
                </div>
                
                <h1 class="fw-bold text-dark mb-3">Lỗi máy chủ nội bộ</h1>
                <p class="text-muted mb-5 fs-5 px-md-5">
                    Đã có lỗi xảy ra từ phía hệ thống. Chúng tôi đã ghi nhận sự cố này và sẽ khắc phục sớm nhất có thể. 
                    Vui lòng thử lại sau vài phút.
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <button onclick="window.location.reload()" class="btn btn-light btn-lg px-4 rounded-pill shadow-sm border">
                        <i class="bi bi-arrow-clockwise me-2"></i>Tải lại trang
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
    }
    .error-visual i {
        display: inline-block;
        animation: rotate-gear 5s linear infinite;
    }
    @keyframes rotate-gear {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection
