@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $copy = trans('teacher::portal.form');
        $selectedPackageId = (string) old('package_id', $application?->package_id ?? ($packages->first()->id ?? ''));
        $selectedPaymentMethod = old('payment_method', $application?->payment_method ?? 'bank_transfer');
        $selectedPackage = $packages->firstWhere('id', (int) $selectedPackageId);
        $selectedPrice = (float) ($selectedPackage?->price ?? 0);
        
        $bankEnabled = (int) setting('payment_bank_enabled', '1') === 1;
        $vnpayEnabled = (int) setting('payment_vnpay_enabled', '1') === 1;
        $momoEnabled = (int) setting('payment_momo_enabled', '1') === 1;
    @endphp

    <section class="teacher-apply-page py-5">
        <div class="container">
            <div class="teacher-apply-shell">
                <div class="teacher-apply-intro">
                    <span class="teacher-apply-kicker">{{ $copy['kicker'] }}</span>
                    <h2>{{ $application?->exists ? $copy['edit_title'] : $copy['create_title'] }}</h2>
                    <p class="mb-0">{{ $copy['intro'] }}</p>
                </div>

                @if (session('msg_success'))
                    <div class="alert alert-success">{{ session('msg_success') }}</div>
                @endif

                @if (session('msg_danger'))
                    <div class="alert alert-danger">{{ session('msg_danger') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $copy['error_summary'] }}</div>
                @endif

                <form action="{{ $application?->exists ? route('teacher.account.update', ['locale' => app()->getLocale()]) : route('teacher.account.submit', ['locale' => app()->getLocale()]) }}"
                    method="POST" class="teacher-apply-form">
                    @csrf

                    <div class="teacher-apply-card mb-4">
                        <div class="teacher-section-heading">
                            <h3>{{ $copy['section_1_title'] }}</h3>
                            <p>{{ $copy['section_1_desc'] }}</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['full_name'] }}</label>
                                <input type="text" name="full_name" class="form-control"
                                    required maxlength="100" autocomplete="name"
                                    value="{{ old('full_name', $application?->full_name ?? $student?->name ?? '') }}">
                                @error('full_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['email'] }}</label>
                                <input type="email" name="email" class="form-control"
                                    required maxlength="100" autocomplete="email" inputmode="email" spellcheck="false"
                                    value="{{ old('email', $application?->email ?? $student?->email ?? '') }}">
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['display_name'] }}</label>
                                <input type="text" name="display_name" class="form-control"
                                    value="{{ old('display_name', $application?->display_name) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['phone'] }}</label>
                                <input type="text" name="phone" class="form-control"
                                    maxlength="20" inputmode="tel" autocomplete="tel"
                                    value="{{ old('phone', $application?->phone) }}">
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['headline'] }}</label>
                                <input type="text" name="headline" class="form-control"
                                    value="{{ old('headline', $application?->headline) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['experience_years'] }}</label>
                                <input type="number" min="0" max="80" name="experience_years" class="form-control"
                                    value="{{ old('experience_years', $application?->experience_years) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ $copy['fields']['specialties'] }}</label>
                                <input type="text" name="specialties" class="form-control"
                                    placeholder="{{ $copy['fields']['specialties_placeholder'] }}"
                                    value="{{ old('specialties', is_array($application?->specialties) ? implode(', ', $application->specialties) : '') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ $copy['fields']['bio'] }}</label>
                                <textarea name="bio" rows="5" class="form-control" maxlength="5000">{{ old('bio', $application?->bio) }}</textarea>
                                @error('bio')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['portfolio_url'] }}</label>
                                <input type="url" name="portfolio_url" class="form-control"
                                    maxlength="255" inputmode="url" placeholder="https://"
                                    value="{{ old('portfolio_url', $application?->portfolio_url) }}">
                                @error('portfolio_url')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['intro_video_url'] }}</label>
                                <input type="url" name="intro_video_url" class="form-control"
                                    maxlength="255" inputmode="url" placeholder="https://"
                                    value="{{ old('intro_video_url', $application?->intro_video_url) }}">
                                @error('intro_video_url')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ $copy['fields']['facebook_url'] }}</label>
                                <input type="url" name="facebook_url" class="form-control"
                                    maxlength="255" inputmode="url" placeholder="https://"
                                    value="{{ old('facebook_url', $application?->facebook_url) }}">
                                @error('facebook_url')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ $copy['fields']['youtube_url'] }}</label>
                                <input type="url" name="youtube_url" class="form-control"
                                    maxlength="255" inputmode="url" placeholder="https://"
                                    value="{{ old('youtube_url', $application?->youtube_url) }}">
                                @error('youtube_url')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ $copy['fields']['linkedin_url'] }}</label>
                                <input type="url" name="linkedin_url" class="form-control"
                                    maxlength="255" inputmode="url" placeholder="https://"
                                    value="{{ old('linkedin_url', $application?->linkedin_url) }}">
                                @error('linkedin_url')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['cv_file'] }}</label>
                                <input type="text" name="cv_file" class="form-control"
                                    value="{{ old('cv_file', $application?->cv_file) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ $copy['fields']['identity_file'] }}</label>
                                <input type="text" name="identity_file" class="form-control"
                                    value="{{ old('identity_file', $application?->identity_file) }}">
                            </div>
                        </div>
                    </div>

                    <div class="teacher-apply-card mb-4">
                        <div class="teacher-section-heading">
                            <h3>{{ $copy['section_2_title'] }}</h3>
                            <p>{{ $copy['section_2_desc'] }}</p>
                        </div>

                        <div class="row g-3">
                            @forelse ($packages as $package)
                                <div class="col-lg-4">
                                    <label class="teacher-package-option" data-package-card>
                                        <input type="radio" name="package_id" value="{{ $package->id }}"
                                            data-package-price="{{ (float) $package->price }}"
                                            {{ $selectedPackageId === (string) $package->id ? 'checked' : '' }}>
                                        <span class="teacher-package-option__code">{{ strtoupper((string) $package->code) }}</span>
                                        <strong class="teacher-package-option__name">{{ $package->name_locale ?: $package->name }}</strong>
                                        <span class="teacher-package-option__price">
                                            {{ (float) $package->price > 0 ? money($package->price) : $copy['package']['free'] }}
                                        </span>
                                        @if (!empty($package->description_locale ?: $package->description))
                                            <span class="teacher-package-option__desc">{{ $package->description_locale ?: $package->description }}</span>
                                        @endif
                                        <span class="teacher-package-option__meta">
                                            {{ str_replace(':rate', rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.'), $copy['package']['commission']) }}
                                            @if ($package->effective_course_limit)
                                                · {{ str_replace(':count', $package->effective_course_limit, $copy['package']['course_limit']) }}
                                            @else
                                                · {{ $copy['package']['unlimited'] }}
                                            @endif
                                        </span>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-warning mb-0">{{ $copy['package']['empty'] }}</div>
                                </div>
                            @endforelse
                        </div>

                        <div class="teacher-payment-summary mt-3">
                            <div>
                                <span>{{ $copy['package']['price_label'] }}</span>
                                <strong data-selected-price>{{ $selectedPrice > 0 ? money($selectedPrice) : $copy['package']['free'] }}</strong>
                            </div>
                            <div class="small text-muted" data-payment-hint>
                                {{ $selectedPrice > 0 ? $copy['package']['payment_required'] : $copy['package']['free_hint'] }}
                            </div>
                        </div>

                        @error('package_id')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="teacher-apply-card mb-4">
                        <div class="teacher-section-heading">
                            <h3>{{ $copy['section_3_title'] }}</h3>
                            <p>{{ $copy['section_3_desc'] }}</p>
                        </div>

                        <div class="row g-3 align-items-start">
                            <div class="col-lg-6">
                                <label class="form-label">{{ $copy['fields']['coupon_code'] }}</label>
                                <input type="text" name="coupon_code" class="form-control"
                                    value="{{ old('coupon_code', $application?->coupon_code) }}"
                                    placeholder="{{ $copy['fields']['coupon_placeholder'] }}">
                                @error('coupon_code')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="teacher-payment-methods mt-3" data-payment-methods>
                            <label class="teacher-payment-option {{ $bankEnabled ? '' : 'is-maintenance' }}">
                                <input type="radio" name="payment_method" value="bank_transfer"
                                    data-enabled="{{ $bankEnabled ? 1 : 0 }}"
                                    data-name="{{ __('teacher::portal.payment_methods.bank_transfer') }}"
                                    {{ $selectedPaymentMethod === 'bank_transfer' ? 'checked' : '' }}>
                                <span class="d-flex align-items-center gap-2">
                                    {{ __('teacher::portal.payment_methods.bank_transfer') }}
                                    @unless ($bankEnabled)
                                        <span class="badge bg-warning text-dark small" style="font-size: 0.7rem;">{{ __('teacher::portal.status.maintenance') }}</span>
                                    @endunless
                                </span>
                            </label>
                            <label class="teacher-payment-option {{ $vnpayEnabled ? '' : 'is-maintenance' }}">
                                <input type="radio" name="payment_method" value="vnpay"
                                    data-enabled="{{ $vnpayEnabled ? 1 : 0 }}"
                                    data-name="VNPay"
                                    {{ $selectedPaymentMethod === 'vnpay' ? 'checked' : '' }}>
                                <span class="d-flex align-items-center gap-2">
                                    {{ __('teacher::portal.payment_methods.vnpay') }}
                                    @unless ($vnpayEnabled)
                                        <span class="badge bg-warning text-dark small" style="font-size: 0.7rem;">{{ __('teacher::portal.status.maintenance') }}</span>
                                    @endunless
                                </span>
                            </label>
                            <label class="teacher-payment-option {{ $momoEnabled ? '' : 'is-maintenance' }}">
                                <input type="radio" name="payment_method" value="momo"
                                    data-enabled="{{ $momoEnabled ? 1 : 0 }}"
                                    data-name="MoMo"
                                    {{ $selectedPaymentMethod === 'momo' ? 'checked' : '' }}>
                                <span class="d-flex align-items-center gap-2">
                                    {{ __('teacher::portal.payment_methods.momo') }}
                                    @unless ($momoEnabled)
                                        <span class="badge bg-warning text-dark small" style="font-size: 0.7rem;">{{ __('teacher::portal.status.maintenance') }}</span>
                                    @endunless
                                </span>
                            </label>
                            @error('payment_method')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary teacher-submit-btn">
                            {{ $application?->exists ? $copy['actions']['update'] : $copy['actions']['submit'] }}
                        </button>

                        @if ($application?->exists)
                            <a href="{{ route('teacher.account.status', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">
                                {{ $copy['actions']['view_status'] }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .teacher-apply-page {
            --teacher-card-bg: #ffffff;
            --teacher-card-border: rgba(37, 99, 235, 0.14);
            --teacher-text: #0f172a;
            --teacher-muted: #64748b;
            --teacher-accent: #2563eb;
            --teacher-input-bg: #ffffff;
            --teacher-input-border: rgba(148, 163, 184, 0.35);
            --teacher-summary-bg: rgba(37, 99, 235, 0.06);
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.05), transparent 30%), var(--bg-color);
            color: var(--teacher-text);
        }

        html[data-theme="dark"] .teacher-apply-page {
            --teacher-card-bg: #0f172a;
            --teacher-card-border: rgba(96, 165, 250, 0.22);
            --teacher-text: #e5eefc;
            --teacher-muted: #a9bbd5;
            --teacher-accent: #60a5fa;
            --teacher-input-bg: #0b1220;
            --teacher-input-border: rgba(148, 163, 184, 0.24);
            --teacher-summary-bg: rgba(59, 130, 246, 0.12);
        }

        .teacher-apply-shell {
            max-width: 1100px;
            margin: 0 auto;
        }

        .teacher-apply-intro {
            margin-bottom: 1.5rem;
            color: var(--teacher-text);
        }

        .teacher-apply-kicker {
            display: inline-flex;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--teacher-accent);
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .teacher-apply-intro h2 {
            margin-top: 1rem;
            margin-bottom: 0.75rem;
            font-size: clamp(2rem, 3.2vw, 3rem);
            font-weight: 800;
            color: var(--teacher-text);
        }

        .teacher-apply-card {
            padding: 1.5rem;
            border-radius: 28px;
            background: var(--teacher-card-bg);
            border: 1px solid var(--teacher-card-border);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }

        html[data-theme="dark"] .teacher-apply-card {
            box-shadow: 0 22px 54px rgba(2, 6, 23, 0.28);
        }

        .teacher-section-heading {
            margin-bottom: 1rem;
        }

        .teacher-section-heading h3 {
            margin-bottom: 0.35rem;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--teacher-text);
        }

        .teacher-section-heading p {
            margin-bottom: 0;
            color: var(--teacher-muted);
        }

        .teacher-package-option {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            height: 100%;
            padding: 1.2rem;
            border: 1px solid var(--teacher-card-border);
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.04), transparent 42%), var(--teacher-card-bg);
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .teacher-package-option:hover {
            transform: translateY(-4px);
            border-color: rgba(37, 99, 235, 0.28);
            box-shadow: 0 20px 36px rgba(37, 99, 235, 0.12);
        }

        .teacher-package-option.is-selected {
            border-color: var(--teacher-accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
        }

        .teacher-package-option input {
            display: none;
        }

        .teacher-package-option__code {
            display: inline-flex;
            width: fit-content;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--teacher-accent);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .teacher-package-option__name,
        .teacher-package-option__price {
            color: var(--teacher-text);
        }

        .teacher-package-option__name {
            font-size: 1.1rem;
        }

        .teacher-package-option__price {
            font-size: 1.4rem;
            font-weight: 800;
        }

        .teacher-package-option__desc,
        .teacher-package-option__meta {
            color: var(--teacher-muted);
        }

        .teacher-payment-summary {
            padding: 1rem 1.1rem;
            border-radius: 20px;
            background: var(--teacher-summary-bg);
        }

        .teacher-payment-summary > div:first-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.4rem;
            color: var(--teacher-text);
        }

        .teacher-payment-methods.is-hidden {
            display: none;
        }

        .teacher-payment-option {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.95rem 1rem;
            border: 1px solid var(--teacher-card-border);
            border-radius: 18px;
            background: color-mix(in srgb, var(--teacher-card-bg) 84%, transparent);
            cursor: pointer;
        }

        .teacher-payment-option.is-maintenance {
            opacity: 0.7;
        }

        .teacher-payment-option + .teacher-payment-option {
            margin-top: 0.75rem;
        }

        .teacher-payment-option span {
            color: var(--teacher-text);
        }

        html[data-theme="dark"] .teacher-payment-option {
            background: rgba(11, 18, 32, 0.72);
        }

        .teacher-apply-page .form-label {
            color: var(--teacher-text);
            font-weight: 600;
        }

        .teacher-apply-page .form-control {
            background: var(--teacher-input-bg);
            color: var(--teacher-text);
            border-color: var(--teacher-input-border);
        }

        .teacher-apply-page .form-control::placeholder {
            color: var(--teacher-muted);
            opacity: 1;
        }

        .teacher-apply-page .form-control:focus {
            background: var(--teacher-input-bg);
            color: var(--teacher-text);
            border-color: rgba(37, 99, 235, 0.5);
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.14);
        }

        .teacher-submit-btn {
            min-width: 220px;
            min-height: 52px;
            font-weight: 700;
        }

        html[data-theme="dark"] .teacher-apply-page .btn-outline-secondary {
            color: #dbeafe;
            border-color: rgba(148, 163, 184, 0.35);
            transition: color 0.22s ease, background-color 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease, transform 0.22s ease;
        }

        html[data-theme="dark"] .teacher-apply-page .btn-outline-secondary:hover,
        html[data-theme="dark"] .teacher-apply-page .btn-outline-secondary:active {
            color: #0f172a;
            background: #dbeafe;
            border-color: #dbeafe;
            transform: translateY(-1px);
        }

        .teacher-apply-page .btn-outline-secondary:focus-visible,
        .teacher-apply-page .teacher-submit-btn:focus-visible {
            outline: none;
            box-shadow:
                0 0 0 3px rgba(8, 17, 31, 0.9),
                0 0 0 6px rgba(96, 165, 250, 0.48);
        }

        html[data-theme="dark"] .teacher-apply-page .btn-outline-secondary:focus-visible,
        html[data-theme="dark"] .teacher-apply-page .teacher-submit-btn:focus-visible {
            box-shadow:
                0 0 0 3px rgba(7, 17, 31, 0.96),
                0 0 0 6px rgba(125, 211, 252, 0.58);
        }

        @media (max-width: 767.98px) {
            .teacher-apply-card {
                padding: 1.25rem;
                border-radius: 22px;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const packageInputs = document.querySelectorAll('input[name="package_id"]');
            const paymentMethods = document.querySelector('[data-payment-methods]');
            const selectedPrice = document.querySelector('[data-selected-price]');
            const paymentHint = document.querySelector('[data-payment-hint]');
            const freeText = @json($copy['package']['free']);
            const freeHint = @json($copy['package']['free_hint']);
            const paidHint = @json($copy['package']['payment_required']);
            const locale = document.documentElement.lang || 'vi';

            const formatMoney = (value) => new Intl.NumberFormat(locale).format(Math.max(Number(value || 0), 0)) + 'd';

            const updateUi = () => {
                const active = document.querySelector('input[name="package_id"]:checked');
                const price = Number(active?.dataset.packagePrice || 0);
                const isFree = price <= 0;

                document.querySelectorAll('[data-package-card]').forEach((card) => card.classList.remove('is-selected'));
                active?.closest('[data-package-card]')?.classList.add('is-selected');

                if (selectedPrice) {
                    selectedPrice.textContent = isFree ? freeText : formatMoney(price);
                }

                if (paymentHint) {
                    paymentHint.textContent = isFree ? freeHint : paidHint;
                }

                if (paymentMethods) {
                    paymentMethods.classList.toggle('is-hidden', isFree);
                }
            };

            packageInputs.forEach((input) => {
                input.addEventListener('change', updateUi);
            });

            const methodInputs = document.querySelectorAll('input[name="payment_method"]');
            methodInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.dataset.enabled === '0') {
                        const name = this.dataset.name;
                        alert(`Phương thức ${name} hiện đang bảo trì. Vui lòng chọn phương thức khác.`);
                        
                        // Tìm phương thức khả dụng đầu tiên
                        const firstValid = document.querySelector('input[name="payment_method"][data-enabled="1"]');
                        if (firstValid) {
                            firstValid.checked = true;
                        } else {
                            this.checked = false;
                        }
                    }
                });
            });

            // Kiểm tra phương thức mặc định lúc load
            const initialMethod = document.querySelector('input[name="payment_method"]:checked');
            if (initialMethod && initialMethod.dataset.enabled === '0') {
                const firstValid = document.querySelector('input[name="payment_method"][data-enabled="1"]');
                if (firstValid) {
                    firstValid.checked = true;
                }
            }

            updateUi();
        })();
    </script>
@endsection
