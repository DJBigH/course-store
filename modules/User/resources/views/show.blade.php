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
                            <li class="list-group-item d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <b>Email</b>
                                <span class="text-start text-md-end ms-md-auto">{{ $user->email }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <b>Ngày tham gia</b>
                                <span class="text-start text-md-end ms-md-auto">{{ $user->created_at->format('d/m/Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <b>2FA Email</b>
                                <span class="text-start text-md-end ms-md-auto">{{ $user->two_factor_email_enabled ? 'Đã bật' : 'Đang tắt' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <b>Phiên admin</b>
                                <span
                                    class="text-start text-md-end ms-md-auto">{{ $activeAdminSessions }}/{{ app(\App\Support\AdminSecurityService::class)->maxDevices() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <b>Đăng nhập gần nhất</b>
                                <span class="text-start text-md-end ms-md-auto">
                                    {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i:s') : 'Chưa có' }}<br>
                                    <small class="text-muted">{{ $user->last_login_ip ?: 'Không rõ IP' }}</small>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <b>Thiết bị</b>
                                <span class="text-start text-md-end ms-md-auto">
                                    {{ $user->last_login_browser ?: 'Chưa rõ trình duyệt' }}<br>
                                    <small class="text-muted">{{ $user->last_login_platform ?: 'Chưa rõ nền tảng' }} -
                                        {{ $user->last_login_device ?: 'Chưa rõ thiết bị' }}</small>
                                </span>
                            </li>
                        </ul>

                        <div class="d-grid gap-2">
                            <form action="{{ route('user.toggle-2fa') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="btn {{ $user->two_factor_email_enabled ? 'btn-outline-danger' : 'btn-outline-primary' }} w-100"
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
                                    <input type="text"
                                        class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                                        placeholder="Tên..." value="{{ old('name') ?? $user->name }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="text"
                                        class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                                        placeholder="Email..." readonly value="{{ old('email') ?? $user->email }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Mật khẩu</label>
                                    <input type="password"
                                        class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                        name="password" placeholder="Mật khẩu mới...">
                                    <small class="text-muted d-block mt-2">Đổi mật khẩu sẽ đăng xuất toàn bộ phiên admin
                                        hiện có.</small>
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

                <div class="row g-4 mt-1">
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h3 class="card-title mb-0">Lịch sử đăng nhập admin</h3>
                                <span class="badge bg-primary">{{ $loginHistories->count() }} gần nhất</span>
                            </div>
                            <div class="card-body">
                                @forelse ($loginHistories as $log)
                                    <div class="border rounded-3 p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2 flex-wrap">
                                            <div>
                                                <div class="fw-semibold">{{ $log->description }}</div>
                                                <div class="small text-muted">
                                                    {{ $log->created_at?->format('d/m/Y H:i:s') }}</div>
                                            </div>
                                            <span class="badge bg-info text-dark">{{ $log->action }}</span>
                                        </div>
                                        <div class="small text-muted">IP:
                                            {{ data_get($log->properties, 'current.ip') ?? (data_get($log->properties, 'ip') ?? ($log->ip ?? 'Không rõ')) }}
                                        </div>
                                        <div class="small text-muted">
                                            Thiết bị:
                                            {{ data_get($log->properties, 'current.device') ?? (data_get($log->properties, 'device') ?? 'Không rõ') }}
                                            |
                                            {{ data_get($log->properties, 'current.browser') ?? (data_get($log->properties, 'browser') ?? 'Unknown browser') }}
                                            |
                                            {{ data_get($log->properties, 'current.platform') ?? (data_get($log->properties, 'platform') ?? 'Unknown platform') }}
                                        </div>
                                        @if (!empty(data_get($log->properties, 'changed_fields')))
                                            <div class="mt-2 d-flex flex-wrap gap-2">
                                                @foreach (data_get($log->properties, 'changed_fields', []) as $field)
                                                    @php
                                                        $badgeMap = [
                                                            'ip' => ['label' => 'Đổi IP', 'class' => 'bg-danger'],
                                                            'device' => [
                                                                'label' => 'Đổi thiết bị',
                                                                'class' => 'bg-warning text-dark',
                                                            ],
                                                            'browser' => [
                                                                'label' => 'Đổi trình duyệt',
                                                                'class' => 'bg-info text-dark',
                                                            ],
                                                            'platform' => [
                                                                'label' => 'Đổi nền tảng',
                                                                'class' => 'bg-primary',
                                                            ],
                                                        ];
                                                        $badge = $badgeMap[$field] ?? [
                                                            'label' => ucfirst((string) $field),
                                                            'class' => 'bg-secondary',
                                                        ];
                                                    @endphp
                                                    <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Chưa có lịch sử đăng nhập.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h3 class="card-title mb-0">Lịch sử thao tác admin</h3>
                                <span class="badge bg-secondary">{{ $actionHistories->count() }} gần nhất</span>
                            </div>
                            <div class="card-body">
                                @forelse ($actionHistories as $log)
                                    <div class="border rounded-3 p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2 flex-wrap">
                                            <div>
                                                <div class="fw-semibold">{{ $log->description }}</div>
                                                <div class="small text-muted">
                                                    {{ $log->created_at?->format('d/m/Y H:i:s') }}</div>
                                            </div>
                                            <span class="badge bg-dark">{{ $log->action }}</span>
                                        </div>
                                        <div class="small text-muted">Nhóm log: {{ $log->log_name ?: 'system' }}</div>
                                        <div class="small text-muted">IP: {{ $log->ip ?: 'Không rõ' }}</div>
                                        @if (!empty($log->subject_type) || !empty($log->subject_id))
                                            <div class="small text-muted">Đối tượng:
                                                {{ class_basename((string) $log->subject_type) }} #{{ $log->subject_id }}
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Chưa có lịch sử thao tác.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
