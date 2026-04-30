@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">{{ $pageTitle }}</h5>
                    <p class="text-muted mb-0">Theo dõi và phê duyệt các yêu cầu nâng cấp gói từ giảng viên hiện tại.</p>
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif

            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach (['pending_payment' => 'Chờ thanh toán', 'pending_review' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Bị từ chối'] as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Lọc</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Giảng viên</th>
                            <th>Gói yêu cầu</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày yêu cầu</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($upgrades as $upgrade)
                            <tr>
                                <td>#{{ $upgrade->id }}</td>
                                <td>
                                    <strong>{{ $upgrade->teacher?->name ?: $upgrade->full_name }}</strong>
                                    <div class="text-muted small">{{ $upgrade->email }}</div>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $upgrade->package?->name ?: '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ strtoupper((string) $upgrade->payment_method) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ match ($upgrade->status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'pending_payment' => 'warning',
                                        default => 'info',
                                    } }}">
                                        {{ $upgrade->display_status }}
                                    </span>
                                </td>
                                <td>{{ optional($upgrade->submitted_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('teacher-upgrades.show', $upgrade->id) }}" class="btn btn-sm btn-primary">
                                        Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Chưa có yêu cầu nâng cấp nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $upgrades->links() }}
            </div>
        </div>
    </div>
@endsection
