@extends('layouts.auth_clients')
@section('content')
    <div class="container">
        <div class="home-back">
            <a href="{{ route('home') }}">
                <span>
                    <i class="fa-solid fa-arrow-left"></i>
                </span>
                Về trang chủ
            </a>
        </div>
        <div class="sign-up">
            <h3>Đặt lại mật khẩu</h3>
            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><strong>Lỗi!</strong> Vui lòng kiểm tra lại dữ liệu nhập vào.</span>
                </div>
            @endif

            @if (session('msg'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><strong>Thành công!</strong> {{ session('msg') }}</span>
                </div>
            @endif

            @if (session('msg_danger'))
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><strong>Lỗi!</strong> {{ session('msg_danger') }}</span>
                </div>
            @endif
            <form action="{{ route('clients.update.password') }}" method="POST">
                @csrf
                <input type="password" name="password" placeholder="Mật khẩu..." />
                @error('password')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu..." />
                @error('confirm_password')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ request()->email }}">
                <button type="submit">
                    <i class="fa-solid fa-user"></i>
                    Xác nhận
                </button>
            </form>
            <p class="sign-in login">
                Quay lại đăng nhập?
                <a href="{{ route('clients-login') }}">Đăng nhập</a>
            </p>
        </div>
    </div>
@endsection
