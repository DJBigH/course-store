@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="mb-1">Yêu cầu nâng cấp #{{ $upgrade->id }}</h5>
                    <p class="text-muted mb-0">Xem thông tin yêu cầu thay đổi gói của giảng viên.</p>
                </div>
                <a href="{{ route('teacher-upgrades.index') }}" class="btn btn-light border">Quay lại</a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="border rounded-4 p-4 h-100">
                        <h6 class="fw-bold mb-3">Thông tin giảng viên & Gói yêu cầu</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Giảng viên</dt>
                            <dd class="col-sm-8">
                                <a href="{{ route('teacher.edit', $upgrade->teacher_id) }}" class="text-decoration-none fw-bold">
                                    {{ $upgrade->teacher?->name ?: $upgrade->full_name }}
                                </a>
                            </dd>
                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $upgrade->email }}</dd>
                            <dt class="col-sm-4">Gói yêu cầu</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-primary fs-6">{{ $upgrade->package?->name ?: '-' }}</span>
                            </dd>
                            <dt class="col-sm-4">Phương thức thanh toán</dt>
                            <dd class="col-sm-8">{{ strtoupper((string) $upgrade->payment_method) }}</dd>
                            <dt class="col-sm-4">Trạng thái hiện tại</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-{{ match ($upgrade->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending_payment' => 'warning',
                                    default => 'info',
                                } }}">
                                    {{ $upgrade->display_status }}
                                </span>
                            </dd>
                            <dt class="col-sm-4">Ngày gửi yêu cầu</dt>
                            <dd class="col-sm-8">{{ optional($upgrade->submitted_at)->format('d/m/Y H:i') ?: '-' }}</dd>
                        </dl>

                        @if ($upgrade->note)
                            <hr>
                            <h6 class="fw-bold mb-2">Ghi chú từ giảng viên</h6>
                            <div class="bg-light p-3 rounded-3 border-start border-4 border-primary">
                                {{ $upgrade->note }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="border rounded-4 p-4 mb-4">
                        <h6 class="fw-bold mb-3">Xử lý yêu cầu</h6>
                        @php $isFinalStatus = in_array($upgrade->status, ['approved', 'cancelled', 'rejected']); @endphp
                        <form method="POST" action="{{ route('teacher-upgrades.approve', $upgrade->id) }}" class="mb-3">
                            @csrf
                            <label class="form-label">Phản hồi từ Admin (Gửi cho giảng viên)</label>
                            <textarea name="admin_note" class="form-control" rows="4" {{ $isFinalStatus ? 'disabled' : '' }}
                                placeholder="Ghi chú phê duyệt hoặc hướng dẫn...">{{ old('admin_note', $upgrade->admin_note) }}</textarea>
                            <button type="submit" class="btn btn-success w-100 mt-3" 
                                {{ $upgrade->status !== 'pending_review' ? 'disabled' : '' }}>
                                <i class="fa-solid fa-check me-2"></i> Phê duyệt nâng cấp
                            </button>
                            @if ($upgrade->status === 'pending_payment')
                                <p class="small text-warning mt-2 mb-0 text-center">
                                    <i class="fa-solid fa-circle-info me-1"></i> Chờ giảng viên hoàn tất thanh toán.
                                </p>
                            @endif
                        </form>

                        <form method="POST" action="{{ route('teacher-upgrades.reject', $upgrade->id) }}">
                            @csrf
                            <label class="form-label">Lý do từ chối</label>
                            <textarea name="admin_note" class="form-control" rows="4" {{ $isFinalStatus ? 'disabled' : '' }}
                                placeholder="Vui lòng nêu rõ lý do từ chối yêu cầu nâng cấp...">{{ old('admin_note', $upgrade->admin_note) }}</textarea>
                            <button type="submit" class="btn btn-outline-danger w-100 mt-3" {{ $isFinalStatus ? 'disabled' : '' }}>
                                <i class="fa-solid fa-xmark me-2"></i> Từ chối yêu cầu
                            </button>
                        </form>
                    </div>

                    <div class="border rounded-4 p-4">
                        <h6 class="fw-bold mb-3">Thông tin Review</h6>
                        <p class="mb-2"><strong>Thời gian xử lý:</strong> {{ optional($upgrade->reviewed_at)->format('d/m/Y H:i') ?: '-' }}</p>
                        <p class="mb-2"><strong>Người xử lý:</strong> {{ $upgrade->reviewer?->name ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
