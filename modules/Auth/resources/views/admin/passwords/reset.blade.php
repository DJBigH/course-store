@extends('layouts.auth')

@section('content')
    <div class="card border-0 shadow-lg" style="border-radius: 28px;">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3"
                    style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-lock fs-4"></i>
                </div>
                <h3 class="fw-bold mb-2">{{ $pageTitle }}</h3>
                <p class="text-muted mb-0">Tạo mật khẩu mới để tiếp tục truy cập khu vực quản trị.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger border-0 rounded-4 mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-floating mb-3">
                    <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com"
                        value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                    <label for="email">Email quản trị</label>
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input class="form-control" id="password" name="password" type="password"
                        placeholder="Mật khẩu mới" required autocomplete="new-password">
                    <label for="password">Mật khẩu mới</label>
                    @error('password')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-floating mb-4">
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password"
                        placeholder="Xác nhận mật khẩu" required autocomplete="new-password">
                    <label for="password_confirmation">Xác nhận mật khẩu</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3">
                    <i class="fa-solid fa-rotate me-2"></i>
                    Đặt lại mật khẩu
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
