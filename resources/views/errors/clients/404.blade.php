@extends('layouts.client')

@section('title', '404 - Không tìm thấy trang')

@section('content')
    <div class="container my-5 py-5">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-6 text-center">
                <div class="mb-4">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/404-error-page-not-found-illustration-download-in-svg-png-gif-file-formats--message-event-pack-network-communication-illustrations-5210416.png?f=webp" alt="404" class="img-fluid" style="max-height: 300px;">
                </div>
                <h1 class="fw-bold display-4 mb-3">Ối! Trang này biến mất rồi</h1>
                <p class="text-muted fs-5 mb-5 px-lg-5">
                    Trang bạn đang tìm kiếm có thể đã bị đổi tên, xóa đi hoặc không còn tồn tại. Đừng lo lắng, hãy bắt đầu lại từ trang chủ nhé!
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                        <i class="bi bi-house-door me-2"></i>Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
