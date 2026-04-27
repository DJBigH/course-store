<section class="sub-header">
    <div class="container">
        @if (session('msg'))
            <div class="alert alert-{{ session('msgType') ?? 'info' }} alert-dismissible fade show mt-3" role="alert">
                {{ session('msg') }}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        @endif
        <div class="sub-title">
            <p>
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"> {{ __('common.home') }}
                </a>
                <i class="fa-solid fa-angle-right"></i>
                {{ $pageName ?? 'Không có dữ liệu' }}
            </p>
            <h2>{{ $pageName ?? 'Không có dữ liệu' }}</h2>
        </div>
    </div>
</section>
