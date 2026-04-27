@extends($layout ?? 'layouts.client')

@section('content')
@if(($layout ?? 'layouts.client') === 'layouts.client')
<main class="page-content" style="min-height: 80vh;">
    <div class="container py-5">
@endif
        <div class="row justify-content-center">
            <div class="col-md-11 mx-auto">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold mb-1">Hộp thư hệ thống</h3>
                        <p class="text-muted mb-0">Các tin nhắn và thông báo quan trọng từ ban quản trị.</p>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="list-group list-group-flush">
                        @forelse($announcements as $item)
                            <a href="{{ route('clients.inbox.show', ['locale' => app()->getLocale(), 'announcement' => $item->id]) }}" 
                               class="list-group-item list-group-item-action p-4 border-bottom {{ !in_array($item->id, $readIds) ? 'unread-item border-start border-4 border-primary' : '' }}">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="rounded-circle {{ !in_array($item->id, $readIds) ? 'bg-primary' : 'bg-secondary' }} opacity-25 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="fas fa-envelope-open {{ !in_array($item->id, $readIds) ? 'text-primary' : 'text-secondary' }} fs-5"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold {{ !in_array($item->id, $readIds) ? 'text-dark' : 'text-muted' }}">{{ $item->title }}</h6>
                                            <small class="text-muted">{{ $item->sent_at ? $item->sent_at->diffForHumans() : $item->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="text-muted mb-0 small text-truncate" style="max-width: 500px;">
                                            {{ $item->message }}
                                        </p>
                                    </div>
                                    @if(!in_array($item->id, $readIds))
                                        <div class="flex-shrink-0">
                                            <span class="badge rounded-pill bg-primary">Mới</span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="p-5 text-center text-muted">
                                <i class="fas fa-inbox fs-1 mb-3 opacity-25"></i>
                                <p>Hộp thư của bạn hiện đang trống.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4">
                    {{ $announcements->links() }}
                </div>
            </div>
        </div>
@if(($layout ?? 'layouts.client') === 'layouts.client')
    </div>
</main>
@endif
@endsection
