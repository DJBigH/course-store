@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Ứng tuyển giảng viên</h5>
                    <p class="text-muted mb-0">Theo dõi các hồ sơ đăng ký mới, trạng thái và gói đã chọn.</p>
                </div>
                <a href="{{ route('teacher-packages.index') }}" class="btn btn-outline-primary">Quản lý gói</a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif

            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Trang thai</label>
                    <select name="status" class="form-select">
                        <option value="">Tat ca</option>
                        @foreach (['draft' => 'Bản nháp', 'pending_payment' => 'Chờ thanh toán', 'pending_review' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Bị từ chối'] as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Loc</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Học viên</th>
                            <th>Gói</th>
                            <th class="text-end">Giá tiền</th>
                            <th>Trạng thái</th>
                            <th>Gửi lúc</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            <tr>
                                <td>#{{ $application->id }}</td>
                                <td>
                                    <strong>{{ $application->full_name }}</strong>
                                    <div class="text-muted small">{{ $application->student?->email }}</div>
                                </td>
                                <td>{{ $application->package?->name ?: '-' }}</td>
                                <td class="text-end fw-bold text-primary">
                                    {{ money($application->orders->first()?->total ?? $application->package?->price ?? 0) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ match ($application->status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'pending_payment' => 'warning',
                                        default => 'info',
                                    } }}">
                                        {{ $application->display_status }}
                                    </span>
                                </td>
                                <td>{{ optional($application->submitted_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('teacher-applications.show', $application->id) }}" class="btn btn-sm btn-primary">
                                        Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chưa có hồ sơ đăng ký nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $applications->links() }}
        </div>
    </div>
@endsection
