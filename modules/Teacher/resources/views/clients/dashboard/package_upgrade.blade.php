@extends('layouts.teacher')

@section('content')
    @php
        $selectedPackageId = (int) old('package_id', $upgradePackages->first()?->id);
        $selectedPaymentMethod = old('payment_method', 'bank_transfer');
        $currentPackageExpiresAt = $teacher->package_expires_at;
        $currentPackageDaysLeft = $currentPackageExpiresAt ? max(now()->startOfDay()->diffInDays($currentPackageExpiresAt->copy()->startOfDay(), false), 0) : null;
        $isRecurringPackage = fn ($package) => in_array($package?->billing_cycle, ['monthly', 'yearly'], true);
        $formatDate = fn ($date) => $date?->format('d/m/Y');
        $packageTermLabel = function ($package) {
            return match ($package?->billing_cycle) {
                'monthly' => __('teacher::dashboard.package.term_monthly'),
                'yearly' => __('teacher::dashboard.package.term_yearly'),
                default => __('teacher::dashboard.package.term_permanent'),
            };
        };
        $calculatePackagePreview = function ($targetPackage) use ($currentPackage, $currentPackageExpiresAt, $isRecurringPackage) {
            $now = now();
            $isTargetRecurring = $isRecurringPackage($targetPackage);

            $calculateExpiresAt = function ($package, $startAt) use ($isRecurringPackage) {
                if (!$isRecurringPackage($package) || !$startAt) {
                    return null;
                }

                return match ($package->billing_cycle) {
                    'monthly' => $startAt->copy()->addMonth(),
                    'yearly' => $startAt->copy()->addYear(),
                    default => null,
                };
            };

            if (!$currentPackage || !$targetPackage) {
                return [
                    'start_at' => $now,
                    'expires_at' => $calculateExpiresAt($targetPackage, $now),
                    'is_queued' => false,
                ];
            }

            if (
                $isRecurringPackage($currentPackage) &&
                $currentPackageExpiresAt &&
                $currentPackageExpiresAt->isFuture()
            ) {
                if (
                    (int) $currentPackage->id === (int) $targetPackage->id &&
                    $currentPackage->billing_cycle === $targetPackage->billing_cycle
                ) {
                    return [
                        'start_at' => $currentPackageExpiresAt->copy(),
                        'expires_at' => $calculateExpiresAt($targetPackage, $currentPackageExpiresAt->copy()),
                        'is_queued' => false,
                    ];
                }

                if ((int) ($targetPackage->sort_order ?? 0) >= (int) ($currentPackage->sort_order ?? 0)) {
                    return [
                        'start_at' => $now,
                        'expires_at' => $calculateExpiresAt($targetPackage, $now),
                        'is_queued' => false,
                    ];
                }

                return [
                    'start_at' => $currentPackageExpiresAt->copy(),
                    'expires_at' => $calculateExpiresAt($targetPackage, $currentPackageExpiresAt->copy()),
                    'is_queued' => true,
                ];
            }

            return [
                'start_at' => $now,
                'expires_at' => $calculateExpiresAt($targetPackage, $now),
                'is_queued' => false,
            ];
        };
        $packageTermDescription = function ($package, ?array $preview = null) use ($isRecurringPackage, $formatDate, $packageTermLabel) {
            if (!$isRecurringPackage($package)) {
                return __('teacher::dashboard.package.term_description_permanent');
            }

            $startAt = $preview['start_at'] ?? null;
            $expiresAt = $preview['expires_at'] ?? null;
            $isQueued = (bool) ($preview['is_queued'] ?? false);

            if (!$startAt || !$expiresAt) {
                return $packageTermLabel($package);
            }

            if ($isQueued) {
                return __('teacher::dashboard.package.term_description_queued', [
                    'start' => $formatDate($startAt),
                    'expires' => $formatDate($expiresAt),
                ]);
            }

            return __('teacher::dashboard.package.term_description_active', [
                'expires' => $formatDate($expiresAt),
            ]);
        };
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
                    'term_label' => $packageTermLabel($package),
                    'name' => $package->name_locale ?: $package->name,
                    'price' => (float) $package->price,
                    'billing_cycle' => $package->billing_cycle,
                    'sort_order' => (int) $package->sort_order,
                    'term_description' => $packageTermDescription($package, $calculatePackagePreview($package)),
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
            'can_manage_students' => __('teacher::dashboard.package_features.labels.can_manage_students'),
            'can_view_student_progress' => __('teacher::dashboard.package_features.labels.can_view_student_progress'),
            'can_view_activity_logs' => __('teacher::dashboard.package_features.labels.can_view_activity_logs'),
            'can_grant_courses' => __('teacher::dashboard.package_features.labels.can_grant_courses'),
            'can_export_orders' => __('teacher::dashboard.package_features.labels.can_export_orders'),
            'can_export_students' => __('teacher::dashboard.package_features.labels.can_export_students'),
            'can_sell_bundles' => __('teacher::dashboard.package_features.labels.can_sell_bundles'),
            'can_schedule_content' => __('teacher::dashboard.package_features.labels.can_schedule_content'),
            'can_send_promotions' => __('teacher::dashboard.package_features.labels.can_send_promotions'),
            'can_issue_certificates' => __('teacher::dashboard.package_features.labels.can_issue_certificates'),
            'can_customize_teacher_landing' => __('teacher::dashboard.package_features.labels.can_customize_teacher_landing'),
            'can_use_affiliate_links' => __('teacher::dashboard.package_features.labels.can_use_affiliate_links'),
        ];
        $featureNotes = [
            'course_limit' => __('teacher::dashboard.package_features.notes.course_limit'),
            'payout_account_limit' => __('teacher::dashboard.package_features.notes.payout_account_limit'),
            'commission_rate' => __('teacher::dashboard.package_features.notes.commission_rate'),
            'coupon_limit' => __('teacher::dashboard.package_features.notes.coupon_limit'),
            'can_duplicate_courses' => __('teacher::dashboard.package_features.notes.can_duplicate_courses'),
            'can_manage_comments' => __('teacher::dashboard.package_features.notes.can_manage_comments'),
            'can_manage_coupons' => __('teacher::dashboard.package_features.notes.can_manage_coupons'),
            'can_manage_students' => __('teacher::dashboard.package_features.notes.can_manage_students'),
            'can_view_student_progress' => __('teacher::dashboard.package_features.notes.can_view_student_progress'),
            'can_view_activity_logs' => __('teacher::dashboard.package_features.notes.can_view_activity_logs'),
            'can_grant_courses' => __('teacher::dashboard.package_features.notes.can_grant_courses'),
            'can_export_orders' => __('teacher::dashboard.package_features.notes.can_export_orders'),
            'can_export_students' => __('teacher::dashboard.package_features.notes.can_export_students'),
            'can_sell_bundles' => __('teacher::dashboard.package_features.notes.can_sell_bundles'),
            'can_schedule_content' => __('teacher::dashboard.package_features.notes.can_schedule_content'),
            'can_send_promotions' => __('teacher::dashboard.package_features.notes.can_send_promotions'),
            'can_issue_certificates' => __('teacher::dashboard.package_features.notes.can_issue_certificates'),
            'can_customize_teacher_landing' => __('teacher::dashboard.package_features.notes.can_customize_teacher_landing'),
            'can_use_affiliate_links' => __('teacher::dashboard.package_features.notes.can_use_affiliate_links'),
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
        $compareDeltaLabel = function ($currentValue, $targetValue, array $options = []) {
            $mode = $options['mode'] ?? 'number';
            $sameLabel = $options['same'] ?? __('teacher::dashboard.package_features.compare_same');
            $betterPrefix = $options['better_prefix'] ?? '+';
            $worsePrefix = $options['worse_prefix'] ?? '-';
            $suffix = $options['suffix'] ?? '';
            $currentUnlimited = (bool) ($options['current_unlimited'] ?? false);
            $targetUnlimited = (bool) ($options['target_unlimited'] ?? false);

            if ($mode === 'feature') {
                if ((bool) $currentValue === (bool) $targetValue) {
                    return ['label' => $sameLabel, 'class' => 'is-same'];
                }

                return (bool) $targetValue
                    ? ['label' => __('teacher::dashboard.package_features.compare_gain'), 'class' => 'is-better']
                    : ['label' => __('teacher::dashboard.package_features.compare_loss'), 'class' => 'is-worse'];
            }

            if ($targetUnlimited && !$currentUnlimited) {
                return ['label' => __('teacher::dashboard.package_features.compare_unlimited'), 'class' => 'is-better'];
            }

            if ($currentUnlimited && !$targetUnlimited) {
                return ['label' => __('teacher::dashboard.package_features.compare_limited'), 'class' => 'is-worse'];
            }

            if ($currentUnlimited && $targetUnlimited) {
                return ['label' => $sameLabel, 'class' => 'is-same'];
            }

            $delta = (float) $targetValue - (float) $currentValue;

            if (abs($delta) < 0.00001) {
                return ['label' => $sameLabel, 'class' => 'is-same'];
            }

            $formattedDelta = rtrim(rtrim(number_format(abs($delta), 2, '.', ''), '0'), '.');

            return $delta > 0
                ? ['label' => $betterPrefix . $formattedDelta . $suffix, 'class' => 'is-better']
                : ['label' => $worsePrefix . $formattedDelta . $suffix, 'class' => 'is-worse'];
        };
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
                <div class="teacher-upgrade-current__term">
                    {{ __('teacher::dashboard.package.term_label') }}: {{ $packageTermLabel($currentPackage) }}
                </div>
                @if ($isRecurringPackage($currentPackage) && $currentPackageExpiresAt)
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
                                $packagePreview = $calculatePackagePreview($package);
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
                                            <li>
                                                {{ __('teacher::dashboard.package.term_label') }}: {{ $packageTermLabel($package) }}
                                            </li>
                                            <li class="teacher-upgrade-card__term">
                                                {{ $packageTermDescription($package, $packagePreview) }}
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
                                        @php
                                            $isSelectedCompareColumn = (int) $package->id === $selectedPackageId;
                                        @endphp
                                        <th data-compare-col="{{ (int) $package->id }}">
                                            <div class="teacher-upgrade-compare__heading">
                                                <div>{{ $package->name_locale ?: $package->name }}</div>
                                                <span class="teacher-upgrade-compare__selected-badge {{ $isSelectedCompareColumn ? 'is-visible' : '' }}" data-compare-selected-label="{{ (int) $package->id }}">
                                                    <span aria-hidden="true">&#10003;</span>
                                                    {{ __('teacher::dashboard.package_features.compare_selected') }}
                                                </span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>{{ __('teacher::dashboard.package.term_label') }}</div>
                                        <div class="small text-muted mt-1">{{ __('teacher::dashboard.package.term_compare_note') }}</div>
                                    </td>
                                    <td>
                                        <span class="teacher-upgrade-compare__pill is-neutral">
                                            {{ $packageTermLabel($currentPackage) }}
                                        </span>
                                        @if ($isRecurringPackage($currentPackage) && $currentPackageExpiresAt)
                                            <div class="teacher-upgrade-compare__delta is-same">
                                                {{ __('teacher::dashboard.package.current_expiry_short', ['date' => $currentPackageExpiresAt->format('d/m/Y')]) }}
                                            </div>
                                        @endif
                                    </td>
                                    @foreach ($upgradePackages as $package)
                                        @php
                                            $packagePreview = $calculatePackagePreview($package);
                                        @endphp
                                        <td data-compare-cell="{{ (int) $package->id }}">
                                            <span class="teacher-upgrade-compare__pill is-neutral">
                                                {{ $packageTermLabel($package) }}
                                            </span>
                                            <div class="teacher-upgrade-compare__delta is-better">
                                                {{ $packageTermDescription($package, $packagePreview) }}
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
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
                                        @php
                                            $courseDelta = $compareDeltaLabel(
                                                $currentPackage?->effective_course_limit,
                                                $package->effective_course_limit,
                                                [
                                                    'current_unlimited' => empty($currentPackage?->effective_course_limit),
                                                    'target_unlimited' => empty($package->effective_course_limit),
                                                    'suffix' => ' ' . __('teacher::dashboard.package_features.compare_unit_courses'),
                                                ]
                                            );
                                        @endphp
                                        <td data-compare-cell="{{ (int) $package->id }}">
                                            <span class="teacher-upgrade-compare__pill is-neutral">
                                                {{ $package->effective_course_limit ?: __('teacher::dashboard.courses.unlimited') }}
                                            </span>
                                            <div class="teacher-upgrade-compare__delta {{ $courseDelta['class'] }}">
                                                {{ $courseDelta['label'] }}
                                            </div>
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
                                        @php
                                            $commissionDelta = $compareDeltaLabel(
                                                (float) ($currentPackage?->commission_rate ?? 0),
                                                (float) $package->commission_rate,
                                                ['suffix' => '%']
                                            );
                                        @endphp
                                        <td data-compare-cell="{{ (int) $package->id }}">
                                            <span class="teacher-upgrade-compare__pill is-neutral">
                                                {{ rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.') }}%
                                            </span>
                                            <div class="teacher-upgrade-compare__delta {{ $commissionDelta['class'] }}">
                                                {{ $commissionDelta['label'] }}
                                            </div>
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
                                        @php
                                            $payoutDelta = $compareDeltaLabel(
                                                (float) ($currentPackage?->effective_payout_account_limit ?? 3),
                                                (float) $package->effective_payout_account_limit,
                                                ['suffix' => ' ' . __('teacher::dashboard.package_features.compare_unit_accounts')]
                                            );
                                        @endphp
                                        <td data-compare-cell="{{ (int) $package->id }}">
                                            <span class="teacher-upgrade-compare__pill is-neutral">
                                                {{ $package->effective_payout_account_limit }}
                                            </span>
                                            <div class="teacher-upgrade-compare__delta {{ $payoutDelta['class'] }}">
                                                {{ $payoutDelta['label'] }}
                                            </div>
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
                                        @php
                                            $couponDelta = $compareDeltaLabel(
                                                $currentPackage?->can_manage_coupons ? $currentPackage?->effective_coupon_limit : 0,
                                                $package->can_manage_coupons ? $package->effective_coupon_limit : 0,
                                                [
                                                    'current_unlimited' => (bool) $currentPackage?->can_manage_coupons && empty($currentPackage?->effective_coupon_limit),
                                                    'target_unlimited' => (bool) $package->can_manage_coupons && empty($package->effective_coupon_limit),
                                                    'suffix' => ' ' . __('teacher::dashboard.package_features.compare_unit_coupons'),
                                                ]
                                            );
                                        @endphp
                                        <td data-compare-cell="{{ (int) $package->id }}">
                                            <span class="teacher-upgrade-compare__pill is-neutral">
                                                @if ($package->can_manage_coupons)
                                                    {{ $package->effective_coupon_limit ?: __('teacher::coupons.labels.unlimited') }}
                                                @else
                                                    {{ __('teacher::dashboard.package_features.unavailable') }}
                                                @endif
                                            </span>
                                            <div class="teacher-upgrade-compare__delta {{ $couponDelta['class'] }}">
                                                {{ $couponDelta['label'] }}
                                            </div>
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
                                                $featureDelta = $compareDeltaLabel($currentPackage?->{$featureKey}, $enabled, ['mode' => 'feature']);
                                            @endphp
                                            <td data-compare-cell="{{ (int) $package->id }}" data-compare-feature-cell="{{ (int) $package->id }}:{{ $featureKey }}" class="{{ $isUpgradeGain ? 'is-static-upgrade-gain' : '' }}">
                                                <span class="teacher-upgrade-compare__pill {{ $enabled ? 'is-on' : 'is-off' }}">
                                                    {{ $enabled ? __('teacher::dashboard.package_features.available') : __('teacher::dashboard.package_features.unavailable') }}
                                                </span>
                                                <div class="teacher-upgrade-compare__delta {{ $featureDelta['class'] }}" data-compare-gain-label="{{ (int) $package->id }}:{{ $featureKey }}">
                                                    {{ $featureDelta['label'] }}
                                                </div>
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
                                        <span class="teacher-upgrade-mobile-card__badge teacher-upgrade-mobile-card__badge--selected {{ $isSelectedMobile ? 'is-visible' : '' }}" data-mobile-compare-selected-label="{{ $packageId }}">
                                            <span aria-hidden="true">&#10003;</span>
                                            {{ __('teacher::dashboard.package_features.compare_selected') }}
                                        </span>
                                        <span class="teacher-upgrade-mobile-card__chevron" aria-hidden="true"></span>
                                    </div>
                                </summary>

                                <div class="teacher-upgrade-mobile-card__list">
                                    @php
                                        $packagePreview = $calculatePackagePreview($package);
                                    @endphp
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('teacher::dashboard.package.term_label') }}</span>
                                        <div class="text-end">
                                            <strong>{{ $packageTermLabel($package) }}</strong>
                                            <small class="d-block teacher-upgrade-mobile-card__delta is-better">{{ $packageTermDescription($package, $packagePreview) }}</small>
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('teacher::dashboard.package_features.labels.course_limit') }}</span>
                                        @php
                                            $courseDelta = $compareDeltaLabel(
                                                $currentPackage?->effective_course_limit,
                                                $package->effective_course_limit,
                                                [
                                                    'current_unlimited' => empty($currentPackage?->effective_course_limit),
                                                    'target_unlimited' => empty($package->effective_course_limit),
                                                    'suffix' => ' ' . __('teacher::dashboard.package_features.compare_unit_courses'),
                                                ]
                                            );
                                        @endphp
                                        <div class="text-end">
                                            <strong>{{ $package->effective_course_limit ?: __('teacher::dashboard.courses.unlimited') }}</strong>
                                            <small class="d-block teacher-upgrade-mobile-card__delta {{ $courseDelta['class'] }}">{{ $courseDelta['label'] }}</small>
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('teacher::dashboard.package_features.labels.payout_account_limit') }}</span>
                                        @php
                                            $payoutDelta = $compareDeltaLabel(
                                                (float) ($currentPackage?->effective_payout_account_limit ?? 3),
                                                (float) $package->effective_payout_account_limit,
                                                ['suffix' => ' ' . __('teacher::dashboard.package_features.compare_unit_accounts')]
                                            );
                                        @endphp
                                        <div class="text-end">
                                            <strong>{{ $package->effective_payout_account_limit }}</strong>
                                            <small class="d-block teacher-upgrade-mobile-card__delta {{ $payoutDelta['class'] }}">{{ $payoutDelta['label'] }}</small>
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <div>
                                            <span>{{ __('teacher::dashboard.package_features.labels.coupon_limit') }}</span>
                                            <small>{{ $featureNotes['coupon_limit'] ?? '' }}</small>
                                        </div>
                                        @php
                                            $couponDelta = $compareDeltaLabel(
                                                $currentPackage?->can_manage_coupons ? $currentPackage?->effective_coupon_limit : 0,
                                                $package->can_manage_coupons ? $package->effective_coupon_limit : 0,
                                                [
                                                    'current_unlimited' => (bool) $currentPackage?->can_manage_coupons && empty($currentPackage?->effective_coupon_limit),
                                                    'target_unlimited' => (bool) $package->can_manage_coupons && empty($package->effective_coupon_limit),
                                                    'suffix' => ' ' . __('teacher::dashboard.package_features.compare_unit_coupons'),
                                                ]
                                            );
                                        @endphp
                                        <div class="text-end">
                                            <strong>
                                                @if ($package->can_manage_coupons)
                                                    {{ $package->effective_coupon_limit ?: __('teacher::coupons.labels.unlimited') }}
                                                @else
                                                    {{ __('teacher::dashboard.package_features.unavailable') }}
                                                @endif
                                            </strong>
                                            <small class="d-block teacher-upgrade-mobile-card__delta {{ $couponDelta['class'] }}">{{ $couponDelta['label'] }}</small>
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('teacher::dashboard.package_features.labels.commission_rate') }}</span>
                                        @php
                                            $commissionDelta = $compareDeltaLabel(
                                                (float) ($currentPackage?->commission_rate ?? 0),
                                                (float) $package->commission_rate,
                                                ['suffix' => '%']
                                            );
                                        @endphp
                                        <div class="text-end">
                                            <strong>{{ rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.') }}%</strong>
                                            <small class="d-block teacher-upgrade-mobile-card__delta {{ $commissionDelta['class'] }}">{{ $commissionDelta['label'] }}</small>
                                        </div>
                                    </div>
                                    @foreach ($featureRows as $featureKey => $featureLabel)
                                        @php
                                            $enabled = (bool) $package->{$featureKey};
                                            $isUpgradeGain = !$currentPackage?->{$featureKey} && $enabled;
                                            $featureDelta = $compareDeltaLabel($currentPackage?->{$featureKey}, $enabled, ['mode' => 'feature']);
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
                                                <small class="d-block teacher-upgrade-mobile-card__delta {{ $featureDelta['class'] }}">{{ $featureDelta['label'] }}</small>
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
                            @php
                                $selectedPackagePreview = $upgradePackages->firstWhere('id', $selectedPackageId);
                            @endphp
                            <div class="teacher-upgrade-summary__label">{{ __('teacher::dashboard.package.status_title') }}</div>
                            <div class="teacher-upgrade-summary__name" data-upgrade-summary-name>
                                {{ $upgradePackages->firstWhere('id', $selectedPackageId)?->name_locale ?: $upgradePackages->firstWhere('id', $selectedPackageId)?->name }}
                            </div>
                            <div class="teacher-upgrade-summary__price" data-upgrade-summary-price></div>
                            <p class="teacher-upgrade-summary__meta" data-upgrade-summary-meta></p>
                            <p class="teacher-upgrade-summary__term" data-upgrade-summary-term>
                                {{ $selectedPackagePreview ? $packageTermDescription($selectedPackagePreview, $calculatePackagePreview($selectedPackagePreview)) : '' }}
                            </p>

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

        .teacher-upgrade-current__term,
        .teacher-upgrade-summary__term,
        .teacher-upgrade-card__term {
            margin-top: 0.45rem;
            color: #8fc3ff;
            line-height: 1.55;
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

        .teacher-upgrade-compare__heading {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .teacher-upgrade-compare__selected-badge {
            display: none;
            align-items: center;
            gap: 0.4rem;
            width: fit-content;
            padding: 0.32rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: #e0f2fe;
            background: rgba(14, 165, 233, 0.18);
            box-shadow: inset 0 0 0 1px rgba(125, 211, 252, 0.28);
        }

        .teacher-upgrade-compare__selected-badge.is-visible {
            display: inline-flex;
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

        .teacher-upgrade-compare__delta {
            margin-top: 0.45rem;
            font-size: 0.74rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .teacher-upgrade-compare__delta.is-better {
            color: #67e8f9;
        }

        .teacher-upgrade-compare__delta.is-worse {
            color: #fda4af;
        }

        .teacher-upgrade-compare__delta.is-same {
            color: #94a3b8;
        }

        .teacher-upgrade-compare th.is-dynamic-selected {
            position: relative;
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.38), rgba(30, 64, 175, 0.22));
            color: #f8fbff;
            box-shadow:
                inset 0 1px 0 rgba(147, 197, 253, 0.22),
                inset 3px 0 0 rgba(56, 189, 248, 0.85),
                inset -3px 0 0 rgba(56, 189, 248, 0.85);
        }

        .teacher-upgrade-compare th.is-dynamic-selected::before,
        .teacher-upgrade-compare td.is-dynamic-selected::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: linear-gradient(180deg, #22d3ee, #38bdf8);
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
            gap: 0.35rem;
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

        .teacher-upgrade-mobile-card__badge--selected {
            display: none;
            background: rgba(14, 165, 233, 0.16);
            color: #e0f2fe;
            text-transform: none;
            letter-spacing: 0.02em;
        }

        .teacher-upgrade-mobile-card__badge--selected.is-visible {
            display: inline-flex;
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

        .teacher-upgrade-mobile-card__delta {
            margin-top: 0.28rem;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .teacher-upgrade-mobile-card__delta.is-better {
            color: #67e8f9;
        }

        .teacher-upgrade-mobile-card__delta.is-worse {
            color: #fda4af;
        }

        .teacher-upgrade-mobile-card__delta.is-same {
            color: #94a3b8;
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

        html[data-theme="light"] .teacher-upgrade-current__term,
        html[data-theme="light"] .teacher-upgrade-summary__term,
        html[data-theme="light"] .teacher-upgrade-card__term {
            color: #1d4ed8;
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

        html[data-theme="light"] .teacher-upgrade-compare__delta.is-better,
        html[data-theme="light"] .teacher-upgrade-mobile-card__delta.is-better {
            color: #0284c7;
        }

        html[data-theme="light"] .teacher-upgrade-compare__delta.is-worse,
        html[data-theme="light"] .teacher-upgrade-mobile-card__delta.is-worse {
            color: #e11d48;
        }

        html[data-theme="light"] .teacher-upgrade-compare__delta.is-same,
        html[data-theme="light"] .teacher-upgrade-mobile-card__delta.is-same {
            color: #64748b;
        }

        html[data-theme="light"] .teacher-upgrade-compare__selected-badge {
            color: #0f172a;
            background: rgba(37, 99, 235, 0.12);
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.18);
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

        html[data-theme="light"] .teacher-upgrade-mobile-card__badge--selected {
            color: #0f172a;
            background: rgba(37, 99, 235, 0.12);
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
            const summaryTerm = document.querySelector('[data-upgrade-summary-term]');
            const warningBox = document.querySelector('[data-upgrade-warning]');
            const compareColumns = [...document.querySelectorAll('[data-compare-col]')];
            const compareCells = [...document.querySelectorAll('[data-compare-cell]')];
            const compareFeatureCells = [...document.querySelectorAll('[data-compare-feature-cell]')];
            const compareSelectedLabels = [...document.querySelectorAll('[data-compare-selected-label]')];
            const mobileCompareCards = [...document.querySelectorAll('[data-mobile-compare-card]')];
            const mobileCompareSelectedLabels = [...document.querySelectorAll('[data-mobile-compare-selected-label]')];
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
                    if (summaryTerm) {
                        summaryTerm.textContent = packageMap[selectedId].term_description || '';
                    }
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

                compareSelectedLabels.forEach((label) => {
                    label.classList.toggle('is-visible', String(label.dataset.compareSelectedLabel) === String(selectedId));
                });

                compareFeatureCells.forEach((cell) => {
                    const [packageId] = String(cell.dataset.compareFeatureCell || '').split(':');
                    cell.classList.toggle('is-dynamic-gain', String(packageId) === String(selectedId) && cell.classList.contains('is-static-upgrade-gain'));
                });

                mobileCompareCards.forEach((card) => {
                    const isSelected = String(card.dataset.mobileCompareCard) === String(selectedId);
                    card.classList.toggle('is-selected', isSelected);
                    card.open = isSelected;
                });

                mobileCompareSelectedLabels.forEach((label) => {
                    label.classList.toggle('is-visible', String(label.dataset.mobileCompareSelectedLabel) === String(selectedId));
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
