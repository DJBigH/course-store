@extends('layouts.teacher')

@section('content')
<div class="teacher-page-shell">
    <div class="row justify-content-center py-5">
        <div class="col-xl-7 col-lg-9">
            <div class="card border-0 shadow-lg overflow-hidden rounded-5 gift-card-container">
                <div class="gift-card-header text-center p-5 position-relative">
                    <!-- Floating Ornaments -->
                    <div class="ornament ornament-1"><i class="fa-solid fa-star"></i></div>
                    <div class="ornament ornament-2"><i class="fa-solid fa-heart"></i></div>
                    <div class="ornament ornament-3"><i class="fa-solid fa-sparkles"></i></div>
                    
                    <div class="gift-box-wrapper mb-4">
                        <div class="gift-box">
                            <div class="gift-box-lid"></div>
                            <div class="gift-box-body"></div>
                            <div class="gift-box-ribbon"></div>
                        </div>
                    </div>
                    
                    <h2 class="fw-extrabold text-white mb-2 display-5">{{ __('teacher::teacher/telegram.gift.success_title') }}</h2>
                    <p class="text-white-50 fs-5 mb-0">{{ __('teacher::teacher/telegram.gift.subtitle') }}</p>
                </div>
                
                <div class="card-body p-5" id="claimFormSection">
                    <div class="text-center mb-5">
                        <div class="badge bg-primary-subtle text-primary rounded-pill px-4 py-2 fs-6 mb-3 border border-primary-subtle">
                            <i class="fa-brands fa-telegram me-2"></i> Telegram Premium Gift
                        </div>
                        <h3 class="fw-bold text-dark mb-3">{{ $package->name_locale }}</h3>
                        <p class="text-muted fs-6 px-lg-5">
                            {!! __('teacher::teacher/telegram.gift.message', ['package' => $package->name_locale]) !!}
                        </p>
                    </div>

                    <div class="package-details-row p-4 rounded-4 bg-light border mb-5">
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-box bg-white text-primary shadow-sm">
                                        <i class="fa-solid fa-calendar-check fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="small text-muted text-uppercase fw-bold">{{ __('teacher::teacher/telegram.expiry_date') }}</div>
                                        <div class="fw-bold fs-5">{{ $package->duration_label }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="small text-muted mb-1">{{ __('teacher::teacher/telegram.gift.units.lifetime') }}</div>
                                <div class="h4 fw-extrabold text-success mb-0">{{ __('FREE') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <button type="button" id="claimGiftBtn" class="btn btn-primary btn-lg py-3 rounded-4 shadow-sm fw-bold">
                            <span class="btn-text"><i class="fa-solid fa-gift me-2"></i> {{ __('teacher::teacher/telegram.gift.claim_btn') }}</span>
                            <i class="fa-solid fa-spinner fa-spin d-none ms-2"></i>
                        </button>
                        <a href="{{ route('teacher.dashboard.telegram.index') }}" class="btn btn-link text-muted fw-bold text-decoration-none">
                            {{ __('teacher::teacher/telegram.cancel') }}
                        </a>
                    </div>
                </div>

                <!-- Success State (Hidden by default) -->
                <div class="card-body p-5 d-none text-center" id="successSection">
                    <div class="success-icon mb-4">
                        <i class="fa-solid fa-circle-check text-success display-1"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">{{ __('teacher::teacher/telegram.gift.success_title') }}</h2>
                    <p class="text-muted fs-5 mb-5">{{ __('teacher::teacher/telegram.gift.success_message') }}</p>
                    
                    <div class="d-grid gap-3">
                        <a href="{{ route('teacher.dashboard.telegram.index') }}" class="btn btn-primary btn-lg py-3 rounded-4 fw-bold">
                            <i class="fa-solid fa-gears me-2"></i> {{ __('teacher::teacher/telegram.gift.back_to_settings') }}
                        </a>
                        <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-outline-secondary btn-lg py-3 rounded-4 fw-bold">
                            <i class="fa-solid fa-house me-2"></i> {{ __('teacher::teacher/telegram.gift.back_to_dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('stylesheets')
<style>
    .fw-extrabold { font-weight: 800; }
    
    .gift-card-container {
        transition: transform 0.3s ease;
    }
    
    .gift-card-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        min-height: 280px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 1;
    }
    
    /* Gift Box Animation */
    .gift-box-wrapper {
        perspective: 1000px;
        position: relative;
        width: 100px;
        height: 100px;
    }
    
    .gift-box {
        width: 80px;
        height: 80px;
        background: #f1f5f9;
        position: absolute;
        bottom: 0;
        left: 10px;
        border-radius: 8px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        animation: giftBounce 2s infinite ease-in-out;
    }
    
    .gift-box-body {
        position: absolute;
        inset: 0;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .gift-box-lid {
        position: absolute;
        top: -15px;
        left: -5px;
        width: 90px;
        height: 25px;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 2;
        animation: lidWobble 2s infinite ease-in-out;
    }
    
    .gift-box-ribbon {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 15px;
        height: 100%;
        background: #ef4444;
        z-index: 1;
    }
    
    .gift-box-ribbon::after {
        content: '';
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 15px;
        background: #ef4444;
        border-radius: 20px;
    }

    @keyframes giftBounce {
        0%, 100% { transform: translateY(0) rotate(0); }
        50% { transform: translateY(-10px) rotate(2deg); }
    }
    
    @keyframes lidWobble {
        0%, 100% { transform: translateY(0) rotate(0); }
        50% { transform: translateY(-5px) rotate(-5deg); }
    }
    
    /* Ornaments */
    .ornament {
        position: absolute;
        color: rgba(255, 255, 255, 0.2);
        font-size: 1.5rem;
        animation: float 4s infinite ease-in-out;
    }
    
    .ornament-1 { top: 20%; left: 15%; animation-delay: 0s; }
    .ornament-2 { top: 60%; right: 20%; animation-delay: 1s; font-size: 1.2rem; }
    .ornament-3 { top: 30%; right: 10%; animation-delay: 2s; font-size: 1.8rem; }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); opacity: 0.2; }
        50% { transform: translateY(-20px); opacity: 0.5; }
    }
    
    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    html[data-theme="dark"] .gift-card-container .card-body {
        background-color: #0f172a;
    }
    html[data-theme="dark"] .bg-light { background-color: #1e293b !important; }
    html[data-theme="dark"] .text-dark { color: #f1f5f9 !important; }
    html[data-theme="dark"] .icon-box { background-color: #334155 !important; }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    $(document).ready(function() {
        $('#claimGiftBtn').click(function() {
            const btn = $(this);
            const btnText = btn.find('.btn-text');
            const btnSpinner = btn.find('.fa-spinner');
            
            btn.prop('disabled', true);
            btnText.addClass('opacity-50');
            btnSpinner.removeClass('d-none');
            
            $.ajax({
                url: '{{ route("telegram.claim.post", ["locale" => app()->getLocale(), "token" => $subscription->claim_token]) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Confetti Celebration
                        confetti({
                            particleCount: 150,
                            spread: 70,
                            origin: { y: 0.6 },
                            colors: ['#0ea5e9', '#2563eb', '#10b981', '#f59e0b']
                        });
                        
                        // Switch sections with animation
                        $('#claimFormSection').fadeOut(300, function() {
                            $(this).addClass('d-none');
                            $('#successSection').removeClass('d-none').hide().fadeIn(500);
                        });
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || '{{ __("teacher::teacher/telegram.gift.error_invalid") }}';
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("teacher::teacher/telegram.gift.error_title") }}',
                        text: message,
                        confirmButtonText: '{{ __("teacher::teacher/telegram.close") }}',
                        confirmButtonColor: '#2563eb'
                    });
                    btn.prop('disabled', false);
                    btnText.removeClass('opacity-50');
                    btnSpinner.addClass('d-none');
                }
            });
        });
    });
</script>
@endsection
