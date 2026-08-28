@extends('layouts.client')

@section('title', '403 - Truy cập bị hạn chế')

@section('content')
    <div class="container my-5 py-5">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-6 text-center">
                <div class="mb-4">
                    <i class="bi bi-shield-lock-fill text-danger" style="font-size: 6rem;"></i>
                </div>
                <h1 class="fw-bold display-4 mb-3">Khu vực hạn chế</h1>
                <p class="text-muted fs-5 mb-5 px-lg-5">
                    Rất tiếc, bạn không có quyền truy cập vào nội dung này. Điều này có thể do tài khoản của bạn chưa được cấp phép hoặc phiên làm việc đã hết hạn.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-lg px-4 rounded-pill border shadow-sm">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </a>
                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                        <i class="bi bi-house-door me-2"></i>Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
