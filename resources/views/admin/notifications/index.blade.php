@extends('layouts.backend')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="unread" @selected(request('status') === 'unread')>Chưa đọc</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Loại</label>
                    <select name="type" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach (($types ?? collect()) as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ notificationTypeLabel($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2 justify-content-md-end flex-wrap">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">Reset</a>
                    <button class="btn btn-primary">Lọc</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <span class="fw-bold">Thông báo</span>
            <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">Đánh dấu tất cả đã đọc</button>
            </form>
        </div>
        <div class="card-body">
            @forelse ($notifications as $notification)
                @php
                    $severityClass = notificationSeverityClass($notification);
                    $meta = notificationData($notification, 'meta', []);
                @endphp
                <div class="border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="d-flex gap-3 flex-grow-1">
                            <div class="pt-1 text-{{ $severityClass }}">
                                <i class="{{ notificationIconClass($notification) }}"></i>
                            </div>
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h5 class="mb-0">{{ notificationText($notification, 'title', 'Thông báo mới') }}</h5>
                                    <span class="badge bg-{{ $severityClass }}">{{ notificationTypeLabel($notification->type) }}</span>
                                    @if (is_null($notification->read_at))
                                        <span class="badge bg-warning text-dark">Chưa đọc</span>
                                    @else
                                        <span class="badge bg-success">Đã đọc</span>
                                    @endif
                                </div>
                                <p class="mb-1 text-muted">{{ notificationText($notification, 'message', 'Không có nội dung.') }}</p>
                                <small class="text-muted d-block">{{ $notification->created_at?->format('d/m/Y H:i') }}</small>
                                @if (!empty($meta))
                                    <div class="small text-muted mt-1">
                                        @foreach ($meta as $key => $value)
                                            @if (filled($value) && !is_array($value))
                                                <span class="me-3"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-start text-md-end ms-md-auto">
                            <a href="{{ route('admin.notifications.read', $notification->id) }}">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="mb-0 text-muted">Không có thông báo nào.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
@endsection


