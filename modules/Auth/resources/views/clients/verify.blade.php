@extends('layouts.auth_clients')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 88vh">
        <div class="card border-0 shadow-lg text-center p-5 rounded-4" style="max-width: 540px; width: 100%;">

            {{-- Icon --}}
            <div class="mb-4">
                <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle
                        bg-danger bg-opacity-10"
                    style="width: 96px; height: 96px;">
                    <i class="fa-solid fa-envelope-circle-check text-danger fs-1"></i>
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            {{-- Title --}}
            <h2 class="fw-bold text-danger mb-3">
                Xác minh email của bạn
            </h2>

            {{-- Description --}}
            <p class="text-muted mb-4 lh-lg">
                Chúng tôi đã gửi email xác minh đến địa chỉ của bạn.<br>
                Vui lòng kiểm tra hộp thư và nhấn vào liên kết<br>
                để kích hoạt tài khoản.
            </p>

            {{-- Primary actions --}}
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-2">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Trang chủ
                </a>

                <a href="https://mail.google.com/" target="_blank" class="btn btn-danger px-4">
                    <i class="fa-solid fa-envelope me-1"></i>
                    Mở Gmail
                </a>
            </div>

            {{-- Divider --}}
            <div class="d-flex align-items-center my-4">
                <div class="flex-grow-1 border-top"></div>
                <span class="px-3 text-muted small">hoặc</span>
                <div class="flex-grow-1 border-top"></div>
            </div>

            {{-- Resend --}}
            <form method="POST" action="{{ route('verification.send') }}" id="resend-form">
                @csrf
                <button type="submit" id="resend-btn" class="btn btn-link text-decoration-none text-danger fw-semibold">
                    <i class="fa-solid fa-rotate-right me-1"></i>
                    Gửi lại email xác minh
                </button>
            </form>

            <div id="countdown" class="mt-3 small text-muted d-none">
                Bạn có thể gửi lại sau
                <span id="timer" class="fw-bold">60</span> giây
            </div>


            {{-- Note --}}
            <div class="mt-3 small text-muted">
                Không nhận được email? Hãy kiểm tra <b>Spam</b> hoặc <b>Quảng cáo</b>.
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resendBtn = document.getElementById('resend-btn');
            const countdown = document.getElementById('countdown');
            const timerEl = document.getElementById('timer');

            @if (session('resent'))
                let timeLeft = 60;

                resendBtn.classList.add('d-none');
                countdown.classList.remove('d-none');

                const interval = setInterval(() => {
                    timeLeft--;
                    timerEl.textContent = timeLeft;

                    if (timeLeft <= 0) {
                        clearInterval(interval);
                        resendBtn.classList.remove('d-none');
                        countdown.classList.add('d-none');
                    }
                }, 1000);
            @endif
        });
    </script>
@endsection
