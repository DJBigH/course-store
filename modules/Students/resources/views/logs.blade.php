@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? 'Lịch sử học viên',
        'backUrl' => route('students.index'),
        'backLabel' => 'Quay lại danh sách',
        'entityTitle' => 'Thông tin học viên',
        'entityItems' => [
            'Tên' => $student->name ?? 'N/A',
            'Email' => $student->email ?? 'N/A',
            'Trạng thái' => isset($student->status) ? ($student->status ? 'Kích hoạt' : 'Chưa kích hoạt') : 'N/A',
        ],
        'filterActions' => [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'assigned_coupon' => 'Được gán mã',
            'revoked_coupon' => 'Bị hủy mã',
            'login' => 'Đăng nhập',
        ],
        'logs' => $logs,
    ])

    <div class="card border-0 shadow-sm rounded-4 mt-4 mb-4">
        <div class="card-header bg-white fw-bold border-bottom-0 pt-3">
            <i class="fa-solid fa-laptop-code text-primary me-2"></i>Nhật ký thiết bị đăng nhập (10 lần gần nhất)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Địa chỉ IP</th>
                            <th>Thiết bị</th>
                            <th>Nền tảng (OS)</th>
                            <th>Trình duyệt</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loginLogs as $login)
                            @php
                                $props = $login->properties ?? [];
                                $isUnusual = $props['is_unusual'] ?? false;
                            @endphp
                            <tr>
                                <td><code>{{ $login->ip }}</code></td>
                                <td>
                                    @if(($props['device'] ?? '') === 'Mobile')
                                        <i class="fa-solid fa-mobile-screen-button text-muted me-1"></i> Mobile
                                    @elseif(($props['device'] ?? '') === 'Tablet')
                                        <i class="fa-solid fa-tablet-screen-button text-muted me-1"></i> Tablet
                                    @else
                                        <i class="fa-solid fa-desktop text-muted me-1"></i> Desktop
                                    @endif
                                </td>
                                <td>{{ $props['platform'] ?? 'N/A' }}</td>
                                <td>{{ $props['browser'] ?? 'N/A' }}</td>
                                <td>{{ $login->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    @if($isUnusual)
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Bất thường</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success px-2 py-1"><i class="fa-solid fa-check me-1"></i>Bình thường</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chưa ghi nhận lịch sử thiết bị đăng nhập.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
