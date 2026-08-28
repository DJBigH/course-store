@extends('layouts.teacher')

@section('content')
    @php
        $selectedPackageId = (int) request('package_id', old('package_id', $selectedPackageId ?? $upgradePackages->first()?->id));
        $selectedPaymentMethod = old('payment_method', 'bank_transfer');
        $currentPackageExpiresAt = $teacher->package_expires_at;
        $currentPackageDaysLeft = $currentPackageExpiresAt ? max(now()->startOfDay()->diffInDays($currentPackageExpiresAt->copy()->startOfDay(), false), 0) : null;
        $isRecurringPackage = fn ($package) => in_array($package?->billing_cycle, ['monthly', 'yearly'], true);
        $formatDate = fn ($date) => $date?->format('d/m/Y');
        $packageTermLabel = function ($package) {
            return match ($package?->billing_cycle) {
                'monthly' => __('packages::teacher.upgrade.term_monthly'),
                'yearly' => __('packages::teacher.upgrade.term_yearly'),
                default => __('packages::teacher.upgrade.term_permanent'),
            };
        };
        $calculatePackagePreview = function ($targetPackage) use ($currentPackage, $currentPackageExpiresAt, $isRecurringPackage) {
            $now = now();
            if (!$currentPackage || !$isRecurringPackage($currentPackage) || !$currentPackageExpiresAt || $currentPackageExpiresAt->isPast()) {
                return [
                    'start_at' => $now,
                    'expires_at' => match ($targetPackage?->billing_cycle) {
                        'monthly' => $now->copy()->addMonth(),
                        'yearly' => $now->copy()->addYear(),
                        default => null,
                    },
                    'is_queued' => false,
                ];
            }
            if ((int) ($targetPackage->sort_order ?? 0) > (int) ($currentPackage->sort_order ?? 0)) {
                return [
                    'start_at' => $now,
                    'expires_at' => match ($targetPackage?->billing_cycle) {
                        'monthly' => $now->copy()->addMonth(),
                        'yearly' => $now->copy()->addYear(),
                        default => null,
                    },
                    'is_queued' => false,
                ];
            }
            $startAt = $currentPackageExpiresAt->copy();
            return [
                'start_at' => $startAt,
                'expires_at' => match ($targetPackage?->billing_cycle) {
                    'monthly' => $startAt->copy()->addMonth(),
                    'yearly' => $startAt->copy()->addYear(),
                    default => null,
                },
                'is_queued' => true,
            ];
        };
        $packageTermDescription = function ($package, ?array $preview = null) use ($isRecurringPackage, $formatDate, $packageTermLabel) {
            if (!$isRecurringPackage($package)) {
                return __('packages::teacher.upgrade.term_description_permanent');
            }

            $startAt = $preview['start_at'] ?? null;
            $expiresAt = $preview['expires_at'] ?? null;
            $isQueued = (bool) ($preview['is_queued'] ?? false);

            if (!$startAt || !$expiresAt) {
                return $packageTermLabel($package);
            }

            if ($isQueued) {
                return __('packages::teacher.upgrade.term_description_queued', [
                    'start' => $formatDate($startAt),
                    'expires' => $formatDate($expiresAt),
                ]);
            }

            return __('packages::teacher.upgrade.term_description_active', [
                'expires' => $formatDate($expiresAt),
            ]);
        };
        $currentPackageMap = [
            'id' => (int) ($currentPackage?->id ?? 0),
            'name' => $currentPackage?->name_locale ?: $currentPackage?->name ?: 'N/A',
            'billing_cycle' => $currentPackage?->billing_cycle,
            'sort_order' => (int) ($currentPackage?->sort_order ?? 0),
            'course_limit' => (int) ($currentPackage?->course_limit ?? 0),
            'commission_rate' => (float) ($currentPackage?->commission_rate ?? 0),
            'coupon_limit' => (int) ($currentPackage?->coupon_limit ?? 0),
            'payout_account_limit' => (int) ($currentPackage?->payout_account_limit ?? 0),
            'expires_at' => $currentPackageExpiresAt?->toIso8601String(),
            'days_left' => $currentPackageDaysLeft,
        ];
        $numericKeys = ['course_limit', 'payout_account_limit', 'commission_rate', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day'];
        foreach ($features as $f) {
            if (in_array($f->key, $numericKeys)) continue;
            $currentPackageMap[$f->key] = (bool) ($currentPackage?->{$f->key} ?? false);
        }

        $packageMap = $upgradePackages
            ->mapWithKeys(function ($package) use ($features, $packageTermLabel, $packageTermDescription, $calculatePackagePreview, $formatDate, $numericKeys) {
                $preview = $calculatePackagePreview($package);
                $data = [
                    'id' => (int) $package->id,
                    'term_label' => $packageTermLabel($package),
                    'name' => $package->name_locale ?: $package->name,
                    'price' => (float) $package->price,
                    'billing_cycle' => $package->billing_cycle,
                    'sort_order' => (int) $package->sort_order,
                    'course_limit' => $package->course_limit,
                    'commission_rate' => (float) $package->commission_rate,
                    'coupon_limit' => $package->coupon_limit,
                    'payout_account_limit' => $package->payout_account_limit,
                    'ai_quiz_limit' => $package->ai_quiz_limit,
                    'max_payout_per_day' => $package->max_payout_per_day,
                    'term_description' => $packageTermDescription($package, $preview),
                    'expires_at_formatted' => $formatDate($preview['expires_at']),
                    'category' => $package->category_locale,
                    'meta' => __('packages::teacher.common.current_meta', [
                        'commission' => rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.'),
                        'limit' => $package->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited'),
                    ]),
                ];
                foreach ($features as $f) {
                    if (in_array($f->key, $numericKeys)) continue;
                    $data[$f->key] = (bool) $package->{$f->key};
                }
                return [$package->id => $data];
            })
            ->all();
        $groupedFeatures = $features->groupBy('group');
        $featureMetadata = $features->keyBy('key');
        $featureRows = $features->pluck('name_locale', 'key')->all();
        $featureNotes = $features->pluck('description_locale', 'key')->all();
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
            $sameLabel = $options['same'] ?? __('packages::teacher.features.compare_same');
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
                    ? ['label' => __('packages::teacher.features.compare_gain'), 'class' => 'is-better']
                    : ['label' => __('packages::teacher.features.compare_loss'), 'class' => 'is-worse'];
            }

            if ($targetUnlimited && !$currentUnlimited) {
                return ['label' => __('packages::teacher.features.compare_unlimited'), 'class' => 'is-better'];
            }

            if ($currentUnlimited && !$targetUnlimited) {
                return ['label' => __('packages::teacher.features.compare_limited'), 'class' => 'is-worse'];
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
        $bankEnabled = (int) setting('payment_bank_enabled', '1') === 1;
        $vnpayEnabled = (int) setting('payment_vnpay_enabled', '1') === 1;
        $momoEnabled = (int) setting('payment_momo_enabled', '1') === 1;

        $bankTransferBankName = setting('bank_transfer_bank_name', 'Techcombank');
        $bankTransferBankBin = setting('bank_transfer_bank_bin', '970407');
        $bankTransferAccountNumber = setting('bank_transfer_account_number', '');
        $bankTransferAccountName = setting('bank_transfer_account_name', '');
        $bankTransferNotePrefix = trim((string) setting('bank_transfer_note_prefix', 'CK'));
        $bankTransferNote = trim($bankTransferNotePrefix . ' T' . auth()->user()->id);
        $allPaymentsDisabled = !$bankEnabled && !$vnpayEnabled && !$momoEnabled;

        $packagesByCategory = $upgradePackages->groupBy(function($pkg) {
            return $pkg->category_locale ?: __('packages::teacher.common.category_other');
        });
        $categoriesList = $packagesByCategory->keys();
        $activeCategoryId = (string) request('category', $categoriesList->first());
    @endphp

    <div class="teacher-page-shell">
        <div class="teacher-panel teacher-upgrade-shell">
            <div class="teacher-upgrade-hero">
                <div>
                    <span class="teacher-upgrade-kicker">{{ __('packages::teacher.upgrade.upgrade_title') }}</span>
                    <h3 class="teacher-upgrade-title">{{ __('packages::teacher.upgrade.upgrade_title') }}</h3>
                    <p class="teacher-upgrade-desc mb-0">{{ __('packages::teacher.upgrade.upgrade_description') }}</p>
                </div>
                </a>
            </div>
            
            @if ($allPaymentsDisabled)
                <div class="alert alert-warning mb-4 rounded-4 shadow-sm border-0" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2) !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation fs-4 text-warning"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-warning">{{ __('packages::teacher.common.all_payments_maintenance_title') }}</div>
                            <div class="small opacity-75 text-warning">{{ __('packages::teacher.common.all_payments_maintenance_desc') }}</div>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('msg_success'))
                <div class="alert alert-success">{{ session('msg_success') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ __('teacher::teacher/course/common.warnings.validation_summary') }}</div>
            @endif

            <div class="teacher-upgrade-current">
                <div class="teacher-upgrade-current__label">{{ __('packages::teacher.common.current_title') }}</div>
                <div class="teacher-upgrade-current__name">{{ $currentPackage?->name_locale ?: $currentPackage?->name }}</div>
                <div class="teacher-upgrade-current__meta">
                    {{ __('packages::teacher.common.current_meta', [
                        'commission' => rtrim(rtrim(number_format((float) ($currentPackage?->commission_rate ?? 0), 2, '.', ''), '0'), '.'),
                        'limit' => $currentPackage?->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited'),
                    ]) }}
                </div>
                @if($currentPackage?->category_locale)
                    <div class="teacher-upgrade-current__category mt-1">
                        <span class="badge bg-soft-info text-info border-0 px-2 py-1" style="font-size: 0.7rem; background: rgba(56, 189, 248, 0.1);">
                            <i class="fa-solid fa-layer-group me-1"></i>{{ $currentPackage->category_locale }}
                        </span>
                    </div>
                @endif
                <div class="teacher-upgrade-current__term">
                    {{ __('packages::teacher.common.term_label') }}: {{ $packageTermLabel($currentPackage) }}
                </div>
                @if ($isRecurringPackage($currentPackage) && $currentPackageExpiresAt)
                    <div class="teacher-upgrade-current__time">
                        {{ __('packages::teacher.expires_at', [
                            'date' => $currentPackageExpiresAt->format('d/m/Y'),
                            'days' => $currentPackageDaysLeft
                        ]) }}
                    </div>
                @endif
                @if ($teacher->application?->granted_by)
                    <div class="mt-2">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.3rem 0.75rem;border-radius:999px;font-size:0.77rem;font-weight:700;background:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.3);">
                            <i class="fa-solid fa-gift" style="font-size:0.72rem;"></i>
                            {{ __('packages::teacher.common.granted_by_admin') }}
                        </span>
                    </div>
                @endif
                @if ($recommendedPackageId)
                    <div class="teacher-upgrade-current__recommend mt-3">
                        {{ __('packages::teacher.features.recommend_intro') }}
                        <strong>{{ $upgradePackages->firstWhere('id', $recommendedPackageId)?->name_locale ?: $upgradePackages->firstWhere('id', $recommendedPackageId)?->name }}</strong>
                        @if ($missingFeatureLabels->isNotEmpty())
                            <div class="small mt-1">
                                {{ __('packages::teacher.features.recommend_missing') }}:
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
                            <h4>{{ __('packages::teacher.common.target_title') }}</h4>
                            <p class="mb-0">{{ __('packages::teacher.upgrade.upgrade_description') }}</p>
                        </div>
                    </div>

                        </div>
                    </div>

                    @if($categoriesList->count() > 1)
                        <div class="teacher-upgrade-categories">
                            <div class="nav nav-pills teacher-upgrade-tabs" role="tablist">
                                @foreach($categoriesList as $catName)
                                    <button class="nav-link {{ $activeCategoryId === $catName ? 'active' : '' }}" 
                                        id="cat-tab-{{ Str::slug($catName) }}" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#cat-content-{{ Str::slug($catName) }}" 
                                        type="button" role="tab">
                                        {{ $catName }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="tab-content teacher-upgrade-tab-content">
                        @foreach($packagesByCategory as $catName => $catPackages)
                            <div class="tab-pane fade {{ $activeCategoryId === $catName ? 'show active' : '' }}" 
                                id="cat-content-{{ Str::slug($catName) }}" 
                                role="tabpanel">
                                <div class="row g-4">
                                    @foreach ($catPackages as $package)
                                        @php
                                            $packageId = (int) $package->id;
                                            $price = (float) $package->price;
                                            $isSelected = $selectedPackageId === $packageId;
                                            $isFeatured = (bool) $package->is_featured;
                                            $isRecommended = $recommendedPackageId !== null && $recommendedPackageId === $packageId;
                                            $packagePreview = $calculatePackagePreview($package);
                                        @endphp
                                        <div class="col-xl-4 col-md-6">
                                            <label class="teacher-upgrade-card {{ $isSelected ? 'is-selected' : '' }} {{ $isFeatured ? 'is-featured' : '' }} {{ $isRecommended ? 'is-recommended' : '' }}" 
                                                data-upgrade-card 
                                                @if($package->badge_tone) style="--package-tone: {{ $package->badge_tone }};" @endif>
                                                <input type="radio" name="package_id" value="{{ $packageId }}" data-package-price="{{ $price }}" @checked($isSelected)>

                                                @if ($isFeatured)
                                                    <span class="teacher-upgrade-card__ribbon">
                                                        {{ __('teacher::landing.packages.most_popular') }}
                                                    </span>
                                                @endif
                                                @if ($isRecommended)
                                                    <span class="teacher-upgrade-card__recommend">
                                                        {{ __('packages::teacher.features.recommended_badge') }}
                                                    </span>
                                                @endif

                                                <div class="teacher-upgrade-card__top">
                                                    <div class="d-flex flex-column gap-1">
                                                        @if($package->category_locale)
                                                            <span class="teacher-upgrade-card__category">{{ $package->category_locale }}</span>
                                                        @endif
                                                        <span class="teacher-upgrade-card__tag">{{ strtoupper((string) $package->code) }}</span>
                                                    </div>
                                                    @if (($package->badge_text_locale ?: '') !== '')
                                                        <span class="teacher-upgrade-card__badge">{{ $package->badge_text_locale }}</span>
                                                    @endif
                                                </div>

                                                <div class="teacher-upgrade-card__body">
                                                    <h5>{{ $package->name_locale ?: $package->name }}</h5>
                                                    <div class="teacher-upgrade-card__price">
                                                        {{ $price > 0 ? moneyLocale($price) : moneyLocale(0) }}
                                                    </div>
                                                    @if (($package->tagline_locale ?: '') !== '')
                                                        <p class="teacher-upgrade-card__tagline">{{ $package->tagline_locale }}</p>
                                                    @endif
                                                    <p class="teacher-upgrade-card__desc">{{ $package->description_locale ?: $package->description }}</p>

                                                    <ul class="teacher-upgrade-card__features">
                                                        <li>
                                                            {{ __('packages::teacher.common.current_meta', [
                                                                'commission' => rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.'),
                                                                'limit' => $package->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited'),
                                                            ]) }}
                                                        </li>
                                                        <li>
                                                            {{ __('packages::teacher.common.term_label') }}: {{ $packageTermLabel($package) }}
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
                            </div>
                        @endforeach
                    </div>

                    @error('package_id')
                        <div class="text-danger small mt-3">{{ $message }}</div>
                    @enderror

                    <div class="teacher-upgrade-warning d-none" data-upgrade-warning></div>
                </div>

                <div class="teacher-upgrade-section mt-4">
                    <div class="teacher-upgrade-section__head" data-compare-anchor>
                        <div>
                            <h4>{{ __('packages::teacher.features.compare_title') }}</h4>
                            <p class="mb-0">{{ __('packages::teacher.features.compare_description') }}</p>
                        </div>
                    </div>

                    <div class="teacher-upgrade-compare-shell is-collapsed" data-compare-shell>
                        <div class="teacher-upgrade-compare-shell__inner" data-compare-content>
                    <div class="table-responsive">
                        <table class="table teacher-upgrade-compare mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('packages::teacher.features.compare_feature') }}</th>
                                    <th>
                                        {{ __('packages::teacher.features.compare_current') }}
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
                                                    {{ __('packages::teacher.features.compare_selected') }}
                                                </span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary small text-uppercase mb-2" style="letter-spacing: 0.5px;">Cấu hình cơ bản</div>
                                        <div>{{ __('packages::teacher.common.term_label') }}</div>
                                        <div class="small text-muted mt-1">{{ __('packages::teacher.upgrade.term_compare_note') }}</div>
                                    </td>
                                    <td>
                                        <span class="teacher-upgrade-compare__pill is-neutral">
                                            {{ $packageTermLabel($currentPackage) }}
                                        </span>
                                        @if ($isRecurringPackage($currentPackage) && $currentPackageExpiresAt)
                                            <div class="teacher-upgrade-compare__delta is-same">
                                                {{ __('packages::teacher.common.current_expiry_short', ['date' => $currentPackageExpiresAt->format('d/m/Y')]) }}
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

                                @foreach($groupedFeatures as $group => $groupItems)
                                    @php
                                        $groupTitle = match($group) {
                                            'course_management' => 'Quản lý khóa học',
                                            'advanced_tools' => 'Công cụ AI & Nâng cao',
                                            'marketing' => 'Marketing & Bán hàng',
                                            'student_care' => 'Chăm sóc học viên',
                                            'finance' => 'Chi phí & Hoa hồng',
                                            'branding' => 'Thương hiệu & Giao diện',
                                            'system' => 'Hệ thống & Bảo mật',
                                            default => 'Tính năng khác'
                                        };
                                    @endphp
                                    <tr class="table-group-divider-row">
                                        <td colspan="{{ $upgradePackages->count() + 2 }}">
                                            <div class="fw-bold text-primary small text-uppercase mt-3 mb-1" style="letter-spacing: 0.5px;">{{ $groupTitle }}</div>
                                        </td>
                                    </tr>
                                    @foreach($groupItems as $featureItem)
                                        @php $featureKey = $featureItem->key; @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($featureItem->icon)
                                                        <i class="{{ $featureItem->icon }} text-muted small"></i>
                                                    @endif
                                                    <span>{{ $featureItem->name_locale }}</span>
                                                    @if($featureItem->is_enabled == 2)
                                                        <span class="badge text-bg-warning ms-1" style="font-size: 10px; padding: 2px 5px;">{{ __('teacher::teacher/dashboard.common.maintenance_badge') }}</span>
                                                    @endif
                                                    @if($featureItem->description_locale)
                                                        <span class="feature-info-trigger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $featureItem->description_locale }}">
                                                            <i class="fa-solid fa-circle-question opacity-50"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            {{-- Cột Gói Hiện Tại --}}
                                            <td>
                                                @if(in_array($featureKey, ['course_limit', 'payout_account_limit', 'commission_rate', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day']))
                                                     <span class="teacher-upgrade-compare__pill is-neutral">
                                                        @if($featureKey === 'course_limit') {{ $currentPackage?->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited') }}
                                                        @elseif($featureKey === 'payout_account_limit') {{ $currentPackage?->effective_payout_account_limit ?? 3 }}
                                                        @elseif($featureKey === 'commission_rate') {{ rtrim(rtrim(number_format((float) ($currentPackage?->commission_rate ?? 0), 2, '.', ''), '0'), '.') }}%
                                                        @elseif($featureKey === 'coupon_limit') {{ $currentPackage?->can_manage_coupons ? ($currentPackage?->effective_coupon_limit ?: __('courses::teacher/messages.courses.unlimited')) : __('packages::teacher.features.unavailable') }}
                                                        @elseif($featureKey === 'ai_quiz_limit') {{ $currentPackage?->can_use_ai_quiz ? ($currentPackage?->effective_ai_quiz_limit ?: __('courses::teacher/messages.courses.unlimited')) : __('packages::teacher.features.unavailable') }}
                                                        @elseif($featureKey === 'max_payout_per_day') {{ $currentPackage?->can_request_payouts ? ($currentPackage?->max_payout_per_day ? moneyLocale((float) $currentPackage?->max_payout_per_day) : __('courses::teacher/messages.courses.unlimited')) : __('packages::teacher.features.unavailable') }}
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="teacher-upgrade-compare__pill {{ $currentPackage?->{$featureKey} ? 'is-on' : 'is-off' }}">
                                                        {{ $currentPackage?->{$featureKey} ? __('packages::teacher.features.available') : __('packages::teacher.features.unavailable') }}
                                                    </span>
                                                @endif
                                            </td>
                                            {{-- Các cột Gói Nâng Cấp --}}
                                            @foreach ($upgradePackages as $package)
                                                @php
                                                    $cellValue = $package->{$featureKey};
                                                    $enabled = (bool) $cellValue;
                                                    
                                                    if(in_array($featureKey, ['course_limit', 'payout_account_limit', 'commission_rate', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day'])) {
                                                        $currentVal = match($featureKey) {
                                                            'course_limit' => $currentPackage?->effective_course_limit,
                                                            'payout_account_limit' => $currentPackage?->effective_payout_account_limit ?? 3,
                                                            'commission_rate' => (float) ($currentPackage?->commission_rate ?? 0),
                                                            'coupon_limit' => $currentPackage?->can_manage_coupons ? $currentPackage?->effective_coupon_limit : 0,
                                                            'ai_quiz_limit' => $currentPackage?->can_use_ai_quiz ? $currentPackage?->effective_ai_quiz_limit : 0,
                                                            'max_payout_per_day' => $currentPackage?->can_request_payouts ? (float) $currentPackage?->max_payout_per_day : 0,
                                                        };
                                                        $targetVal = match($featureKey) {
                                                            'course_limit' => $package->effective_course_limit,
                                                            'payout_account_limit' => $package->effective_payout_account_limit,
                                                            'commission_rate' => (float) $package->commission_rate,
                                                            'coupon_limit' => $package->can_manage_coupons ? $package->effective_coupon_limit : 0,
                                                            'ai_quiz_limit' => $package->can_use_ai_quiz ? $package->effective_ai_quiz_limit : 0,
                                                            'max_payout_per_day' => $package->can_request_payouts ? (float) $package->max_payout_per_day : 0,
                                                        };
                                                        $delta = $compareDeltaLabel($currentVal, $targetVal, [
                                                            'mode' => 'number',
                                                            'current_unlimited' => in_array($featureKey, ['course_limit', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day']) && empty($currentVal),
                                                            'target_unlimited' => in_array($featureKey, ['course_limit', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day']) && empty($targetVal),
                                                            'suffix' => match($featureKey) {
                                                                'course_limit' => ' ' . __('packages::teacher.features.compare_unit_courses'),
                                                                'payout_account_limit' => ' ' . __('packages::teacher.features.compare_unit_accounts'),
                                                                'commission_rate' => '%',
                                                                'coupon_limit' => ' ' . __('packages::teacher.features.compare_unit_coupons'),
                                                                'ai_quiz_limit' => ' ' . 'Quiz',
                                                                'max_payout_per_day' => ' VND',
                                                            }
                                                        ]);
                                                    } else {
                                                        $delta = $compareDeltaLabel($currentPackage?->{$featureKey}, $enabled, ['mode' => 'feature']);
                                                    }
                                                @endphp
                                                <td data-compare-cell="{{ (int) $package->id }}" class="{{ (!$currentPackage?->{$featureKey} && $enabled) ? 'is-static-upgrade-gain' : '' }}">
                                                    <span class="teacher-upgrade-compare__pill {{ in_array($featureKey, ['course_limit', 'payout_account_limit', 'commission_rate', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day']) ? 'is-neutral' : ($enabled ? 'is-on' : 'is-off') }}">
                                                        @if($featureKey === 'course_limit') {{ $package->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited') }}
                                                        @elseif($featureKey === 'payout_account_limit') {{ $package->effective_payout_account_limit }}
                                                        @elseif($featureKey === 'commission_rate') {{ rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.') }}%
                                                        @elseif($featureKey === 'coupon_limit') {{ $package->can_manage_coupons ? ($package->effective_coupon_limit ?: __('courses::teacher/messages.courses.unlimited')) : __('packages::teacher.features.unavailable') }}
                                                        @elseif($featureKey === 'ai_quiz_limit') {{ $package->can_use_ai_quiz ? ($package->effective_ai_quiz_limit ?: __('courses::teacher/messages.courses.unlimited')) : __('packages::teacher.features.unavailable') }}
                                                        @elseif($featureKey === 'max_payout_per_day') {{ $package->can_request_payouts ? ($package->max_payout_per_day ? moneyLocale((float) $package->max_payout_per_day) : __('courses::teacher/messages.courses.unlimited')) : __('packages::teacher.features.unavailable') }}
                                                        @else {{ $enabled ? __('packages::teacher.features.available') : __('packages::teacher.features.unavailable') }}
                                                        @endif
                                                    </span>
                                                    <div class="teacher-upgrade-compare__delta {{ $delta['class'] }}">
                                                        {{ $delta['label'] }}
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
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
                                            {{ __('packages::teacher.common.current_meta', [
                                                'commission' => rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.'),
                                                'limit' => $package->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited'),
                                            ]) }}
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__head-meta">
                                        @if ($recommendedPackageId !== null && $recommendedPackageId === $packageId)
                                            <span class="teacher-upgrade-mobile-card__badge">{{ __('packages::teacher.features.recommended_badge') }}</span>
                                        @endif
                                        <span class="teacher-upgrade-mobile-card__badge teacher-upgrade-mobile-card__badge--selected {{ $isSelectedMobile ? 'is-visible' : '' }}" data-mobile-compare-selected-label="{{ $packageId }}">
                                            <span aria-hidden="true">&#10003;</span>
                                            {{ __('packages::teacher.features.compare_selected') }}
                                        </span>
                                        <span class="teacher-upgrade-mobile-card__chevron" aria-hidden="true"></span>
                                    </div>
                                </summary>

                                <div class="teacher-upgrade-mobile-card__list">
                                    @php
                                        $packagePreview = $calculatePackagePreview($package);
                                    @endphp
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('packages::teacher.common.term_label') }}</span>
                                        <div class="text-end">
                                            <strong>{{ $packageTermLabel($package) }}</strong>
                                            <small class="d-block teacher-upgrade-mobile-card__delta is-better">{{ $packageTermDescription($package, $packagePreview) }}</small>
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('packages::teacher.features.labels.course_limit') }}</span>
                                        @php
                                            $courseDelta = $compareDeltaLabel(
                                                $currentPackage?->effective_course_limit,
                                                $package->effective_course_limit,
                                                [
                                                    'current_unlimited' => empty($currentPackage?->effective_course_limit),
                                                    'target_unlimited' => empty($package->effective_course_limit),
                                                    'suffix' => ' ' . __('packages::teacher.features.compare_unit_courses'),
                                                ]
                                            );
                                        @endphp
                                        <div class="text-end">
                                            <strong>{{ $package->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited') }}</strong>
                                            <small class="d-block teacher-upgrade-mobile-card__delta {{ $courseDelta['class'] }}">{{ $courseDelta['label'] }}</small>
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('packages::teacher.features.labels.payout_account_limit') }}</span>
                                        @php
                                            $payoutDelta = $compareDeltaLabel(
                                                (float) ($currentPackage?->effective_payout_account_limit ?? 3),
                                                (float) $package->effective_payout_account_limit,
                                                ['suffix' => ' ' . __('packages::teacher.features.compare_unit_accounts')]
                                            );
                                        @endphp
                                        <div class="text-end">
                                            <strong>{{ $package->effective_payout_account_limit }}</strong>
                                            <small class="d-block teacher-upgrade-mobile-card__delta {{ $payoutDelta['class'] }}">{{ $payoutDelta['label'] }}</small>
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <div>
                                            <span>{{ __('packages::teacher.features.labels.coupon_limit') }}</span>
                                            <small>{{ $featureNotes['coupon_limit'] ?? '' }}</small>
                                        </div>
                                        @php
                                            $couponDelta = $compareDeltaLabel(
                                                $currentPackage?->can_manage_coupons ? $currentPackage?->effective_coupon_limit : 0,
                                                $package->can_manage_coupons ? $package->effective_coupon_limit : 0,
                                                [
                                                    'current_unlimited' => (bool) $currentPackage?->can_manage_coupons && empty($currentPackage?->effective_coupon_limit),
                                                    'target_unlimited' => (bool) $package->can_manage_coupons && empty($package->effective_coupon_limit),
                                                    'suffix' => ' ' . __('packages::teacher.features.compare_unit_coupons'),
                                                ]
                                            );
                                        @endphp
                                        <div class="text-end">
                                            <strong>
                                                @if ($package->can_manage_coupons)
                                                    {{ $package->effective_coupon_limit ?: __('courses::teacher/messages.courses.unlimited') }}
                                                @else
                                                    {{ __('packages::teacher.features.unavailable') }}
                                                @endif
                                            </strong>
                                            <small class="d-block teacher-upgrade-mobile-card__delta {{ $couponDelta['class'] }}">{{ $couponDelta['label'] }}</small>
                                        </div>
                                    </div>
                                    <div class="teacher-upgrade-mobile-card__item">
                                        <span>{{ __('packages::teacher.features.labels.commission_rate') }}</span>
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
                                    @foreach($groupedFeatures as $group => $groupItems)
                                        @foreach($groupItems as $featureItem)
                                            @php
                                                $featureKey = $featureItem->key;
                                                $enabled = (bool) $package->{$featureKey};
                                                $isUpgradeGain = !$currentPackage?->{$featureKey} && $enabled;
                                                
                                                if(in_array($featureKey, ['course_limit', 'payout_account_limit', 'commission_rate', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day'])) {
                                                    $currentVal = match($featureKey) {
                                                        'course_limit' => $currentPackage?->effective_course_limit,
                                                        'payout_account_limit' => $currentPackage?->effective_payout_account_limit ?? 3,
                                                        'commission_rate' => (float) ($currentPackage?->commission_rate ?? 0),
                                                        'coupon_limit' => $currentPackage?->can_manage_coupons ? $currentPackage?->effective_coupon_limit : 0,
                                                        'ai_quiz_limit' => $currentPackage?->can_use_ai_quiz ? $currentPackage?->effective_ai_quiz_limit : 0,
                                                        'max_payout_per_day' => $currentPackage?->can_request_payouts ? (float) $currentPackage?->max_payout_per_day : 0,
                                                    };
                                                    $targetVal = match($featureKey) {
                                                        'course_limit' => $package->effective_course_limit,
                                                        'payout_account_limit' => $package->effective_payout_account_limit,
                                                        'commission_rate' => (float) $package->commission_rate,
                                                        'coupon_limit' => $package->can_manage_coupons ? $package->effective_coupon_limit : 0,
                                                        'ai_quiz_limit' => $package->can_use_ai_quiz ? $package->effective_ai_quiz_limit : 0,
                                                        'max_payout_per_day' => $package->can_request_payouts ? (float) $package->max_payout_per_day : 0,
                                                    };
                                                    $delta = $compareDeltaLabel($currentVal, $targetVal, [
                                                        'mode' => 'number',
                                                        'current_unlimited' => in_array($featureKey, ['course_limit', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day']) && empty($currentVal),
                                                        'target_unlimited' => in_array($featureKey, ['course_limit', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day']) && empty($targetVal),
                                                        'suffix' => match($featureKey) {
                                                            'course_limit' => ' ' . __('packages::teacher.features.compare_unit_courses'),
                                                            'payout_account_limit' => ' ' . __('packages::teacher.features.compare_unit_accounts'),
                                                            'commission_rate' => '%',
                                                            'coupon_limit' => ' ' . __('packages::teacher.features.compare_unit_coupons'),
                                                            'ai_quiz_limit' => ' ' . 'Quiz',
                                                            'max_payout_per_day' => ' VND',
                                                        }
                                                    ]);
                                                } else {
                                                    $delta = $compareDeltaLabel($currentPackage?->{$featureKey}, $enabled, ['mode' => 'feature']);
                                                }
                                            @endphp
                                            <div class="teacher-upgrade-mobile-card__item">
                                                <div>
                                                    <span class="d-flex align-items-center gap-2">
                                                        @if($featureItem->icon)
                                                            <i class="{{ $featureItem->icon }} text-muted opacity-50 small"></i>
                                                        @endif
                                                        {{ $featureItem->name_locale }}
                                                    </span>
                                                    @if($featureItem->description_locale)
                                                        <small class="d-block opacity-75">{{ $featureItem->description_locale }}</small>
                                                    @endif
                                                </div>
                                                <div class="text-end">
                                                    <strong class="{{ in_array($featureKey, ['course_limit', 'payout_account_limit', 'commission_rate', 'coupon_limit', 'ai_quiz_limit', 'max_payout_per_day']) ? '' : ($enabled ? 'text-success' : 'text-muted') }}">
                                                        @if($featureKey === 'course_limit') {{ $package->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited') }}
                                                        @elseif($featureKey === 'payout_account_limit') {{ $package->effective_payout_account_limit }}
                                                        @elseif($featureKey === 'commission_rate') {{ rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.') }}%
                                                        @elseif($featureKey === 'coupon_limit') {{ $package->can_manage_coupons ? ($package->effective_coupon_limit ?: __('courses::teacher/messages.courses.unlimited')) : __('packages::teacher.features.unavailable') }}
                                                        @elseif($featureKey === 'ai_quiz_limit') {{ $package->can_use_ai_quiz ? ($package->effective_ai_quiz_limit ?: __('courses::teacher/messages.courses.unlimited')) : __('packages::teacher.features.unavailable') }}
                                                        @elseif($featureKey === 'max_payout_per_day') {{ $package->can_request_payouts ? ($package->max_payout_per_day ? moneyLocale((float) $package->max_payout_per_day) : __('courses::teacher/messages.courses.unlimited')) : __('packages::teacher.features.unavailable') }}
                                                        @else {{ $enabled ? __('packages::teacher.features.available') : __('packages::teacher.features.unavailable') }}
                                                        @endif
                                                    </strong>
                                                    <small class="d-block teacher-upgrade-mobile-card__delta {{ $delta['class'] }}">{{ $delta['label'] }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
                        </div>
                        <div class="teacher-upgrade-compare-shell__fade" data-compare-fade aria-hidden="true"></div>
                    </div>
                    <div class="teacher-upgrade-compare-shell__actions" data-compare-actions>
                        <button type="button" class="btn btn-outline-secondary teacher-upgrade-compare-shell__toggle" data-compare-toggle>
                            <span data-compare-toggle-label>Xem them</span>
                            <span class="teacher-upgrade-compare-shell__toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>

                <div class="row g-4 mt-1 align-items-start">
                    <div class="col-lg-7">
                        <div class="teacher-upgrade-section" data-payment-section>
                            <div class="teacher-upgrade-section__head">
                                <div>
                                    <h4>{{ __('packages::teacher.common.payment_method') }}</h4>
                                    <p class="mb-0">{{ __('packages::teacher.common.pending_payment_description') }}</p>
                                </div>
                            </div>

                            <div class="teacher-upgrade-payment-grid" data-upgrade-payment-methods>
                                @foreach ([
                                    'wallet' => [
                                        'label' => __('teacher::portal.payment_methods.wallet'),
                                        'enabled' => $walletEnabled
                                    ],
                                    'bank_transfer' => [
                                        'label' => __('teacher::portal.payment_methods.bank_transfer'),
                                        'enabled' => $bankEnabled
                                    ],
                                    'vnpay' => [
                                        'label' => __('teacher::portal.payment_methods.vnpay'),
                                        'enabled' => $vnpayEnabled
                                    ],
                                    'momo' => [
                                        'label' => __('teacher::portal.payment_methods.momo'),
                                        'enabled' => $momoEnabled
                                    ],
                                ] as $method => $data)
                                    <label class="teacher-upgrade-payment {{ $selectedPaymentMethod === $method ? 'is-selected' : '' }} {{ !$data['enabled'] ? 'is-maintenance' : '' }}" 
                                        data-upgrade-payment 
                                        data-method-id="{{ $method }}"
                                        data-enabled="{{ $data['enabled'] ? 1 : 0 }}"
                                        data-method-name="{{ $data['label'] }}">
                                        <input type="radio" name="payment_method" value="{{ $method }}" @checked($selectedPaymentMethod === $method)>
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <span>{{ $data['label'] }}</span>
                                            @if ($method === 'wallet')
                                                <span class="small text-muted" style="font-size: 0.7rem;">({{ moneyLocale($availableBalance) }})</span>
                                            @endif
                                            @unless ($data['enabled'])
                                                <span class="badge bg-warning text-dark px-2 py-1" style="font-size: 0.65rem;">
                                                    {{ __('packages::teacher.common.payment_maintenance') }}
                                                </span>
                                            @endunless
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Thông tin chuyển khoản ngân hàng --}}
                            <div id="bank-transfer-details" class="teacher-upgrade-bank-info mt-4 d-none">
                                <div class="card bg-dark-subtle border-0 rounded-4 overflow-hidden shadow-sm">
                                    <div class="card-header bg-primary text-white py-3 px-4">
                                        <h6 class="mb-0 fw-bold"><i class="bi bi-bank me-2"></i>{{ __('packages::teacher.common.bank_transfer_info.title') }}</h6>
                                    </div>
                                    <div class="card-body p-4 text-white">
                                        <div class="row g-4 align-items-center">
                                            <div class="col-md-7">
                                                <div class="d-flex flex-column gap-3">
                                                    <div class="bank-info-item">
                                                        <label class="text-white-50 small mb-1">{{ __('packages::teacher.common.bank_transfer_info.bank_name') }}</label>
                                                        <div class="fw-bold fs-5">{{ $bankTransferBankName }}</div>
                                                    </div>
                                                    <div class="bank-info-item">
                                                        <label class="text-white-50 small mb-1">{{ __('packages::teacher.common.bank_transfer_info.bank_account') }}</label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="fw-bold fs-4 text-primary font-monospace" data-copy-text="{{ $bankTransferAccountNumber }}">{{ $bankTransferAccountNumber }}</div>
                                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill copy-btn" data-copy="{{ $bankTransferAccountNumber }}">
                                                                <i class="bi bi-copy me-1"></i>{{ __('packages::teacher.common.bank_transfer_info.copy') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="bank-info-item">
                                                        <label class="text-white-50 small mb-1">{{ __('packages::teacher.common.bank_transfer_info.bank_account_name') }}</label>
                                                        <div class="fw-bold text-uppercase">{{ $bankTransferAccountName }}</div>
                                                    </div>
                                                    <div class="bank-info-item">
                                                        <label class="text-white-50 small mb-1">{{ __('packages::teacher.common.bank_transfer_info.transfer_content') }}</label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="fw-bold text-warning font-monospace" data-copy-text="{{ $bankTransferNote }}">{{ $bankTransferNote }}</div>
                                                            <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2 rounded-pill copy-btn" data-copy="{{ $bankTransferNote }}">
                                                                <i class="bi bi-copy me-1"></i>{{ __('packages::teacher.common.bank_transfer_info.copy') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5 text-center">
                                                <div class="bg-white p-3 rounded-4 d-inline-block shadow-lg">
                                                    <img id="vietqr-img" 
                                                        src="https://img.vietqr.io/image/{{ $bankTransferBankBin }}-{{ $bankTransferAccountNumber }}-compact2.jpg?amount=0&addInfo={{ rawurlencode($bankTransferNote) }}" 
                                                        class="img-fluid" style="max-height: 180px;" alt="VietQR">
                                                    <div class="mt-2">
                                                        <a href="#" class="text-primary text-decoration-none small fw-bold" id="download-qr">
                                                            <i class="bi bi-download me-1"></i>{{ __('packages::teacher.common.bank_transfer_info.download_qr') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <aside class="teacher-upgrade-summary">
                            @php
                                $selectedPackagePreview = $upgradePackages->firstWhere('id', $selectedPackageId);
                            @endphp
                            <div class="teacher-upgrade-summary__label">{{ __('packages::teacher.common.status_title') }}</div>
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
                                    {{ __('packages::teacher.common.cancel') }}
                                </a>
                                <button type="submit" id="upgrade-submit-btn" class="btn btn-primary flex-grow-1">
                                    {{ __('packages::teacher.common.submit_upgrade') }}
                                </button>
                            </div>
                        </aside>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="upgradeConfirmModal" tabindex="-1" aria-labelledby="upgradeConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content teacher-upgrade-section">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="upgradeConfirmModalLabel">
                        {{ __('packages::teacher.upgrade.confirm.upgrade_title') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div id="confirm-desc" class="mb-4"></div>

                    <!-- Tóm tắt thay đổi -->
                    <div class="teacher-upgrade-summary-box mb-4 p-3 rounded-4 shadow-sm" style="background: rgba(var(--admin-primary-rgb), 0.1); border: 1px solid rgba(var(--admin-primary-rgb), 0.2);">
                        <div class="small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px; color: var(--admin-primary);">{{ __('packages::teacher.features.compare_title') }}</div>
                        <div id="confirm-change-summary" class="d-flex flex-column gap-2"></div>
                    </div>
                    
                    <div id="downgrade-warning" class="teacher-upgrade-warning d-none mb-4 p-3 rounded-4">
                        <div class="d-flex gap-3">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                            <div>
                                <div class="fw-bold mb-1">{{ __('packages::teacher.upgrade.confirm.downgrade_warning') }}</div>
                                <div id="feature-loss-list" class="small mt-2">
                                    <div class="fw-bold mb-1">{{ __('packages::teacher.upgrade.confirm.feature_loss_warning') }}</div>
                                    <ul class="mb-0 ps-3"></ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="recurring-warning" class="teacher-upgrade-warning d-none mb-4 p-3 rounded-4" style="background: rgba(var(--admin-primary-rgb), 0.1); border-color: rgba(var(--admin-primary-rgb), 0.2); color: var(--admin-primary);">
                        <div class="d-flex gap-3">
                            <i class="bi bi-info-circle-fill fs-4"></i>
                            <div>
                                <div class="fw-bold mb-1">{{ __('packages::teacher.upgrade.confirm.permanent_to_recurring_warning') }}</div>
                                <div id="expiry-preview" class="small mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        {{ __('packages::teacher.upgrade.confirm.cancel_btn') }}
                    </button>
                    <button type="button" id="confirm-submit-btn" class="btn btn-primary px-4">
                        {{ __('packages::teacher.upgrade.confirm.confirm_btn') }}
                    </button>
                </div>
            </div>
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

        .teacher-upgrade-card__category {
            display: inline-flex;
            width: fit-content;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.85);
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .teacher-upgrade-card__body h5 {
            color: #ffffff;
            background: linear-gradient(to right, #ffffff, #bfdbfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.55rem;
            font-weight: 800;
            margin-bottom: 0.6rem;
            letter-spacing: -0.02em;
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

        .teacher-upgrade-payment.is-maintenance {
            opacity: 0.7;
            cursor: not-allowed;
            background: rgba(30, 41, 59, 0.5);
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

        .teacher-upgrade-compare-shell {
            position: relative;
        }

        .teacher-upgrade-compare-shell__inner {
            overflow: hidden;
            transition: max-height 0.42s ease;
        }

        .teacher-upgrade-compare-shell.is-collapsed .teacher-upgrade-compare-shell__inner {
            max-height: 700px;
        }

        .teacher-upgrade-compare-shell:not(.is-collapsed) .teacher-upgrade-compare-shell__inner {
            max-height: 10000px;
        }

        .teacher-upgrade-compare-shell__fade {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 140px;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0), rgba(15, 23, 42, 0.92) 72%, rgba(15, 23, 42, 1));
            opacity: 0;
            transition: opacity 0.24s ease;
        }

        .teacher-upgrade-compare-shell.is-collapsed .teacher-upgrade-compare-shell__fade {
            opacity: 1;
        }

        .teacher-upgrade-compare-shell__actions {
            display: flex;
            justify-content: center;
            margin-top: 1.1rem;
        }

        .teacher-upgrade-compare-shell__actions.is-hidden {
            display: none;
        }

        .teacher-upgrade-compare-shell__toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding-inline: 1.05rem;
            border-radius: 999px;
            font-weight: 800;
        }

        .teacher-upgrade-compare-shell__toggle-icon {
            width: 10px;
            height: 10px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg);
            transition: transform 0.2s ease;
            margin-top: -0.18rem;
        }

        .teacher-upgrade-compare-shell:not(.is-collapsed) .teacher-upgrade-compare-shell__toggle-icon {
            transform: rotate(-135deg);
            margin-top: 0.18rem;
        }

        .teacher-upgrade-current {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(96, 165, 250, 0.15);
            border-radius: 24px;
            padding: 2.2rem;
            margin-bottom: 4rem;
            position: relative;
            overflow: hidden;
        }

        .teacher-upgrade-current::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(96, 165, 250, 0.3), transparent);
        }

        .teacher-upgrade-current__label {
            color: #60a5fa;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            margin-bottom: 1rem;
        }

        .teacher-upgrade-current__name {
            font-size: 2.2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.75rem;
        }

        .teacher-upgrade-categories {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .teacher-upgrade-tabs {
            display: inline-flex;
            padding: 0.4rem;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            box-shadow: 
                0 4px 24px -1px rgba(0, 0, 0, 0.2),
                inset 0 0 20px rgba(255, 255, 255, 0.02);
            gap: 0.25rem;
        }

        .teacher-upgrade-tabs .nav-link {
            border: none;
            background: transparent !important;
            color: #94a3b8;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 0.7rem 1.75rem;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            position: relative;
            z-index: 1;
            white-space: nowrap;
        }

        .teacher-upgrade-tabs .nav-link:hover {
            color: #f1f5f9;
        }

        .teacher-upgrade-tabs .nav-link.active {
            color: #ffffff !important;
            background: rgba(59, 130, 246, 0.85) !important;
            box-shadow: 
                0 10px 25px -5px rgba(37, 99, 235, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        }

        .teacher-upgrade-tab-content {
            animation: premiumFadeIn 0.5s cubic-bezier(0.22, 1, 0.36, 1);
        }

        @keyframes premiumFadeIn {
            from { 
                opacity: 0; 
                transform: translateY(15px) scale(0.98);
                filter: blur(4px);
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
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

        html[data-theme="light"] .teacher-upgrade-compare-shell__fade {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0), rgba(248, 250, 252, 0.94) 72%, rgba(248, 250, 252, 1));
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

        /* Dynamic Package Tone */
        :root {
            --package-tone: #2563eb;
        }

        .teacher-upgrade-card.is-featured,
        .teacher-upgrade-card.is-selected {
            border-color: var(--package-tone) !important;
        }

        .teacher-upgrade-card.is-selected .teacher-upgrade-card__check {
            background: var(--package-tone) !important;
            color: #fff !important;
        }

        .teacher-upgrade-card__tag {
            background: rgba(var(--package-tone-rgb, 37, 99, 235), 0.1) !important;
            color: var(--package-tone) !important;
        }

        /* Tooltip & Feature Trigger */
        .feature-info-trigger {
            cursor: help;
            color: var(--admin-muted);
            transition: color 0.2s;
        }
        .feature-info-trigger:hover {
            color: var(--admin-text);
        }

        .tooltip-inner {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 0.6rem 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
        }

        .table-group-divider-row td {
            background: rgba(var(--admin-border-rgb, 148, 163, 184), 0.03);
            border-bottom: none !important;
        }

        html[data-theme="light"] .table-group-divider-row td {
            background: rgba(0, 0, 0, 0.02);
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
            .teacher-upgrade-compare-shell.is-collapsed .teacher-upgrade-compare-shell__inner {
                max-height: 560px;
            }

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
            const compareAnchor = document.querySelector('[data-compare-anchor]');
            const compareShell = document.querySelector('[data-compare-shell]');
            const compareContent = document.querySelector('[data-compare-content]');
            const compareActions = document.querySelector('[data-compare-actions]');
            const compareToggle = document.querySelector('[data-compare-toggle]');
            const compareToggleLabel = document.querySelector('[data-compare-toggle-label]');
            const unlimitedText = @json(__('courses::teacher/messages.courses.unlimited'));
            const currentPackage = @json($currentPackageMap);
            const packageMap = @json($packageMap);
            const allPaymentsDisabled = @json($allPaymentsDisabled);
            const getCollapsedCompareHeight = () => window.matchMedia('(max-width: 767.98px)').matches ? 560 : 700;

            const formatMoney = (value) => {
                const locale = document.documentElement.lang || 'vi';
                const formatted = new Intl.NumberFormat(locale).format(Math.max(Number(value || 0), 0));
                const symbol = @json(__('courses::teacher/messages.common.currency_symbol'));
                
                // Vietnamese usually puts symbol at end with space, others might differ
                if (locale === 'vi') {
                    return formatted + ' ' + symbol;
                }
                return symbol + formatted;
            };

            const updateCompareToggleState = () => {
                if (!compareShell || !compareContent || !compareActions || !compareToggleLabel) {
                    return;
                }

                const hasOverflow = compareContent.scrollHeight > getCollapsedCompareHeight() + 24;
                compareActions.classList.toggle('is-hidden', !hasOverflow);

                if (!hasOverflow) {
                    compareShell.classList.remove('is-collapsed');
                    compareToggleLabel.textContent = @json(__('courses::teacher/messages.common.collapse'));
                    return;
                }

                compareToggleLabel.textContent = compareShell.classList.contains('is-collapsed') ? @json(__('courses::teacher/messages.common.view_more')) : @json(__('courses::teacher/messages.common.collapse'));
            };

            const scrollToCompareTop = () => {
                if (!compareAnchor) {
                    return;
                }

                const top = compareAnchor.getBoundingClientRect().top + window.scrollY - 24;
                window.scrollTo({
                    top: Math.max(top, 0),
                    behavior: 'smooth',
                });
            };

            const expandCompare = () => {
                if (!compareShell || !compareContent) {
                    return;
                }

                const hiddenHeight = Math.max(compareContent.scrollHeight - getCollapsedCompareHeight(), 0);
                compareShell.classList.remove('is-collapsed');
                updateCompareToggleState();

                if (hiddenHeight > 0) {
                    window.scrollBy({
                        top: Math.min(hiddenHeight, 520),
                        behavior: 'smooth',
                    });
                }
            };

            const collapseCompare = () => {
                if (!compareShell) {
                    return;
                }

                scrollToCompareTop();
                window.setTimeout(() => {
                    compareShell.classList.add('is-collapsed');
                    updateCompareToggleState();
                }, 120);
            };

            const updateCards = (updateUrl = true) => {
                let selectedId = null;
                let selectedPrice = 0;

                const checkedInput = document.querySelector('input[name="package_id"]:checked');
                if (checkedInput) {
                    selectedId = checkedInput.value;
                    selectedPrice = Number(checkedInput.dataset.packagePrice || 0);
                    
                    if (updateUrl) {
                        const url = new URL(window.location.href);
                        url.searchParams.set('package_id', selectedId);
                        window.history.replaceState({}, '', url);
                    }
                }

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
                    summaryPrice.textContent = selectedPrice > 0 ? formatMoney(selectedPrice) : formatMoney(0);
                    summaryMeta.textContent = packageMap[selectedId].meta || unlimitedText;
                    if (summaryTerm) {
                        summaryTerm.textContent = packageMap[selectedId].term_description || '';
                    }
                }

                if (paymentGrid) {
                    paymentGrid.classList.toggle('is-hidden', selectedPrice <= 0 || allPaymentsDisabled);
                }

                const paymentSection = document.querySelector('[data-payment-section]');
                if (paymentSection) {
                    paymentSection.classList.toggle('d-none', selectedPrice <= 0 || (allPaymentsDisabled && selectedPrice > 0));
                }

                const submitBtn = document.getElementById('upgrade-submit-btn');
                if (submitBtn) {
                    if (allPaymentsDisabled && selectedPrice > 0) {
                        submitBtn.style.display = 'none';
                        let mtBtn = document.getElementById('maintenance-btn');
                        if (!mtBtn) {
                            mtBtn = document.createElement('button');
                            mtBtn.id = 'maintenance-btn';
                            mtBtn.type = 'button';
                            mtBtn.className = 'btn btn-outline-warning flex-grow-1 opacity-75';
                            mtBtn.disabled = true;
                            mtBtn.textContent = @json(__('packages::teacher.common.payment_maintenance'));
                            submitBtn.parentNode.appendChild(mtBtn);
                        } else {
                            mtBtn.style.display = '';
                        }
                    } else {
                        submitBtn.style.display = '';
                        const mtBtn = document.getElementById('maintenance-btn');
                        if (mtBtn) mtBtn.style.display = 'none';
                    }
                }

                if (warningBox && packageMap[selectedId]) {
                    const target = packageMap[selectedId];
                    const currentCycle = currentPackage.billing_cycle;
                    const targetCycle = target.billing_cycle;
                    const currentExpiresAt = currentPackage.expires_at ? new Date(currentPackage.expires_at) : null;
                    const hasRemainingRecurring = currentExpiresAt && currentExpiresAt > new Date() && ['monthly', 'yearly'].includes(currentCycle);
                    let warning = '';

                    if (currentCycle === 'one_time' && ['monthly', 'yearly'].includes(targetCycle)) {
                        warning = @json(__('packages::teacher.warnings.one_time_to_recurring'));
                    } else if (hasRemainingRecurring && Number(selectedId) === currentPackage.id) {
                        warning = @json(__('packages::teacher.warnings.same_package_recurring'));
                    } else if (hasRemainingRecurring && target.sort_order !== currentPackage.sort_order) {
                        const expiresText = currentExpiresAt.toLocaleDateString(document.documentElement.lang || 'vi');
                        warning = @json(__('packages::teacher.warnings.queued_package')).replace(':date', expiresText);
                    }

                    if (allPaymentsDisabled && target.price > 0) {
                        warning = @json(__('packages::teacher.common.all_payments_maintenance_block'));
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

                // Cập nhật QR Code với giá tiền nếu có
                const qrImg = document.getElementById('vietqr-img');
                if (qrImg && selectedPrice > 0) {
                    const currentSrc = new URL(qrImg.src);
                    currentSrc.searchParams.set('amount', selectedPrice);
                    qrImg.src = currentSrc.toString();
                }

                // Đồng bộ hiển thị thông tin ngân hàng
                const bankInfo = document.getElementById('bank-transfer-details');
                if (bankInfo) {
                    const bankLabel = document.querySelector('[data-method-id="bank_transfer"]');
                    const bankInput = bankLabel?.querySelector('input');
                    const isBankSelected = bankInput?.checked;
                    const isBankEnabled = bankLabel?.dataset.enabled === '1';
                    
                    if (!isBankSelected || !isBankEnabled || allPaymentsDisabled) {
                        bankInfo.setAttribute('style', 'display: none !important');
                    } else {
                        bankInfo.setAttribute('style', '');
                        bankInfo.classList.remove('d-none');
                    }
                }
            };

            const updatePayments = () => {
                paymentOptions.forEach((option) => {
                    const input = option.querySelector('input[name="payment_method"]');
                    const isSelected = !!input?.checked;
                    option.classList.toggle('is-selected', isSelected);
                });
            };

            const handlePaymentSelection = (event) => {
                const label = event.currentTarget;
                const input = label.querySelector('input[name="payment_method"]');
                const isEnabled = label.dataset.enabled === '1';

                if (!isEnabled) {
                    event.preventDefault();
                    if (input) input.checked = false;
                    
                    const methodName = label.dataset.methodName || 'Method';
                    const msg = @json(__('packages::teacher.common.payment_under_maintenance')).replace(':gateway', methodName);
                    alert(msg);
                    
                    // Re-select first enabled or clear
                    const firstEnabled = paymentOptions.find(opt => opt.dataset.enabled === '1');
                    if (firstEnabled) {
                        const firstInput = firstEnabled.querySelector('input[name="payment_method"]');
                        if (firstInput) {
                            firstInput.checked = true;
                            updatePayments();
                        }
                    } else {
                        updatePayments();
                    }
                    return;
                }
                updatePayments();
                updateCards(false); // Gọi updateCards để đồng bộ lại thông tin ngân hàng
            };

            cards.forEach((card) => {
                const input = card.querySelector('input[name="package_id"]');
                card.addEventListener('click', () => {
                    if (input) {
                        input.checked = true;
                        updateCards();
                    }
                });
                input?.addEventListener('change', updateCards);
            });

            paymentOptions.forEach((option) => {
                option.addEventListener('click', handlePaymentSelection);
            });

            compareToggle?.addEventListener('click', () => {
                if (!compareShell?.classList.contains('is-collapsed')) {
                    collapseCompare();
                    return;
                }

                expandCompare();
            });

            window.addEventListener('resize', updateCompareToggleState);

            // Khởi tạo trạng thái ban đầu
            updateCards(false);
            updatePayments();

            const featureLabels = @json($groupedFeatures->flatMap(fn($g) => collect($g)->mapWithKeys(fn($f) => [$f->key => $f->name_locale])));
            const confirmModal = new bootstrap.Modal(document.getElementById('upgradeConfirmModal'));
            const confirmSubmitBtn = document.getElementById('confirm-submit-btn');
            let isConfirming = false;

            form?.addEventListener('submit', (event) => {
                if (isConfirming) return;
                event.preventDefault();

                const selectedInput = document.querySelector('input[name="package_id"]:checked');
                if (!selectedInput) return;

                const target = packageMap[selectedInput.value];
                if (!target) return;

                // 1. Update Description
                const descEl = document.getElementById('confirm-desc');
                descEl.innerHTML = @json(__('packages::teacher.upgrade.confirm.upgrade_desc'))
                    .replace(':current', currentPackage.name)
                    .replace(':target', target.name);

                // 1.1 Update Change Summary
                const summaryEl = document.getElementById('confirm-change-summary');
                let summaryHtml = '';
                
                const compareFields = [
                    { key: 'commission_rate', label: @json(__('packages::teacher.features.labels.commission_rate')), suffix: '%', invert: true },
                    { key: 'course_limit', label: @json(__('packages::teacher.features.labels.course_limit')), suffix: ' ' + @json(__('packages::teacher.features.compare_unit_courses')), isLimit: true },
                    { key: 'coupon_limit', label: @json(__('packages::teacher.features.labels.coupon_limit')), suffix: ' ' + @json(__('packages::teacher.features.compare_unit_coupons')), isLimit: true },
                    { key: 'payout_account_limit', label: @json(__('packages::teacher.features.labels.payout_account_limit')), suffix: ' ' + @json(__('packages::teacher.features.compare_unit_accounts')), isLimit: true },
                ];

                compareFields.forEach(field => {
                    // Ép kiểu về số để so sánh chính xác (0 == null == undefined trong ngữ cảnh này)
                    const curVal = Number(currentPackage[field.key] || 0);
                    const tarVal = Number(target[field.key] || 0);
                    
                    if (curVal !== tarVal) {
                        const format = (v) => (field.isLimit && v === 0) ? @json(__('courses::teacher/messages.courses.unlimited')) : v + (field.suffix || '');
                        summaryHtml += `
                            <div class="d-flex justify-content-between align-items-center small">
                                <span class="opacity-75">${field.label}:</span>
                                <div class="fw-bold">
                                    <span class="text-decoration-line-through opacity-50 mr-2">${format(curVal)}</span>
                                    <i class="bi bi-arrow-right mx-1 opacity-50"></i>
                                    <span style="color: var(--admin-primary);">${format(tarVal)}</span>
                                </div>
                            </div>
                        `;
                    }
                });
                summaryEl.innerHTML = summaryHtml || `<div class="small text-muted">${@json(__('packages::teacher.features.compare_same'))}</div>`;

                // 2. Check Downgrade & Feature Loss
                const downgradeWarning = document.getElementById('downgrade-warning');
                const featureLossList = document.getElementById('feature-loss-list');
                const featureLossUl = featureLossList.querySelector('ul');
                
                // Kiểm tra xem các thông số có tệ hơn không
                let isWorseLimit = false;
                if (target.course_limit !== 0) {
                    if (currentPackage.course_limit === 0 || target.course_limit < currentPackage.course_limit) isWorseLimit = true;
                }
                if (target.commission_rate > currentPackage.commission_rate) isWorseLimit = true;
                if (target.coupon_limit !== 0) {
                    if (currentPackage.coupon_limit === 0 || target.coupon_limit < currentPackage.coupon_limit) isWorseLimit = true;
                }

                const isLowerOrder = target.sort_order < currentPackage.sort_order;
                
                // Loại bỏ các trường định lượng khỏi danh sách "tính năng bị mất"
                const numericKeys = ['course_limit', 'payout_account_limit', 'commission_rate', 'coupon_limit'];
                const lostFeatures = Object.keys(featureLabels).filter(key => {
                    if (numericKeys.includes(key)) return false;
                    return currentPackage[key] && !target[key];
                });
                
                if (isLowerOrder || lostFeatures.length > 0 || isWorseLimit) {
                    downgradeWarning.classList.remove('d-none');
                    if (lostFeatures.length > 0) {
                        featureLossList.classList.remove('d-none');
                        featureLossUl.innerHTML = lostFeatures.map(key => `<li>${featureLabels[key]}</li>`).join('');
                    } else {
                        featureLossList.classList.add('d-none');
                    }
                } else {
                    downgradeWarning.classList.add('d-none');
                }

                // 3. Check Permanent to Recurring
                const recurringWarning = document.getElementById('recurring-warning');
                const expiryPreview = document.getElementById('expiry-preview');
                const isOneTimeToRecurring = currentPackage.billing_cycle === 'one_time' && ['monthly', 'yearly'].includes(target.billing_cycle);
                
                if (isOneTimeToRecurring) {
                    recurringWarning.classList.remove('d-none');
                    const previewData = target.term_description || ''; // We could calculate this more accurately if needed
                    expiryPreview.innerHTML = @json(__('packages::teacher.upgrade.confirm.preview_expiry'))
                        .replace(':date', target.expires_at_formatted || '---');
                } else {
                    recurringWarning.classList.add('d-none');
                }

                confirmModal.show();
            });

            confirmSubmitBtn?.addEventListener('click', () => {
                isConfirming = true;
                form.submit();
            });

            updateCards(false);
            updatePayments();
            updateCompareToggleState();

            // Copy to clipboard
            document.querySelectorAll('.copy-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const text = btn.dataset.copy;
                    navigator.clipboard.writeText(text).then(() => {
                        const originalText = btn.innerHTML;
                        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>{{ __("packages::teacher.common.bank_transfer_info.copied") }}';
                        btn.classList.replace('btn-outline-primary', 'btn-success');
                        btn.classList.replace('btn-outline-warning', 'btn-success');
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.classList.replace('btn-success', 'btn-outline-primary');
                            btn.classList.replace('btn-success', 'btn-outline-warning');
                        }, 2000);
                    });
                });
            });

            // Download QR
            document.getElementById('download-qr')?.addEventListener('click', function(e) {
                e.preventDefault();
                const img = document.getElementById('vietqr-img');
                const link = document.createElement('a');
                link.href = img.src;
                link.download = 'vietqr.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            const initialPayment = document.querySelector('input[name="payment_method"]:checked');
            if (initialPayment?.value === 'bank_transfer') {
                document.getElementById('bank-transfer-details')?.classList.remove('d-none');
            }

            // Initialize Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        })();
    </script>
@endsection
