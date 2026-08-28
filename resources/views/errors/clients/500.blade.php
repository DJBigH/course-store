@extends('layouts.client')

@section('title', '500 - Có lỗi xảy ra')

@section('content')
    <div class="container my-5 py-5">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-6 text-center">
                <div class="mb-4">
                    <i class="bi bi-gear-wide-connected text-primary" style="font-size: 6rem; display: inline-block; animation: spin 4s linear infinite;"></i>
                </div>
                <h1 class="fw-bold display-4 mb-3">Hệ thống đang bận</h1>
                <p class="text-muted fs-5 mb-5 px-lg-5">
                    Đã có một sự cố kỹ thuật xảy ra. Đội ngũ kỹ thuật của chúng tôi đã được thông báo và đang nỗ lực khắc phục. Vui lòng quay lại sau ít phút.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <button onclick="window.location.reload()" class="btn btn-light btn-lg px-4 rounded-pill border shadow-sm">
                        <i class="bi bi-arrow-clockwise me-2"></i>Thử lại
                    </button>
                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                        <i class="bi bi-house-door me-2"></i>Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
@endsection
