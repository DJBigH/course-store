@extends('layouts.teacher')

@section('content')
    @php
        $commission = rtrim(rtrim(number_format((float) ($effectiveCommissionRate ?? $teacher->commission_rate), 2, '.', ''), '0'), '.');
        $currentRangeKey = $dashboardRange['key'] ?? 'today';
        $rangeLabels = [
            'today' => __('finances::teacher/earnings.overview.ranges.today'),
            '7d' => __('finances::teacher/earnings.overview.ranges.7d'),
            '14d' => __('finances::teacher/earnings.overview.ranges.14d'),
            'month' => __('finances::teacher/earnings.overview.ranges.month'),
            'year' => __('finances::teacher/earnings.overview.ranges.year'),
        ];
        $currentRangeLabel = $dashboardRange['label'] ?? __('finances::teacher/earnings.overview.ranges.month');
        $currentRangeLabel = $rangeLabels[$currentRangeKey] ?? $currentRangeLabel;
        $maxDailyRevenue = max((float) (($revenueInsights['daily'] ?? collect())->max('teacher_revenue') ?? 0), 1);
        $maxCourseRevenue = max((float) (($coursePerformance ?? collect())->max('teacher_revenue') ?? 0), 1);
    @endphp

    <div class="teacher-panel" data-earnings-dashboard data-endpoint="{{ route('teacher.dashboard.earnings') }}">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('finances::teacher/earnings.title') }}</h3>
                <p class="text-muted mb-0">{{ __('finances::teacher/earnings.description') }}</p>
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
                <strong>{{ __('finances::teacher/earnings.overview.ranges.viewing_data') }}</strong>
                <p class="mb-0">{{ __('finances::teacher/earnings.overview.ranges.current_filter_prefix', ['prefix' => '']) }} <span data-range-label>{{ $currentRangeLabel }}</span></p>
            </div>
            <span class="teacher-range-banner__badge" data-loading-label>{{ __('finances::teacher/earnings.overview.ranges.ready') }}</span>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('finances::teacher/earnings.gross_revenue') }}</div>
                    <div class="teacher-stat-card__value" data-earnings-summary="gross_revenue">{{ moneyLocale($summary['gross_amount'], true) }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('finances::teacher/earnings.teacher_revenue', ['rate' => $commission]) }}</div>
                    <div class="teacher-stat-card__value" data-earnings-summary="teacher_revenue">{{ moneyLocale($summary['teacher_revenue'], true) }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-4">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title mb-3">
                        <div>
                            <h4 class="h5 mb-1">{{ __('finances::teacher/earnings.breakdown.daily_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('finances::teacher/earnings.breakdown.daily_description') }}</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('finances::teacher/earnings.breakdown.table.period') }}</th>
                                    <th>{{ __('finances::teacher/earnings.breakdown.table.orders') }}</th>
                                    <th>{{ __('finances::teacher/earnings.breakdown.table.revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody id="teacher-earnings-daily-rows">
                                @forelse (($revenueInsights['daily'] ?? collect()) as $row)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Carbon::parse($row->date)->format('d/m') }}</td>
                                        <td>{{ number_format((int) $row->orders) }}</td>
                                        <td>{{ moneyLocale($row->teacher_revenue, true) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">{{ __('finances::teacher/earnings.empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title mb-3">
                        <div>
                            <h4 class="h5 mb-1">{{ __('finances::teacher/earnings.breakdown.monthly_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('finances::teacher/earnings.breakdown.monthly_description') }}</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('finances::teacher/earnings.breakdown.table.period') }}</th>
                                    <th>{{ __('finances::teacher/earnings.breakdown.table.orders') }}</th>
                                    <th>{{ __('finances::teacher/earnings.breakdown.table.revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody id="teacher-earnings-monthly-rows">
                                @forelse (($revenueInsights['monthly'] ?? collect()) as $row)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $row->month)->format('m/Y') }}</td>
                                        <td>{{ number_format((int) $row->orders) }}</td>
                                        <td>{{ moneyLocale($row->teacher_revenue, true) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">{{ __('finances::teacher/earnings.empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title mb-3">
                        <div>
                            <h4 class="h5 mb-1">{{ __('finances::teacher/earnings.breakdown.course_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('finances::teacher/earnings.breakdown.course_description') }}</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('finances::teacher/earnings.table.course') }}</th>
                                    <th>{{ __('finances::teacher/earnings.breakdown.table.orders') }}</th>
                                    <th>{{ __('finances::teacher/earnings.breakdown.table.revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody id="teacher-earnings-course-rows">
                                @forelse (($revenueInsights['courses'] ?? collect()) as $row)
                                    <tr>
                                        <td>{{ $row->course_name }}</td>
                                        <td>{{ number_format((int) $row->orders) }}</td>
                                        <td>{{ moneyLocale($row->teacher_revenue, true) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">{{ __('finances::teacher/earnings.empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title mb-3">
                        <div>
                            <h4 class="h5 mb-1">{{ __('finances::teacher/earnings.charts.daily_revenue_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('finances::teacher/earnings.charts.daily_revenue_desc') }}</p>
                        </div>
                    </div>
                    <div class="teacher-chart-legend">
                        <span class="text-muted"><i class="teacher-chart-dot teacher-chart-dot--green"></i> {{ __('finances::teacher/earnings.breakdown.table.revenue') }}</span>
                        <span class="text-muted"><i class="teacher-chart-dot teacher-chart-dot--amber"></i> {{ __('finances::teacher/earnings.charts.highlights') }}</span>
                    </div>
                    <div class="teacher-mini-chart-scroll mb-0">
                        <div class="teacher-mini-chart" id="teacher-earnings-chart">
                            @forelse (($revenueInsights['daily'] ?? collect())->take(10)->reverse()->values() as $index => $row)
                                @php
                                    $chartColors = [
                                        ['#34d399', '#059669'],
                                        ['#22c55e', '#15803d'],
                                        ['#f59e0b', '#dc2626'],
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
                                <div class="text-muted">{{ __('finances::teacher/earnings.empty') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title mb-3">
                        <div>
                            <h4 class="h5 mb-1">{{ __('finances::teacher/earnings.charts.conversion_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('finances::teacher/earnings.charts.conversion_desc') }}</p>
                        </div>
                    </div>
                    <div class="teacher-chart-legend">
                        <span><i class="teacher-chart-dot teacher-chart-dot--blue"></i> {{ __('finances::teacher/earnings.charts.bar_legend') }}</span>
                    </div>
                    <div class="d-flex flex-column gap-3" id="teacher-earnings-course-performance">
                        @forelse ($coursePerformance->take(6) as $row)
                            <div>
                                <div class="d-flex justify-content-between gap-3 mb-1">
                                    <strong>{{ $row->course_name }}</strong>
                                    <span>{{ moneyLocale($row->teacher_revenue, true) }}</span>
                                </div>
                                <div class="teacher-course-bar">
                                    <div class="teacher-course-bar__fill" style="width: {{ max(($row->teacher_revenue / $maxCourseRevenue) * 100, 4) }}%"></div>
                                </div>
                                <div class="small text-muted mt-1">
                                    {{ __('finances::teacher/earnings.charts.view_metric', ['count' => number_format((int) $row->views)]) }} • {{ __('finances::teacher/earnings.charts.order_metric', ['count' => number_format((int) $row->orders)]) }} • {{ number_format((float) $row->conversion_rate, 2) }}%
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">{{ __('finances::teacher/earnings.empty') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="teacher-panel">
            <div class="teacher-section-title mb-3">
                <div>
                    <h4 class="h5 mb-1">{{ __('finances::teacher/earnings.recent_transactions_title') }}</h4>
                    <p class="text-muted mb-0">{{ __('finances::teacher/earnings.recent_transactions_desc') }}</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('finances::teacher/earnings.table.order') }}</th>
                            <th>{{ __('finances::teacher/earnings.table.course') }}</th>
                            <th>{{ __('finances::teacher/earnings.table.student') }}</th>
                            <th>{{ __('finances::teacher/earnings.table.gross') }}</th>
                            <th>{{ __('finances::teacher/earnings.table.discount') }}</th>
                            <th>{{ __('finances::teacher/earnings.table.net') }}</th>
                            <th>{{ __('finances::teacher/earnings.table.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody id="teacher-earnings-items">
                        @forelse ($items as $item)
                            <tr>
                                <td>#{{ $item->order?->code }}</td>
                                <td>{{ $item->courses?->name_locale ?: '-' }}</td>
                                <td>{{ $item->order?->students?->name ?: '-' }}</td>
                                <td>{{ moneyLocale($item->finance_breakdown['gross_amount'], true) }}</td>
                                <td class="text-danger">-{{ moneyLocale($item->finance_breakdown['allocated_discount'], true) }}</td>
                                <td>{{ moneyLocale($item->finance_breakdown['net_revenue'], true) }}</td>
                                <td>{{ moneyLocale($item->finance_breakdown['teacher_revenue'], true) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('finances::teacher/earnings.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4" id="teacher-earnings-pagination">
            {{ $items->links() }}
        </div>
        <p class="small text-muted mt-3 d-none" id="teacher-earnings-pagination-note">
            {{ __('finances::teacher/earnings.recent_transactions_note') }}
        </p>
    </div>

    <script type="application/json" id="teacher-earnings-payload">@json($earningsPayload, JSON_UNESCAPED_UNICODE)</script>
@endsection

@section('stylesheets')
    <style>
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

        .teacher-range-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.1rem;
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
            grid-template-columns: repeat(10, minmax(70px, 1fr));
            gap: 0.65rem;
            align-items: end;
            min-height: 220px;
            min-width: 680px;
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
            height: 150px;
            display: flex;
            align-items: end;
            justify-content: center;
            background: rgba(148, 163, 184, 0.06);
            border-radius: 16px;
            padding: 0.45rem;
            border: 1px solid rgba(148, 163, 184, 0.08);
        }

        .teacher-mini-chart__bar {
            width: 100%;
            border-radius: 12px;
            background: linear-gradient(180deg, var(--bar-start, #34d399) 0%, var(--bar-end, #059669) 100%);
            box-shadow: 0 12px 22px rgba(15, 23, 42, 0.22);
        }

        .teacher-chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
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

        .teacher-chart-dot--green {
            background: #34d399;
        }

        .teacher-chart-dot--amber {
            background: #f59e0b;
        }

        .teacher-chart-dot--blue {
            background: #3b82f6;
        }

        .teacher-course-bar {
            width: 100%;
            height: 12px;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.12);
            overflow: hidden;
        }

        .teacher-course-bar__fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #60a5fa 0%, #2563eb 100%);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
        }

        html[data-theme="light"] .teacher-filter-chip {
            background: var(--admin-surface);
            color: var(--admin-text);
            border-color: var(--admin-border);
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

        html[data-theme="light"] .teacher-mini-chart__value {
            color: #0f172a !important;
        }

        html[data-theme="light"] .teacher-stat-card__label {
            color: #64748b !important;
        }

        html[data-theme="light"] .teacher-stat-card__value {
            color: #0f172a !important;
            text-shadow: none !important;
        }

        html[data-theme="light"] .teacher-chart-legend {
            color: var(--admin-muted);
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-earnings-dashboard]');
            const payloadNode = document.getElementById('teacher-earnings-payload');

            if (!root || !payloadNode) {
                return;
            }

            const endpoint = root.dataset.endpoint;
            const emptyText = @json(__('finances::teacher/earnings.empty'));
            const rangeLabels = {
                today: @json(__('finances::teacher/earnings.overview.ranges.today')),
                '7d': @json(__('finances::teacher/earnings.overview.ranges.7d')),
                '14d': @json(__('finances::teacher/earnings.overview.ranges.14d')),
                month: @json(__('finances::teacher/earnings.overview.ranges.month')),
                year: @json(__('finances::teacher/earnings.overview.ranges.year'))
            };
            const chips = Array.from(root.querySelectorAll('[data-range-chip]'));
            const loadingLabel = root.querySelector('[data-loading-label]');
            const rangeLabel = root.querySelector('[data-range-label]');
            const summaryMap = {
                gross_revenue: root.querySelector('[data-earnings-summary="gross_revenue"]'),
                teacher_revenue: root.querySelector('[data-earnings-summary="teacher_revenue"]'),
            };
            const dailyRows = document.getElementById('teacher-earnings-daily-rows');
            const monthlyRows = document.getElementById('teacher-earnings-monthly-rows');
            const courseRows = document.getElementById('teacher-earnings-course-rows');
            const chartRoot = document.getElementById('teacher-earnings-chart');
            const coursePerformanceRoot = document.getElementById('teacher-earnings-course-performance');
            const itemsRoot = document.getElementById('teacher-earnings-items');
            const paginationRoot = document.getElementById('teacher-earnings-pagination');
            const paginationNote = document.getElementById('teacher-earnings-pagination-note');

            let currentPayload = JSON.parse(payloadNode.textContent);
            let activeRange = currentPayload.range?.key || @json($currentRangeKey);
            let isLoading = false;

            const renderRows = (target, rows, renderer, colspan) => {
                if (!target) {
                    return;
                }

                if (!rows || !rows.length) {
                    target.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted py-4">${emptyText}</td></tr>`;
                    return;
                }

                target.innerHTML = rows.map(renderer).join('');
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
                    coursePerformanceRoot.innerHTML = `<div class="text-muted">${emptyText}</div>`;
                    return;
                }

                coursePerformanceRoot.innerHTML = rows.map((row) => `
                    <div>
                        <div class="d-flex justify-content-between gap-3 mb-1">
                            <strong>${row.course_name}</strong>
                            <span>${row.revenue}</span>
                        </div>
                        <div class="teacher-course-bar">
                            <div class="teacher-course-bar__fill" style="width: ${row.width}%"></div>
                        </div>
                        <div class="small text-muted mt-1">${row.meta}</div>
                    </div>
                `).join('');
            };

            const renderItems = (rows) => {
                if (!itemsRoot) {
                    return;
                }

                if (!rows || !rows.length) {
                    itemsRoot.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">${emptyText}</td></tr>`;
                    return;
                }

                itemsRoot.innerHTML = rows.map((row) => `
                    <tr>
                        <td>${row.order_code}</td>
                        <td>${row.course_name}</td>
                        <td>${row.student_name}</td>
                        <td>${row.gross}</td>
                        <td class="text-danger">${row.discount}</td>
                        <td>${row.net}</td>
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
                    loadingLabel.textContent = value ? @json(__('finances::teacher/earnings.common.loading')) : @json(__('finances::teacher/earnings.overview.ranges.ready'));
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

                Object.entries(payload.summary || {}).forEach(([key, value]) => {
                    if (summaryMap[key]) {
                        summaryMap[key].textContent = value;
                    }
                });

                renderRows(dailyRows, payload.daily_rows, (row) => `
                    <tr>
                        <td>${row.short_period}</td>
                        <td>${row.orders}</td>
                        <td>${row.revenue}</td>
                    </tr>
                `, 3);

                renderRows(monthlyRows, payload.monthly_rows, (row) => `
                    <tr>
                        <td>${row.period}</td>
                        <td>${row.orders}</td>
                        <td>${row.revenue}</td>
                    </tr>
                `, 3);

                renderRows(courseRows, payload.course_rows, (row) => `
                    <tr>
                        <td>${row.course_name}</td>
                        <td>${row.orders}</td>
                        <td>${row.revenue}</td>
                    </tr>
                `, 3);

                renderChart(payload.revenue_chart || []);
                renderCoursePerformance(payload.course_performance || []);
                renderItems(payload.items || []);
                setActiveChip(activeRange);

                if (paginationRoot) {
                    paginationRoot.classList.add('d-none');
                }

                if (paginationNote) {
                    paginationNote.classList.remove('d-none');
                }
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
                            throw new Error('Failed to load dashboard payload');
                        }

                        const payload = await response.json();
                        applyPayload(payload);
                    } catch (error) {
                        console.error(error);
                        if (loadingLabel) {
                            loadingLabel.textContent = @json(__('finances::teacher/earnings.common.failed_to_load'));
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
