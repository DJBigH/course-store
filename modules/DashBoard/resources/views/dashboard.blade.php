@extends('layouts.backend')

@section('content')
    @php
        $pct = (float) ($kpi['revenue_change_percent'] ?? 0);
        $isUp = $pct >= 0;
        $orderRate = (float) ($kpi['conversion_rate_by_created_at'] ?? 0);
        $paymentRate = (float) ($kpi['conversion_rate_by_payment_complete_date'] ?? 0);
        $failedRate = (float) ($kpi['failed_rate'] ?? 0);
        $activeRangeLabel = mb_strtolower($rangeLabel ?? 'hôm nay', 'UTF-8');
    @endphp

    <div class="dashboard-shell">
        <section class="dashboard-hero card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-xl-5">
                <div class="row align-items-center g-4">
                    <div class="col-12 col-xl-8">
                        <span class="dashboard-kicker">Admin workspace</span>
                        <h3 class="dashboard-hero__title mb-3">Tổng quan vận hành trong {{ $activeRangeLabel }}</h3>
                        <p class="dashboard-hero__desc mb-4">
                            Theo dõi doanh thu, tiến trình thanh toán, tăng trưởng học viên và các đơn hàng mới nhất
                            trên cùng một màn hình.
                        </p>

                        <div class="dashboard-highlight-grid">
                            <div class="dashboard-highlight">
                                <span class="dashboard-highlight__label">Doanh thu</span>
                                <strong>{{ number_format((float) ($kpi['revenue'] ?? 0), 0, ',', '.') }} đ</strong>
                                <small>
                                    So với {{ $compareLabel ?? 'hôm qua' }}:
                                    <span class="{{ $isUp ? 'text-success' : 'text-danger' }}">
                                        {{ $isUp ? '+' : '' }}{{ $pct }}%
                                    </span>
                                </small>
                            </div>
                            <div class="dashboard-highlight">
                                <span class="dashboard-highlight__label">Đơn đã thanh toán</span>
                                <strong>{{ number_format($kpi['orders'] ?? 0) }}</strong>
                                <small>Tỷ lệ hoàn tất theo ngày tạo: {{ $orderRate }}%</small>
                            </div>
                            <div class="dashboard-highlight">
                                <span class="dashboard-highlight__label">Học viên mới</span>
                                <strong>{{ number_format($kpi['new_students'] ?? 0) }}</strong>
                                <small>Tổng học viên đang hoạt động: {{ number_format($kpi['student'] ?? 0) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="dashboard-toolbar mb-3">
                            <form method="GET" class="dashboard-filter">
                                <label for="dashboard-range" class="form-label mb-2">Khoảng thời gian</label>
                                <select id="dashboard-range" name="range" class="form-select" onchange="this.form.submit()">
                                    <option value="today" @selected(($range ?? 'today') === 'today')>Hôm nay</option>
                                    <option value="7d" @selected(($range ?? '') === '7d')>7 ngày</option>
                                    <option value="14d" @selected(($range ?? '') === '14d')>14 ngày</option>
                                    <option value="1m" @selected(($range ?? '') === '1m')>1 tháng</option>
                                    <option value="1y" @selected(($range ?? '') === '1y')>1 năm</option>
                                </select>
                            </form>

                            <a href="{{ url()->full() }}" class="btn btn-light dashboard-refresh">
                                <i class="fa-solid fa-rotate me-1"></i>
                                Làm mới số liệu
                            </a>
                        </div>

                        <div class="dashboard-quickstats">
                            <div class="dashboard-quickstats__item">
                                <span>AOV</span>
                                <strong>{{ money($kpi['aov'] ?? 0) }}</strong>
                            </div>
                            <div class="dashboard-quickstats__item">
                                <span>Bắt đầu thanh toán</span>
                                <strong>{{ number_format($kpi['payment_started_orders'] ?? 0) }}</strong>
                            </div>
                            <div class="dashboard-quickstats__item">
                                <span>Tỷ lệ thất bại</span>
                                <strong>{{ $failedRate }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <article class="dashboard-stat dashboard-stat--revenue card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="dashboard-stat__icon">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <p class="dashboard-stat__label">Doanh thu {{ $activeRangeLabel }}</p>
                        <h4 class="dashboard-stat__value">{{ number_format((float) ($kpi['revenue'] ?? 0), 0, ',', '.') }} đ</h4>
                        <p class="dashboard-stat__meta mb-0">
                            So với {{ $compareLabel ?? 'hôm qua' }}:
                            <span class="{{ $isUp ? 'text-success' : 'text-danger' }} fw-semibold">
                                {{ $isUp ? '+' : '' }}{{ $pct }}%
                            </span>
                        </p>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <article class="dashboard-stat card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="dashboard-stat__icon">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                        <p class="dashboard-stat__label">Đơn hàng đã thanh toán</p>
                        <h4 class="dashboard-stat__value">{{ number_format($kpi['orders'] ?? 0) }}</h4>
                        <p class="dashboard-stat__meta mb-0">Tỷ lệ Đơn hàng thành công: {{ $paymentRate }}%</p>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <article class="dashboard-stat card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="dashboard-stat__icon">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <p class="dashboard-stat__label">Học viên mới</p>
                        <h4 class="dashboard-stat__value">{{ number_format($kpi['new_students'] ?? 0) }}</h4>
                        <p class="dashboard-stat__meta mb-0">Tổng học viên đang hoạt động: {{ number_format($kpi['student'] ?? 0) }}</p>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <article class="dashboard-stat card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="dashboard-stat__icon">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <p class="dashboard-stat__label">Nội dung đào tạo</p>
                        <h4 class="dashboard-stat__value">{{ number_format($kpi['courses_count'] ?? 0) }} khóa</h4>
                        <p class="dashboard-stat__meta mb-0">{{ number_format($kpi['lessons_count'] ?? 0) }} bài giảng đang xuất bản</p>
                    </div>
                </article>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">{{ $chartTitle ?? 'Doanh thu' }}</div>
                            <div class="small text-muted">Diễn biến doanh thu theo ngày</div>
                        </div>
                        <span class="dashboard-chip">VND</span>
                    </div>
                    <div class="card-body pt-0 px-4 pb-4">
                        <canvas id="chartRevenue"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Trạng thái đơn hàng</div>
                            <div class="small text-muted">Tổng {{ number_format($kpi['orders_count'] ?? 0) }} đơn hàng được tạo</div>
                        </div>
                        <span class="dashboard-chip dashboard-chip--muted">Theo trạng thái</span>
                    </div>
                    <div class="card-body pt-0 px-4 pb-4">
                        <canvas id="chartOrderStatus"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Top khóa học bán chạy</div>
                            <div class="small text-muted">Số lượt mua: {{ number_format($totalBuys ?? 0) }}</div>
                        </div>
                        <span class="dashboard-chip dashboard-chip--accent">Top 3</span>
                    </div>
                    <div class="card-body pt-0 px-4 pb-4">
                        <canvas id="chartTopCourses"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Đơn hàng gần đây</div>
                            <div class="small text-muted">Danh sách đơn mới nhất trong khoảng đang xem</div>
                        </div>
                        <a href="{{ route('orders.index') }}" class="small text-decoration-none">Xem tất cả</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Mã</th>
                                        <th>Khách hàng</th>
                                        <th class="text-end">Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th class="pe-4 text-end">Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrders as $od)
                                        @php
                                            $badge = match ((int) $od['status_id']) {
                                                2 => 'success',
                                                1 => 'warning',
                                                3 => 'danger',
                                                4 => 'secondary',
                                                default => 'light',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $od['code'] }}</td>
                                            <td>{{ $od['customer'] }}</td>
                                            <td class="text-end">{{ money($od['total']) }}</td>
                                            <td><span class="badge rounded-pill bg-{{ $badge }}">{{ $od['status'] }}</span></td>
                                            <td class="pe-4 text-end text-muted small">{{ $od['created_at']->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                    @if ($recentOrders->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">Chưa có đơn hàng trong khoảng thời gian này</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .dashboard-shell {
            padding-bottom: 1rem;
        }

        .dashboard-hero,
        .dashboard-stat,
        .card {
            border-radius: 22px;
        }

        .dashboard-hero {
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(13, 110, 253, 0.24), transparent 32%),
                linear-gradient(135deg, #0f172a 0%, #172554 55%, #1d4ed8 100%);
            color: #fff;
        }

        .dashboard-kicker {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .dashboard-hero__title {
            font-size: clamp(1.8rem, 2vw, 2.6rem);
            font-weight: 700;
            line-height: 1.2;
        }

        .dashboard-hero__desc {
            max-width: 680px;
            color: rgba(255, 255, 255, 0.82);
        }

        .dashboard-highlight-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .dashboard-highlight,
        .dashboard-quickstats__item {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            padding: 1rem 1.1rem;
        }

        .dashboard-highlight strong,
        .dashboard-quickstats__item strong {
            display: block;
            font-size: 1.2rem;
            line-height: 1.3;
            margin: 0.25rem 0;
        }

        .dashboard-highlight small,
        .dashboard-quickstats__item span {
            color: rgba(255, 255, 255, 0.78);
        }

        .dashboard-highlight__label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.66);
        }

        .dashboard-toolbar,
        .dashboard-quickstats {
            display: grid;
            gap: 0.85rem;
        }

        .dashboard-filter label {
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.9rem;
        }

        .dashboard-filter .form-select,
        .dashboard-refresh {
            border: 0;
            min-height: 48px;
            border-radius: 14px;
        }

        .dashboard-refresh {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .dashboard-stat__icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: #e8f0ff;
            color: #1d4ed8;
            font-size: 1.2rem;
        }

        .dashboard-stat--revenue .dashboard-stat__icon {
            background: #dcfce7;
            color: #15803d;
        }

        .dashboard-stat__label {
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .dashboard-stat__value {
            margin-bottom: 0.35rem;
            font-size: 1.65rem;
            font-weight: 700;
            color: #0f172a;
        }

        .dashboard-stat__meta {
            color: #64748b;
            font-size: 0.9rem;
        }

        .dashboard-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .dashboard-chip--muted {
            background: #f1f5f9;
            color: #475569;
        }

        .dashboard-chip--accent {
            background: #fef3c7;
            color: #b45309;
        }

        #chartRevenue,
        #chartOrderStatus,
        #chartTopCourses {
            width: 100% !important;
            min-height: 320px;
        }

        @media (max-width: 991.98px) {
            .dashboard-hero__title {
                font-size: 1.6rem;
            }
        }
    </style>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
            new bootstrap.Tooltip(el);
        });

        const revenueLabels = @json($revenueLabels);
        const revenueData = @json($revenueData);
        const orderStatus = {!! json_encode($orderStatus, JSON_UNESCAPED_UNICODE) !!};
        const topCourses = @json($topCourses);

        new Chart(document.getElementById('chartRevenue'), {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Doanh thu',
                    data: revenueData,
                    tension: 0.35,
                    fill: true,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    pointBackgroundColor: '#1d4ed8',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const v = ctx.parsed.y ?? 0;
                                return ' ' + new Intl.NumberFormat('vi-VN').format(v) + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (v) => new Intl.NumberFormat('vi-VN').format(v)
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('chartOrderStatus'), {
            type: 'doughnut',
            data: {
                labels: orderStatus.labels,
                datasets: [{
                    label: 'Orders',
                    data: orderStatus.data,
                    backgroundColor: ['#2563eb', '#14b8a6', '#f59e0b', '#ef4444', '#8b5cf6', '#94a3b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        new Chart(document.getElementById('chartTopCourses'), {
            type: 'bar',
            data: {
                labels: topCourses.labels,
                datasets: [{
                    label: 'Số lượt mua',
                    data: topCourses.data,
                    borderRadius: 10,
                    backgroundColor: ['#1d4ed8', '#0f766e', '#b45309']
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
@endpush
