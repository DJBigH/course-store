@extends('layouts.auth_clients')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh">
        <div class="card shadow-sm border-0 text-center p-4" style="max-width: 520px; width: 100%;">

            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10"
                    style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-lock text-danger fs-1"></i>
                </div>
            </div>

            <h2 class="fw-bold text-danger mb-2">
                Tài khoản đã bị khóa
            </h2>

            <p class="text-muted mb-4">
                Tài khoản của bạn hiện đang bị tạm khóa.<br>
                Vui lòng liên hệ <strong>CSKH</strong> để được hỗ trợ mở lại.
            </p>

            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Về trang chủ
                </a>

                <a href="#" class="btn btn-danger">
                    <i class="fa-solid fa-headset me-1"></i>
                    Liên hệ CSKH
                </a>
            </div>
        </div>
    </div>
@endsection
