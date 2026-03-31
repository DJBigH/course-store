@extends('layouts.auth')

@section('content')
    <div class="card border-0 shadow-lg" style="border-radius: 28px;">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3"
                    style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-key fs-4"></i>
                </div>
                <h3 class="fw-bold mb-2">{{ $pageTitle }}</h3>
                <p class="text-muted mb-0">Nhập email quản trị để nhận liên kết đặt lại mật khẩu.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success border-0 rounded-4 mb-4">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 rounded-4 mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}">
                @csrf

                <div class="form-floating mb-4">
                    <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com"
                        value="{{ old('email') }}" required autocomplete="email" autofocus>
                    <label for="email">Email quản trị</label>
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3">
                    <i class="fa-solid fa-paper-plane me-2"></i>
                    Gửi liên kết đặt lại mật khẩu
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>
@endsection
