@extends('layouts.teacher')

@section('content')
    @php
        $selectedPackageId = (int) old('package_id', $upgradePackages->first()?->id);
        $selectedPaymentMethod = old('payment_method', 'bank_transfer');
        $currentPackageExpiresAt = $teacher->package_expires_at;
        $currentPackageDaysLeft = $currentPackageExpiresAt ? max(now()->startOfDay()->diffInDays($currentPackageExpiresAt->copy()->startOfDay(), false), 0) : null;
        $currentPackageMap = [
            'id' => (int) ($currentPackage?->id ?? 0),
            'billing_cycle' => $currentPackage?->billing_cycle,
            'sort_order' => (int) ($currentPackage?->sort_order ?? 0),
            'expires_at' => $currentPackageExpiresAt?->toIso8601String(),
            'days_left' => $currentPackageDaysLeft,
        ];
        $packageMap = $upgradePackages
            ->mapWithKeys(fn ($package) => [
                $package->id => [
                    'name' => $package->name_locale ?: $package->name,
                    'price' => (float) $package->price,
                    'billing_cycle' => $package->billing_cycle,
                    'sort_order' => (int) $package->sort_order,
                    'meta' => __('teacher::dashboard.package.current_meta', [
                        'commission' => rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.'),
                        'limit' => $package->effective_course_limit ?: __('teacher::dashboard.courses.unlimited'),
                    ]),
                ],
            ])
            ->all();
    @endphp

    <div class="teacher-page-shell">
        <div class="teacher-panel teacher-upgrade-shell">
            <div class="teacher-upgrade-hero">
                <div>
                    <span class="teacher-upgrade-kicker">{{ __('teacher::dashboard.package.upgrade_title') }}</span>
                    <h3 class="teacher-upgrade-title">{{ __('teacher::dashboard.package.upgrade_title') }}</h3>
                    <p class="teacher-upgrade-desc mb-0">{{ __('teacher::dashboard.package.upgrade_description') }}</p>
                </div>
                <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-outline-secondary teacher-upgrade-back">
                    {{ __('teacher::dashboard.common.back') }}
                </a>
            </div>

            @if (session('msg_success'))
                <div class="alert alert-success">{{ session('msg_success') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ __('teacher::dashboard.common.validation_summary') }}</div>
            @endif

            <div class="teacher-upgrade-current">
                <div class="teacher-upgrade-current__label">{{ __('teacher::dashboard.package.current_title') }}</div>
                <div class="teacher-upgrade-current__name">{{ $currentPackage?->name_locale ?: $currentPackage?->name }}</div>
                <div class="teacher-upgrade-current__meta">
                    {{ __('teacher::dashboard.package.current_meta', [
                        'commission' => rtrim(rtrim(number_format((float) ($currentPackage?->commission_rate ?? 0), 2, '.', ''), '0'), '.'),
                        'limit' => $currentPackage?->effective_course_limit ?: __('teacher::dashboard.courses.unlimited'),
                    ]) }}
                </div>
                @if ($currentPackageExpiresAt)
                    <div class="teacher-upgrade-current__time">
                        Hết hạn ngày {{ $currentPackageExpiresAt->format('d/m/Y') }} • còn {{ $currentPackageDaysLeft }} ngày
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('teacher.dashboard.package.upgrade.store') }}">
                @csrf

                <div class="teacher-upgrade-section">
                    <div class="teacher-upgrade-section__head">
                        <div>
                            <h4>{{ __('teacher::dashboard.package.target_title') }}</h4>
                            <p class="mb-0">{{ __('teacher::dashboard.package.upgrade_description') }}</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        @foreach ($upgradePackages as $package)
                            @php
                                $packageId = (int) $package->id;
                                $price = (float) $package->price;
                                $isSelected = $selectedPackageId === $packageId;
                                $isFeatured = (bool) $package->is_featured;
                            @endphp
                            <div class="col-xl-4 col-md-6">
                                <label class="teacher-upgrade-card {{ $isSelected ? 'is-selected' : '' }} {{ $isFeatured ? 'is-featured' : '' }}" data-upgrade-card>
                                    <input type="radio" name="package_id" value="{{ $packageId }}" data-package-price="{{ $price }}" @checked($isSelected)>

                                    @if ($isFeatured)
                                        <span class="teacher-upgrade-card__ribbon">
                                            {{ __('teacher::landing.packages.most_popular') }}
                                        </span>
                                    @endif

                                    <div class="teacher-upgrade-card__top">
                                        <span class="teacher-upgrade-card__tag">{{ strtoupper((string) $package->code) }}</span>
                                        @if (($package->badge_text_locale ?: '') !== '')
                                            <span class="teacher-upgrade-card__badge">{{ $package->badge_text_locale }}</span>
                                        @endif
                                    </div>

                                    <div class="teacher-upgrade-card__body">
                                        <h5>{{ $package->name_locale ?: $package->name }}</h5>
                                        <div class="teacher-upgrade-card__price">
                                            {{ $price > 0 ? money($price) : '0 đ' }}
                                        </div>
                                        @if (($package->tagline_locale ?: '') !== '')
                                            <p class="teacher-upgrade-card__tagline">{{ $package->tagline_locale }}</p>
                                        @endif
                                        <p class="teacher-upgrade-card__desc">{{ $package->description_locale ?: $package->description }}</p>

                                        <ul class="teacher-upgrade-card__features">
                                            <li>
                                                {{ __('teacher::dashboard.package.current_meta', [
                                                    'commission' => rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.'),
                                                    'limit' => $package->effective_course_limit ?: __('teacher::dashboard.courses.unlimited'),
                                                ]) }}
                                            </li>
                                            @if (($package->support_level_locale ?: '') !== '')
                                                <li>{{ $package->support_level_locale }}</li>
                                            @endif
                                        </ul>
                                    </div>

                                    <div class="teacher-upgrade-card__check">
                                        <span>{{ $isSelected ? '✓' : '' }}</span>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @error('package_id')
                        <div class="text-danger small mt-3">{{ $message }}</div>
                    @enderror

                    <div class="teacher-upgrade-warning d-none" data-upgrade-warning></div>
                </div>

                <div class="row g-4 mt-1 align-items-start">
                    <div class="col-lg-7">
                        <div class="teacher-upgrade-section">
                            <div class="teacher-upgrade-section__head">
                                <div>
                                    <h4>{{ __('teacher::dashboard.package.payment_method') }}</h4>
                                    <p class="mb-0">{{ __('teacher::dashboard.package.pending_payment_description') }}</p>
                                </div>
                            </div>

                            <div class="teacher-upgrade-payment-grid" data-upgrade-payment-methods>
                                @foreach ([
                                    'bank_transfer' => __('teacher::portal.payment_methods.bank_transfer'),
                                    'vnpay' => __('teacher::portal.payment_methods.vnpay'),
                                    'momo' => __('teacher::portal.payment_methods.momo'),
                                ] as $method => $label)
                                    <label class="teacher-upgrade-payment {{ $selectedPaymentMethod === $method ? 'is-selected' : '' }}" data-upgrade-payment>
                                        <input type="radio" name="payment_method" value="{{ $method }}" @checked($selectedPaymentMethod === $method)>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <aside class="teacher-upgrade-summary">
                            <div class="teacher-upgrade-summary__label">{{ __('teacher::dashboard.package.status_title') }}</div>
                            <div class="teacher-upgrade-summary__name" data-upgrade-summary-name>
                                {{ $upgradePackages->firstWhere('id', $selectedPackageId)?->name_locale ?: $upgradePackages->firstWhere('id', $selectedPackageId)?->name }}
                            </div>
                            <div class="teacher-upgrade-summary__price" data-upgrade-summary-price></div>
                            <p class="teacher-upgrade-summary__meta" data-upgrade-summary-meta></p>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-outline-secondary">
                                    {{ __('teacher::dashboard.common.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    {{ __('teacher::dashboard.package.submit_upgrade') }}
                                </button>
                            </div>
                        </aside>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-upgrade-shell {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 26%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-upgrade-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .teacher-upgrade-kicker {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.14);
            color: #8fc3ff;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .teacher-upgrade-title {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-upgrade-desc {
            max-width: 740px;
            color: #a9bbd5;
            font-size: 1rem;
            line-height: 1.7;
        }

        .teacher-upgrade-current,
        .teacher-upgrade-section,
        .teacher-upgrade-summary {
            border: 1px solid rgba(96, 165, 250, 0.18);
            background: rgba(15, 23, 42, 0.72);
            border-radius: 26px;
            box-shadow: 0 22px 54px rgba(2, 6, 23, 0.2);
        }

        .teacher-upgrade-current {
            padding: 1.35rem 1.4rem;
            margin-bottom: 1.6rem;
        }

        .teacher-upgrade-current__label,
        .teacher-upgrade-summary__label {
            color: #8fb5e9;
            font-size: 0.84rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }

        .teacher-upgrade-current__name,
        .teacher-upgrade-summary__name {
            margin-top: 0.35rem;
            color: #f8fbff;
            font-size: 1.4rem;
            font-weight: 800;
        }

        .teacher-upgrade-current__meta,
        .teacher-upgrade-summary__meta {
            margin-top: 0.35rem;
            color: #a9bbd5;
        }

        .teacher-upgrade-current__time {
            margin-top: 0.75rem;
            color: #8fc3ff;
            font-weight: 700;
        }

        .teacher-upgrade-section {
            padding: 1.45rem;
        }

        .teacher-upgrade-section__head {
            margin-bottom: 1.1rem;
        }

        .teacher-upgrade-section__head h4 {
            margin-bottom: 0.35rem;
            color: #f8fbff;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .teacher-upgrade-section__head p {
            color: #9fb1cc;
        }

        .teacher-upgrade-card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            padding: 1.25rem;
            border-radius: 24px;
            border: 1px solid rgba(96, 165, 250, 0.16);
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 24%),
                linear-gradient(180deg, rgba(18, 28, 50, 0.96) 0%, rgba(12, 21, 39, 0.96) 100%);
            cursor: pointer;
            transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
            overflow: hidden;
        }

        .teacher-upgrade-card:hover {
            transform: translateY(-6px);
            border-color: rgba(125, 211, 252, 0.34);
            box-shadow: 0 28px 56px rgba(2, 6, 23, 0.28);
        }

        .teacher-upgrade-card.is-selected {
            border-color: rgba(96, 165, 250, 0.48);
            box-shadow:
                0 0 0 3px rgba(8, 17, 31, 0.86),
                0 0 0 6px rgba(96, 165, 250, 0.24),
                0 34px 68px rgba(2, 6, 23, 0.32);
        }

        .teacher-upgrade-card.is-featured {
            border-color: rgba(45, 212, 191, 0.28);
            box-shadow: 0 24px 52px rgba(2, 6, 23, 0.26);
        }

        .teacher-upgrade-card input {
            display: none;
        }

        .teacher-upgrade-card__ribbon {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            padding: 0.42rem 0.82rem;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(45, 212, 191, 0.95), rgba(59, 130, 246, 0.92));
            color: #04111f;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            box-shadow: 0 14px 28px rgba(20, 184, 166, 0.22);
        }

        .teacher-upgrade-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-top: 2rem;
        }

        .teacher-upgrade-card__tag,
        .teacher-upgrade-card__badge {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .teacher-upgrade-card__tag {
            background: rgba(37, 99, 235, 0.16);
            color: #8fc3ff;
        }

        .teacher-upgrade-card__badge {
            background: rgba(45, 212, 191, 0.16);
            color: #8af4e3;
        }

        .teacher-upgrade-card__body h5 {
            color: #f8fbff;
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 0.45rem;
        }

        .teacher-upgrade-card__price {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 0.65rem;
        }

        .teacher-upgrade-card__tagline {
            color: #8fc3ff;
            font-weight: 600;
            margin-bottom: 0.45rem;
        }

        .teacher-upgrade-card__desc {
            color: #a9bbd5;
            line-height: 1.65;
            margin-bottom: 0.9rem;
        }

        .teacher-upgrade-card__features {
            margin: 0;
            padding-left: 1rem;
            color: #d9e8ff;
        }

        .teacher-upgrade-card__features li + li {
            margin-top: 0.35rem;
        }

        .teacher-upgrade-card__check {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 32px;
            height: 32px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.24);
            background: rgba(15, 23, 42, 0.72);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #60a5fa;
            font-size: 1.1rem;
            font-weight: 900;
        }

        .teacher-upgrade-payment-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .teacher-upgrade-payment-grid.is-hidden {
            display: none;
        }

        .teacher-upgrade-payment {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 64px;
            padding: 1rem 1.15rem;
            border-radius: 20px;
            border: 1px solid rgba(96, 165, 250, 0.16);
            background: rgba(18, 28, 50, 0.78);
            color: #e5eefc;
            font-weight: 700;
            cursor: pointer;
            transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        .teacher-upgrade-payment:hover,
        .teacher-upgrade-payment.is-selected {
            transform: translateY(-2px);
            border-color: rgba(125, 211, 252, 0.3);
            box-shadow: 0 18px 40px rgba(2, 6, 23, 0.22);
        }

        .teacher-upgrade-payment input {
            display: none;
        }

        .teacher-upgrade-warning {
            margin-top: 1rem;
            padding: 1rem 1.1rem;
            border-radius: 18px;
            border: 1px solid rgba(250, 204, 21, 0.2);
            background: rgba(120, 53, 15, 0.22);
            color: #fde68a;
            line-height: 1.65;
        }

        .teacher-upgrade-summary {
            position: sticky;
            top: 96px;
            padding: 1.4rem;
        }

        .teacher-upgrade-summary__price {
            margin-top: 0.85rem;
            color: #ffffff;
            font-size: 2.2rem;
            font-weight: 900;
            line-height: 1.1;
        }

        @media (max-width: 991.98px) {
            .teacher-upgrade-payment-grid {
                grid-template-columns: 1fr;
            }

            .teacher-upgrade-summary {
                position: static;
            }
        }

        @media (max-width: 767.98px) {
            .teacher-upgrade-hero {
                flex-direction: column;
            }

            .teacher-upgrade-section,
            .teacher-upgrade-current,
            .teacher-upgrade-summary {
                padding: 1.2rem;
                border-radius: 22px;
            }

            .teacher-upgrade-title {
                font-size: 2rem;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const form = document.querySelector('form[action="{{ route('teacher.dashboard.package.upgrade.store') }}"]');
            const cards = [...document.querySelectorAll('[data-upgrade-card]')];
            const paymentGrid = document.querySelector('[data-upgrade-payment-methods]');
            const paymentOptions = [...document.querySelectorAll('[data-upgrade-payment]')];
            const summaryName = document.querySelector('[data-upgrade-summary-name]');
            const summaryPrice = document.querySelector('[data-upgrade-summary-price]');
            const summaryMeta = document.querySelector('[data-upgrade-summary-meta]');
            const warningBox = document.querySelector('[data-upgrade-warning]');
            const unlimitedText = @json(__('teacher::dashboard.courses.unlimited'));
            const currentPackage = @json($currentPackageMap);
            const packageMap = @json($packageMap);

            const formatMoney = (value) => {
                const locale = document.documentElement.lang || 'vi';
                return new Intl.NumberFormat(locale).format(Math.max(Number(value || 0), 0)) + ' đ';
            };

            const updateCards = () => {
                let selectedId = null;
                let selectedPrice = 0;

                cards.forEach((card) => {
                    const input = card.querySelector('input[name="package_id"]');
                    const selected = !!input?.checked;
                    card.classList.toggle('is-selected', selected);
                    const dot = card.querySelector('.teacher-upgrade-card__check span');
                    if (dot) {
                        dot.textContent = selected ? '✓' : '';
                    }
                    if (selected) {
                        selectedId = input.value;
                        selectedPrice = Number(input.dataset.packagePrice || 0);
                    }
                });

                if (summaryName && packageMap[selectedId]) {
                    summaryName.textContent = packageMap[selectedId].name;
                    summaryPrice.textContent = selectedPrice > 0 ? formatMoney(selectedPrice) : '0 đ';
                    summaryMeta.textContent = packageMap[selectedId].meta || unlimitedText;
                }

                if (paymentGrid) {
                    paymentGrid.classList.toggle('is-hidden', selectedPrice <= 0);
                }

                if (warningBox && packageMap[selectedId]) {
                    const target = packageMap[selectedId];
                    const currentCycle = currentPackage.billing_cycle;
                    const targetCycle = target.billing_cycle;
                    const currentExpiresAt = currentPackage.expires_at ? new Date(currentPackage.expires_at) : null;
                    const hasRemainingRecurring = currentExpiresAt && currentExpiresAt > new Date() && ['monthly', 'yearly'].includes(currentCycle);
                    let warning = '';

                    if (currentCycle === 'one_time' && ['monthly', 'yearly'].includes(targetCycle)) {
                        warning = 'Bạn đang chuyển từ gói mua 1 lần sang gói có thời hạn. Sau khi duyệt, gói mới sẽ thay thế gói hiện tại và bắt đầu tính thời gian sử dụng.';
                    } else if (hasRemainingRecurring && Number(selectedId) === currentPackage.id) {
                        warning = 'Gói hiện tại chưa hết hạn. Nếu bạn mua tiếp cùng loại gói thời hạn, hệ thống sẽ cộng dồn thêm thời gian sử dụng.';
                    } else if (hasRemainingRecurring && target.sort_order !== currentPackage.sort_order) {
                        const expiresText = currentExpiresAt.toLocaleDateString('vi-VN');
                        warning = `Gói hiện tại của bạn còn hiệu lực đến ${expiresText}. Gói mới sẽ được xếp hàng chờ và chỉ tự kích hoạt sau khi gói hiện tại dùng hết.`;
                    }

                    warningBox.textContent = warning;
                    warningBox.classList.toggle('d-none', warning === '');
                }
            };

            const updatePayments = () => {
                paymentOptions.forEach((option) => {
                    const input = option.querySelector('input[name="payment_method"]');
                    option.classList.toggle('is-selected', !!input?.checked);
                });
            };

            cards.forEach((card) => {
                const input = card.querySelector('input[name="package_id"]');
                input?.addEventListener('change', updateCards);
            });

            paymentOptions.forEach((option) => {
                const input = option.querySelector('input[name="payment_method"]');
                input?.addEventListener('change', updatePayments);
            });

            form?.addEventListener('submit', (event) => {
                const selectedInput = document.querySelector('input[name="package_id"]:checked');
                if (!selectedInput) {
                    return;
                }

                const target = packageMap[selectedInput.value];
                if (!target) {
                    return;
                }

                if (currentPackage.billing_cycle === 'one_time' && ['monthly', 'yearly'].includes(target.billing_cycle)) {
                    const confirmed = window.confirm('Bạn đang đổi từ gói mua 1 lần sang gói có thời hạn. Sau khi duyệt, gói mới sẽ thay thế gói hiện tại. Bạn có chắc muốn tiếp tục không?');
                    if (!confirmed) {
                        event.preventDefault();
                    }
                }
            });

            updateCards();
            updatePayments();
        })();
    </script>
@endsection
