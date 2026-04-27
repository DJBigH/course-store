@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $typeLabels = [
            'App\\Notifications\\CouponStudentNotification' => 'Ma giam gia',
            'App\\Notifications\\StudentNotification' => 'Hoc vien',
        ];
    @endphp

    <section class="account-page py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="account-content">
                        <div class="card mb-3">
                            <div class="card-body">
                                <form method="GET"
                                    action="{{ route('students.notifications.index', ['locale' => app()->getLocale()]) }}"
                                    class="row g-3 align-items-end">
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
                                            @foreach ($types ?? collect() as $type)
                                                <option value="{{ $type }}" @selected(request('type') === $type)>
                                                    {{ $typeLabels[$type] ?? class_basename(str_replace('Notification', '', $type)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex gap-2 justify-content-md-end flex-wrap">
                                        <a href="{{ route('students.notifications.index', ['locale' => app()->getLocale()]) }}"
                                            class="btn btn-outline-secondary">Reset</a>
                                        <button class="btn btn-primary">Lọc</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0" data-pagination-scroll>
                            <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <span class="fw-bold">{{ __('clients/common.notifications') }}</span>
                                <form action="{{ route('students.notifications.mark-all-read', ['locale' => app()->getLocale()]) }}"
                                    method="POST" class="m-0" data-mark-all-read-form>
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary"
                                        data-mark-all-read-button>Đánh dấu tất cả đã đọc</button>
                                </form>
                            </div>
                            <div class="card-body" data-pagination-container="student-notifications">
                                @forelse ($notifications as $notification)
                                    @php
                                        $severity = data_get($notification->data, 'severity', 'secondary');
                                        $severityClass = match ($severity) {
                                            'success' => 'success',
                                            'warning' => 'warning',
                                            'danger', 'error', 'critical' => 'danger',
                                            'info', 'primary' => 'primary',
                                            default => 'secondary',
                                        };
                                        $iconClass = data_get($notification->data, 'icon', 'fas fa-bell');
                                        $titleTranslations = data_get($notification->data, 'title_translations');
                                        $messageTranslations = data_get($notification->data, 'message_translations');
                                        $title = is_array($titleTranslations)
                                            ? $titleTranslations[app()->getLocale()] ??
                                                $titleTranslations['vi'] ??
                                                data_get($notification->data, 'title', 'Thông báo mới')
                                            : data_get($notification->data, 'title', 'Thông báo mới');
                                        $message = is_array($messageTranslations)
                                            ? $messageTranslations[app()->getLocale()] ??
                                                $messageTranslations['vi'] ??
                                                data_get($notification->data, 'message', 'Không có nội dung.')
                                            : data_get($notification->data, 'message', 'Không có nội dung.');
                                        $meta = data_get($notification->data, 'meta', []);
                                    @endphp
                                    <div class="border-bottom py-3" data-notification-item>
                                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                            <div class="d-flex gap-3 flex-grow-1">
                                                <div class="pt-1 text-{{ $severityClass }}">
                                                    <i class="{{ $iconClass }}"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                        <h5 class="mb-0">{{ $title }}</h5>
                                                        <span class="badge bg-{{ $severityClass }}">
                                                            {{ $typeLabels[$notification->type] ?? class_basename(str_replace('Notification', '', $notification->type)) }}
                                                        </span>
                                                        @if (is_null($notification->read_at))
                                                            <span class="badge bg-warning text-dark" data-notification-status
                                                                data-read-label="Đã đọc">Chưa đọc</span>
                                                        @else
                                                            <span class="badge bg-success" data-notification-status
                                                                data-read-label="Đã đọc">Đã đọc</span>
                                                        @endif
                                                    </div>
                                                    <p class="mb-1 text-muted">{{ $message }}</p>
                                                    @if (!empty($meta))
                                                        <div class="small text-muted mb-1">
                                                            @foreach ($meta as $key => $value)
                                                                @if (filled($value) && !is_array($value))
                                                                    <span class="me-3"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                                        {{ $value }}</span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <small
                                                        class="text-muted">{{ $notification->created_at?->format('d/m/Y H:i') }}</small>
                                                </div>
                                            </div>
                                            <div class="text-start text-md-end ms-md-auto">
                                                <a
                                                    href="{{ route('students.notifications.read', ['locale' => app()->getLocale(), 'id' => $notification->id]) }}">
                                                    Xem chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="mb-0 text-muted">{{ __('clients/common.no_notifications') }}</p>
                                @endforelse

                                <div class="mt-4">
                                    {{ $notifications->links('students::clients.pagination.boostrap') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
