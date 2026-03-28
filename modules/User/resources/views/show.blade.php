@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">Vui lòng kiểm tra lại dữ liệu đã nhập.</div>
        @endif

        @if (session('msg'))
            <div class="alert alert-success">{{ session('msg') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-primary card-outline h-100">
                    <div class="card-body box-profile text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="{{ $user->avatar ?? asset('backend/assets/img/admin.jfif') }}" alt="Ảnh đại diện"
                            width="100px">

                        <h3 class="profile-username mt-3">{{ $user->name }}</h3>
                        <p class="text-muted">{{ $user->group?->name ?? 'Quản trị viên' }}</p>

                        <ul class="list-group list-group-unbordered mb-3 text-start">
                            <li class="list-group-item">
                                <b>Email</b>
                                <span class="float-end">{{ $user->email }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Ngày tham gia</b>
                                <span class="float-end">{{ $user->created_at->format('d/m/Y') }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>2FA Email</b>
                                <span class="float-end">{{ $user->two_factor_email_enabled ? 'Đã bật' : 'Đang tắt' }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Phiên admin</b>
                                <span class="float-end">{{ $activeAdminSessions }}/{{ app(\App\Support\AdminSecurityService::class)->maxDevices() }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Đăng nhập gần nhất</b>
                                <span class="float-end text-end">
                                    {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i:s') : 'Chưa có' }}<br>
                                    <small class="text-muted">{{ $user->last_login_ip ?: 'Không rõ IP' }}</small>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <b>Thiết bị</b>
                                <span class="float-end text-end">
                                    {{ $user->last_login_browser ?: 'Chưa rõ trình duyệt' }}<br>
                                    <small class="text-muted">{{ $user->last_login_platform ?: 'Chưa rõ nền tảng' }} - {{ $user->last_login_device ?: 'Chưa rõ thiết bị' }}</small>
                                </span>
                            </li>
                        </ul>

                        <div class="d-grid gap-2">
                            <form action="{{ route('user.toggle-2fa') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn {{ $user->two_factor_email_enabled ? 'btn-outline-danger' : 'btn-outline-primary' }} w-100"
                                    onclick="return confirm('{{ $user->two_factor_email_enabled ? 'Bạn có chắc chắn muốn tắt xác thực 2 lớp?' : 'Bạn có chắc chắn muốn bật xác thực 2 lớp qua email?' }}')">
                                    {{ $user->two_factor_email_enabled ? 'Tắt xác thực 2 lớp' : 'Bật xác thực 2 lớp' }}
                                </button>
                            </form>

                            <form action="{{ route('user.logout-all-sessions') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100"
                                    onclick="return confirm('Bạn có chắc chắn muốn đăng xuất tất cả các phiên admin không?')">
                                    Đăng xuất tất cả các phiên
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cập nhật thông tin cá nhân</h3>
                    </div>

                    <form action="{{ route('user.post-show') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Tên</label>
                                    <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                                        placeholder="Tên..." value="{{ old('name') ?? $user->name }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                                        placeholder="Email..." readonly value="{{ old('email') ?? $user->email }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Mật khẩu</label>
                                    <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                        name="password" placeholder="Mật khẩu mới...">
                                    <small class="text-muted d-block mt-2">Đổi mật khẩu sẽ đăng xuất toàn bộ phiên admin hiện có.</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
