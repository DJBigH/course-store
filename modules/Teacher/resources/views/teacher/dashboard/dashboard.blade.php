@extends('layouts.teacher')

@section('content')
    @php
        $commission = rtrim(rtrim(number_format((float) ($effectiveCommissionRate ?? $teacher->commission_rate), 2, '.', ''), '0'), '.');
        $currentRangeKey = $dashboardRange['key'] ?? 'today';
        $rangeLabels = [
            'today' => __('teacher::teacher/dashboard.overview.ranges.today'),
            '7d' => __('teacher::teacher/dashboard.overview.ranges.7d'),
            '14d' => __('teacher::teacher/dashboard.overview.ranges.14d'),
            'month' => __('teacher::teacher/dashboard.overview.ranges.month'),
            'year' => __('teacher::teacher/dashboard.overview.ranges.year'),
        ];
        $currentRangeLabel = $dashboardRange['label'] ?? __('teacher::teacher/dashboard.overview.ranges.month');
        $currentRangeLabel = $rangeLabels[$currentRangeKey] ?? $currentRangeLabel;
        $maxDailyRevenue = max((float) (($revenueInsights['daily'] ?? collect())->max('teacher_revenue') ?? 0), 1);
        // Maintenance checks
        $stateCourseLimit = $teacher->getFeatureState('course_limit');
        $statePromotions = $teacher->getFeatureState('can_send_promotions');
        $statePayouts = $teacher->getFeatureState('can_request_payouts');
        
        $maintCourse = $stateCourseLimit['is_maintenance'];
        $maintPromotions = $statePromotions['is_maintenance'];
        $maintPayouts = $statePayouts['is_maintenance'];
        $maintEarnings = false; // Add specific logic if needed
        $maintProfile = false;
    @endphp

    <div class="teacher-page-shell">
        <section class="teacher-hero teacher-hero--dashboard">
            <div class="teacher-hero__content">
                <div class="teacher-hero__eyebrow">
                    <i class="fas fa-star"></i>
                    {{ __('teacher::teacher/dashboard.overview.eyebrow') }}
                </div>
                <h3 class="teacher-hero__title">
                    {{ __('teacher::teacher/dashboard.overview.title', ['name' => $teacher->name]) }}
                </h3>
                <p class="teacher-hero__desc">
                    {{ __('teacher::teacher/dashboard.overview.description') }}
                </p>

                <div class="teacher-chip-list mt-4">
                    <span class="teacher-chip teacher-chip--dark">
                        <i class="fas fa-percent"></i>
                        {{ __('teacher::teacher/dashboard.common.commission', ['rate' => $commission]) }}
                    </span>
                    <span class="teacher-chip teacher-chip--dark">
                        <i class="fas fa-book"></i>
                        <span data-overview-stat="courses">{{ $stats['courses'] }}</span> {{ __('teacher::teacher/dashboard.overview.labels.total_courses_short') }}
                    </span>
                    <span class="teacher-chip teacher-chip--dark">
                        <i class="fas fa-user-graduate"></i>
                        <span data-overview-stat="students">{{ $stats['students'] }}</span> {{ __('teacher::teacher/dashboard.overview.labels.students_short') }}
                    </span>
                </div>
                <a href="{{ $maintPromotions ? 'javascript:void(0)' : ($teacher->packageHasFeature('can_send_promotions') ? route('teacher.dashboard.promotions') : route('teacher.dashboard.package.upgrade')) }}" 
                   class="teacher-overview-shortcut mt-4 {{ $maintPromotions ? 'is-maintenance' : '' }}">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-bullhorn"></i></span>
                    <div>
                        <strong>
                            {{ __('teacher::teacher/dashboard.promotions.shortcut_title') }}
                            @if($maintPromotions) <span class="ms-1 badge bg-warning text-dark">{{ __('teacher/sidebar.nav.maintenance_badge') ?? 'BAO TRI' }}</span> @endif
                        </strong>
                        <p class="mb-0">
                            {{ $maintPromotions 
                                ? (__('teacher::teacher/dashboard.promotions.maintenance_msg') ?? 'Tính năng đang được bảo trì, vui lòng quay lại sau.')
                                : ($teacher->packageHasFeature('can_send_promotions')
                                    ? __('teacher::teacher/dashboard.promotions.shortcut_description')
                                    : __('teacher::teacher/dashboard.promotions.shortcut_locked')) }}
                        </p>
                    </div>
                </a>
            </div>

            <div class="teacher-hero__rail">
                @if ($packageSummary)
                    <div class="teacher-hero__mini teacher-hero__mini--package">
                        <span>{{ __('packages::teacher.upgrade.current_label') }}</span>
                        <strong>{{ $packageSummary['name'] }}</strong>
                        <small>
                            {{ __('packages::teacher.upgrade.summary', [
                                'commission' => rtrim(rtrim(number_format($packageSummary['commission_rate'], 2, '.', ''), '0'), '.'),
                                'limit' => $packageSummary['course_limit'] ?: __('teacher::teacher/dashboard.common.unlimited'),
                            ]) }}
                        </small>
                        @if (!empty($packageSummary['expires_at']))
                            <small>
                                {{ __('packages::teacher.upgrade.expires_on', [
                                    'date' => $packageSummary['expires_at']->format('d/m/Y'),
                                    'days' => $packageSummary['days_left'] ?? 0,
                                ]) }}
                            </small>
                        @endif
                        @if (!empty($packageSummary['pending_upgrade']) && !empty($packageSummary['pending_upgrade_starts_at']))
                            <small class="teacher-hero__package-note">
                                @if (!empty($packageSummary['pending_upgrade_is_queued']))
                                    {{ __('packages::teacher.upgrade.pending_starts_on', [
                                        'name' => $packageSummary['pending_upgrade_name'],
                                        'date' => $packageSummary['pending_upgrade_starts_at']->format('d/m/Y'),
                                    ]) }}
                                    @if (!is_null($packageSummary['pending_upgrade_days_until_activation']))
                                        <span class="d-block">
                                            {{ __('packages::teacher.upgrade.pending_days_left', [
                                                'days' => $packageSummary['pending_upgrade_days_until_activation'],
                                            ]) }}
                                        </span>
                                    @endif
                                @else
                                    {{ __('packages::teacher.upgrade.pending_status', [
                                        'status' => $packageSummary['pending_upgrade_status'],
                                    ]) }}
                                @endif
                            </small>
                        @endif
                        @if ($packageSummary['can_upgrade'])
                            <a href="{{ $packageSummary['upgrade_url'] }}" class="btn btn-sm btn-light mt-2 align-self-start">
                                @if ($packageSummary['has_higher_package'])
                                    {{ __('packages::teacher.upgrade.upgrade_cta', ['name' => $packageSummary['upgrade_name']]) }}
                                @else
                                    {{ __('packages::teacher.upgrade.change_cta') }}
                                @endif
                            </a>
                        @elseif ($packageSummary['pending_upgrade'])
                            <a href="{{ $packageSummary['pending_upgrade_url'] }}" class="btn btn-sm btn-light mt-2 align-self-start">
                                {{ __('teacher::teacher/dashboard.package.status_title') }}
                            </a>
                        @else
                            <small class="teacher-hero__package-note">{{ __('packages::teacher.upgrade.maxed') }}</small>
                        @endif
                    </div>
                @endif

                <div class="teacher-hero__mini">
                    <span>{{ __('teacher::teacher/dashboard.overview.labels.available_balance') }}</span>
                    <strong data-overview-stat="available_balance">{{ moneyLocale($stats['available_balance'], true) }}</strong>
                </div>
                <div class="teacher-hero__mini teacher-hero__mini--glass">
                    <span>{{ __('teacher::teacher/dashboard.overview.labels.estimated_revenue') }}</span>
                    <strong data-overview-stat="estimated_revenue">{{ moneyLocale($stats['estimated_revenue'], true) }}</strong>
                </div>
            </div>
        </section>

        <div class="teacher-panel" data-overview-dashboard data-endpoint="{{ route('teacher.dashboard.index') }}">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
                <div>
                    <h3 class="fw-bold mb-2">{{ __('teacher::teacher/dashboard.overview.section_title') }}</h3>
                    <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.section_desc') }}</p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @foreach ($dashboardRangeOptions as $rangeOption)
                        <button
                            type="button"
                            class="teacher-filter-chip {{ $currentRangeKey === $rangeOption['key'] ? 'teacher-filter-chip--active' : '' }}"
                            data-range-chip
                            data-range="{{ $rangeOption['key'] }}"
                        >
                            {{ $rangeLabels[$rangeOption['key']] ?? $rangeOption['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="teacher-range-banner mb-4">
                <div>
                    <strong>{{ __('teacher::teacher/dashboard.overview.ranges.viewing_data') }}</strong>
                    <p class="mb-0">{{ __('teacher::teacher/dashboard.overview.ranges.current_filter', ['label' => '']) }}<span data-range-label>{{ $currentRangeLabel }}</span></p>
                </div>
                <span class="teacher-range-banner__badge" data-loading-label>{{ __('teacher::teacher/dashboard.overview.ranges.ready') }}</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.labels.total_courses') }}</div>
                        <div class="teacher-stat-card__value" data-overview-stat="courses">{{ $stats['courses'] }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.labels.active_courses') }}</div>
                        <div class="teacher-stat-card__value" data-overview-stat="active_courses">{{ $stats['active_courses'] }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.labels.students') }}</div>
                        <div class="teacher-stat-card__value" data-overview-stat="students">{{ $stats['students'] }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.labels.available_balance') }}</div>
                        <div class="teacher-stat-card__value" data-overview-stat="available_balance">{{ money($stats['available_balance'], __('teacher::teacher/dashboard.common.currency_symbol'), __('teacher::teacher/dashboard.common.currency_zero'), true) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.labels.gross_revenue') }}</div>
                        <div class="teacher-stat-card__value" data-overview-stat="gross_revenue">{{ money($stats['gross_revenue'], __('teacher::teacher/dashboard.common.currency_symbol'), __('teacher::teacher/dashboard.common.currency_zero'), true) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.labels.allocated_discount') }}</div>
                        <div class="teacher-stat-card__value" data-overview-stat="allocated_discount">{{ money($stats['allocated_discount'], __('teacher::teacher/dashboard.common.currency_symbol'), __('teacher::teacher/dashboard.common.currency_zero'), true) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.labels.estimated_revenue') }}</div>
                        <div class="teacher-stat-card__value" data-overview-stat="estimated_revenue">{{ money($stats['estimated_revenue'], __('teacher::teacher/dashboard.common.currency_symbol'), __('teacher::teacher/dashboard.common.currency_zero'), true) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.labels.platform_revenue') }}</div>
                        <div class="teacher-stat-card__value" data-overview-stat="platform_revenue">{{ money($stats['platform_revenue'], __('teacher::teacher/dashboard.common.currency_symbol'), __('teacher::teacher/dashboard.common.currency_zero'), true) }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-lg-5">
                    <div class="teacher-subtle-card h-100">
                        <div class="teacher-section-title mb-3">
                            <div>
                                <h4 class="h5 mb-1">{{ __('teacher::teacher/dashboard.overview.conversion.title') }}</h4>
                                <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.conversion.description') }}</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="teacher-stat-card">
                                    <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.conversion.labels.created') }}</div>
                                    <div class="teacher-stat-card__value" data-overview-conversion="created">{{ number_format((int) ($conversionSummary['orders_this_month'] ?? 0)) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="teacher-stat-card">
                                    <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.conversion.labels.paid') }}</div>
                                    <div class="teacher-stat-card__value" data-overview-conversion="paid">{{ number_format((int) ($conversionSummary['orders_paid_this_month'] ?? 0)) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="teacher-stat-card">
                                    <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.conversion.labels.rate') }}</div>
                                    <div class="teacher-stat-card__value" data-overview-conversion="rate">{{ number_format((float) ($conversionSummary['conversion_rate_created'] ?? 0), 1) }}%</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="teacher-stat-card">
                                    <div class="teacher-stat-card__label">{{ __('teacher::teacher/dashboard.overview.conversion.labels.failed') }}</div>
                                    <div class="teacher-stat-card__value" data-overview-conversion="failed">{{ number_format((int) ($conversionSummary['failed_orders'] ?? 0)) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="teacher-subtle-card h-100">
                        <div class="teacher-section-title mb-3">
                            <div>
                                <h4 class="h5 mb-1">{{ __('teacher::teacher/dashboard.overview.revenue_breakdown.title') }}</h4>
                                <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.revenue_breakdown.description') }}</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('teacher::teacher/dashboard.overview.revenue_breakdown.table.period') }}</th>
                                        <th>{{ __('teacher::teacher/dashboard.overview.revenue_breakdown.table.orders') }}</th>
                                        <th>{{ __('teacher::teacher/dashboard.overview.revenue_breakdown.table.gross') }}</th>
                                        <th>{{ __('teacher::teacher/dashboard.overview.revenue_breakdown.table.revenue') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="teacher-overview-revenue-rows">
                                    @forelse (($revenueInsights['daily'] ?? collect())->take(5) as $row)
                                        <tr>
                                            <td>{{ \Illuminate\Support\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                            <td>{{ number_format((int) $row->orders) }}</td>
                                            <td>{{ money($row->gross_amount, __('teacher::teacher/dashboard.common.currency_symbol'), __('teacher::teacher/dashboard.common.currency_zero'), true) }}</td>
                                            <td>{{ money($row->teacher_revenue, __('teacher::teacher/dashboard.common.currency_symbol'), __('teacher::teacher/dashboard.common.currency_zero'), true) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted text-center py-4">{{ __('teacher::teacher/dashboard.common.empty') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="teacher-chart-legend mt-3 mb-2">
                            <span><i class="teacher-chart-dot teacher-chart-dot--blue"></i> {{ __('teacher::teacher/dashboard.overview.revenue_breakdown.legend.actual') }}</span>
                            <span><i class="teacher-chart-dot teacher-chart-dot--amber"></i> {{ __('teacher::teacher/dashboard.overview.revenue_breakdown.legend.highlight') }}</span>
                        </div>
                        <div class="teacher-mini-chart-scroll mb-0">
                            <div class="teacher-mini-chart mt-3" id="teacher-overview-chart">
                                @forelse (($revenueInsights['daily'] ?? collect())->take(7)->reverse()->values() as $index => $row)
                                    @php
                                        $chartColors = [
                                            ['#60a5fa', '#2563eb'],
                                            ['#38bdf8', '#0f766e'],
                                            ['#f59e0b', '#ea580c'],
                                        ];
                                        $activeColors = $chartColors[$index % count($chartColors)];
                                    @endphp
                                    <div class="teacher-mini-chart__item">
                                        <strong class="teacher-mini-chart__value">{{ moneyLocale($row->teacher_revenue, true) }}</strong>
                                        <div class="teacher-mini-chart__bar-wrap">
                                            <div class="teacher-mini-chart__bar" style="height: {{ max(($row->teacher_revenue / $maxDailyRevenue) * 100, 8) }}%; --bar-start: {{ $activeColors[0] }}; --bar-end: {{ $activeColors[1] }};"></div>
                                        </div>
                                        <span>{{ \Illuminate\Support\Carbon::parse($row->date)->format('d/m') }}</span>
                                    </div>
                                @empty
                                    <div class="text-muted">{{ __('teacher::teacher/dashboard.common.empty') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="teacher-subtle-card mt-4">
                <div class="teacher-section-title mb-3">
                    <div>
                        <h4 class="h5 mb-1">{{ __('teacher::teacher/dashboard.overview.course_performance.title') }}</h4>
                        <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.course_performance.description') }}</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('teacher::teacher/dashboard.overview.course_performance.table.course') }}</th>
                                <th>{{ __('teacher::teacher/dashboard.overview.course_performance.table.views') }}</th>
                                <th>{{ __('teacher::teacher/dashboard.overview.course_performance.table.orders') }}</th>
                                <th>{{ __('teacher::teacher/dashboard.overview.course_performance.table.conversion') }}</th>
                                <th>{{ __('teacher::teacher/dashboard.overview.course_performance.table.revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody id="teacher-overview-course-performance">
                            @forelse ($coursePerformance as $row)
                                <tr>
                                    <td>{{ $row->course_name }}</td>
                                    <td>{{ number_format((int) $row->views) }}</td>
                                    <td>{{ number_format((int) $row->orders) }}</td>
                                    <td>{{ number_format((float) $row->conversion_rate, 2) }}%</td>
                                    <td>{{ moneyLocale($row->teacher_revenue, true) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('teacher::teacher/dashboard.common.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title">
                        <div>
                            <h4 class="h5">{{ __('teacher::teacher/dashboard.overview.recent_courses_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.recent_courses_desc') }}</p>
                        </div>
                        <a class="teacher-soft-link" href="{{ route('teacher.dashboard.courses') }}">
                            {{ __('teacher::teacher/dashboard.common.view_all') }}
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($recentCourses as $course)
                            <div class="teacher-subtle-card">
                                <strong class="d-block">{{ $course->name_locale }}</strong>
                                <small class="text-muted">
                                    {{ __('teacher::teacher/dashboard.overview.labels.course_status', [
                                        'status' => $course->status ? __('teacher::teacher/dashboard.common.status_active') : __('teacher::teacher/dashboard.common.course_hidden'),
                                    ]) }}
                                </small>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.no_courses') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title">
                        <div>
                            <h4 class="h5">{{ __('teacher::teacher/dashboard.overview.recent_sales_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.recent_sales_desc') }}</p>
                        </div>
                        <a class="teacher-soft-link" href="{{ route('teacher.dashboard.earnings') }}">
                            {{ __('teacher::teacher/dashboard.common.view_earnings') }}
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($recentSales as $detail)
                            <div class="teacher-subtle-card">
                                <strong class="d-block">
                                    {{ $detail->courses?->name_locale ?: __('teacher::teacher/dashboard.common.unknown_course') }}
                                </strong>
                                <small class="d-block text-muted">
                                    {{ __('teacher::teacher/dashboard.overview.labels.order_code', ['code' => $detail->order?->code]) }}
                                    - {{ optional($detail->created_at)->format('d/m/Y H:i') }}
                                </small>
                                <span class="text-primary fw-semibold">{{ moneyLocale($detail->finance_breakdown['teacher_revenue'], true) }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.no_sales') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title">
                        <div>
                            <h4 class="h5">{{ __('teacher::teacher/dashboard.overview.top_bundles_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.top_bundles_desc') }}</p>
                        </div>
                        <a class="teacher-soft-link" href="{{ route('teacher.dashboard.bundles') }}">
                            {{ __('teacher::teacher/dashboard.common.view_all') }}
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($topBundles as $bundleStat)
                            <div class="teacher-subtle-card">
                                <strong class="d-block">
                                    {{ $bundleStat->bundle?->name ?: __('teacher::teacher/dashboard.common.unknown_course') }}
                                </strong>
                                <small class="d-block text-muted">
                                    {{ __('teacher::teacher/dashboard.overview.top_bundles_sales', ['count' => $bundleStat->sales_count]) }}
                                </small>
                                <span class="text-primary fw-semibold">{{ moneyLocale($bundleStat->net_revenue, true) }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.top_bundles_empty') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="teacher-panel mt-4">
            <div class="teacher-section-title mb-3">
                <div>
                    <h4 class="h5 mb-1">{{ __('teacher::teacher/dashboard.overview.shortcuts.title') }}</h4>
                    <p class="text-muted mb-0">{{ __('teacher::teacher/dashboard.overview.shortcuts.description') }}</p>
                </div>
            </div>
    
            <div class="teacher-overview-shortcuts mb-0">
            <div class="teacher-overview-shortcuts mb-0">
                <a href="{{ $maintCourse ? 'javascript:void(0)' : route('teacher.dashboard.courses') }}" 
                   class="teacher-overview-shortcut {{ $maintCourse ? 'is-maintenance' : '' }}">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-book-open"></i></span>
                    <div>
                        <strong>
                            {{ __('teacher::teacher/dashboard.overview.shortcuts.my_courses.title') }}
                            @if($maintCourse) <span class="ms-1 badge bg-warning text-dark">{{ __('teacher/sidebar.nav.maintenance_badge') ?? 'BAO TRI' }}</span> @endif
                        </strong>
                        <p class="mb-0">{{ $maintCourse ? (__('teacher::teacher/dashboard.common.maintenance_msg') ?? 'Đang bảo trì') : __('teacher::teacher/dashboard.overview.shortcuts.my_courses.description') }}</p>
                    </div>
                </a>
    
                <a href="{{ $maintEarnings ? 'javascript:void(0)' : route('teacher.dashboard.earnings') }}" 
                   class="teacher-overview-shortcut {{ $maintEarnings ? 'is-maintenance' : '' }}">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-chart-line"></i></span>
                    <div>
                        <strong>
                            {{ __('teacher::teacher/dashboard.overview.shortcuts.earnings.title') }}
                            @if($maintEarnings) <span class="ms-1 badge bg-warning text-dark">{{ __('teacher/sidebar.nav.maintenance_badge') ?? 'BAO TRI' }}</span> @endif
                        </strong>
                        <p class="mb-0">{{ $maintEarnings ? (__('teacher::teacher/dashboard.common.maintenance_msg') ?? 'Đang bảo trì') : __('teacher::teacher/dashboard.overview.shortcuts.earnings.description') }}</p>
                    </div>
                </a>
    
                <a href="{{ $maintPayouts ? 'javascript:void(0)' : route('teacher.dashboard.payouts.index') }}" 
                   class="teacher-overview-shortcut {{ $maintPayouts ? 'is-maintenance' : '' }}">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-wallet"></i></span>
                    <div>
                        <strong>
                            {{ __('teacher::teacher/dashboard.overview.shortcuts.payouts.title') }}
                            @if($maintPayouts) <span class="ms-1 badge bg-warning text-dark">{{ __('teacher/sidebar.nav.maintenance_badge') ?? 'BAO TRI' }}</span> @endif
                        </strong>
                        <p class="mb-0">{{ $maintPayouts ? (__('teacher::teacher/dashboard.common.maintenance_msg') ?? 'Đang bảo trì') : __('teacher::teacher/dashboard.overview.shortcuts.payouts.description') }}</p>
                    </div>
                </a>
    
                <a href="{{ $maintProfile ? 'javascript:void(0)' : route('teacher.dashboard.profile') }}" 
                   class="teacher-overview-shortcut {{ $maintProfile ? 'is-maintenance' : '' }}">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-id-card"></i></span>
                    <div>
                        <strong>
                            {{ __('teacher::teacher/dashboard.overview.shortcuts.profile.title') }}
                            @if($maintProfile) <span class="ms-1 badge bg-warning text-dark">{{ __('teacher/sidebar.nav.maintenance_badge') ?? 'BAO TRI' }}</span> @endif
                        </strong>
                        <p class="mb-0">{{ $maintProfile ? (__('teacher::teacher/dashboard.common.maintenance_msg') ?? 'Đang bảo trì') : __('teacher::teacher/dashboard.overview.shortcuts.profile.description') }}</p>
                    </div>
                </a>
            </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="teacher-overview-payload">@json($overviewPayload, JSON_UNESCAPED_UNICODE)</script>
@endsection

@section('stylesheets')
    <style>
        .teacher-overview-shortcuts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .teacher-filter-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1rem;
            border-radius: 999px;
            border: 1px solid var(--admin-border);
            background: var(--admin-surface-3);
            color: var(--admin-text);
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 600;
            font-size: 0.88rem;
        }

        .teacher-filter-chip:hover {
            transform: translateY(-1px);
            border-color: var(--admin-primary);
            background: var(--admin-hover-bg);
        }

        .teacher-filter-chip--active {
            background: var(--admin-primary) !important;
            border-color: var(--admin-primary) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        html[data-theme="dark"] .teacher-filter-chip {
            background: rgba(18, 28, 50, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.16);
            color: #e8f1ff;
        }

        html[data-theme="dark"] .teacher-filter-chip--active {
            background: rgba(37, 99, 235, 0.45) !important;
            border-color: rgba(125, 211, 252, 0.34) !important;
            color: #ffffff !important;
        }

        .teacher-filter-chip[disabled] {
            opacity: 0.65;
            cursor: wait;
        }

        /* Package Feature Notice Custom Styling */
        .teacher-panel .alert-warning {
            background: color-mix(in srgb, var(--admin-primary) 8%, var(--admin-subtle-bg, #f8fafc));
            border: 1px solid color-mix(in srgb, var(--admin-primary) 15%, transparent);
            border-radius: 20px;
        }

        html[data-theme="dark"] .teacher-panel .alert-warning {
            background: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .teacher-panel .alert-warning strong {
            color: var(--admin-primary);
        }

        .teacher-panel .alert-warning .text-muted {
            color: var(--admin-text) !important;
            opacity: 0.8;
        }

        .teacher-range-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(17, 24, 39, 0.78));
        }

        .teacher-range-banner__badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 110px;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.16);
            color: #86efac;
            font-weight: 600;
        }

        .teacher-mini-chart-scroll {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            scrollbar-width: thin;
        }

        .teacher-mini-chart-scroll::-webkit-scrollbar {
            height: 4px;
        }

        .teacher-mini-chart-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.2);
            border-radius: 10px;
        }

        .teacher-mini-chart {
            display: grid;
            grid-template-columns: repeat(7, minmax(80px, 1fr));
            gap: 0.65rem;
            align-items: end;
            min-height: 210px;
            min-width: 560px;
        }

        .teacher-mini-chart__item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.45rem;
        }

        .teacher-mini-chart__value {
            font-size: 0.72rem;
            line-height: 1.35;
            color: var(--admin-text);
            text-align: center;
            opacity: 0.9;
        }

        .teacher-mini-chart__bar-wrap {
            width: 100%;
            height: 130px;
            display: flex;
            align-items: end;
            justify-content: center;
            background: rgba(148, 163, 184, 0.06);
            border-radius: 14px;
            padding: 0.45rem;
            border: 1px solid rgba(148, 163, 184, 0.08);
        }

        .teacher-mini-chart__bar {
            width: 100%;
            border-radius: 10px;
            background: linear-gradient(180deg, var(--bar-start, #60a5fa) 0%, var(--bar-end, #2563eb) 100%);
            box-shadow: 0 12px 22px rgba(15, 23, 42, 0.22);
        }

        .teacher-chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: #cbd5e1;
            font-size: 0.9rem;
        }

        .teacher-chart-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 0.35rem;
            border-radius: 999px;
        }

        .teacher-chart-dot--blue {
            background: #3b82f6;
        }

        .teacher-chart-dot--amber {
            background: #f59e0b;
        }

        .teacher-overview-shortcut {
            display: flex;
            gap: 1rem;
            padding: 1.1rem 1.15rem;
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            color: #e8f1ff;
            text-decoration: none;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .teacher-overview-shortcut:hover {
            transform: translateY(-4px);
            color: #fff;
            border-color: rgba(125, 211, 252, 0.34);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-overview-shortcut.is-maintenance {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
            filter: grayscale(0.5);
            border-style: dashed !important;
            border-color: #f59e0b !important;
            background: rgba(245, 158, 11, 0.05) !important;
        }

        .teacher-overview-shortcut.is-maintenance .teacher-overview-shortcut__icon {
            background: rgba(245, 158, 11, 0.2) !important;
            color: #f59e0b !important;
        }

        .teacher-overview-shortcut__icon {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(37, 99, 235, 0.08);
            color: var(--admin-primary);
        }

        html[data-theme="dark"] .teacher-overview-shortcut__icon {
            background: rgba(37, 99, 235, 0.18);
            color: #8fc3ff;
        }

        .teacher-overview-shortcut strong {
            display: block;
            margin-bottom: 0.25rem;
            color: #fff;
        }

        .teacher-overview-shortcut p {
            color: rgba(226, 232, 240, 0.78);
            line-height: 1.6;
        }

        .teacher-hero__mini--package small {
            display: block;
            color: rgba(226, 232, 240, 0.78);
            margin-top: 0.35rem;
            line-height: 1.45;
        }

        .teacher-hero__package-note {
            color: rgba(125, 211, 252, 0.88);
            font-weight: 600;
        }

        html[data-theme="light"] .teacher-hero {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(59, 130, 246, 0.08), transparent 32%),
                linear-gradient(135deg, #ffffff 0%, #f1f5f9 60%, #e0f2fe 100%);
            color: #0f172a;
            border: 1px solid var(--admin-border);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }

        html[data-theme="light"] .teacher-hero__eyebrow {
            background: rgba(37, 99, 235, 0.08);
            color: #2563eb;
            border: 1px solid rgba(37, 99, 235, 0.12);
        }

        html[data-theme="light"] .teacher-hero__title {
            color: #0f172a !important;
        }

        html[data-theme="light"] .teacher-hero__desc {
            color: #334155 !important;
        }

        html[data-theme="light"] .teacher-chip--dark {
            background: rgba(30, 41, 59, 0.05) !important;
            border-color: rgba(30, 41, 59, 0.1) !important;
            color: #0f172a !important;
        }

        html[data-theme="light"] .teacher-overview-shortcut {
            background: var(--admin-subtle-bg);
            border-color: var(--admin-border);
            color: #0f172a !important;
        }

        html[data-theme="light"] .teacher-overview-shortcut strong {
            color: #0f172a !important;
        }

        html[data-theme="light"] .teacher-overview-shortcut p {
            color: #475569 !important;
        }

        html[data-theme="light"] .teacher-hero__mini {
            background: #ffffff;
            border: 1px solid var(--admin-border);
            backdrop-filter: none;
            box-shadow: 0 8px 32px rgba(15, 23, 42, 0.04);
            color: #0f172a !important;
        }

        html[data-theme="light"] .teacher-hero__mini span,
        html[data-theme="light"] .teacher-hero__mini small {
            color: #475569 !important;
        }

        html[data-theme="light"] .teacher-hero__mini strong {
            color: #0f172a !important;
        }

        html[data-theme="light"] .teacher-range-banner {
            background: var(--admin-subtle-bg);
            border-color: var(--admin-border);
        }

        html[data-theme="light"] .teacher-range-banner__badge {
            background: rgba(34, 197, 94, 0.08);
            color: #166534;
        }

        html[data-theme="light"] .teacher-mini-chart__bar-wrap {
            background: rgba(148, 163, 184, 0.04);
            border-color: rgba(148, 163, 184, 0.1);
        }

        html[data-theme="light"] .teacher-stat-card__label {
            color: #64748b !important;
        }

        html[data-theme="light"] .teacher-stat-card__value {
            color: #0f172a !important;
            text-shadow: none !important;
        }

        @media (max-width: 991.98px) {
            .teacher-overview-shortcuts {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-overview-dashboard]');
            const payloadNode = document.getElementById('teacher-overview-payload');

            if (!root || !payloadNode) {
                return;
            }

            const endpoint = root.dataset.endpoint;
            const emptyText = @json(__('teacher::teacher/dashboard.common.empty'));
            const rangeLabels = {
                today: @json(__('teacher::teacher/dashboard.overview.ranges.today')),
                '7d': @json(__('teacher::teacher/dashboard.overview.ranges.7d')),
                '14d': @json(__('teacher::teacher/dashboard.overview.ranges.14d')),
                month: @json(__('teacher::teacher/dashboard.overview.ranges.month')),
                year: @json(__('teacher::teacher/dashboard.overview.ranges.year'))
            };
            const chips = Array.from(root.querySelectorAll('[data-range-chip]'));
            const loadingLabel = root.querySelector('[data-loading-label]');
            const rangeLabel = root.querySelector('[data-range-label]');
            const revenueRows = document.getElementById('teacher-overview-revenue-rows');
            const chartRoot = document.getElementById('teacher-overview-chart');
            const coursePerformanceRoot = document.getElementById('teacher-overview-course-performance');
            const statNodes = {};
            const conversionNodes = {};

            root.querySelectorAll('[data-overview-stat]').forEach((node) => {
                const key = node.getAttribute('data-overview-stat');
                statNodes[key] = statNodes[key] || [];
                statNodes[key].push(node);
            });

            root.querySelectorAll('[data-overview-conversion]').forEach((node) => {
                const key = node.getAttribute('data-overview-conversion');
                conversionNodes[key] = node;
            });

            let currentPayload = JSON.parse(payloadNode.textContent);
            let activeRange = currentPayload.range?.key || @json($currentRangeKey);
            let isLoading = false;

            const renderRevenueRows = (rows) => {
                if (!revenueRows) {
                    return;
                }

                if (!rows || !rows.length) {
                    revenueRows.innerHTML = `<tr><td colspan="4" class="text-muted text-center py-4">${emptyText}</td></tr>`;
                    return;
                }

                revenueRows.innerHTML = rows.map((row) => `
                    <tr>
                        <td>${row.period}</td>
                        <td>${row.orders}</td>
                        <td>${row.gross}</td>
                        <td>${row.revenue}</td>
                    </tr>
                `).join('');
            };

            const renderChart = (rows) => {
                if (!chartRoot) {
                    return;
                }

                if (!rows || !rows.length) {
                    chartRoot.innerHTML = `<div class="text-muted">${emptyText}</div>`;
                    return;
                }

                chartRoot.innerHTML = rows.map((row) => `
                    <div class="teacher-mini-chart__item">
                        <strong class="teacher-mini-chart__value">${row.value}</strong>
                        <div class="teacher-mini-chart__bar-wrap">
                            <div class="teacher-mini-chart__bar" style="height: ${row.height}%; --bar-start: ${row.start_color}; --bar-end: ${row.end_color};"></div>
                        </div>
                        <span>${row.label}</span>
                    </div>
                `).join('');
            };

            const renderCoursePerformance = (rows) => {
                if (!coursePerformanceRoot) {
                    return;
                }

                if (!rows || !rows.length) {
                    coursePerformanceRoot.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">${emptyText}</td></tr>`;
                    return;
                }

                coursePerformanceRoot.innerHTML = rows.map((row) => `
                    <tr>
                        <td>${row.course_name}</td>
                        <td>${row.views}</td>
                        <td>${row.orders}</td>
                        <td>${row.conversion_rate}</td>
                        <td>${row.revenue}</td>
                    </tr>
                `).join('');
            };

            const setLoading = (value) => {
                isLoading = value;
                chips.forEach((chip) => {
                    chip.disabled = value;
                });

                if (loadingLabel) {
                    loadingLabel.textContent = value ? @json(__('teacher::teacher/dashboard.common.loading')) : @json(__('teacher::teacher/dashboard.overview.ranges.ready'));
                }
            };

            const setActiveChip = (rangeKey) => {
                chips.forEach((chip) => {
                    chip.classList.toggle('teacher-filter-chip--active', chip.dataset.range === rangeKey);
                });
            };

            const applyPayload = (payload) => {
                currentPayload = payload;
                activeRange = payload.range?.key || activeRange;

                if (rangeLabel && payload.range?.key) {
                    rangeLabel.textContent = rangeLabels[payload.range.key] || payload.range.label || payload.range.key;
                }

                Object.entries(payload.stats || {}).forEach(([key, value]) => {
                    (statNodes[key] || []).forEach((node) => {
                        node.textContent = value;
                    });
                });

                Object.entries(payload.conversion || {}).forEach(([key, value]) => {
                    if (conversionNodes[key]) {
                        conversionNodes[key].textContent = value;
                    }
                });

                renderRevenueRows(payload.revenue_rows || []);
                renderChart(payload.revenue_chart || []);
                renderCoursePerformance(payload.course_performance || []);
                setActiveChip(activeRange);
            };

            chips.forEach((chip) => {
                chip.addEventListener('click', async () => {
                    const rangeKey = chip.dataset.range;

                    if (!rangeKey || rangeKey === activeRange || isLoading) {
                        return;
                    }

                    setLoading(true);

                    try {
                        const response = await fetch(`${endpoint}?ajax=1&range=${encodeURIComponent(rangeKey)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load overview payload');
                        }

                        const payload = await response.json();
                        applyPayload(payload);
                    } catch (error) {
                        console.error(error);
                        if (loadingLabel) {
                            loadingLabel.textContent = @json(__('teacher::teacher/dashboard.common.failed_to_load'));
                        }
                    } finally {
                        setLoading(false);
                    }
                });
            });

            applyPayload(currentPayload);
        })();
    </script>
@endsection
