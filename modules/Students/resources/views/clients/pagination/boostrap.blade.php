@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2">

        {{-- Mobile: Previous / Next --}}
        <div class="d-flex d-sm-none gap-2">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="btn btn-outline-secondary btn-sm disabled">
                    <i class="bi bi-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <span class="btn btn-outline-secondary btn-sm disabled">
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
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
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
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>

            </ul>
        </div>
    </nav>
@endif
@section('stylesheets')
    <style>
        .pagination-custom .page-link {
            border-radius: 8px;
            margin: 0 2px;
            color: #0d6efd;
            transition: all 0.2s ease;
        }

        .pagination-custom .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
            font-weight: 600;
        }

        .pagination-custom .page-link:hover {
            background-color: rgba(13, 110, 253, .1);
        }

        .pagination-custom .page-item.disabled .page-link {
            color: #adb5bd;
        }
    </style>
@endsection
