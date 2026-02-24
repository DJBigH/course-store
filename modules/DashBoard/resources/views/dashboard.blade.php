@extends('layouts.backend')

@section('content')
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <select name="range" class="form-select" onchange="this.form.submit()">
                        <option value="today" @selected(($range ?? 'today') === 'today')>Hôm nay</option>
                        <option value="7d" @selected(($range ?? '') === '7d')>1 tuần</option>
                        <option value="14d" @selected(($range ?? '') === '14d')>2 tuần</option>
                        <option value="1m" @selected(($range ?? '') === '1m')>1 tháng</option>
                        <option value="1y" @selected(($range ?? '') === '1y')>1 năm</option>
                    </select>
                </form>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ url()->full() }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate me-1"></i> Refresh
                </a>
                {{-- <a href="#" class="btn btn-primary">
                    <i class="fa-solid fa-file-export me-1"></i> Export
                </a> --}}
            </div>
        </div>

        {{-- KPI CARDS --}}
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Doanh thu ({{ $rangeLabel ?? 'Hôm nay' }})</div>
                                <div class="fs-4 fw-bold">{{ number_format($kpi['revenue']) }} đ</div>
                            </div>
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                style="width:48px;height:48px;">
                                <i class="fa-solid fa-sack-dollar fs-4"></i>
                            </div>
                        </div>
                        @php
                            $pct = (float) ($kpi['revenue_change_percent'] ?? 0);
                            $isUp = $pct >= 0;
                        @endphp

                        <div class="mt-2 small text-muted">
                            So với {{ $compareLabel ?? 'hôm qua' }}:
                            <span class="{{ $isUp ? 'text-success' : 'text-danger' }} fw-semibold">
                                {{ $isUp ? '+' : '' }}{{ $pct }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Đơn hàng</div>
                                <div class="fs-4 fw-bold">{{ number_format($kpi['orders']) }} đơn hàng</div>
                            </div>
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                style="width:48px;height:48px;">
                                <i class="fa-solid fa-cart-shopping fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">
                            Tỉ lệ chuyển đổi:
                            <span class="fw-semibold">{{ $kpi['conversion_rate'] ?? 0 }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Học viên mới</div>
                                <div class="fs-4 fw-bold">{{ number_format($kpi['new_students']) }} học viên</div>
                            </div>
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                style="width:48px;height:48px;">
                                <i class="fa-solid fa-user-plus fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-2 small text-muted">Tổng học viên: <span
                                class="fw-semibold">{{ number_format($kpi['student']) }} học viên</span></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Tổng khóa học</div>
                                <div class="fs-4 fw-bold">{{ number_format($kpi['courses_count'] ?? 0) }} khóa</div>
                            </div>
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                style="width:48px;height:48px;">
                                <i class="fa-solid fa-book fs-4"></i>
                            </div>
                        </div>

                        <div class="mt-2 small text-muted">
                            Tổng bài giảng:
                            <span class="fw-semibold">{{ number_format($kpi['lessons_count'] ?? 0) }} bài giảng</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div class="fw-semibold">{{ $chartTitle ?? 'Doanh thu' }}</div>
                        <div class="small text-muted">VNĐ</div>
                    </div>
                    <div class="card-body">
                        <canvas id="chartRevenue" height="110"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div class="fw-semibold">Trạng thái đơn hàng</div>
                        <div class="small text-muted">Tổng {{ $kpi['orders_count'] }} đơn hàng</div>
                    </div>
                    <div class="card-body">
                        <canvas id="chartOrderStatus" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div class="fw-semibold">Top khóa học bán chạy</div>
                        <div class="small text-muted">Số lượt mua: {{ $totalBuys }}</div>
                    </div>
                    <div class="card-body">
                        <canvas id="chartTopCourses" height="180"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div class="fw-semibold">Đơn hàng gần đây</div>
                        <a href="{{ route('orders.index') }}" class="small">Xem tất cả</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Mã</th>
                                        <th>Khách</th>
                                        <th class="text-end">Tổng</th>
                                        <th>Trạng thái</th>
                                        <th class="pe-3 text-end">Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrders as $od)
                                        @php
                                            $badge = match ((int) $od['status_id']) {
                                                2 => 'success', // đã thanh toán
                                                1 => 'warning', // chờ
                                                3 => 'secondary', // hủy
                                                4 => 'danger', // thất bại/hoàn tiền tùy bạn
                                                default => 'light',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ps-3 fw-semibold">{{ $od['code'] }}</td>
                                            <td>{{ $od['customer'] }}</td>
                                            <td class="text-end">{{ money($od['total']) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $badge }}">{{ $od['status'] }}</span>
                                            </td>
                                            <td class="pe-3 text-end text-muted small">
                                                {{ $od['created_at']->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($recentOrders->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Chưa có đơn hàng</td>
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

@push('styles')
    <style>
        .card {
            border-radius: 14px;
        }
    </style>
@endpush

@push('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        // ====== DATA từ PHP sang JS ======
        const revenueLabels = @json($revenueLabels);
        const revenueData = @json($revenueData);

        const orderStatus = {!! json_encode($orderStatus, JSON_UNESCAPED_UNICODE) !!};
        const topCourses = @json($topCourses);

        // ====== LINE: REVENUE ======
        new Chart(document.getElementById('chartRevenue'), {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Doanh thu',
                    data: revenueData,
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
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

        // ====== DOUGHNUT: ORDER STATUS ======
        new Chart(document.getElementById('chartOrderStatus'), {
            type: 'doughnut',
            data: {
                labels: orderStatus.labels,
                datasets: [{
                    label: 'Orders',
                    data: orderStatus.data
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // ====== BAR (Horizontal): TOP COURSES ======
        new Chart(document.getElementById('chartTopCourses'), {
            type: 'bar',
            data: {
                labels: topCourses.labels,
                datasets: [{
                    label: 'Số lượt mua',
                    data: topCourses.data
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
    </script>
@endpush
