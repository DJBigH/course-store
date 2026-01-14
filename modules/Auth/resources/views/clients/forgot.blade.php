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
        <div class="sign-in">
            <h3>Quên mật khẩu</h3>
            <p class="mb-3">Vui lòng nhập email để đặt lại mật khẩu</p>
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
                <input type="text" name="email" placeholder="Email..." />
                @error('email')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <button type="submit">Xác nhận</button>
                @csrf
            </form>
            <p class="sign-up register">
                Quay lại đăng nhập?
                <a href="{{ route('clients-login') }}">Đăng nhập</a>
            </p>
        </div>
    </div>
@endsection
