@extends('layouts.teacher')

@section('content')
<div class="teacher-panel">
    <div class="teacher-section-title mb-4">
        <h3 class="fw-bold">{{ $pageTitle }}</h3>
        <p class="text-muted">{{ __('courses::teacher/messages.cancellation.description') }}</p>
    </div>

    @if (session('msg_success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('msg_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('msg_danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('msg_danger') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            @if ($activeRequest)
                <div class="teacher-stat-card border-warning mb-4 shadow-sm" style="background: rgba(245, 158, 11, 0.05);">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 rounded bg-warning-soft text-warning me-3">
                            <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                        </div>
                        <div>
                            <h4 class="h5 fw-bold mb-1">
                                {{ __('courses::teacher/messages.cancellation.active_request.status_prefix') }}
                                {{ match($activeRequest->status) {
                                    'pending' => __('courses::teacher/messages.cancellation.active_request.status_pending'),
                                    'approved' => __('courses::teacher/messages.cancellation.active_request.status_approved'),
                                    'rejected' => __('courses::teacher/messages.cancellation.active_request.status_rejected'),
                                    default => $activeRequest->status
                                } }}
                            </h4>
                            <p class="text-muted small mb-0">{{ __('courses::teacher/messages.cancellation.active_request.last_update', ['time' => $activeRequest->updated_at->format('d/m/Y H:i')]) }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 rounded bg-opacity-10 bg-secondary border">
                        <div class="mb-3">
                            <label class="small fw-bold text-muted text-uppercase mb-1">{{ __('courses::teacher/messages.cancellation.active_request.your_reason') }}</label>
                            <div class="text-body fw-medium">{{ $activeRequest->reason }}</div>
                        </div>

                        @if ($activeRequest->admin_note)
                            <div class="mt-3 pt-3 border-top">
                                <label class="small fw-bold text-muted text-uppercase mb-1">{{ __('courses::teacher/messages.cancellation.active_request.admin_note') }}</label>
                                <div class="p-2 rounded bg-opacity-10 bg-primary border-start border-4 border-primary">
                                    <i class="fa-solid fa-reply me-2 text-primary small"></i><span class="text-body">{{ $activeRequest->admin_note }}</span>
                                </div>
                                <div class="small text-end text-muted mt-2">{{ __('courses::teacher/messages.cancellation.active_request.processed_by', ['name' => $activeRequest->processor?->name ?? 'Admin']) }}</div>
                            </div>
                        @endif
                    </div>

                    @if ($activeRequest->status === 'rejected')
                        <div class="mt-4">
                            <a href="{{ route('teacher.dashboard.cancellation', ['reset' => 1]) }}" class="btn btn-primary px-4 py-2 shadow-sm">
                                <i class="fa-solid fa-redo me-2"></i>{{ __('courses::teacher/messages.cancellation.active_request.resubmit_cta') }}
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="teacher-panel shadow-sm border-0 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05) !important;">
                    <div class="alert alert-warning border-0 mb-4" style="background: rgba(245, 158, 11, 0.1);">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <strong>{{ __('courses::teacher/messages.cancellation.form.notice_title') }}</strong> {{ __('courses::teacher/messages.cancellation.form.notice_text') }}
                    </div>

                    <form action="{{ route('teacher.dashboard.cancellation.store') }}" method="POST" id="cancellation-form">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2">{{ __('courses::teacher/messages.cancellation.form.reason_label') }} <span class="text-danger">*</span></label>
                            <textarea name="reason" rows="6" class="form-control" placeholder="{{ __('courses::teacher/messages.cancellation.form.reason_placeholder') }}" required>{{ old('reason') }}</textarea>
                        </div>

                        <div class="mb-4 p-4 rounded-4 border bg-opacity-10" style="background: rgba(59, 130, 246, 0.03); border: 1px dashed rgba(59, 130, 246, 0.2) !important;">
                            <label class="form-label fw-bold mb-2">{{ __('courses::teacher/messages.cancellation.form.security_title') }} <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-3">{{ __('courses::teacher/messages.cancellation.form.security_help') }}</p>
                            
                            <div class="input-group mb-2" style="max-width: 400px;">
                                <input type="text" name="otp" class="form-control form-control-lg fw-bold text-center" placeholder="{{ __('courses::teacher/messages.cancellation.form.otp_placeholder') }}" maxlength="6" required>
                                <button type="button" class="btn btn-outline-primary" id="btn-send-otp">{{ __('courses::teacher/messages.cancellation.form.otp_send') }}</button>
                            </div>
                            <div id="otp-status" class="small mt-2"></div>
                        </div>

                        <div class="mt-5 pt-3 border-top text-end">
                            <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-light px-4 me-2">{{ __('courses::teacher/messages.cancellation.form.back_cta') }}</a>
                            <button type="submit" class="btn btn-danger btn-lg px-5 shadow-sm" id="btn-submit-cancellation">
                                <i class="fa-solid fa-paper-plane me-2"></i>{{ __('courses::teacher/messages.cancellation.form.submit_cta') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="teacher-stat-card bg-primary-soft text-primary border-0 p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-info-circle me-2"></i>{{ __('courses::teacher/messages.cancellation.guide.title') }}</h5>
                <ul class="list-unstyled mb-0 d-grid gap-3 small">
                    <li class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-check mt-1"></i>
                        <span>{{ __('courses::teacher/messages.cancellation.guide.step1') }}</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-check mt-1"></i>
                        <span>{{ __('courses::teacher/messages.cancellation.guide.step2') }}</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-check mt-1"></i>
                        <span>{{ __('courses::teacher/messages.cancellation.guide.step3') }}</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-check mt-1"></i>
                        <span>{{ __('courses::teacher/messages.cancellation.guide.step4') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const i18n = @json(__('courses::teacher/messages.cancellation.js'));
        const btnSendOtp = document.getElementById('btn-send-otp');
        const otpStatus = document.getElementById('otp-status');
        const cancellationForm = document.getElementById('cancellation-form');
        let cooldown = 0;
        let timer = null;

        if (btnSendOtp) {
            btnSendOtp.addEventListener('click', async function() {
                if (cooldown > 0) return;

                btnSendOtp.disabled = true;
                btnSendOtp.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>' + i18n.sending;
                
                try {
                    const response = await fetch("{{ route('teacher.dashboard.cancellation.otp') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();

                    if (data.success) {
                        otpStatus.innerHTML = '<span class="text-success fw-semibold"><i class="fa-solid fa-check-circle me-1"></i>' + data.message + '</span>';
                        cooldown = 60;
                        startTimer();
                    } else {
                        otpStatus.innerHTML = '<span class="text-danger fw-semibold"><i class="fa-solid fa-xmark-circle me-1"></i>' + data.message + '</span>';
                        btnSendOtp.disabled = false;
                        btnSendOtp.innerHTML = i18n.send_otp;
                    }
                } catch (error) {
                    console.error('OTP Error:', error);
                    otpStatus.innerHTML = '<span class="text-danger fw-semibold">' + i18n.connect_error + '</span>';
                    btnSendOtp.disabled = false;
                    btnSendOtp.innerHTML = i18n.send_otp;
                }
            });
        }

        function startTimer() {
            clearInterval(timer);
            timer = setInterval(() => {
                cooldown--;
                if (cooldown <= 0) {
                    clearInterval(timer);
                    btnSendOtp.disabled = false;
                    btnSendOtp.innerHTML = i18n.resend_otp;
                } else {
                    btnSendOtp.innerHTML = i18n.resend_wait.replace(':time', cooldown + 's');
                }
            }, 1000);
        }

        if (cancellationForm) {
            cancellationForm.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('btn-submit-cancellation');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>' + i18n.processing;
            });
        }
    });
</script>
@endsection

@section('stylesheets')
<style>
    .bg-primary-soft { background: rgba(var(--admin-primary-rgb), 0.08); }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
    .truncate-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
