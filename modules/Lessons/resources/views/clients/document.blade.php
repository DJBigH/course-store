@php
    $documents = getLessonByPosition($course, null, true)->filter(fn($item) => !empty($item->document?->url));
@endphp

<div class="cp-document-container p-3">
    @forelse ($documents as $item)
        @php
            $availability = $lessonAvailabilityMap[$item->id] ?? null;
            $canOpenDocument = $availability['can_open'] ?? ($hasCourse || (int) $item->is_trial === 1);
            $documentLockedMessage = $availability['message'] ?? __('lessons::clients/common.buy_to_view_document');
        @endphp
        
        <div class="cp-document-item {{ !$canOpenDocument ? 'locked' : '' }}">
            <div class="cp-document-left">
                <div class="cp-document-icon">
                    @if ($canOpenDocument)
                        <i class="fa-solid fa-file-pdf"></i>
                    @else
                        <i class="fa-solid fa-lock"></i>
                    @endif
                </div>
                <div class="cp-document-info">
                    @if ($canOpenDocument)
                        <a target="_blank" href="{{ $item->document->url }}" class="cp-document-name">
                            {{ $item->name_locale }}
                        </a>
                        <span class="cp-document-size">{{ getSize($item->document->size) }}</span>
                    @else
                        <span class="cp-document-name text-muted">{{ $item->name_locale }}</span>
                        <span class="cp-document-locked-msg">{{ $documentLockedMessage }}</span>
                    @endif
                </div>
            </div>
            
            @if ($canOpenDocument)
                <a target="_blank" href="{{ $item->document->url }}" class="cp-document-download">
                    <i class="fa-solid fa-download"></i>
                </a>
            @endif
        </div>
    @empty
        <div class="text-center py-5 opacity-50">
            <i class="fa-solid fa-folder-open d-block mb-2 fs-3"></i>
            <div class="small">{{ __('lessons::clients/common.no_document') }}</div>
        </div>
    @endforelse
</div>

<style>
    .cp-document-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background-color: var(--cp-nav-bg);
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        border: 1px solid var(--cp-border);
        transition: all 0.2s;
    }

    .cp-document-item:hover:not(.locked) {
        border-color: var(--cp-tab-active);
        transform: translateY(-2px);
    }

    .cp-document-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    .cp-document-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--cp-sidebar-bg);
        border-radius: 0.5rem;
        color: var(--cp-tab-active);
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .cp-document-item.locked .cp-document-icon {
        color: var(--cp-text-muted);
        opacity: 0.5;
    }

    .cp-document-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .cp-document-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--cp-text-main);
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cp-document-size, .cp-document-locked-msg {
        font-size: 0.75rem;
        color: var(--cp-text-muted);
    }

    .cp-document-download {
        color: var(--cp-text-muted);
        font-size: 1rem;
        padding: 0.5rem;
        transition: color 0.2s;
    }

    .cp-document-download:hover {
        color: var(--cp-tab-active);
    }
</style>
