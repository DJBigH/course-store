@extends('layouts.auth')

@section('content')
    <div class="card border-0 shadow-lg" style="border-radius: 28px;">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3"
                    style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-shield-halved fs-4"></i>
                </div>
                <h3 class="fw-bold mb-2">{{ $pageTitle }}</h3>
                <p class="text-muted mb-0">Chỉ tài khoản quản trị mới có thể truy cập trang này.</p>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4 mb-4">
                    {{ session('msg') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 rounded-4 mb-4">
                    Vui lòng kiểm tra lại thông tin đăng nhập hoặc quyền truy cập.
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input class="form-control" id="inputEmail" name="email" type="email" placeholder="name@example.com"
                        value="{{ old('email') }}" autocomplete="username">
                    <label for="inputEmail">Email quản trị</label>
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-floating mb-3">
                    <input class="form-control" id="inputPassword" name="password" type="password"
                        placeholder="Mật khẩu" autocomplete="current-password">
                    <label for="inputPassword">Mật khẩu</label>
                    @error('password')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember"
                            @checked(old('remember'))>
                        <label class="form-check-label" for="remember">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>

                    <a href="{{ route('admin.password.request') }}" class="small text-decoration-none fw-semibold">
                        Quên mật khẩu?
                    </a>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Đăng nhập quản trị
                </button>
            </form>
        </div>
    </div>
@endsection
