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
        $featureRows = [
            'can_duplicate_courses' => __('teacher::dashboard.package_features.labels.can_duplicate_courses'),
            'can_manage_comments' => __('teacher::dashboard.package_features.labels.can_manage_comments'),
            'can_manage_coupons' => __('teacher::dashboard.package_features.labels.can_manage_coupons'),
            'can_grant_courses' => __('teacher::dashboard.package_features.labels.can_grant_courses'),
            'can_export_orders' => __('teacher::dashboard.package_features.labels.can_export_orders'),
            'can_export_students' => __('teacher::dashboard.package_features.labels.can_export_students'),
        ];
        $featureNotes = [
            'course_limit' => __('teacher::dashboard.package_features.notes.course_limit'),
            'payout_account_limit' => __('teacher::dashboard.package_features.notes.payout_account_limit'),
            'commission_rate' => __('teacher::dashboard.package_features.notes.commission_rate'),
            'coupon_limit' => __('teacher::dashboard.package_features.notes.coupon_limit'),
            'can_duplicate_courses' => __('teacher::dashboard.package_features.notes.can_duplicate_courses'),
            'can_manage_comments' => __('teacher::dashboard.package_features.notes.can_manage_comments'),
            'can_manage_coupons' => __('teacher::dashboard.package_features.notes.can_manage_coupons'),
            'can_grant_courses' => __('teacher::dashboard.package_features.notes.can_grant_courses'),
            'can_export_orders' => __('teacher::dashboard.package_features.notes.can_export_orders'),
            'can_export_students' => __('teacher::dashboard.package_features.notes.can_export_students'),
        ];
        $missingFeatureKeys = collect(array_keys($featureRows))
            ->filter(fn ($featureKey) => !$currentPackage?->{$featureKey})
            ->values();
        $recommendedPackageId = $missingFeatureKeys->isEmpty()
            ? null
            : optional($upgradePackages->first(function ($package) use ($missingFeatureKeys) {
                foreach ($missingFeatureKeys as $featureKey) {
                    if (!(bool) $package->{$featureKey}) {
                        return false;
                    }
                }

                return true;
            }))->id;
        $missingFeatureLabels = $missingFeatureKeys
            ->map(fn ($featureKey) => $featureRows[$featureKey] ?? $featureKey)
            ->values();
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
                @if ($recommendedPackageId)
                    <div class="teacher-upgrade-current__recommend mt-3">
                        {{ __('teacher::dashboard.package_features.recommend_intro') }}
                        <strong>{{ $upgradePackages->firstWhere('id', $recommendedPackageId)?->name_locale ?: $upgradePackages->firstWhere('id', $recommendedPackageId)?->name }}</strong>
                        @if ($missingFeatureLabels->isNotEmpty())
                            <div class="small mt-1">
                                {{ __('teacher::dashboard.package_features.recommend_missing') }}:
                                {{ $missingFeatureLabels->implode(', ') }}
                            </div>
                        @endif
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
                                $isRecommended = $recommendedPackageId !== null && $recommendedPackageId === $packageId;
                            @endphp
                            <div class="col-xl-4 col-md-6">
                                <label class="teacher-upgrade-card {{ $isSelected ? 'is-selected' : '' }} {{ $isFeatured ? 'is-featured' : '' }} {{ $isRecommended ? 'is-recommended' : '' }}" data-upgrade-card>
                                    <input type="radio" name="package_id" value="{{ $packageId }}" data-package-price="{{ $price }}" @checked($isSelected)>

                                    @if ($isFeatured)
                                        <span class="teacher-upgrade-card__ribbon">
                                            {{ __('teacher::landing.packages.most_popular') }}
                                        </span>
                                    @endif
                                    @if ($isRecommended)
                                        <span class="teacher-upgrade-card__recommend">
                                            {{ __('teacher::dashboard.package_features.recommended_badge') }}
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

                <div class="teacher-upgrade-section mt-4">
                    <div class="teacher-upgrade-section__head">
                        <div>
                            <h4>{{ __('teacher::dashboard.package_features.compare_title') }}</h4>
                            <p class="mb-0">{{ __('teacher::dashboard.package_features.compare_description') }}</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table teacher-upgrade-compare mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('teacher::dashboard.package_features.compare_feature') }}</th>
                                    <th>
                                        {{ __('teacher::dashboard.package_features.compare_current') }}
                                        <div class="small text-muted mt-1">{{ $currentPackage?->name_locale ?: $currentPackage?->name }}</div>
                                    </th>
                                    @foreach ($upgradePackages as $package)
                                        <th data-compare-col="{{ (int) $package->id }}">
                                            {{ $package->name_locale ?: $package->name }}
                                            @if ((int) $package->id === $selectedPackageId)
                                                <div class="small mt-1 text-info" data-compare-selected-label="{{ (int) $package->id }}">{{ __('teacher::dashboard.package_features.compare_selected') }}</div>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>{{ __('teacher::dashboard.package_features.labels.course_limit') }}</div>
                                        <div class="small text-muted mt-1">{{ $featureNotes['course_limit'] }}</div>
                                    </td>
                                    <td>
                                        <span class="teacher-upgrade-compare__pill is-neutral">
                                            {{ $currentPackage?->effective_course_limit ?: __('teacher::dashboard.courses.unlimited') }}
                                        </span>
                                    </td>
                                    @foreach ($upgradePackages as $package)
                                        <td data-compare-cell="{{ (int) $package->id }}">
                                            <span class="teacher-upgrade-compare__pill is-neutral">
                                                {{ $package->effective_course_limit ?: __('teacher::dashboard.courses.unlimited') }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>
                                        <div>{{ __('teacher::dashboard.package_features.labels.commission_rate') }}</div>
                                        <div class="small text-muted mt-1">{{ $featureNotes['commission_rate'] }}</div>
                                    </td>
                                    <td>
                                        <span class="teacher-upgrade-compare__pill is-neutral">
                                            {{ rtrim(rtrim(number_format((float) ($currentPackage?->commission_rate ?? 0), 2, '.', ''), '0'), '.') }}%
                                        </span>
                                    </td>
                                    @foreach ($upgradePackages as $package)
                                        <td data-compare-cell="{{ (int) $package->id }}">
                                            <span class="teacher-upgrade-compare__pill is-neutral">
                                                {{ rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.') }}%
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>
                                        <div>{{ __('teacher::dashboard.package_features.labels.payout_account_limit') }}</div>
                                        <div class="small text-muted mt-1">{{ $featureNotes['payout_account_limit'] }}</div>
                                    </td>
                                    <td>
                                        <span class="teacher-upgrade-compare__pill is-neutral">
                                            {{ $currentPackage?->effective_payout_account_limit ?? 3 }}
                                        </span>
                                    </td>
                                    @foreach ($upgradePackages as $package)
                                        <td data-compare-cell="{{ (int) $package->id }}">
                                            <span class="teacher-upgrade-compare__pill is-neutral">
                                                {{ $package->effective_payout_account_limit }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>
                                        <div>{{ __('teacher::dashboard.package_features.labels.coupon_limit') }}</div>
                                        <div class="small text-muted mt-1">{{ $featureNotes['coupon_limit'] }}</div>
                                    </td>
                                    <td>
                                        <span class="teacher-upgrade-compare__pill is-neutral">
                                            @if ($currentPackage?->can_manage_coupons)
                                                {{ $currentPackage?->effective_coupon_limit ?: __('teacher::coupons.labels.unlimited') }}
                                            @else
                                                {{ __('teacher::dashboard.package_features.unavailable') }}
                                            @endif
                                        </span>
                                    </td>
                                    @foreach ($upgradePackages as $package)
                                        <td data-compare-cell="{{ (int) $package->id }}">
                                            <span class="teacher-upgrade-compare__pill is-neutral">
                                                @if ($package->can_manage_coupons)
                                                    {{ $package->effective_coupon_limit ?: __('teacher::coupons.labels.unlimited') }}
                                                @else
                                                    {{ __('teacher::dashboard.package_features.unavailable') }}
                                                @endif
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                                @foreach ($featureRows as $featureKey => $featureLabel)
                                    <tr>
                                        <td>
                                            <div>{{ $featureLabel }}</div>
                                            <div class="small text-muted mt-1">{{ $featureNotes[$featureKey] ?? '' }}</div>
                                        </td>
                                        <td>
                                            <span class="teacher-upgrade-compare__pill {{ $currentPackage?->{$featureKey} ? 'is-on' : 'is-off' }}">
                                                {{ $currentPackage?->{$featureKey} ? __('teacher::dashboard.package_features.available') : __('teacher::dashboard.package_features.unavailable') }}
                                            </span>
                                        </td>
                                        @foreach ($upgradePackages as $package)
                                            @php
                                                $enabled = (bool) $package->{$featureKey};
                                                $isUpgradeGain = !$currentPackage?->{$featureKey} && $enabled;
                                            @endphp
                                            <td data-compare-cell="{{ (int) $package->id }}" data-compare-feature-cell="{{ (int) $package->id }}:{{ $featureKey }}" class="{{ $isUpgradeGain ? 'is-static-upgrade-gain' : '' }}">
                                                <span class="teacher-upgrade-compare__pill {{ $enabled ? 'is-on' : 'is-off' }}">
                                                    {{ $enabled ? __('teacher::dashboard.package_features.available') : __('teacher::dashboard.package_features.unavailable') }}
                                                </span>
                                                @if ($isUpgradeGain)
                                                    <div class="small text-info mt-1" data-compare-gain-label="{{ (int) $package->id }}:{{ $featureKey }}">{{ __('teacher::dashboard.package_features.new_in_upgrade') }}</div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="teacher-upgrade-compare-mobile">
                        @foreach ($upgradePackages as $package)
                            @php
                                $packageId = (int) $package->id;
                                $isSelectedMobile = $selectedPackageId === $packageId;
                            @endphp
                            <details class="teacher-upgrade-mobile-card {{ $isSelectedMobile ? 'is-selected' : '' }}" data-mobile-compare-card="{{ $packageId }}" {{ $isSelectedMobile ? 'open' : '' }}>
                                <summary class="teacher-upgrade-mobile-card__head">
                                    <div>
                                        <strong>{{ $package->name_locale ?: $package->name }}</strong>
                                        <div class="small text-muted mt-1">
                                            {{ __('teacher::dashboard.package.current_meta', [
                                                'commission' => rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.'),
                                                'limit' => $package->effective_course_limit ?: __('teacher::dashboard.courses.unlimited'),
                                            ]) }}
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__head-meta">
                                        @if ($recommendedPackageId !== null && $recommendedPackageId === $packageId)
                                            <span class="teacher-upgrade-mobile-card__badge">{{ __('teacher::dashboard.package_features.recommended_badge') }}</span>
                                        @endif
                                        <span class="teacher-upgrade-mobile-card__chevron" aria-hidden="true"></span>
                                    </div>
                                </summary>

                                <div class="teacher-upgrade-mobile-card__list">
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('teacher::dashboard.package_features.labels.course_limit') }}</span>
                                        <strong>{{ $package->effective_course_limit ?: __('teacher::dashboard.courses.unlimited') }}</strong>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('teacher::dashboard.package_features.labels.payout_account_limit') }}</span>
                                        <strong>{{ $package->effective_payout_account_limit }}</strong>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <div>
                                            <span>{{ __('teacher::dashboard.package_features.labels.coupon_limit') }}</span>
                                            <small>{{ $featureNotes['coupon_limit'] ?? '' }}</small>
                                        </div>
                                        <strong>
                                            @if ($package->can_manage_coupons)
                                                {{ $package->effective_coupon_limit ?: __('teacher::coupons.labels.unlimited') }}
                                            @else
                                                {{ __('teacher::dashboard.package_features.unavailable') }}
                                            @endif
                                        </strong>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('teacher::dashboard.package_features.labels.commission_rate') }}</span>
                                        <strong>{{ rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.') }}%</strong>
                                    </div>
                                    @foreach ($featureRows as $featureKey => $featureLabel)
                                        @php
                                            $enabled = (bool) $package->{$featureKey};
                                            $isUpgradeGain = !$currentPackage?->{$featureKey} && $enabled;
                                        @endphp
                                        <div class="teacher-upgrade-mobile-card__item">
                                            <div>
                                                <span>{{ $featureLabel }}</span>
                                                <small>{{ $featureNotes[$featureKey] ?? '' }}</small>
                                            </div>
                                            <div class="text-end">
                                                <strong class="{{ $enabled ? 'text-success' : 'text-muted' }}">
                                                    {{ $enabled ? __('teacher::dashboard.package_features.available') : __('teacher::dashboard.package_features.unavailable') }}
                                                </strong>
                                                @if ($isUpgradeGain)
                                                    <small class="d-block text-info">{{ __('teacher::dashboard.package_features.new_in_upgrade') }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
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

        .teacher-upgrade-current__recommend {
            padding: 0.9rem 1rem;
            border-radius: 18px;
            border: 1px solid rgba(250, 204, 21, 0.22);
            background: rgba(120, 53, 15, 0.18);
            color: #fde68a;
            line-height: 1.6;
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

        .teacher-upgrade-card.is-recommended {
            border-color: rgba(250, 204, 21, 0.3);
            box-shadow:
                0 0 0 1px rgba(250, 204, 21, 0.16),
                0 24px 52px rgba(2, 6, 23, 0.26);
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

        .teacher-upgrade-card__recommend {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            padding: 0.42rem 0.82rem;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(250, 204, 21, 0.96), rgba(251, 146, 60, 0.92));
            color: #291407;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            box-shadow: 0 14px 28px rgba(251, 191, 36, 0.18);
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

        .teacher-upgrade-compare {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: transparent;
            --bs-table-striped-color: inherit;
            --bs-table-active-bg: transparent;
            --bs-table-active-color: inherit;
            --bs-table-hover-bg: transparent;
            --bs-table-hover-color: inherit;
            color: #dce9fb;
        }

        .teacher-upgrade-compare th,
        .teacher-upgrade-compare td {
            vertical-align: middle;
            border-color: rgba(96, 165, 250, 0.14);
            min-width: 170px;
        }

        .teacher-upgrade-compare th:first-child,
        .teacher-upgrade-compare td:first-child {
            min-width: 220px;
        }

        .teacher-upgrade-compare__pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .teacher-upgrade-compare__pill.is-on {
            background: rgba(34, 197, 94, 0.14);
            color: #86efac;
        }

        .teacher-upgrade-compare__pill.is-off {
            background: rgba(148, 163, 184, 0.14);
            color: #cbd5e1;
        }

        .teacher-upgrade-compare__pill.is-neutral {
            background: rgba(59, 130, 246, 0.12);
            color: #bfdbfe;
        }

        .teacher-upgrade-compare th.is-dynamic-selected {
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.38), rgba(30, 64, 175, 0.22));
            color: #f8fbff;
            box-shadow:
                inset 0 1px 0 rgba(147, 197, 253, 0.22),
                inset 3px 0 0 rgba(56, 189, 248, 0.85),
                inset -3px 0 0 rgba(56, 189, 248, 0.85);
        }

        .teacher-upgrade-compare td.is-dynamic-selected {
            position: relative;
            background: linear-gradient(180deg, rgba(30, 64, 175, 0.18), rgba(37, 99, 235, 0.1));
            box-shadow:
                inset 3px 0 0 rgba(56, 189, 248, 0.75),
                inset -3px 0 0 rgba(56, 189, 248, 0.75);
        }

        .teacher-upgrade-compare td.is-dynamic-selected .teacher-upgrade-compare__pill {
            transform: scale(1.03);
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.18);
        }

        .teacher-upgrade-compare td.is-dynamic-gain {
            background:
                linear-gradient(180deg, rgba(8, 145, 178, 0.12), rgba(37, 99, 235, 0.08)),
                rgba(59, 130, 246, 0.06);
        }

        .teacher-upgrade-compare td.is-dynamic-gain .teacher-upgrade-compare__pill {
            box-shadow:
                0 0 0 2px rgba(34, 211, 238, 0.22),
                0 12px 28px rgba(8, 47, 73, 0.16);
        }

        .teacher-upgrade-compare-mobile {
            display: none;
        }

        .teacher-upgrade-mobile-card {
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.78);
            padding: 1rem;
        }

        .teacher-upgrade-mobile-card + .teacher-upgrade-mobile-card {
            margin-top: 1rem;
        }

        .teacher-upgrade-mobile-card.is-selected {
            border-color: rgba(96, 165, 250, 0.38);
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.18);
        }

        .teacher-upgrade-mobile-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            cursor: pointer;
            list-style: none;
            margin: -1rem;
            padding: 1rem;
        }

        .teacher-upgrade-mobile-card__head::-webkit-details-marker {
            display: none;
        }

        .teacher-upgrade-mobile-card__head strong {
            color: #f8fbff;
            font-size: 1rem;
        }

        .teacher-upgrade-mobile-card__head-meta {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-shrink: 0;
        }

        .teacher-upgrade-mobile-card__badge {
            display: inline-flex;
            align-items: center;
            padding: 0.36rem 0.72rem;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(250, 204, 21, 0.96), rgba(251, 146, 60, 0.92));
            color: #291407;
            font-size: 0.72rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .teacher-upgrade-mobile-card__chevron {
            width: 11px;
            height: 11px;
            border-right: 2px solid rgba(191, 219, 254, 0.9);
            border-bottom: 2px solid rgba(191, 219, 254, 0.9);
            transform: rotate(45deg);
            transition: transform 0.18s ease;
            margin-top: 0.45rem;
        }

        .teacher-upgrade-mobile-card[open] .teacher-upgrade-mobile-card__chevron {
            transform: rotate(-135deg);
        }

        .teacher-upgrade-mobile-card__list {
            display: grid;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .teacher-upgrade-mobile-card__item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(96, 165, 250, 0.1);
        }

        .teacher-upgrade-mobile-card__item:first-child {
            border-top: 0;
            padding-top: 0;
        }

        .teacher-upgrade-mobile-card__item span {
            display: block;
            color: #dce9fb;
            font-weight: 700;
        }

        .teacher-upgrade-mobile-card__item small {
            display: block;
            margin-top: 0.2rem;
            color: #9fb1cc;
            line-height: 1.5;
        }

        html[data-theme="light"] .teacher-upgrade-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 26%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%);
        }

        html[data-theme="light"] .teacher-upgrade-kicker {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-upgrade-title {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-upgrade-desc {
            color: #475569;
        }

        html[data-theme="light"] .teacher-upgrade-current,
        html[data-theme="light"] .teacher-upgrade-section,
        html[data-theme="light"] .teacher-upgrade-summary {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-upgrade-current__label,
        html[data-theme="light"] .teacher-upgrade-summary__label {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-upgrade-current__name,
        html[data-theme="light"] .teacher-upgrade-summary__name,
        html[data-theme="light"] .teacher-upgrade-section__head h4 {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-upgrade-current__meta,
        html[data-theme="light"] .teacher-upgrade-summary__meta,
        html[data-theme="light"] .teacher-upgrade-section__head p {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-upgrade-current__time {
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-upgrade-current__recommend {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.22);
            color: #92400e;
        }

        html[data-theme="light"] .teacher-upgrade-card {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-upgrade-card.is-recommended {
            border-color: rgba(245, 158, 11, 0.3);
            box-shadow:
                0 0 0 1px rgba(245, 158, 11, 0.12),
                var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-upgrade-card__tag {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-upgrade-card__badge {
            background: rgba(16, 185, 129, 0.12);
            color: #047857;
        }

        html[data-theme="light"] .teacher-upgrade-card__body h5,
        html[data-theme="light"] .teacher-upgrade-card__price {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-upgrade-card__tagline,
        html[data-theme="light"] .teacher-upgrade-card__desc {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-upgrade-card__features {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-upgrade-card__check {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            color: #2563eb;
        }

        html[data-theme="light"] .teacher-upgrade-payment {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            color: var(--admin-text);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-upgrade-warning {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.25);
            color: #92400e;
        }

        html[data-theme="light"] .teacher-upgrade-summary__price {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-upgrade-compare {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-upgrade-compare th,
        html[data-theme="light"] .teacher-upgrade-compare td {
            border-color: var(--admin-border);
        }

        html[data-theme="light"] .teacher-upgrade-compare__pill.is-on {
            background: rgba(16, 185, 129, 0.12);
            color: #047857;
        }

        html[data-theme="light"] .teacher-upgrade-compare__pill.is-off {
            background: rgba(148, 163, 184, 0.16);
            color: #475569;
        }

        html[data-theme="light"] .teacher-upgrade-compare__pill.is-neutral {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-upgrade-compare th.is-dynamic-selected {
            background: linear-gradient(180deg, rgba(191, 219, 254, 0.96), rgba(219, 234, 254, 0.92));
            color: #0f172a;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.78),
                inset 3px 0 0 rgba(37, 99, 235, 0.9),
                inset -3px 0 0 rgba(37, 99, 235, 0.9);
        }

        html[data-theme="light"] .teacher-upgrade-compare td.is-dynamic-selected {
            background: linear-gradient(180deg, rgba(239, 246, 255, 0.98), rgba(219, 234, 254, 0.9));
            box-shadow:
                inset 3px 0 0 rgba(37, 99, 235, 0.85),
                inset -3px 0 0 rgba(37, 99, 235, 0.85);
        }

        html[data-theme="light"] .teacher-upgrade-compare td.is-dynamic-selected .teacher-upgrade-compare__pill {
            box-shadow: 0 10px 20px rgba(148, 163, 184, 0.18);
        }

        html[data-theme="light"] .teacher-upgrade-compare td.is-dynamic-gain {
            background:
                linear-gradient(180deg, rgba(207, 250, 254, 0.88), rgba(219, 234, 254, 0.72)),
                rgba(37, 99, 235, 0.04);
        }

        html[data-theme="light"] .teacher-upgrade-compare td.is-dynamic-gain .teacher-upgrade-compare__pill {
            box-shadow:
                0 0 0 2px rgba(14, 165, 233, 0.18),
                0 12px 20px rgba(148, 163, 184, 0.16);
        }

        html[data-theme="light"] .teacher-upgrade-mobile-card {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-upgrade-mobile-card__head strong,
        html[data-theme="light"] .teacher-upgrade-mobile-card__item span {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-upgrade-mobile-card__chevron {
            border-right-color: #1d4ed8;
            border-bottom-color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-upgrade-mobile-card__item small {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-upgrade-mobile-card__item {
            border-top-color: var(--admin-border);
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

            .teacher-upgrade-compare {
                display: none;
            }

            .teacher-upgrade-compare-mobile {
                display: block;
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
            const compareColumns = [...document.querySelectorAll('[data-compare-col]')];
            const compareCells = [...document.querySelectorAll('[data-compare-cell]')];
            const compareFeatureCells = [...document.querySelectorAll('[data-compare-feature-cell]')];
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

                compareColumns.forEach((column) => {
                    column.classList.toggle('is-dynamic-selected', String(column.dataset.compareCol) === String(selectedId));
                });

                compareCells.forEach((cell) => {
                    cell.classList.toggle('is-dynamic-selected', String(cell.dataset.compareCell) === String(selectedId));
                });

                compareFeatureCells.forEach((cell) => {
                    const [packageId] = String(cell.dataset.compareFeatureCell || '').split(':');
                    cell.classList.toggle('is-dynamic-gain', String(packageId) === String(selectedId) && cell.classList.contains('is-static-upgrade-gain'));
                });
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
