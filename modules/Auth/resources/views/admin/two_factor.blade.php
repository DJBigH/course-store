@extends('layouts.auth')

@section('content')
    <div class="card border-0 shadow-lg" style="border-radius: 28px;">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3"
                    style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-shield-halved fs-4"></i>
                </div>
                <h3 class="fw-bold mb-2">Xác thực 2 lớp quản trị</h3>
                <p class="text-muted mb-0">Nhập mã 6 số đã được gửi đến email {{ $user->email }} để tiếp tục đăng nhập.</p>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4 mb-4">{{ session('msg') }}</div>
            @endif

            @if (session('msg_danger'))
                <div class="alert alert-danger border-0 rounded-4 mb-4">{{ session('msg_danger') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 rounded-4 mb-4">Vui lòng kiểm tra lại mã xác thực.</div>
            @endif

            @if ($expiresAt)
                <div class="alert alert-light border rounded-4 mb-4 text-center" data-two-factor-expire-at="{{ $expiresAt->toIso8601String() }}">
                    <div class="small text-muted mb-1">Mã sẽ hết hạn sau</div>
                    <div class="fw-semibold" id="two-factor-countdown">Đang tính...</div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.2fa.verify') }}">
                @csrf
                <div class="form-floating mb-3">
                    <input class="form-control" id="inputCode" name="code" type="text" maxlength="6"
                        inputmode="numeric" autocomplete="one-time-code" placeholder="Mã xác thực">
                    <label for="inputCode">Mã xác thực</label>
                    @error('code')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3">
                    <i class="fa-solid fa-unlock-keyhole me-2"></i>
                    Xác thực đăng nhập
                </button>
            </form>

            <div class="text-center mt-4">
                <form method="POST" action="{{ route('admin.2fa.resend') }}" class="d-inline-block">
                    @csrf
                    <button type="submit" class="btn btn-link text-decoration-none fw-semibold p-0">Gửi lại mã</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const timer = document.querySelector('[data-two-factor-expire-at]');
            const countdown = document.getElementById('two-factor-countdown');
            if (!timer || !countdown) return;

            const expireAt = new Date(timer.dataset.twoFactorExpireAt).getTime();

            const formatTime = (seconds) => {
                const minutes = Math.floor(seconds / 60);
                const remain = seconds % 60;
                return `${String(minutes).padStart(2, '0')}:${String(remain).padStart(2, '0')}`;
            };

            const render = () => {
                const remaining = Math.max(0, Math.floor((expireAt - Date.now()) / 1000));
                if (remaining <= 0) {
                    countdown.textContent = 'Mã đã hết hạn';
                    return false;
                }

                countdown.textContent = formatTime(remaining);
                return true;
            };

            if (!render()) return;

            const interval = setInterval(() => {
                if (!render()) {
                    clearInterval(interval);
                }
            }, 1000);
        })();
    </script>
@endsection
