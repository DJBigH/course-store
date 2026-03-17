@extends('layouts.backend')

@section('content')
    <div class="card">
        <div class="card-body">
            @forelse ($notifications as $notification)
                <div class="border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h5 class="mb-1">{{ notificationText($notification, 'title', 'Thong bao moi') }}</h5>
                            <p class="mb-1 text-muted">{{ notificationText($notification, 'message', 'Khong co noi dung.') }}</p>
                            <small class="text-muted">{{ $notification->created_at?->format('d/m/Y H:i') }}</small>
                        </div>
                        @if (is_null($notification->read_at))
                            <span class="badge bg-warning text-dark">Chua doc</span>
                        @else
                            <span class="badge bg-success">Da doc</span>
                        @endif
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('admin.notifications.read', $notification->id) }}">Xem chi tiet</a>
                    </div>
                </div>
            @empty
                <p class="mb-0 text-muted">Khong co thong bao nao.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
@endsection
