@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $bankEnabled = (int) setting('payment_bank_enabled', '1') === 1;
        $vnpayEnabled = (int) setting('payment_vnpay_enabled', '1') === 1;
        $momoEnabled = (int) setting('payment_momo_enabled', '1') === 1;
        
        $firstEnabled = 'bank';
        if (!$bankEnabled) {
            if ($vnpayEnabled) $firstEnabled = 'vnpay';
            elseif ($momoEnabled) $firstEnabled = 'momo';
        }
        $bankTransferBankName = setting('bank_transfer_bank_name', 'Techcombank');
        $bankTransferBankBin = setting('bank_transfer_bank_bin', 'techcombank');
        $bankTransferAccountNumber = setting('bank_transfer_account_number', '61043040524');
        $bankTransferAccountName = setting('bank_transfer_account_name', __('students::clients/checkout.checkout.bank_account_name_bank'));
        $bankTransferNotePrefix = trim((string) setting('bank_transfer_note_prefix', __('students::clients/checkout.checkout.transfer_note_qr')));
        $bankTransferNote = trim($bankTransferNotePrefix . ' ' . $order->code);
    @endphp

    <section class="account-page py-5 bg-light checkout-page">
        <div class="container">
            @if (session('msg'))
                <div class="alert alert-{{ session('msgType', 'info') }} mb-4">
                    {{ session('msg') }}
                </div>
            @endif

            <div class="mb-4">
                <h2 class="fw-bold">
                    {{ __('students::clients/checkout.checkout.page_title') }}
                    <span class="text-primary"><a
                            href="{{ route('students.account.order-detail', ['locale' => app()->getLocale(), 'id' => $order->id]) }}">#{{ $order->code }}</a></span>
                    @if (!$isFreeOrder && config('checkout.checkout_countdown') > 0)
                        <span class="countdown"><span class="cd-minute">00</span>:<span class="cd-second">00</span>
                    @endif
                </h2>
                <p class="text-muted mb-0">
                    {{ $isFreeOrder ? __('students::clients/checkout.checkout.notice_free_order') : __('students::clients/checkout.checkout.notice_complete_payment') }}
                </p>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 mb-4 checkout-main-card">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-receipt me-1 text-primary"></i>
                                {{ __('students::clients/checkout.checkout.order_info') }}
                            </h5>

                            <table class="table table-bordered align-middle mb-4">
                                <tbody>
                                    <tr>
                                        <th class="bg-light checkout-summary-label">
                                            {{ __('students::clients/checkout.checkout.order_code') }}</th>
                                        <td>#{{ $order->code }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('students::clients/checkout.checkout.subtotal') }}</th>
                                        <td class="fw-semibold">{{ moneyLocale($order->total, $order->currency) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-success">
                                            {{ __('students::clients/checkout.checkout.discount') }}
                                        </th>
                                        <td class="text-success fw-medium discount-value">
                                            - {{ moneyLocale($order->discount ?? 0, $order->currency) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('students::clients/checkout.checkout.order_time') }}</th>
                                        <td>{{ format_date_dmy($order->created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">{{ __('students::clients/checkout.checkout.status') }}</th>
                                        <td>
                                            <span class="badge bg-{{ $order->status->color }} px-3 py-2">
                                                {{ $order->status->name_locale }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Phương thức thanh toán</th>
                                        <td>
                                            <span class="badge rounded-pill px-3 py-2" style="{{ $order->payment_method_badge_style }}">
                                                {{ $order->payment_method_label }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="table-secondary">
                                        <th class="fw-bold">{{ __('students::clients/checkout.checkout.total_payment') }}</th>
                                        <td class="fw-bold text-danger fs-4 total_value">
                                            {{ moneyLocale($payableAmount, $order->currency) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-journal-text me-1 text-success"></i>
                                {{ __('students::clients/checkout.checkout.course_details') }}
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('students::clients/checkout.checkout.course') }}</th>
                                            <th class="text-end">{{ __('students::clients/checkout.checkout.price') }}</th>
                                            <th>{{ __('students::clients/checkout.checkout.instructor') }}</th>
                                            <th class="text-center">
                                                {{ __('students::clients/checkout.checkout.course_status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->detail as $key => $item)
                                            <tr>
                                                <td class="text-muted" data-label="#"> {{ $key + 1 }}</td>
                                                <td class="fw-semibold"
                                                    data-label="{{ __('students::clients/checkout.checkout.course') }}">
                                                    {{ $item?->courses?->name_locale }}
                                                </td>
                                                <td class="text-end text-danger fw-semibold">
                                                    {{ moneyLocale($item?->price, $order->currency) }}
                                                </td>
                                                <td data-label="{{ __('students::clients/checkout.checkout.instructor') }}">
                                                    {{ $item?->courses?->teacher?->name_locale }}
                                                </td>
                                                <td class="text-center"
                                                    data-label="{{ __('students::clients/checkout.checkout.course_status') }}">
                                                    <span
                                                        class="badge bg-{{ $item?->courses?->status ? 'success' : 'danger' }}-subtle text-{{ $item?->courses?->status ? 'success' : 'danger' }}">
                                                        {{ $item?->courses?->status ? __('students::clients/checkout.checkout.active') : __('students::clients/checkout.checkout.inactive') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
                                        class="btn btn-outline-primary btn-sm px-3 d-flex align-items-center">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        {{ __('students::clients/checkout.checkout.back_home') }}
                                    </a>

                                    <a href="{{ route('courses.home', ['locale' => app()->getLocale()]) }}"
                                        class="btn btn-success btn-sm px-3 d-flex align-items-center">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        {{ __('students::clients/checkout.checkout.buy_another_course') }}
                                    </a>
                                </div>
                            </div>

                            @unless ($isFreeOrder)
                                <hr>
                                <div class="mb-4">
                                    <label
                                        class="form-label fw-semibold">{{ __('students::clients/checkout.checkout.choose_payment_method') }}</label>

                                    <div class="form-check mb-2 payment-method-option {{ $bankEnabled ? '' : 'is-maintenance' }}">
                                        <input class="form-check-input payment-method" type="radio" name="payment_method"
                                            value="bank" {{ $firstEnabled === 'bank' ? 'checked' : '' }}
                                            data-enabled="{{ $bankEnabled ? 1 : 0 }}"
                                            data-maintenance-message="{{ __('students::clients/checkout.checkout.payment_under_maintenance', ['gateway' => __('students::clients/checkout.checkout.qr_transfer')]) }}">
                                        <label class="form-check-label d-inline-flex align-items-center gap-2">
                                            <span>{{ __('students::clients/checkout.checkout.qr_transfer') }}</span>
                                            @unless ($bankEnabled)
                                                <span class="badge bg-warning text-dark">
                                                    {{ __('students::clients/checkout.checkout.maintenance') }}
                                                </span>
                                            @endunless
                                        </label>
                                    </div>

                                    <div class="form-check mb-2 payment-method-option {{ $vnpayEnabled ? '' : 'is-maintenance' }}">
                                        <input class="form-check-input payment-method" type="radio" name="payment_method"
                                            value="vnpay" {{ $firstEnabled === 'vnpay' ? 'checked' : '' }}
                                            data-enabled="{{ $vnpayEnabled ? 1 : 0 }}"
                                            data-maintenance-message="{{ __('students::clients/checkout.checkout.payment_under_maintenance', ['gateway' => 'VNPay']) }}">
                                        <img src="{{ asset('clients/assets/vnpay.png') }}" alt="VNPay"
                                            style="width: 40px;">
                                        <label class="form-check-label d-inline-flex align-items-center gap-2">
                                            <span>VNPay</span>
                                            @unless ($vnpayEnabled)
                                                <span class="badge bg-warning text-dark">
                                                    {{ __('students::clients/checkout.checkout.maintenance') }}
                                                </span>
                                            @endunless
                                        </label>
                                    </div>

                                    <div class="form-check payment-method-option {{ $momoEnabled ? '' : 'is-maintenance' }}">
                                        <input class="form-check-input payment-method" type="radio" name="payment_method"
                                            value="momo" {{ $firstEnabled === 'momo' ? 'checked' : '' }}
                                            data-enabled="{{ $momoEnabled ? 1 : 0 }}"
                                            data-maintenance-message="{{ __('students::clients/checkout.checkout.payment_under_maintenance', ['gateway' => 'MoMo']) }}">
                                        <img src="{{ asset('clients/assets/momo.png') }}" alt="MoMo"
                                            style="width: 30px;">
                                        <label class="form-check-label d-inline-flex align-items-center gap-2">
                                            <span>MoMo</span>
                                            @unless ($momoEnabled)
                                                <span class="badge bg-warning text-dark">
                                                    {{ __('students::clients/checkout.checkout.maintenance') }}
                                                </span>
                                            @endunless
                                        </label>
                                    </div>
                                </div>
                            @endunless
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow border-0 sticky-top checkout-side-card">
                        <div class="card-body p-4">
                            @if ($isFreeOrder)
                                <div class="alert alert-success mb-4">
                                    <i class="bi bi-gift me-1"></i>
                                    {{ __('students::clients/checkout.checkout.free_order_message') }}
                                </div>

                                <form method="POST"
                                    action="{{ route('students.account.checkout-payment', ['locale' => app()->getLocale(), 'id' => $order->id]) }}">
                                    @csrf
                                    <button class="btn btn-success w-100 py-2 fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ __('students::clients/checkout.checkout.activate_free_course') }}
                                    </button>
                                </form>
                            @else
                                <div id="payment-bank" class="{{ $firstEnabled === 'bank' ? '' : 'd-none' }}">
                                    <h5 class="fw-bold mb-3">
                                        <i class="bi bi-credit-card me-1 text-success"></i>
                                        {{ __('students::clients/checkout.checkout.bank_transfer') }}
                                    </h5>
                                    @include('students::clients.partials.coupons')
                                    <ul class="list-unstyled small mb-3">
                                        <li>🏦 <strong>{{ __('students::clients/checkout.checkout.bank_name') }}:</strong>
                                            {{ $bankTransferBankName }}</li>
                                        <li>
                                            🔢 <strong>{{ __('students::clients/checkout.checkout.bank_account') }}:</strong>
                                            <span class="copy-text" data-copy="{{ $bankTransferAccountNumber }}">{{ $bankTransferAccountNumber }}</span>
                                            <i class="bank-copy fa-regular fa-copy"></i>
                                        </li>
                                        <li>👤
                                            <strong>{{ __('students::clients/checkout.checkout.bank_account_name') }}:</strong>
                                            {{ $bankTransferAccountName }}
                                        </li>
                                        <li>💰 <strong>{{ __('students::clients/checkout.checkout.amount') }}:</strong>
                                            <span class="text-danger fw-bold total_value">{{ moneyLocale($payableAmount, $order->currency) }}</span>
                                        </li>
                                        <li>
                                            📝
                                            <strong>{{ __('students::clients/checkout.checkout.transfer_content') }}:</strong>
                                            <span class="copy-text"
                                                data-copy="{{ $bankTransferNote }}">
                                                {{ $bankTransferNote }}
                                            </span>
                                            <i class="bank-copy fa-regular fa-copy"></i>
                                        </li>
                                    </ul>

                                    <div class="text-center my-4">
                                        <div class="border rounded-3 p-3 bg-light d-inline-block checkout-qr-card">
                                            <img id="vietqr-img"
                                                src="https://img.vietqr.io/image/{{ $bankTransferBankBin }}-{{ $bankTransferAccountNumber }}-compact2.jpg?amount={{ $payableAmount }}&addInfo={{ rawurlencode($bankTransferNote) }}"
                                                class="img-fluid mb-2 qr-image" style="max-width: 220px" alt="VietQR">
                                            <div>
                                                <button type="button" class="btn btn-outline-primary btn-sm download-qr">
                                                    <i class="bi bi-download me-1"></i>
                                                    {{ __('students::clients/checkout.checkout.download_qr') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning small">
                                        <i class="bi bi-info-circle me-1"></i>
                                        {{ __('students::clients/checkout.checkout.after_transfer_notice') }}
                                        <strong>{{ __('students::clients/checkout.checkout.confirm_paid') }}</strong>
                                        {{ __('students::clients/checkout.checkout.complete_order_notice') }}
                                    </div>

                                    <form method="POST"
                                        action="{{ route('students.account.checkout-payment', ['locale' => app()->getLocale(), 'id' => $order->id]) }}">
                                        @csrf
                                        <button class="btn btn-success w-100 py-2 fw-semibold">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ __('students::clients/checkout.checkout.i_have_paid') }}
                                        </button>
                                    </form>

                                    @if (!$order->status->is_success)
                                        <form method="POST" class="mt-2 js-cancel-order"
                                            data-confirm="{{ __('students::clients/checkout.checkout.cancel_confirm') }}"
                                            action="{{ route('students.account.checkout-cancel', [
                                                'locale' => app()->getLocale(),
                                                'id' => $order->id,
                                            ]) }}">
                                            @csrf

                                            <button type="submit" class="btn btn-outline-danger w-100 py-2 fw-semibold">
                                                <i class="bi bi-x-circle me-1"></i>
                                                {{ __('students::clients/checkout.checkout.cancel_order') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                @if ($vnpayEnabled || $firstEnabled === 'vnpay')
                                    <div id="payment-vnpay" class="{{ $firstEnabled === 'vnpay' ? '' : 'd-none' }}">
                                        @include('students::clients.partials.coupons')
                                        <p class="text-muted small">
                                            {{ __('students::clients/checkout.checkout.vnpay_notice') }}
                                        </p>

                                        <form method="POST"
                                            action="{{ route('students.account.checkout-vnpay', ['locale' => app()->getLocale(), 'id' => $order->id]) }}">
                                            @csrf
                                            <button class="btn btn-primary w-100">
                                                {{ __('students::clients/checkout.checkout.pay_with_vnpay') }}
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                @if ($momoEnabled || $firstEnabled === 'momo')
                                    <div id="payment-momo" class="{{ $firstEnabled === 'momo' ? '' : 'd-none' }}">
                                        @include('students::clients.partials.coupons')
                                        <p class="text-muted small">
                                            {{ __('students::clients/checkout.checkout.momo_notice') }}
                                        </p>

                                        <form method="POST"
                                            action="{{ route('students.account.checkout-momo', ['locale' => app()->getLocale(), 'id' => $order->id]) }}">
                                            @csrf
                                            <button class="btn btn-danger w-100">
                                                {{ __('students::clients/checkout.checkout.pay_with_momo') }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .list-unstyled i {
            cursor: pointer;
        }

        .countdown {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 1.4rem;
            color: #198754;
        }

        .countdown .cd-minute,
        .countdown .cd-second {
            min-width: 44px;
            padding: 6px 10px;
            text-align: center;
            border-radius: 8px;
            background: linear-gradient(135deg, #dc3545, #ff6b6b);
            color: #fff;
            box-shadow: 0 4px 8px rgba(25, 135, 84, 0.25);
            transition: all 0.3s ease;
        }

        .countdown.warning .cd-minute,
        .countdown.warning .cd-second {
            background: linear-gradient(135deg, #ffc107, #ffdd57);
            color: #000;
        }

        .countdown.expired .cd-minute,
        .countdown.expired .cd-second {
            background: linear-gradient(135deg, #dc3545, #ff6b6b);
        }

        .checkout-side-card {
            top: 90px;
        }

        .checkout-summary-label {
            width: 30%;
        }

        .payment-method-option.is-maintenance {
            opacity: 0.8;
        }

        .payment-method-option .form-check-label {
            cursor: pointer;
        }

        @media (max-width: 991.98px) {
            .checkout-side-card {
                top: 0;
            }
        }

        @media (max-width: 767.98px) {
            .checkout-page {
                padding-top: 1.5rem !important;
                padding-bottom: 1.5rem !important;
            }

            .checkout-page h2 {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                font-size: 1.5rem;
                line-height: 1.35;
            }

            .countdown {
                font-size: 1rem;
            }

            .countdown .cd-minute,
            .countdown .cd-second {
                min-width: 38px;
                padding: 4px 8px;
            }

            .checkout-summary-label {
                width: 42%;
            }

            .checkout-main-card .card-body,
            .checkout-side-card .card-body {
                padding: 1.25rem !important;
            }

            .checkout-main-card .table-responsive {
                overflow: visible;
            }

            .checkout-main-card .table-responsive thead {
                display: none;
            }

            .checkout-main-card .table-responsive table,
            .checkout-main-card .table-responsive tbody,
            .checkout-main-card .table-responsive tr,
            .checkout-main-card .table-responsive td {
                display: block;
                width: 100%;
            }

            .checkout-main-card .table-responsive tr {
                margin-bottom: 14px;
                padding: 14px;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                background: #fff;
                box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            }

            .checkout-main-card .table-responsive td {
                padding: 0;
                border: 0;
                text-align: left !important;
            }

            .checkout-main-card .table-responsive td+td {
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid #e2e8f0;
            }

            .checkout-main-card .table-responsive td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 6px;
                color: #64748b;
                font-size: 0.78rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .checkout-main-card .table-responsive td:first-child {
                color: #64748b;
                font-weight: 700;
            }

            .checkout-main-card .table-responsive td:first-child::before {
                margin-bottom: 0;
            }

            .checkout-main-card .table-responsive .badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .checkout-qr-card {
                width: 100%;
            }

            .qr-image {
                max-width: min(100%, 220px) !important;
            }
        }

        @media (max-width: 767.98px) {
            html[data-theme="dark"] .checkout-main-card .table-responsive tr {
                border-color: rgba(148, 163, 184, 0.18);
                background: rgba(15, 23, 42, 0.92);
                box-shadow: 0 14px 28px rgba(2, 6, 23, 0.28);
            }

            html[data-theme="dark"] .checkout-main-card .table-responsive td {
                color: #e2e8f0;
            }

            html[data-theme="dark"] .checkout-main-card .table-responsive td + td {
                border-top-color: rgba(148, 163, 184, 0.14);
            }

            html[data-theme="dark"] .checkout-main-card .table-responsive td::before {
                color: #93c5fd;
            }

            html[data-theme="dark"] .checkout-main-card .table-responsive td:first-child {
                color: #cbd5e1;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        const paymentMethods = document.querySelectorAll('.payment-method');
        const bankMethod = document.querySelector('.payment-method[value="bank"]');

        function showPaymentSection(method) {
            document.getElementById('payment-bank')?.classList.add('d-none');
            document.getElementById('payment-vnpay')?.classList.add('d-none');
            document.getElementById('payment-momo')?.classList.add('d-none');

            if (method === 'bank') {
                document.getElementById('payment-bank')?.classList.remove('d-none');
            }
            if (method === 'vnpay') {
                document.getElementById('payment-vnpay')?.classList.remove('d-none');
            }
            if (method === 'momo') {
                document.getElementById('payment-momo')?.classList.remove('d-none');
            }
        }

        paymentMethods.forEach(el => {
            el.addEventListener('change', function() {
                if (this.dataset.enabled === '0') {
                    alert(this.dataset.maintenanceMessage);
                    
                    // Quay lại phương thức khả dụng đầu tiên
                    const firstValid = document.querySelector('.payment-method[data-enabled="1"]');
                    if (firstValid) {
                        firstValid.checked = true;
                        showPaymentSection(firstValid.value);
                    } else {
                        // Nếu không cái nào bật, giữ nguyên nhưng không hiện nội dung
                        showPaymentSection('none');
                    }
                    return;
                }

                showPaymentSection(this.value);
            });
        });

        // Khởi tạo hiển thị
        const currentChecked = document.querySelector('.payment-method:checked');
        if (currentChecked) {
             if (currentChecked.dataset.enabled === '0') {
                 const firstValid = document.querySelector('.payment-method[data-enabled="1"]');
                 if (firstValid) {
                     firstValid.checked = true;
                     showPaymentSection(firstValid.value);
                 }
             } else {
                 showPaymentSection(currentChecked.value);
             }
        }
    </script>
@endsection
