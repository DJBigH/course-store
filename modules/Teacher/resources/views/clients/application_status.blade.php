@extends('layouts.client')

@section('content')

    @php
        $copy = trans('teacher::portal.status');
        $statusClass = match ($application->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending_payment' => 'warning',
            default => 'info',
        };
        $applicantTypeLabel = $application->applicant_type === 'guest'
            ? $copy['labels']['guest']
            : $copy['labels']['student'];
        $statusMessage = $copy['messages'][$application->status] ?? null;
        $paymentGuide = $copy['payment_guide'][$application->payment_method] ?? $copy['payment_guide']['bank_transfer'];

        $bankEnabled = (int) setting('payment_bank_enabled', '1') === 1;
        $vnpayEnabled = (int) setting('payment_vnpay_enabled', '1') === 1;
        $momoEnabled = (int) setting('payment_momo_enabled', '1') === 1;

        $isBank = $application->payment_method === 'bank_transfer' || empty($application->payment_method);
        $isVnpay = $application->payment_method === 'vnpay';
        $isMomo = $application->payment_method === 'momo';

        $methodEnabled = match($application->payment_method) {
            'vnpay' => $vnpayEnabled,
            'momo' => $momoEnabled,
            default => $bankEnabled,
        };

        $bankTransferBankName = setting('bank_transfer_bank_name', 'Techcombank');
        $bankTransferBankBin = setting('bank_transfer_bank_bin', 'techcombank');
        $bankTransferAccountNumber = setting('bank_transfer_account_number', '61043040524');
        $bankTransferAccountName = setting('bank_transfer_account_name', 'Nguyễn Văn A');
        $bankTransferNotePrefix = trim((string) setting('bank_transfer_note_prefix', 'CK'));
        $bankTransferNote = trim($bankTransferNotePrefix . ' GV' . $application->id);
    @endphp

    <section class="teacher-status-page py-5">
        <div class="container">
            <div class="teacher-status-wrapper mx-auto">
                
                {{-- Status Hero Section --}}
                <div class="status-hero text-center mb-5 p-5 rounded-5 status-{{ $application->status }}">
                    <div class="status-icon-wrapper mb-4">
                        @if($application->status === 'approved')
                            <div class="status-icon bg-success shadow-success"><i class="fas fa-check-double"></i></div>
                        @elseif($application->status === 'rejected')
                            <div class="status-icon bg-danger shadow-danger"><i class="fas fa-times"></i></div>
                        @elseif($application->status === 'pending_payment')
                            <div class="status-icon bg-warning shadow-warning"><i class="fas fa-wallet"></i></div>
                        @else
                            <div class="status-icon bg-primary shadow-primary"><i class="fas fa-clock"></i></div>
                        @endif
                    </div>
                    <div class="status-badge mb-3 px-3 py-1 rounded-pill d-inline-block">
                        {{ $application->display_status }}
                    </div>
                    <h1 class="fw-bold display-5 mb-3">{{ $copy['title'] }}</h1>
                    <p class="text-muted lead mx-auto" style="max-width: 600px;">
                        @if ($statusMessage)
                            {{ $statusMessage }}
                        @else
                            {{ $copy['intro'] }}
                        @endif
                    </p>
                </div>

                @if (session('msg_success'))
                    <div class="alert alert-success rounded-4 mb-4 shadow-sm border-0 px-4 py-3">
                        <i class="fas fa-check-circle me-2"></i> {{ session('msg_success') }}
                    </div>
                @endif
                @if (session('msg_danger'))
                    <div class="alert alert-danger rounded-4 mb-4 shadow-sm border-0 px-4 py-3">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('msg_danger') }}
                    </div>
                @endif

                <div class="row g-4">
                    {{-- Left Column: Application Info --}}
                    <div class="col-lg-7">
                        <div class="status-card h-100 p-4 rounded-5 border shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <div class="card-icon-sm bg-primary-soft text-primary"><i class="fas fa-user"></i></div>
                                <h3 class="h4 fw-bold mb-0">{{ $copy['sections']['profile'] }}</h3>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <div class="info-group">
                                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">{{ $copy['labels']['full_name'] }}</label>
                                        <div class="fw-bold fs-5">{{ $application->full_name }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-group">
                                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">{{ $copy['labels']['display_name'] }}</label>
                                        <div class="fw-bold fs-5">{{ $application->display_name ?: '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-group">
                                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">{{ $copy['labels']['email'] }}</label>
                                        <div class="fw-bold fs-5">{{ $application->email }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-group">
                                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">{{ $copy['labels']['applicant_type'] }}</label>
                                        <div class="fw-bold">{{ $applicantTypeLabel }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-group">
                                        <label class="text-muted small fw-bold text-uppercase mb-1 d-block">{{ $copy['labels']['submitted_at'] }}</label>
                                        <div class="fw-bold">{{ optional($application->submitted_at)->format('d/m/Y H:i') ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            @if (!empty($application->bio))
                                <div class="mt-5 pt-4 border-top">
                                    <h4 class="h5 fw-bold mb-3">{{ $copy['sections']['bio'] }}</h4>
                                    <div class="p-3 bg-light rounded-4 bio-text" style="word-break: break-word;">
                                        {{ $application->bio }}
                                    </div>
                                </div>
                            @endif

                            @if (!empty($application->admin_note))
                                <div class="mt-4 p-3 bg-danger-soft rounded-4 border-start border-4 border-danger">
                                    <h4 class="h6 fw-bold mb-2 text-danger">{{ $copy['sections']['admin_note'] }}</h4>
                                    <p class="mb-0 text-danger-emphasis">{{ $application->admin_note }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right Column: Package & Payment --}}
                    <div class="col-lg-5">
                        <div class="status-card p-4 p-md-5 rounded-5 shadow-sm border h-100">
                            {{-- Header --}}
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                                <h3 class="h5 fw-bold mb-0 text-uppercase letter-spacing-1">Chi tiết đơn hàng</h3>
                                <div class="badge bg-primary-soft text-primary rounded-pill px-3">{{ $application->package?->name_locale ?: $application->package?->name ?: $copy['labels']['not_selected'] }}</div>
                            </div>

                            {{-- Price Summary --}}
                            <div class="mb-5">
                                <div class="d-flex justify-content-between mb-2 small text-muted">
                                    <span>Đơn giá gói</span>
                                    <span>{{ $application->package ? money($application->package->price) : '-' }}</span>
                                </div>
                                @if ($application->coupon_code)
                                    <div class="d-flex justify-content-between mb-2 text-success small">
                                        <span>Giảm giá ({{ $application->coupon_code }})</span>
                                        <span class="fw-bold">-{{ money($application->discount_amount ?? 0) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <span class="fw-bold opacity-75">Tổng thanh toán</span>
                                    <span class="fw-bold fs-3 text-primary">{{ money($application->payable_amount) }}</span>
                                </div>
                            </div>

                            @if ($application->status === 'pending_payment')
                                {{-- Bank Transfer Section --}}
                                <div class="payment-section mt-5">
                                    <div class="d-flex align-items-center gap-2 mb-4">
                                        <div class="card-icon-sm bg-warning-soft text-warning" style="width: 32px; height: 32px; font-size: 0.8rem;"><i class="fas fa-university"></i></div>
                                        <h4 class="h6 fw-bold mb-0 text-uppercase">Thanh toán chuyển khoản</h4>
                                    </div>

                                    @if (!$methodEnabled)
                                        <div class="alert alert-warning rounded-4 border-0 small">
                                            <i class="fas fa-tools me-2"></i> Phương thức <strong>{{ $application->payment_method_label }}</strong> đang bảo trì.
                                        </div>
                                    @else
                                        @if ($isBank)
                                            <div class="bank-card rounded-4 p-4 mb-4">
                                                <div class="row g-4">
                                                    <div class="col-12 border-bottom pb-3 mb-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="small text-muted fw-bold">NGÂN HÀNG</span>
                                                            <span class="fw-bold">{{ $bankTransferBankName }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 border-bottom pb-3 mb-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="small text-muted fw-bold d-block mb-1">SỐ TÀI KHOẢN</span>
                                                                <span class="fs-5 fw-bold text-primary">{{ $bankTransferAccountNumber }}</span>
                                                            </div>
                                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="navigator.clipboard.writeText('{{ $bankTransferAccountNumber }}')">
                                                                <i class="far fa-copy me-1"></i> Copy
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="small text-muted fw-bold d-block mb-1">NỘI DUNG CK</span>
                                                                <span class="fw-bold text-danger">{{ $bankTransferNote }}</span>
                                                            </div>
                                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="navigator.clipboard.writeText('{{ $bankTransferNote }}')">
                                                                <i class="far fa-copy me-1"></i> Copy
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-4 pt-3 border-top text-center">
                                                    <span class="small text-muted">Chủ tài khoản: <strong>{{ $bankTransferAccountName }}</strong></span>
                                                </div>
                                            </div>

                                            {{-- QR Code --}}
                                            <div class="qr-wrapper text-center">
                                                <div class="d-inline-block p-3 bg-white rounded-5 border shadow-sm mb-3">
                                                    <img src="https://img.vietqr.io/image/{{ $bankTransferBankBin }}-{{ $bankTransferAccountNumber }}-compact2.jpg?amount={{ $application->payable_amount }}&addInfo={{ rawurlencode($bankTransferNote) }}" 
                                                         alt="VietQR" class="img-fluid rounded-4" style="max-height: 220px;">
                                                </div>
                                                <p class="small text-muted fw-bold mb-0">Quét mã VietQR để thanh toán tự động</p>
                                            </div>
                                        @else
                                            <div class="alert alert-info rounded-4 border-0 mb-3 small">
                                                <i class="fas fa-info-circle me-2"></i> Thanh toán qua <strong>{{ $application->payment_method_label }}</strong>. Vui lòng liên hệ Admin nếu cần hỗ trợ.
                                            </div>
                                        @endif
                                        
                                        <div class="mt-4 p-3 rounded-4 bg-warning-soft text-warning-emphasis small text-center border border-warning-subtle">
                                            <i class="fas fa-lightbulb me-2"></i> {{ $paymentGuide }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-5">
                    @if ($application->status === 'approved' && $application->teacher?->status === 'active')
                        <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                            <i class="fas fa-tachometer-alt me-2"></i> {{ $copy['actions']['go_dashboard'] }}
                        </a>
                    @endif

                    @if ($application->status === 'pending_payment')
                        <form method="POST" action="{{ route('teacher.account.mark-paid', ['locale' => app()->getLocale()]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                                <i class="fas fa-check-circle me-2"></i> {{ $copy['actions']['mark_paid'] }}
                            </button>
                        </form>
                    @endif

                    @if (in_array($application->status, ['rejected', 'pending_payment', 'draft'], true))
                        <a href="{{ route('teacher.account.edit', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-primary btn-lg rounded-pill px-5">
                            <i class="fas fa-edit me-2"></i> {{ $copy['actions']['update_profile'] }}
                        </a>
                    @endif

                    <a href="{{ route('teacher.portal.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary btn-lg rounded-pill px-5">
                        <i class="fas fa-home me-2"></i> {{ $copy['actions']['back_landing'] }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .teacher-status-page {
            --ts-primary: #2563eb;
            --ts-primary-soft: rgba(37, 99, 235, 0.08);
            --ts-success: #10b981;
            --ts-warning: #f59e0b;
            --ts-danger: #ef4444;
            --ts-card-bg: #ffffff;
            --ts-card-border: rgba(37, 99, 235, 0.1);
            --ts-text: #1e293b;
            --ts-muted: #64748b;
            
            color: var(--ts-text);
            min-height: 80vh;
            background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.05), transparent 400px),
                        radial-gradient(circle at bottom left, rgba(37, 99, 235, 0.03), transparent 400px);
        }

        html[data-theme="dark"] .teacher-status-page {
            --ts-card-bg: #111827;
            --ts-card-border: rgba(96, 165, 250, 0.12);
            --ts-text: #f1f5f9;
            --ts-muted: #94a3b8;
            --ts-primary-soft: rgba(59, 130, 246, 0.12);
            background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 400px),
                        #0a0f1a;
        }

        .teacher-status-wrapper {
            max-width: 1000px;
        }

        /* Hero Section */
        .status-hero {
            background: var(--ts-card-bg);
            border: 1px solid var(--ts-card-border);
            box-shadow: 0 20px 50px rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        html[data-theme="dark"] .status-hero {
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            background: rgba(17, 24, 39, 0.8);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .status-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--ts-primary);
        }

        .status-hero.status-approved::before { background: var(--ts-success); }
        .status-hero.status-rejected::before { background: var(--ts-danger); }
        .status-hero.status-pending_payment::before { background: var(--ts-warning); }

        .status-icon-wrapper {
            display: inline-block;
            position: relative;
        }

        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto;
        }

        .shadow-primary { box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4); }
        .shadow-success { box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4); }
        .shadow-warning { box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4); }
        .shadow-danger { box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4); }

        .status-badge {
            background: var(--ts-primary-soft);
            color: var(--ts-primary);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-approved .status-badge { color: var(--ts-success); background: rgba(16, 185, 129, 0.1); }
        .status-rejected .status-badge { color: var(--ts-danger); background: rgba(239, 68, 68, 0.1); }
        .status-pending_payment .status-badge { color: var(--ts-warning); background: rgba(245, 158, 11, 0.1); }

        /* Cards */
        .status-card {
            background: var(--ts-card-bg);
            border: 1px solid var(--ts-card-border) !important;
            transition: transform 0.3s ease;
        }

        .card-icon-sm {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .bg-primary-soft { background: var(--ts-primary-soft); }
        .bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
        .bg-danger-soft { background: rgba(239, 68, 68, 0.08); }

        .info-group label {
            letter-spacing: 0.02em;
        }

        .bio-text {
            line-height: 1.6;
            color: var(--ts-text);
            background: rgba(0,0,0,0.02) !important;
        }

        html[data-theme="dark"] .bio-text {
            background: rgba(255,255,255,0.03) !important;
        }

        .bank-card {
            background: rgba(0, 0, 0, 0.02);
            border: 1px solid var(--ts-card-border);
        }

        html[data-theme="dark"] .bank-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Buttons */
        .btn-lg {
            padding: 1rem 2.5rem;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary.shadow {
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2) !important;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }

        @media (max-width: 768px) {
            .status-hero { padding: 3rem 1.5rem !important; }
            .display-5 { font-size: 2rem; }
        }
    </style>
@endsection
