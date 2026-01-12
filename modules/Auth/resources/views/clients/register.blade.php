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
            <h3>Đăng kí</h3>
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
            <form action="" method="POST">
                @csrf
                <input type="text" name="name" placeholder="Họ và tên..." />
                @error('name')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="text" name="email" placeholder="Email..." />
                @error('email')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="text" name="phone" placeholder="Số điện thoại..." />
                @error('phone')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="password" name="password" placeholder="Mật khẩu..." />
                @error('password')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <input type="password" name="confirm_password" placeholder="Lặp lại mật khẩu..." />
                @error('confirm_password')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <button type="submit">
                    <i class="fa-solid fa-user"></i>
                    Đăng kí
                </button>
            </form>
            <p class="sign-in login">
                Bạn đã có tài khoản?
                <a href="{{ route('clients-login') }}">Đăng nhập ngay</a>
            </p>
        </div>
    </div>
@endsection
