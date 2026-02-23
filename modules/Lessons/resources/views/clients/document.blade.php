@php
    $documents = getLessonByPosition($course, null, true)->filter(fn($item) => !empty($item->document?->url));
@endphp

<ul class="list-group mt-3 document-list">
    @forelse ($documents as $item)
        <li class="list-group-item d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <a target="_blank" href="{{ $item->document->url }}">
                    <i class="fa-solid fa-file-arrow-down text-primary"></i>
                </a>
                <a target="_blank" href="{{ $item->document->url }}" class="document-name">
                    {{ $item->name }}
                </a>
            </div>

            <span class="badge bg-light text-dark document-size">
                {{ getSize($item->document->size) }}
            </span>
        </li>
    @empty
        <li class="list-group-item text-center text-muted">
            {{ __('lessons::clients/common.no_document') }}
        </li>
    @endforelse
</ul>
