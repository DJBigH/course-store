@php
    $documents = getLessonByPosition($course, null, true)->filter(fn($item) => !empty($item->document?->url));
@endphp

<ul class="list-group mt-3 document-list">
    @forelse ($documents as $item)
        @php
            $canOpenDocument = $hasCourse || (int) $item->is_trial === 1;
        @endphp
        <li class="list-group-item d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                @if ($canOpenDocument)
                    <a target="_blank" href="{{ $item->document->url }}">
                        <i class="fa-solid fa-file-arrow-down text-primary"></i>
                    </a>
                    <a target="_blank" href="{{ $item->document->url }}" class="document-name">
                        {{ $item->name_locale }}
                    </a>
                @else
                    <span class="text-secondary">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <div>
                        <span class="document-name text-muted d-block">{{ $item->name_locale }}</span>
                        <small class="text-muted">{{ __('lessons::clients/common.buy_to_view_document') }}</small>
                    </div>
                @endif
            </div>

            <span class="badge bg-light text-dark document-size">
                @if ($canOpenDocument)
                    {{ getSize($item->document->size) }}
                @else
                    {{ __('lessons::clients/common.locked_document') }}
                @endif
            </span>
        </li>
    @empty
        <li class="list-group-item text-center text-muted">
            {{ __('lessons::clients/common.no_document') }}
        </li>
    @endforelse
</ul>
