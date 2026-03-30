@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="py-5">
        <div class="container">
            <div class="card shadow-sm border-0" data-pagination-scroll>
                <div class="card-body" data-pagination-container="student-notifications">
                    @forelse ($notifications as $notification)
                        <div class="border-bottom py-3">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h5 class="mb-1">{{ notificationText($notification, 'title', 'Thông báo mới') }}</h5>
                                    <p class="mb-1 text-muted">{{ notificationText($notification, 'message', 'Không có nội dung.') }}</p>
                                    <small class="text-muted">{{ $notification->created_at?->format('d/m/Y H:i') }}</small>
                                </div>
                                @if (is_null($notification->read_at))
                                    <span class="badge bg-warning text-dark">Chưa đọc</span>
                                @else
                                    <span class="badge bg-success">Da doc</span>
                                @endif
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('students.notifications.read', $notification->id) }}">Xem chi tiết</a>
                            </div>
                        </div>
                    @empty
                        <p class="mb-0 text-muted">Bạn chưa có thông báo nào.</p>
                    @endforelse

                    <div class="mt-4">
                        {{ $notifications->links('students::clients.pagination.boostrap') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
