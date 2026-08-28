@extends('layouts.backend')

@section('title', '400 - Yêu cầu không hợp lệ')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 75vh">
            <div class="col-xl-6 col-lg-8 text-center">
                <div class="error-visual mb-5">
                    <div class="display-1 fw-bold text-warning mb-0" style="font-size: 8rem; line-height: 1; opacity: 0.1; position: absolute; left: 50%; transform: translateX(-50%); z-index: -1;">400</div>
                    <i class="bi bi-exclamation-octagon text-warning" style="font-size: 5rem;"></i>
                </div>
                
                <h1 class="fw-bold text-dark mb-3">Yêu cầu không hợp lệ</h1>
                <p class="text-muted mb-5 fs-5 px-md-5">
                    Dữ liệu gửi lên máy chủ không đúng định dạng hoặc bị thiếu thông tin cần thiết. 
                    Vui lòng quay lại và kiểm tra lại các thông tin bạn đã nhập.
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
