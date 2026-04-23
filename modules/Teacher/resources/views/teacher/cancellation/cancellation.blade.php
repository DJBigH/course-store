@extends('layouts.teacher')

@section('content')
<div class="teacher-panel">
    {{-- Header Section --}}
    <div class="teacher-section-title mb-4">
        <h3 class="fw-bold">{{ $pageTitle }}</h3>
        <p class="text-muted">{{ __('teacher::teacher/cancellation.description') }}</p>
    </div>

    {{-- Alert Messages --}}
    @if (session('msg_success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-4 me-3"></i>
                <div>{{ session('msg_success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('msg_danger'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-triangle-exclamation fs-4 me-3"></i>
                <div>{{ session('msg_danger') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            @if ($activeRequest)
                {{-- Current Request Status --}}
                <div class="card border-0 shadow-sm resignation-status-card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="status-icon-wrapper me-3 status-{{ $activeRequest->status }}">
                                <i class="fa-solid {{ match($activeRequest->status) {
                                    'pending' => 'fa-clock-rotate-left',
                                    'approved' => 'fa-check-double',
                                    'rejected' => 'fa-circle-xmark',
                                    default => 'fa-info-circle'
                                } }} fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">
                                    {{ __('teacher::teacher/cancellation.active_request.status_prefix') }}
                                    <span class="status-text-{{ $activeRequest->status }}">
                                        {{ __('teacher::teacher/cancellation.status.' . $activeRequest->status) }}
                                    </span>
                                </h5>
                                <p class="text-muted small mb-0">{{ __('teacher::teacher/cancellation.active_request.last_update', ['time' => $activeRequest->updated_at?->format('d/m/Y H:i')]) }}</p>
                            </div>
                        </div>

                        <div class="resignation-details p-3 rounded-4">
                            <div class="mb-3">
                                <label class="small fw-bold text-muted text-uppercase mb-2 d-block">{{ __('teacher::teacher/cancellation.active_request.your_reason') }}</label>
                                <div class="reason-text p-3 rounded-3 bg-light-subtle border">
                                    {{ $activeRequest->reason }}
                                </div>
                            </div>

                            @if ($activeRequest->admin_note)
                                <div class="admin-feedback mt-4 pt-3 border-top">
                                    <label class="small fw-bold text-muted text-uppercase mb-2 d-block">{{ __('teacher::teacher/cancellation.active_request.admin_note') }}</label>
                                    <div class="p-3 rounded-3 admin-note-box border-start border-4 border-primary shadow-sm">
                                        <i class="fa-solid fa-reply me-2 text-primary small"></i>
                                        <span class="fw-medium text-dark-emphasis">{{ $activeRequest->admin_note }}</span>
                                    </div>
                                    <div class="small text-end text-muted mt-2 fst-italic">
                                        {{ __('teacher::teacher/cancellation.active_request.processed_by', ['name' => $activeRequest->processor?->name ?? 'Administrator']) }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($activeRequest->status === 'rejected')
                            <div class="text-center mt-4">
                                <a href="{{ route('teacher.dashboard.cancellation', ['reset' => 1]) }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                                    <i class="fa-solid fa-redo me-2"></i>{{ __('teacher::teacher/cancellation.active_request.resubmit_cta') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                {{-- New Resignation Form --}}
                <div class="card border-0 shadow-sm resignation-form-card mb-4">
                    <div class="card-body p-4">
                        <div class="alert alert-warning border-0 p-3 rounded-4 mb-4 resignation-notice">
                            <div class="d-flex">
                                <i class="fa-solid fa-triangle-exclamation fs-4 me-3 mt-1"></i>
                                <div>
                                    <strong class="d-block mb-1">{{ __('teacher::teacher/cancellation.form.notice_title') }}</strong>
                                    <span class="small opacity-80">{{ __('teacher::teacher/cancellation.form.notice_text') }}</span>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('teacher.dashboard.cancellation.store') }}" method="POST" id="cancellation-form">
                            @csrf
                            <div class="form-group-custom mb-4">
                                <label class="form-label-friendly mb-2 d-flex align-items-center">
                                    {{ __('teacher::teacher/cancellation.form.reason_label') }}
                                    <span class="text-danger ms-1">*</span>
                                </label>
                                <span class="form-hint mb-2">{{ __('teacher::teacher/cancellation.form.reason_hint') }}</span>
                                <textarea name="reason" rows="6" class="form-control input-friendly" 
                                    placeholder="{{ __('teacher::teacher/cancellation.form.reason_placeholder') }}" required>{{ old('reason') }}</textarea>
                            </div>

                            <div class="security-verification p-4 rounded-4 mb-4 border-dashed">
                                <h6 class="fw-bold mb-3 d-flex align-items-center">
                                    <i class="fa-solid fa-shield-halved text-primary me-2"></i>
                                    {{ __('teacher::teacher/cancellation.form.security_title') }}
                                </h6>
                                <p class="text-muted small mb-4">{{ __('teacher::teacher/cancellation.form.security_help') }}</p>
                                
                                <div class="otp-container">
                                    <div class="input-group otp-group">
                                        <input type="text" name="otp" id="otp-input" class="form-control form-control-lg text-center fw-bold" 
                                            placeholder="{{ __('teacher::teacher/cancellation.form.otp_placeholder') }}" maxlength="6" required>
                                        <button type="button" class="btn btn-primary px-4" id="btn-send-otp">
                                            {{ __('teacher::teacher/cancellation.form.otp_send') }}
                                        </button>
                                    </div>
                                    <div id="otp-status" class="mt-3"></div>
                                </div>
                            </div>

                            <div class="form-actions mt-5 d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-link text-muted text-decoration-none">
                                    <i class="fa-solid fa-arrow-left me-2"></i>{{ __('teacher::teacher/cancellation.form.back_cta') }}
                                </a>
                                <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 shadow" id="btn-submit-cancellation">
                                    <i class="fa-solid fa-paper-plane me-2"></i>{{ __('teacher::teacher/cancellation.form.submit_cta') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Guide Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm resignation-guide-card sticky-top" style="top: 2rem;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 d-flex align-items-center">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i>
                        {{ __('teacher::teacher/cancellation.form.notice_title') }}
                    </h6>
                    <div class="guide-steps">
                        @for ($i = 1; $i <= 4; $i++)
                            <div class="guide-step mb-3 d-flex">
                                <div class="step-icon text-primary me-2"><i class="fa-solid fa-check-circle small"></i></div>
                                <span class="small opacity-75">{{ __('teacher::teacher/cancellation.guide.step' . $i) }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Inline CSS --}}
<style>
    /* Status Styling */
    .status-icon-wrapper {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .status-icon-wrapper.status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .status-icon-wrapper.status-approved { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .status-icon-wrapper.status-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    
    .status-text-pending { color: #f59e0b; }
    .status-text-approved { color: #10b981; }
    .status-text-rejected { color: #ef4444; }

    /* Layout Styling */
    .resignation-details { background: rgba(0, 0, 0, 0.02); border: 1px solid rgba(0, 0, 0, 0.05); }
    [data-bs-theme="dark"] .resignation-details { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); }
    
    .admin-note-box { background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.1) !important; }
    
    .border-dashed { border: 2px dashed rgba(0, 0, 0, 0.1) !important; }
    [data-bs-theme="dark"] .border-dashed { border: 2px dashed rgba(255, 255, 255, 0.1) !important; }

    .input-friendly { border-radius: 12px; padding: 12px 15px; border: 1.5px solid rgba(0,0,0,0.1); }
    [data-bs-theme="dark"] .input-friendly { background: #1f2937; border-color: rgba(255,255,255,0.1); color: #fff; }
    
    .form-label-friendly { font-weight: 600; color: var(--admin-text-dark); }
    .form-hint { font-size: 0.85rem; color: #6b7280; display: block; }

    .otp-group .form-control { border-radius: 12px 0 0 12px !important; letter-spacing: 0.5rem; font-size: 1.5rem; }
    .otp-group .btn { border-radius: 0 12px 12px 0 !important; }
    
    /* Animation */
    .card { transition: transform 0.2s; border-radius: 1.25rem !important; }
</style>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const i18n = @json(__('teacher::teacher/cancellation.js'));
        const btnSendOtp = document.getElementById('btn-send-otp');
        const otpStatus = document.getElementById('otp-status');
        const cancellationForm = document.getElementById('cancellation-form');
        let cooldown = 0;
        let timer = null;

        if (btnSendOtp) {
            btnSendOtp.addEventListener('click', async function() {
                if (cooldown > 0) return;

                btnSendOtp.disabled = true;
                btnSendOtp.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>' + i18n.sending;
                
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                        otpStatus.innerHTML = '<div class="alert alert-success border-0 py-2 small"><i class="fa-solid fa-check-circle me-1"></i>' + data.message + '</div>';
                        cooldown = 60;
                        startTimer();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Lỗi', text: data.message });
                        btnSendOtp.disabled = false;
                        btnSendOtp.innerHTML = i18n.send_otp;
                    }
                } catch (error) {
                    console.error('OTP Error:', error);
                    Swal.fire({ icon: 'error', title: 'Lỗi kết nối', text: i18n.connect_error });
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
                
                // Front-end validation
                const reason = form.querySelector('[name="reason"]').value.trim();
                const otp = document.getElementById('otp-input').value.trim();
                
                if (!reason || !otp) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng nhập đầy đủ lý do và mã OTP.' });
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>' + i18n.processing;
            });
        }
    });
</script>
@endsection
