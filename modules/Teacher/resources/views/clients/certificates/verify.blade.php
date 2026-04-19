@extends('layouts.client')

@section('content')
<div class="certificate-verify-wrap py-5" style="background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.05), transparent), radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.05), transparent); min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10">
                <div class="verification-card p-4 p-md-5" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-radius: 32px; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 40px 100px rgba(15, 23, 42, 0.08);">
                    
                    @if($success)
                        <div class="text-center mb-5">
                            <div class="verify-icon-wrap mb-4" style="display: inline-flex; width: 84px; height: 84px; background: #ecfdf5; color: #10b981; border-radius: 50%; align-items: center; justify-content: center; font-size: 40px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h1 class="h2 fw-bold mb-2 text-dark">{{ __('courses::teacher/messages.certificates.verify.title_success') }}</h1>
                            <p class="text-muted">{{ __('courses::teacher/messages.certificates.verify.desc_success') }}</p>
                        </div>

                        <div class="verify-details">
                            <div class="detail-row mb-4">
                                <label class="text-muted small text-uppercase fw-bold ls-1 mb-1 d-block">{{ __('courses::teacher/messages.certificates.mail.student_label') }}</label>
                                <div class="h4 fw-bold text-dark">{{ $certificate->student_name_snapshot }}</div>
                            </div>

                            <div class="detail-row mb-4">
                                <label class="text-muted small text-uppercase fw-bold ls-1 mb-1 d-block">{{ __('courses::teacher/messages.certificates.mail.course_label') }}</label>
                                <div class="h5 fw-bold text-primary">{{ $certificate->course_name_snapshot }}</div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 mb-4">
                                    <label class="text-muted small text-uppercase fw-bold ls-1 mb-1 d-block">{{ __('courses::teacher/messages.certificates.issued_date_label') }}</label>
                                    <div class="fw-bold text-dark">{{ optional($certificate->issued_at)->format('d/m/Y') }}</div>
                                </div>
                                <div class="col-sm-6 mb-4">
                                    <label class="text-muted small text-uppercase fw-bold ls-1 mb-1 d-block">{{ __('courses::teacher/messages.certificates.mail.code_label') }}</label>
                                    <div class="fw-bold text-dark">{{ $certificate->code }}</div>
                                </div>
                            </div>

                            <div class="detail-row mb-4 pt-4 border-top">
                                <label class="text-muted small text-uppercase fw-bold ls-1 mb-1 d-block">{{ __('courses::teacher/messages.certificates.mail.teacher_label') }}</label>
                                <div class="d-flex align-items: center; gap: 12px;">
                                    @if($certificate->teacher && $certificate->teacher->image)
                                        <img src="{{ image_url($certificate->teacher->image) }}" alt="" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                                    @else
                                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #64748b;">
                                            {{ substr($certificate->teacher_name_snapshot, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $certificate->teacher_name_snapshot }}</div>
                                        <div class="small text-muted">{{ $certificate->teacher && $certificate->teacher->title ? $certificate->teacher->title : __('courses::teacher/messages.certificates.status_issued') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 text-center">
                            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-primary rounded-pill px-5 py-2 fw-bold">{{ __('courses::teacher/messages.certificates.verify.back_home') }}</a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="verify-icon-wrap mb-4" style="display: inline-flex; width: 84px; height: 84px; background: #fff1f2; color: #f43f5e; border-radius: 50%; align-items: center; justify-content: center; font-size: 40px; box-shadow: 0 10px 25px rgba(244, 63, 94, 0.15);">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h1 class="h3 fw-bold mb-3 text-dark">{{ __('courses::teacher/messages.certificates.verify.title_failed') }}</h1>
                            
                            <div class="alert alert-light border-0 p-3 rounded-4 mb-4" style="background: #f8fafc; color: #475569;">
                                {{ $message }}
                                @if(!empty($code))
                                    <div class="mt-2 small text-muted">{{ __('courses::teacher/messages.certificates.verify.check_code_label') }}: <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $code }}</code></div>
                                @endif
                            </div>

                            <p class="text-muted mb-4 px-lg-5">{{ __('courses::teacher/messages.certificates.verify.desc_failed') }}</p>
                            
                            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">{{ __('courses::teacher/messages.certificates.verify.back_home') }}</a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ls-1 { letter-spacing: 0.05em; }
    .verification-card {
        animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes cardAppear {
        from { opacity: 0; transform: translateY(40px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .detail-row { position: relative; }
</style>
@endsection
