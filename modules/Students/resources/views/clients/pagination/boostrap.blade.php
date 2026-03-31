@if ($paginator->hasPages())
    <nav class="pagination-nav d-flex justify-content-between align-items-center flex-wrap gap-2">

        {{-- Mobile: Previous / Next --}}
        <div class="d-flex d-sm-none gap-2 account-pagination-mobile">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="btn btn-outline-secondary btn-sm disabled page-link-compact page-link-compact--mobile" aria-disabled="true">
                    <i class="bi bi-chevron-left"></i>
                    <span>{{ __('pagination.previous') }}</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-outline-primary btn-sm page-link-compact page-link-compact--mobile" rel="prev">
                    <i class="bi bi-chevron-left"></i>
                    <span>{{ __('pagination.previous') }}</span>
                </a>
            @endif

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-outline-primary btn-sm page-link-compact page-link-compact--mobile" rel="next">
                    <span>{{ __('pagination.next') }}</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <span class="btn btn-outline-secondary btn-sm disabled page-link-compact page-link-compact--mobile" aria-disabled="true">
                    <span>{{ __('pagination.next') }}</span>
                    <i class="bi bi-chevron-right"></i>
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="d-none d-sm-flex align-items-center justify-content-between w-100">

            {{-- Info --}}
            <div class="text-muted small">
                {{ __('students::clients/paginator.display') }}
                <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                –
                <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                /
                <span class="fw-semibold">{{ $paginator->total() }}</span>
                {{ __('students::clients/paginator.result') }}
            </div>

            {{-- Pagination --}}
            <ul class="pagination mb-0 pagination-custom">

                {{-- Previous --}}
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    @if ($paginator->onFirstPage())
                        <span class="page-link" aria-hidden="true">
                            <i class="bi bi-chevron-left"></i>
                        </span>
                    @else
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous" rel="prev">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    @endif
                </li>

                {{-- Pages --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled">
                            <span class="page-link">{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    @if ($paginator->hasMorePages())
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next" rel="next">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @else
                        <span class="page-link" aria-hidden="true">
                            <i class="bi bi-chevron-right"></i>
                        </span>
                    @endif
                </li>

            </ul>
        </div>
    </nav>
@endif
