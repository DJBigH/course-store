@php
    $typeLabels = __('contacts::teacher.support.types');
    $categoryLabels = __('contacts::teacher.support.categories');
    $statusLabels = __('contacts::teacher.support.status');
@endphp

<style>
    .friendly-history-item {
        background: var(--admin-surface-1);
        border: 1px solid var(--admin-sidebar-border);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
        transition: all 0.2s ease;
    }
    html[data-theme="dark"] .friendly-history-item {
        border-color: #374151;
        background: #1f2937;
    }
    .friendly-history-item:hover {
        border-color: var(--admin-primary) !important;
    }
    .status-badge-friendly {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 800;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    .status-new { background: #6b7280; color: #fff; }
    .status-in_progress { background: #3b82f6; color: #fff; }
    .status-resolved { background: #10b981; color: #fff; }
    .status-rejected { background: #ef4444; color: #fff; }
    .status-need_info { background: #f59e0b; color: #fff; }

    .history-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--admin-muted);
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }
    .history-content {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--admin-text);
        margin-bottom: 1rem;
    }
    .history-content p { margin-bottom: 0; }
</style>

@if ($items->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <h5 class="fw-bold text-muted">Bạn chưa có yêu cầu hỗ trợ nào.</h5>
    </div>
@else
    <div class="history-list">
        @foreach ($items as $item)
            <div class="friendly-history-item">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-2 px-3">{{ $typeLabels[$item->submission_type] ?? $item->submission_type }}</span>
                            <span class="text-muted small fw-bold">{{ $categoryLabels[$item->category] ?? $item->category }}</span>
                        </div>
                        <div class="history-content">
                            {!! $item->subject !!}
                        </div>
                        <div class="text-muted small">
                            <i class="far fa-clock me-1"></i> {{ __('contacts::teacher.support.history.label_submitted') }}: {{ $item->created_at?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="history-label">{{ __('contacts::teacher.support.history.label_status') }}</div>
                        <span class="status-badge-friendly status-{{ $item->workflow_status }}">
                            {{ $statusLabels[$item->workflow_status] ?? $item->workflow_status }}
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top small text-muted opacity-50">
                    {{ __('contacts::teacher.support.history.label_id') }}: #{{ $item->id }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 ajax-pagination">
        {{ $items->links() }}
    </div>
@endif
