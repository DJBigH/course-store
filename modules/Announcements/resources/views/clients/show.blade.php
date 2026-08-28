@extends($layout ?? 'layouts.client')

@section('content')
@if(($layout ?? 'layouts.client') === 'layouts.client')
<section class="account-page py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="account-sidebar">
                    @include('students::clients.menu')
                </div>
            </div>
            <div class="col-lg-9">
                <div class="account-content">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('students.account.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-home me-1"></i> Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('students.notifications.index', ['locale' => app()->getLocale()]) }}">Thông báo</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
                        </ol>
                    </nav>
@else
    <div class="row justify-content-center">
        <div class="col-md-11 mx-auto">
@endif

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header p-4 p-md-5 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 64px; height: 64px;">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-2 text-dark fw-bold" style="line-height:1.4;">{{ $announcement->title }}</h3>
                            <div class="text-muted" style="font-size:0.95rem;">
                                <i class="fas fa-shield-alt me-1 text-primary"></i> Từ: <strong class="text-dark">Ban quản trị hệ thống</strong>
                                <span class="mx-2 opacity-50">•</span>
                                <i class="far fa-clock me-1"></i> {{ $announcement->sent_at ? $announcement->sent_at->format('H:i - d/m/Y') : $announcement->created_at->format('H:i - d/m/Y') }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4 p-md-5" style="font-size: 1.05rem; line-height: 1.8;">
                    <div class="announcement-content ck-content">
                        {!! $announcement->content !!}
                    </div>

                    @if($announcement->action_url)
                        <div class="mt-5 pt-4 text-center border-top">
                            <a href="{{ $announcement->action_url }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm">
                                {{ $announcement->action_label ?: 'Xem chi tiết' }} <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    @endif
                </div>
                
                <div class="card-footer p-4 text-center border-0">
                    <a href="{{ route('students.notifications.index', ['locale' => app()->getLocale()]) }}" class="btn btn-link text-muted text-decoration-none">
                        <i class="fas fa-chevron-left me-2"></i> Quay lại danh sách
                    </a>
                </div>
            </div>

@if(($layout ?? 'layouts.client') === 'layouts.client')
                </div>
            </div>
        </div>
    </div>
</section>
@else
        </div>
    </div>
@endif
@endsection

@section('stylesheets')
<style>
    .announcement-content img {
        max-width: 100%;
        height: auto !important;
        border-radius: 12px;
        margin: 20px 0;
    }
</style>
@endsection
