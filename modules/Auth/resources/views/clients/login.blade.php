@extends('layouts.auth_clients')
@section('content')
    <div class="container">
        <div class="home-back">
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                <span>
                    <i class="fa-solid fa-arrow-left"></i>
                </span>
                Về trang chủ
            </a>
        </div>
        <div class="sign-in">
            <h3>Đăng nhập</h3>
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
                <input type="password" name="password" placeholder="Mật khẩu..." />
                @error('password')
                    <span class="text-start text-danger">{{ $message }}</span>
                @enderror
                <div class="checker">
                    <input type="checkbox" name="remember" value="1"/>
                    <span>Tự động đăng nhập</span>
                </div>
                <p class="forgot-password"><a href="{{ route('clients-forgot') }}" style="color: rgb(81, 81, 81); font-weight: normal; ">Quên mật khẩu đăng nhập</a></p>
                <button type="submit">Đăng nhập</button>
                @csrf
            </form>
            <p class="sign-up register">
                Bạn chưa có tài khoản?
                <a href="{{ route('clients-register') }}">Đăng kí ngay</a>
            </p>
        </div>
    </div>
@endsection
