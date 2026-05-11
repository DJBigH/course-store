@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <div class="teacher-hero">
            <div class="teacher-hero--dashboard">
                <div class="teacher-hero__content">
                    <div class="teacher-hero__eyebrow">
                        <i class="fa-brands fa-telegram me-2"></i> Telegram Integration
                    </div>
                    <h2 class="teacher-hero__title">{{ __('teacher::teacher/telegram.title') }}</h2>
                    <p class="teacher-hero__desc">{{ __('teacher::teacher/telegram.title_1') }}</p>
                </div>
                <div class="teacher-hero__rail">
                    <div class="teacher-hero__mini teacher-hero__mini--glass">
                        <span>{{ __('teacher::teacher/telegram.status_label') }}</span>
                        @php $status = $teacher->getTelegramPackageStatus(); @endphp
                        @if ($status['status'] === 'active')
                            @if ($status['expires_at']->year >= 9000)
                                <strong class="text-success">{{ __('teacher::teacher/telegram.lifetime') }}</strong>
                            @else
                                <strong class="text-success">{{ __('teacher::teacher/telegram.status_active') }}</strong>
                                <div class="small mt-1 text-white-50">{{ __('teacher::teacher/telegram.expiry_date') }}: {{ $status['expires_at']->format('d/m/Y H:i') }}</div>
                            @endif
                        @elseif ($status['status'] === 'pending')
                            <strong class="text-warning"><i class="fa-solid fa-clock me-1"></i> {{ __('teacher::teacher/telegram.status_pending') }}</strong>
                        @elseif ($status['status'] === 'pending_claim')
                            <strong class="text-info"><i class="fa-solid fa-gift me-1"></i> {{ __('teacher::teacher/telegram.status_pending_claim') }}</strong>
                        @else
                            <strong class="text-danger">{{ __('teacher::teacher/telegram.status_expired') }}</strong>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">{{ session('msg_danger') }}</div>
        @endif
        @if (session('msg_warning'))
            <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4">{{ session('msg_warning') }}</div>
        @endif

        <div class="row g-4">
            {{-- Cấu hình Telegram --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-gear me-2"></i> {{ __('teacher::teacher/telegram.connection_settings') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('teacher.dashboard.telegram.settings') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">{{ __('teacher::teacher/telegram.chat_id') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-id-card"></i></span>
                                    <input type="password" name="telegram_chat_id" class="form-control border-start-0" 
                                           value="{{ old('telegram_chat_id', $teacher->telegram_chat_id) }}" 
                                           placeholder="{{ __('teacher::teacher/telegram.chat_id_placeholder') }}">
                                </div>
                                <div class="mt-2 small text-muted">
                                    <i class="fa-solid fa-circle-info me-1"></i> {!! __('teacher::teacher/telegram.get_chat_id_guide', ['bot' => '<a href="https://t.me/userinfobot" target="_blank">@userinfobot</a>']) !!}
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="is_telegram_notifications_enabled" id="notifySwitch" 
                                           value="1" {{ $teacher->is_telegram_notifications_enabled ? 'checked' : '' }}
                                           {{ $status['status'] !== 'active' ? 'disabled' : '' }}>
                                    <label class="form-check-label fw-bold" for="notifySwitch">{{ __('teacher::teacher/telegram.enable_notifications') }}</label>
                                </div>
                                @if ($status['status'] !== 'active')
                                    <div class="mt-2 text-danger small">
                                        <i class="fa-solid fa-lock me-1"></i> {{ __('teacher::teacher/telegram.need_package_to_enable') }}
                                    </div>
                                @endif
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-8">
                                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3" {{ $status['status'] !== 'active' ? 'disabled' : '' }}>
                                        <i class="fa-solid fa-save me-2"></i> {{ __('teacher::teacher/telegram.save_settings') }}
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" id="testTelegramBtn" class="btn btn-outline-info w-100 py-2 rounded-3 {{ $teacher->telegram_chat_id && $status['status'] === 'active' && $teacher->is_telegram_notifications_enabled ? '' : 'd-none' }}">
                                        <i class="fa-solid fa-paper-plane me-2"></i> {{ __('teacher::teacher/telegram.test_connection') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4 p-3 bg-light rounded-4 border">
                            <h6 class="fw-bold mb-2 small text-uppercase">{{ __('teacher::teacher/telegram.quick_guide') }}</h6>
                            <ol class="small mb-0 ps-3">
                                <li>{!! __('teacher::teacher/telegram.guide_step_1', ['bot' => '<a href="https://t.me/userinfobot" target="_blank">@userinfobot</a>']) !!}</li>
                                <li>{!! __('teacher::teacher/telegram.guide_step_2', ['bot' => '<a href="https://t.me/bigk_udemy_teacher_alert_bot" target="_blank">@bigk_udemy_teacher_alert_bot</a>']) !!}</li>
                                <li>{!! __('teacher::teacher/telegram.guide_step_3') !!}</li>
                                <li>{!! __('teacher::teacher/telegram.guide_step_4') !!}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danh sách gói --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-shopping-cart me-2"></i> {{ __('teacher::teacher/telegram.subscription_info') }}</h5>
                        @if ($status['status'] === 'active')
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="togglePackages">
                                <i class="fa-solid fa-plus me-1"></i> <span class="btn-text">{{ __('teacher::teacher/telegram.extend_upgrade_btn') }}</span>
                            </button>
                        @elseif ($status['status'] === 'pending')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('teacher::teacher/telegram.awaiting_approval') }}
                            </span>
                        @elseif ($status['status'] === 'pending_claim')
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-2">
                                <i class="fa-solid fa-gift me-1"></i> {{ __('teacher::teacher/telegram.status_pending_claim') }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-4 {{ $status['status'] === 'active' || $status['status'] === 'pending' ? 'd-none' : '' }}" id="packagesContainer">
                        <div class="row g-3">
                            @foreach ($packages as $package)
                                <div class="col-md-6">
                                    <div class="package-card p-3 rounded-4 border h-100 d-flex flex-column transition-all">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ $package->name_locale }}</h6>
                                                <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                                    {{ $package->duration_label }}
                                                </div>
                                            </div>
                                            <i class="fa-brands fa-telegram text-primary fs-4 opacity-50"></i>
                                        </div>
                                        
                                        <div class="mb-3">
                                            @if ($package->sale_price)
                                                <div class="text-decoration-line-through text-muted small">{{ moneyLocale($package->price) }}</div>
                                                <div class="fw-bold fs-5 text-primary">{{ moneyLocale($package->sale_price) }}</div>
                                            @else
                                                <div class="fw-bold fs-5 text-primary">{{ moneyLocale($package->price) }}</div>
                                            @endif
                                        </div>

                                        <p class="small text-muted mb-4 flex-grow-1">
                                            {{ $package->description_locale ?: __('teacher::teacher/telegram.default_pkg_desc') }}
                                        </p>

                                        @if ($status['expires_at'] && $status['expires_at']->year >= 9000)
                                            <button class="btn btn-secondary w-100 rounded-3" disabled>{{ __('teacher::teacher/telegram.owned_lifetime') }}</button>
                                        @else
                                            <button type="button" class="btn btn-outline-primary w-100 rounded-3 purchase-btn" 
                                                    data-id="{{ $package->id }}" 
                                                    data-name="{{ $package->name_locale }}"
                                                    data-price="{{ moneyLocale($package->sale_price ?? $package->price) }}"
                                                    data-is-lifetime="{{ $package->duration_unit === 'lifetime' ? '1' : '0' }}">
                                                {{ __('teacher::teacher/telegram.buy_btn') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if ($status['status'] === 'active')
                        <div class="card-body p-5 text-center" id="activeStatusPlaceholder">
                            <div class="mb-3 text-success">
                                <i class="fa-solid fa-circle-check fs-1"></i>
                            </div>
                            <h6 class="fw-bold">{{ __('teacher::teacher/telegram.active_status_title') }}</h6>
                            <p class="small text-muted mb-0">{{ __('teacher::teacher/telegram.active_status_desc') }}</p>
                        </div>
                    @elseif ($status['status'] === 'pending')
                        <div class="card-body p-5 text-center" id="pendingStatusPlaceholder">
                            <div class="mb-3 text-warning">
                                <i class="fa-solid fa-clock-rotate-left fs-1"></i>
                            </div>
                            <h6 class="fw-bold">{{ __('teacher::teacher/telegram.pending_status_title') }}</h6>
                            <p class="small text-muted mb-3">{{ __('teacher::teacher/telegram.pending_status_desc') }}</p>
                            <form action="{{ route('teacher.dashboard.telegram.purchase.cancel-pending') }}" method="POST" onsubmit="return confirm('{{ __('teacher::teacher/telegram.confirm_cancel_pending') }}')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4">
                                    <i class="fa-solid fa-xmark me-1"></i> {{ __('teacher::teacher/telegram.cancel_request_btn') }}
                                </button>
                            </form>
                        </div>
                    @elseif ($status['status'] === 'pending_claim')
                        <div class="card-body p-5 text-center" id="pendingClaimStatusPlaceholder">
                            <div class="mb-3 text-info">
                                <i class="fa-solid fa-gift fs-1"></i>
                            </div>
                            <h6 class="fw-bold">{{ __('teacher::teacher/telegram.pending_claim_status_title') }}</h6>
                            <p class="small text-muted mb-3">{{ __('teacher::teacher/telegram.pending_claim_status_desc') }}</p>
                            <a href="{{ route('teacher.dashboard.notifications') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                                <i class="fa-solid fa-bell me-1"></i> {{ __('teacher::teacher/telegram.claim_now_btn') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i> {{ __('teacher::teacher/telegram.history.title') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-top">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 px-3 py-3 small text-uppercase fw-bold text-muted">{{ __('teacher::teacher/telegram.history.type') }}</th>
                                        <th class="border-0 px-3 py-3 small text-uppercase fw-bold text-muted">{{ __('teacher::teacher/telegram.history.package') }}</th>
                                        <th class="border-0 px-3 py-3 small text-uppercase fw-bold text-muted">{{ __('teacher::teacher/telegram.history.price') }}</th>
                                        <th class="border-0 px-3 py-3 small text-uppercase fw-bold text-muted">{{ __('teacher::teacher/telegram.history.status') }}</th>
                                        <th class="border-0 px-3 py-3 small text-uppercase fw-bold text-muted">{{ __('teacher::teacher/telegram.history.date') }}</th>
                                        <th class="border-0 px-3 py-3 small text-uppercase fw-bold text-muted">{{ __('teacher::teacher/telegram.history.expiry') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($history as $item)
                                        <tr>
                                            <td class="px-3">
                                                @if ($item->claimed_at)
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">
                                                        <i class="fa-solid fa-gift me-1"></i> {{ __('teacher::teacher/telegram.history.type_gift') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">
                                                        <i class="fa-solid fa-cart-shopping me-1"></i> {{ __('teacher::teacher/telegram.history.type_purchase') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3">
                                                <div class="fw-bold">{{ $item->package->name_locale ?? 'N/A' }}</div>
                                                <div class="small text-muted">{{ $item->package->duration_label }}</div>
                                            </td>
                                            <td class="px-3">
                                                @if ($item->claimed_at)
                                                    <span class="text-success fw-bold">FREE</span>
                                                @else
                                                    <span class="fw-bold">{{ moneyLocale($item->price) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3">
                                                @php
                                                    $itemStatus = $item->status;
                                                    if ($itemStatus === 'active' && $item->expires_at && $item->expires_at->isPast()) {
                                                        $itemStatus = 'expired';
                                                    }
                                                @endphp
                                                @if ($itemStatus === 'active')
                                                    <span class="badge bg-success rounded-pill px-3">{{ __('teacher::teacher/telegram.status_active') }}</span>
                                                @elseif ($itemStatus === 'pending')
                                                    <span class="badge bg-warning text-dark rounded-pill px-3">{{ __('teacher::teacher/telegram.status_pending') }}</span>
                                                @elseif ($itemStatus === 'pending_claim')
                                                    <span class="badge bg-info rounded-pill px-3">Chờ nhận quà</span>
                                                @else
                                                    <span class="badge bg-danger rounded-pill px-3">{{ __('teacher::teacher/telegram.status_expired') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 small text-muted">
                                                {{ $item->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-3">
                                                @if ($item->expires_at)
                                                    @if ($item->expires_at->year >= 9000)
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
                                                            {{ __('teacher::teacher/telegram.lifetime') }}
                                                        </span>
                                                    @else
                                                        <span class="small {{ $item->expires_at->isPast() ? 'text-danger text-decoration-line-through' : 'text-dark fw-bold' }}">
                                                            {{ $item->expires_at->format('d/m/Y H:i') }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">---</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fa-solid fa-inbox display-4 d-block mb-3 opacity-25"></i>
                                                {{ __('teacher::teacher/telegram.history.no_data') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal xác nhận mua --}}
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-primary">
                        <i class="fa-solid fa-circle-question fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-2">{{ __('teacher::teacher/telegram.confirm_purchase_title') }}</h5>
                    <p class="text-muted mb-4">{!! __('teacher::teacher/telegram.confirm_purchase_desc') !!}</p>
                    
                    <form id="purchaseForm" method="POST" action="">
                        @csrf
                        <div class="mb-4 text-start">
                            <label class="form-label fw-bold small text-uppercase mb-3">{{ __('teacher::teacher/telegram.payment_method_label') }}</label>
                            <div class="payment-methods-grid">
                                @php
                                    $isWalletEnabled = ($paymentSettings['payment_wallet_enabled'] ?? '1') === '1';
                                    $isBankEnabled = ($paymentSettings['payment_bank_enabled'] ?? '1') === '1';
                                    $isMomoEnabled = ($paymentSettings['payment_momo_enabled'] ?? '1') === '1';
                                    $isVnpayEnabled = ($paymentSettings['payment_vnpay_enabled'] ?? '1') === '1';
                                @endphp

                                <div class="payment-option">
                                    <input type="radio" name="payment_method" value="wallet" id="pay_wallet" {{ $isWalletEnabled ? 'checked' : 'disabled' }}>
                                    <label for="pay_wallet" class="payment-label {{ !$isWalletEnabled ? 'opacity-50 grayscale' : '' }}">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="payment-icon bg-warning-subtle text-warning">
                                                    <i class="fa-solid fa-wallet"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ __('teacher::teacher/telegram.payment_wallet') }}</div>
                                                    <div class="small text-muted">{{ __('teacher::teacher/telegram.payment_wallet_balance') }} <span class="text-success fw-semibold">{{ moneyLocale($availableBalance) }}</span></div>
                                                </div>
                                            </div>
                                            @if (!$isWalletEnabled)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small">{{ __('teacher::teacher/telegram.maintenance') }}</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>

                                <div class="payment-option">
                                    <input type="radio" name="payment_method" value="bank_transfer" id="pay_bank" {{ !$isBankEnabled ? 'disabled' : '' }}>
                                    <label for="pay_bank" class="payment-label {{ !$isBankEnabled ? 'opacity-50 grayscale' : '' }}">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="payment-icon bg-primary-subtle text-primary">
                                                    <i class="fa-solid fa-building-columns"></i>
                                                </div>
                                                <div class="fw-bold">{{ __('teacher::teacher/telegram.payment_bank') }}</div>
                                            </div>
                                            @if (!$isBankEnabled)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small">{{ __('teacher::teacher/telegram.maintenance') }}</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" name="payment_method" value="vnpay" id="pay_vnpay" {{ !$isVnpayEnabled ? 'disabled' : '' }}>
                                    <label for="pay_vnpay" class="payment-label {{ !$isVnpayEnabled ? 'opacity-50 grayscale' : '' }}">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="payment-icon bg-info-subtle text-info">
                                                    <img src="/clients/assets/vnpay.png" alt="VNPay" width="24" onerror="this.src='https://sandbox.vnpayment.vn/paymentv2/images/img/logos/vnpay-logo.png'">
                                                </div>
                                                <div class="fw-bold">{{ __('teacher::teacher/telegram.payment_vnpay') }}</div>
                                            </div>
                                            @if (!$isVnpayEnabled)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small">{{ __('teacher::teacher/telegram.maintenance') }}</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" name="payment_method" value="momo" id="pay_momo" {{ !$isMomoEnabled ? 'disabled' : '' }}>
                                    <label for="pay_momo" class="payment-label {{ !$isMomoEnabled ? 'opacity-50 grayscale' : '' }}">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="payment-icon bg-danger-subtle text-danger">
                                                    <img src="/clients/assets/momo.png" alt="MoMo" width="24" onerror="this.src='https://static.mservice.io/img/logo-momo.png'">
                                                </div>
                                                <div class="fw-bold">{{ __('teacher::teacher/telegram.payment_momo') }}</div>
                                            </div>
                                            @if (!$isMomoEnabled)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small">{{ __('teacher::teacher/telegram.maintenance') }}</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div id="stack-info" class="alert alert-info border-0 small mb-4 py-2">
                            <i class="fa-solid fa-clock me-1"></i> {!! __('teacher::teacher/telegram.stack_info') !!}
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-light w-100 py-2 rounded-3 border" data-bs-dismiss="modal">{{ __('teacher::teacher/telegram.cancel') }}</button>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 btn-confirm-purchase">
                                    <span class="btn-text">{{ __('teacher::teacher/telegram.confirm_pay') }}</span>
                                    <i class="fa-solid fa-spinner fa-spin d-none ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Modal Hướng dẫn Chuyển khoản --}}
<div class="modal fade" id="bankTransferModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-5">
                <div class="text-center mb-4">
                    <div class="display-1 text-primary mb-3">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <h4 class="fw-bold">{{ __('teacher::teacher/telegram.bank_transfer_confirm_title') }}</h4>
                    <p class="text-muted">{{ __('teacher::teacher/telegram.bank_transfer_confirm_desc') }}</p>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-7">
                        <div class="p-4 bg-light rounded-4 border">
                            <div class="mb-3">
                                <label class="small text-muted text-uppercase fw-bold">{{ __('teacher::teacher/telegram.bank_name') }}</label>
                                <div class="fw-bold fs-5">{{ $paymentSettings['bank_transfer_bank_name'] ?? 'N/A' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted text-uppercase fw-bold">{{ __('teacher::teacher/telegram.account_number') }}</label>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fw-bold fs-5" id="final-acc">{{ $paymentSettings['bank_transfer_account_number'] ?? 'N/A' }}</div>
                                    <button type="button" class="btn btn-sm btn-link copy-btn" data-clipboard-target="#final-acc">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted text-uppercase fw-bold">{{ __('teacher::teacher/telegram.account_name') }}</label>
                                <div class="fw-bold fs-5">{{ $paymentSettings['bank_transfer_account_name'] ?? 'N/A' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted text-uppercase fw-bold">{{ __('teacher::teacher/telegram.amount_label') }}</label>
                                <div class="fw-bold fs-5 text-danger" id="final-amount"></div>
                            </div>
                            <div class="mb-0">
                                <label class="small text-muted text-uppercase fw-bold">{{ __('teacher::teacher/telegram.transfer_note') }}</label>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fw-bold fs-5 text-primary" id="final-note"></div>
                                    <button type="button" class="btn btn-sm btn-link copy-btn" data-clipboard-target="#final-note">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center p-4 bg-white border rounded-4 shadow-sm">
                            <img id="final-qr-code" src="" alt="QR Code" class="img-fluid mb-3" style="max-height: 200px;">
                            <div class="text-center small text-muted">
                                {{ __('teacher::teacher/telegram.qr_scan_help') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning border-0 rounded-4 mb-4">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ __('teacher::teacher/telegram.bank_transfer_warning') }}
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-light w-100 py-3 rounded-3 border fw-bold" id="cancelTransferBtn">
                            {{ __('teacher::teacher/telegram.cancel_order') }}
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-primary w-100 py-3 rounded-3 fw-bold" id="confirmTransferredBtn">
                            {{ __('teacher::teacher/telegram.confirmed_transferred') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('stylesheets')
    <style>
        .payment-methods-grid {
            display: grid;
            gap: 12px;
        }
        .grayscale {
            filter: grayscale(1);
        }
        .payment-option input[type="radio"] {
            display: none;
        }
        .payment-label {
            display: block;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .payment-option input[type="radio"]:checked + .payment-label {
            border-color: var(--teacher-accent);
            background-color: rgba(14, 165, 233, 0.05);
        }
        .payment-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        html[data-theme="dark"] .payment-label { border-color: #334155; }
        html[data-theme="dark"] .payment-option input[type="radio"]:checked + .payment-label { background-color: rgba(14, 165, 233, 0.1); }
        
        /* Dark Mode for Modal */
        html[data-theme="dark"] .modal-content {
            background-color: #1e293b;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .modal-body .btn-light {
            background-color: #334155;
            border-color: #475569 !important;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .modal-body .text-muted {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .modal-body .alert-info {
            background-color: rgba(14, 165, 233, 0.1);
            color: #7dd3fc;
        }
        html[data-theme="dark"] .modal-body .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
        }
        
        /* Dark Mode for Card */
        html[data-theme="dark"] .card {
            background-color: #1e293b;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .form-control {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .form-control:focus {
            background-color: #0f172a;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .input-group-text {
            background-color: #334155;
            border-color: #334155;
            color: #94a3b8;
        }
        html[data-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Toggle test button based on notify switch and chat id
            $('#notifySwitch').change(function() {
                const isChecked = $(this).is(':checked');
                const hasChatId = $('input[name="telegram_chat_id"]').val().trim() !== '';
                const isActive = {{ $status['status'] === 'active' ? 'true' : 'false' }};
                
                if (isChecked && hasChatId && isActive) {
                    $('#testTelegramBtn').removeClass('d-none');
                } else {
                    $('#testTelegramBtn').addClass('d-none');
                }
            });

            // Also check on chat id input change
            $('input[name="telegram_chat_id"]').on('input', function() {
                $('#notifySwitch').trigger('change');
            });

            $('#togglePackages').click(function() {
                const container = $('#packagesContainer');
                const placeholder = $('#activeStatusPlaceholder');
                
                if (container.hasClass('d-none')) {
                    container.removeClass('d-none').hide().fadeIn();
                    placeholder.fadeOut(function() { $(this).addClass('d-none'); });
                    $(this).html('<i class="fa-solid fa-xmark me-1"></i> {{ __('teacher::teacher/telegram.close') }}');
                } else {
                    container.fadeOut(function() { $(this).addClass('d-none'); });
                    placeholder.removeClass('d-none').hide().fadeIn();
                    $(this).html('<i class="fa-solid fa-plus me-1"></i> {{ __('teacher::teacher/telegram.extend_upgrade_btn') }}');
                }
            });

            $('#testTelegramBtn').click(function() {
                const btn = $(this);
                const originalHtml = btn.html();
                
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> {{ __('teacher::teacher/telegram.sending') }}');

                $.ajax({
                    url: '{{ route("teacher.dashboard.telegram.test") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || '{{ __('teacher::teacher/telegram.test_error') }}');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            $('#purchaseForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('.btn-confirm-purchase');
                const btnText = btn.find('.btn-text');
                const btnSpinner = btn.find('.fa-spinner');

                btn.prop('disabled', true);
                btnText.addClass('opacity-50');
                btnSpinner.removeClass('d-none');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            if ($('input[name="payment_method"]:checked').val() === 'bank_transfer') {
                                // Nếu là chuyển khoản, hiện modal hướng dẫn thay vì redirect ngay
                                $('#purchaseModal').modal('hide');
                                $('#bankTransferModal').modal('show');
                            } else {
                                window.location.href = response.redirect;
                            }
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Có lỗi xảy ra, vui lòng thử lại.';
                        alert(message);
                        btn.prop('disabled', false);
                        btnText.removeClass('opacity-50');
                        btnSpinner.addClass('d-none');
                    }
                });
            });

            $('.purchase-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const price = $(this).data('price');
                const isLifetime = $(this).data('is-lifetime') == '1';

                $('#purchaseForm').attr('action', `/teacher/telegram/purchase/${id}`);

                if (isLifetime) {
                    $('#lifetime-warning').removeClass('d-none');
                    $('#stack-info').addClass('d-none');
                } else {
                    $('#lifetime-warning').addClass('d-none');
                    // Chỉ hiển thị cộng dồn nếu đang có gói hoạt động
                    const hasActiveSubscription = @json($subscriptionStatus['is_active']);
                    if (hasActiveSubscription) {
                        $('#stack-info').removeClass('d-none');
                    } else {
                        $('#stack-info').addClass('d-none');
                    }
                }

                $('#modalPackageName').text(name);
                $('#modalPackagePrice').text(price);
                
                // Cập nhật thông tin chuyển khoản
                $('#final-amount').text(price);
                const prefix = "{{ $paymentSettings['bank_transfer_note_prefix'] ?? 'CK' }}";
                const teacherId = "{{ $teacher->id }}";
                const note = `${prefix} TELE ${teacherId}`;
                $('#final-note').text(note);
                
                const bin = "{{ $paymentSettings['bank_transfer_bank_bin'] ?? '' }}";
                const acc = "{{ $paymentSettings['bank_transfer_account_number'] ?? '' }}";
                const name_acc = encodeURIComponent("{{ $paymentSettings['bank_transfer_account_name'] ?? '' }}");
                const amountClean = price.replace(/[^0-9]/g, '');
                
                if (bin && acc) {
                    const qrUrl = `https://img.vietqr.io/image/${bin}-${acc}-compact2.png?amount=${amountClean}&addInfo=${encodeURIComponent(note)}&accountName=${name_acc}`;
                    $('#final-qr-code').attr('src', qrUrl);
                }


                $('#purchaseModal').modal('show');
            });

            // Logic Copy
            $(document).on('click', '.copy-btn', function(e) {
                e.preventDefault();
                const target = $(this).data('clipboard-target');
                const text = $(target).text().trim();
                const $btn = $(this);
                const originalHtml = $btn.html();

                navigator.clipboard.writeText(text).then(() => {
                    $btn.html('<i class="fa-solid fa-check text-success"></i>');
                    setTimeout(() => {
                        $btn.html(originalHtml);
                    }, 2000);
                }).catch(err => {
                    console.error('Lỗi khi copy: ', err);
                });
            });

            $('#confirmTransferredBtn').click(function() {
                window.location.href = '{{ route('teacher.dashboard.telegram.index') }}';
            });

            $('#cancelTransferBtn').click(function() {
                if (confirm('{{ __('teacher::teacher/telegram.confirm_cancel_bank') }}')) {
                    window.location.reload();
                }
            });
        });
    </script>
@endsection