@extends('layouts.client')

@section('content')
<main class="page-content" style="background:#f4f6f8; min-height: 80vh;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-9 mx-auto">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('students.account.index', ['locale' => app()->getLocale()]) }}"><i class="fas fa-home me-1"></i> Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('students.notifications.index', ['locale' => app()->getLocale()]) }}">Thông báo</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Chi tiết Hộp thư</li>
                    </ol>
                </nav>

                <div class="card shadow-sm border-0 rounded-4" style="overflow: hidden;">
                    <div class="card-header bg-white p-4 p-md-5 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-shrink-0">
                                @if($promotion->teacher && $promotion->teacher->image)
                                    <img src="{{ getUrlImage($promotion->teacher->image) }}" alt="{{ $promotion->teacher->name }}" class="rounded-circle" width="64" height="64" style="object-fit: cover; border: 2px solid #e2e8f0;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 64px; height: 64px;">
                                        {{ mb_substr($promotion->teacher?->name ?: 'Giảng viên', 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="mb-2 text-dark fw-bold" style="line-height:1.4;">{{ $promotion->title }}</h3>
                                <div class="text-muted" style="font-size:0.95rem;">
                                    <i class="fas fa-user-circle me-1 text-primary"></i> Từ: <strong class="text-dark">{{ $promotion->teacher?->name ?: 'Giảng viên hệ thống' }}</strong>
                                    <span class="mx-2 opacity-50">•</span>
                                    <i class="far fa-clock me-1"></i> {{ $promotion->created_at->format('H:i - d/m/Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 p-md-5 bg-white" style="font-size: 1.05rem; line-height: 1.8; color: #334155;">
                        @if(!empty($promotion->filters['message_html']))
                            <div class="promotion-content ck-content">
                                {!! $promotion->filters['message_html'] !!}
                            </div>
                        @else
                            <div class="promotion-content" style="white-space: pre-wrap;">
                                {{ $promotion->message }}
                            </div>
                        @endif

                        @if(!empty($promotion->filters['cta_enabled']) && !empty($promotion->filters['cta_url']) && !empty($promotion->filters['cta_label']))
                            <div class="mt-5 pt-4 text-center border-top">
                                <a href="{{ $promotion->filters['cta_url'] }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm" target="_blank" rel="noopener noreferrer">
                                    {{ $promotion->filters['cta_label'] }} <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
