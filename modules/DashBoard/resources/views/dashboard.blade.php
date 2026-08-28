@extends('layouts.backend')

@section('content')
    @php
        $pct = (float) ($kpi['revenue_change_percent'] ?? 0);
        $isUp = $pct >= 0;
        $orderRate = (float) ($kpi['conversion_rate'] ?? 0);
        $failedRate = (float) ($kpi['failed_rate'] ?? 0);
        $activeRangeLabel = mb_strtolower($rangeLabel ?? 'hôm nay', 'UTF-8');
        
        $grossRevenue = $kpi['gross_revenue'] ?? 0;
        $platformRevenue = $kpi['platform_revenue'] ?? 0;
        $teacherRevenue = $kpi['teacher_revenue'] ?? 0;
        $allocatedDiscount = $kpi['allocated_discount'] ?? 0;
    @endphp

    <div class="dashboard-shell">
        <section class="dashboard-hero card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-xl-5">
                <div class="row align-items-center g-4">
                    <div class="col-12 col-xl-8">
                        <span class="dashboard-kicker">Trung tâm điều hành</span>
                        <h3 class="dashboard-hero__title mb-3">Tổng quan vận hành trong {{ $activeRangeLabel }}</h3>
                        <p class="dashboard-hero__desc mb-4">
                            Theo dõi dòng tiền, tỷ lệ chuyển đổi, tăng trưởng học viên và các yêu cầu cần xử lý của hệ thống.
                        </p>

                        <div class="dashboard-financial-grid">
                            <div class="dashboard-highlight dashboard-highlight--gross">
                                <span class="dashboard-highlight__label">Tổng GMV (Gross)</span>
                                <strong>{{ $currency === 'ALL' ? ($grossRevenue == 0 ? '0 VND' : money($grossRevenue)) : number_format($grossRevenue) . ' ' . $currency }}</strong>
                                <small>Tăng trưởng: <span class="{{ $isUp ? 'text-success' : 'text-danger' }}">{{ $isUp ? '+' : '' }}{{ $pct }}%</span></small>
                            </div>
                            <div class="dashboard-highlight dashboard-highlight--net">
                                <span class="dashboard-highlight__label">Doanh thu nền tảng (Net)</span>
                                <strong>{{ $currency === 'ALL' ? ($platformRevenue == 0 ? '0 VND' : money($platformRevenue)) : number_format($platformRevenue) . ' ' . $currency }}</strong>
                                <small>Lợi nhuận gộp thực nhận</small>
                            </div>
                            <div class="dashboard-highlight dashboard-highlight--teacher">
                                <span class="dashboard-highlight__label">Thu nhập Giảng viên</span>
                                <strong>{{ $currency === 'ALL' ? ($teacherRevenue == 0 ? '0 VND' : money($teacherRevenue)) : number_format($teacherRevenue) . ' ' . $currency }}</strong>
                                <small>Đang chờ đối soát</small>
                            </div>
                            <div class="dashboard-highlight dashboard-highlight--discount">
                                <span class="dashboard-highlight__label">Tổng chiết khấu</span>
                                <strong>{{ $currency === 'ALL' ? ($allocatedDiscount == 0 ? '0 VND' : money($allocatedDiscount)) : number_format($allocatedDiscount) . ' ' . $currency }}</strong>
                                <small>Mã giảm giá đã dùng</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="dashboard-toolbar mb-3">
                            <form method="GET" class="dashboard-filter" id="dashboardFilterForm">
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label for="dashboard-currency" class="form-label mb-1">Tiền tệ</label>
                                        <select id="dashboard-currency" name="currency" class="form-select dashboard-select" onchange="this.form.submit()">
                                            <option value="ALL" @selected($currency === 'ALL')>Tất cả (Quy đổi Base)</option>
                                            @foreach($currencies as $c)
                                                <option value="{{ $c }}" @selected($currency === $c)>{{ $c }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label for="dashboard-range" class="form-label mb-1">Thời gian</label>
                                        <select id="dashboard-range" name="range" class="form-select dashboard-select" onchange="toggleCustomRange(this.value)">
                                            <option value="yesterday" @selected($range === 'yesterday')>Hôm qua</option>
                                            <option value="today" @selected($range === 'today')>Hôm nay</option>
                                            <option value="7d" @selected($range === '7d')>7 ngày qua</option>
                                            <option value="14d" @selected($range === '14d')>14 ngày qua</option>
                                            <option value="1m" @selected($range === '1m')>1 tháng qua</option>
                                            <option value="1y" @selected($range === '1y')>1 năm qua</option>
                                            <option value="custom" @selected($range === 'custom')>Tùy chỉnh</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div id="custom-range-inputs" class="row g-2 mb-3" style="display: {{ $range === 'custom' ? 'flex' : 'none' }};">
                                    <div class="col-6">
                                        <input type="date" name="start_date" id="start_date" class="form-control dashboard-select" value="{{ request('start_date', optional($from ?? null)->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" name="end_date" id="end_date" class="form-control dashboard-select" value="{{ request('end_date', optional($to ?? null)->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-12 mt-2 text-end">
                                        <button type="button" class="btn btn-primary btn-sm px-3" onclick="submitCustomRange()">Áp dụng</button>
                                    </div>
                                </div>

                                <div class="dashboard-range-buttons" role="group">
                                    <button type="button" onclick="setRange('yesterday')" class="btn {{ $range === 'yesterday' ? 'is-active' : '' }}">Hôm qua</button>
                                    <button type="button" onclick="setRange('today')" class="btn {{ $range === 'today' ? 'is-active' : '' }}">Hôm nay</button>
                                    <button type="button" onclick="setRange('7d')" class="btn {{ $range === '7d' ? 'is-active' : '' }}">7 ngày</button>
                                    <button type="button" onclick="setRange('1m')" class="btn {{ $range === '1m' ? 'is-active' : '' }}">1 tháng</button>
                                </div>
                            </form>
                            
                            <a href="{{ route('admin.index') }}" class="btn btn-light dashboard-refresh mt-2">
                                <i class="fa-solid fa-rotate me-1"></i> Làm mới số liệu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- ACTION ITEMS (To-do list) -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-danger-subtle border-danger text-danger overflow-hidden" style="border-radius: 16px;">
                    <div class="card-body p-3 d-flex align-items-center flex-wrap gap-4">
                        <div class="fw-bold d-flex align-items-center gap-2" style="font-size: 1.1rem; color: #dc3545;">
                            <i class="fa-solid fa-bell fa-shake"></i> Cần xử lý:
                        </div>
                        <div class="d-flex flex-wrap gap-4 align-items-center flex-grow-1 action-items-wrapper">
                            @if($actionItems['pending_payouts_count'] > 0)
                            <a href="#" class="action-item-link text-danger text-decoration-none d-flex align-items-center gap-2 fw-semibold">
                                <span class="badge bg-danger rounded-pill">{{ $actionItems['pending_payouts_count'] }}</span> 
                                Rút tiền ({{ $currency === 'ALL' ? money($actionItems['pending_payouts_amount']) : number_format($actionItems['pending_payouts_amount']) . ' ' . $currency }})
                            </a>
                            @else
                            <span class="action-item-done d-flex align-items-center gap-2"><i class="fa-solid fa-check-circle"></i> Đã duyệt hết rút tiền</span>
                            @endif

                            @if($actionItems['pending_teachers'] > 0)
                            <a href="{{ route('teacher.index') }}" class="action-item-link text-danger text-decoration-none d-flex align-items-center gap-2 fw-semibold">
                                <span class="badge bg-danger rounded-pill">{{ $actionItems['pending_teachers'] }}</span> 
                                Giảng viên
                            </a>
                            @else
                            <span class="action-item-done d-flex align-items-center gap-2"><i class="fa-solid fa-check-circle"></i> Đã duyệt hết giảng viên</span>
                            @endif

                            @if($actionItems['pending_courses'] > 0)
                            <a href="{{ route('courses.index') }}" class="action-item-link text-danger text-decoration-none d-flex align-items-center gap-2 fw-semibold">
                                <span class="badge bg-danger rounded-pill">{{ $actionItems['pending_courses'] }}</span> 
                                Khóa học
                            </a>
                            @else
                            <span class="action-item-done d-flex align-items-center gap-2"><i class="fa-solid fa-check-circle"></i> Không có khóa học chờ duyệt</span>
                            @endif

                            @if($actionItems['pending_contacts'] > 0)
                            <a href="{{ route('contacts.index') ?? '#' }}" class="action-item-link text-danger text-decoration-none d-flex align-items-center gap-2 fw-semibold">
                                <span class="badge bg-danger rounded-pill">{{ $actionItems['pending_contacts'] }}</span> 
                                Liên hệ mới
                            </a>
                            @endif

                            @if(!empty($actionItems['health_alerts']))
                                @foreach($actionItems['health_alerts'] as $alert)
                                <a href="{{ $alert['link'] }}" class="action-item-link text-danger text-decoration-none d-flex align-items-center gap-2 fw-bold pulse-alert" title="{{ $alert['desc'] }}">
                                    <span class="badge bg-danger"><i class="{{ $alert['icon'] }}"></i></span> 
                                    {{ $alert['label'] }}
                                </a>
                                @endforeach
                            @endif

                            @if($actionItems['pending_reports'] > 0)
                            <a href="{{ route('comments.index') ?? '#' }}" class="action-item-link text-danger text-decoration-none d-flex align-items-center gap-2 fw-semibold">
                                <span class="badge bg-danger rounded-pill">{{ $actionItems['pending_reports'] }}</span> 
                                Góp ý / Báo cáo
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GROWTH & CONVERSION (Stat Cards) -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <article class="dashboard-stat dashboard-stat--revenue card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="dashboard-stat__icon">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                        <p class="dashboard-stat__label">Đơn hàng hoàn tất</p>
                        <h4 class="dashboard-stat__value">{{ number_format($kpi['orders'] ?? 0) }}</h4>
                        <p class="dashboard-stat__meta mb-0 fw-semibold text-success">
                            Tỷ lệ chuyển đổi: {{ $orderRate }}%
                        </p>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <article class="dashboard-stat card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="dashboard-stat__icon" style="background:#fee2e2; color:#ef4444;">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>
                        <p class="dashboard-stat__label">Đơn lỗi / Hủy</p>
                        <h4 class="dashboard-stat__value">{{ number_format($kpi['failed_orders'] ?? 0) }}</h4>
                        <p class="dashboard-stat__meta mb-0 fw-semibold text-danger">Tỷ lệ lỗi: {{ $failedRate }}%</p>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <article class="dashboard-stat card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="dashboard-stat__icon" style="background:#e0e7ff; color:#4f46e5;">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                        <p class="dashboard-stat__label">Học viên mới</p>
                        <h4 class="dashboard-stat__value">{{ number_format($kpi['new_students'] ?? 0) }}</h4>
                        <p class="dashboard-stat__meta mb-0">Tổng đang HĐ: {{ number_format($kpi['total_students'] ?? 0) }}</p>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <article class="dashboard-stat card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="dashboard-stat__icon" style="background:#ffedd5; color:#ea580c;">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <p class="dashboard-stat__label">Giảng viên mới</p>
                        <h4 class="dashboard-stat__value">{{ number_format($kpi['new_teachers'] ?? 0) }}</h4>
                        <p class="dashboard-stat__meta mb-0">Tổng đang HĐ: {{ number_format($kpi['total_teachers'] ?? 0) }}</p>
                    </div>
                </article>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-bold fs-5">{{ $chartTitle ?? 'Doanh thu' }}</div>
                            <div class="small text-muted">Biên độ lợi nhuận theo ngày (Gross vs Net)</div>
                        </div>
                        <span class="dashboard-chip">{{ $currency === 'ALL' ? 'Base Currency' : $currency }}</span>
                    </div>
                    <div class="card-body pt-0 px-4 pb-4">
                        <canvas id="chartRevenue"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-bold fs-5">Trạng thái đơn hàng</div>
                            <div class="small text-muted">Tổng {{ number_format($kpi['orders_count'] ?? 0) }} đơn hàng được tạo</div>
                        </div>
                    </div>
                    <div class="card-body pt-0 px-4 pb-4">
                        <canvas id="chartOrderStatus"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLES 8-4 Layout -->
        <div class="row g-3">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-bold fs-5">Đơn hàng gần đây</div>
                            <div class="small text-muted">Danh sách giao dịch mới nhất trong khoảng đang xem (AOV: {{ $currency === 'ALL' ? money($kpi['aov']) : number_format($kpi['aov']) . ' ' . $currency }})</div>
                        </div>
                        <a href="{{ route('orders.index') }}" class="small text-decoration-none fw-semibold">Xem tất cả</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive dashboard-orders-table" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light sticky-top">
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
                                            <td class="text-end fw-bold">{{ $currency === 'ALL' ? money($od['total']) : number_format($od['total']) . ' ' . $od['currency'] }}</td>
                                            <td><span class="badge rounded-pill bg-{{ $badge }}">{{ $od['status'] }}</span></td>
                                            <td class="pe-4 text-end text-muted small">{{ $od['created_at']->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                    @if ($recentOrders->isEmpty())
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">Chưa có giao dịch nào</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <ul class="nav nav-tabs border-0" id="topPerformersTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold border-0 bg-transparent custom-tab-btn" id="top-courses-tab" data-bs-toggle="tab" data-bs-target="#top-courses" type="button" role="tab">Top Khóa học ({{ $kpi['top_courses_count'] ?? 0 }})</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold border-0 bg-transparent text-muted" id="top-teachers-tab" data-bs-toggle="tab" data-bs-target="#top-teachers" type="button" role="tab">Top Giảng viên</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold border-0 bg-transparent text-muted" id="top-packages-tab" data-bs-toggle="tab" data-bs-target="#top-packages" type="button" role="tab">Top Gói</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body pt-3 px-4 pb-4">
                        <div class="tab-content" id="topPerformersTabContent">
                            <div class="tab-pane fade show active" id="top-courses" role="tabpanel">
                                <div class="mt-2">
                                    @foreach($topCourses['labels'] as $idx => $label)
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="top-rank-badge top-course-badge rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">{{ $idx + 1 }}</div>
                                            <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $topCourses['slugs'][$idx] ?? '#']) }}" target="_blank" class="fw-semibold text-truncate text-decoration-none text-dark" style="max-width: 180px;">{{ $label }}</a>
                                        </div>
                                        <div class="fw-bold text-success">{{ $topCourses['data'][$idx] }} lượt mua</div>
                                    </div>
                                    @endforeach
                                    @if(empty($topCourses['labels']) || count($topCourses['labels']) === 0)
                                        <div class="text-center py-4">
                                            <i class="fa-solid fa-book-open text-muted mb-2 d-block" style="font-size: 1.2rem; opacity: 0.3;"></i>
                                            <div class="text-muted small fw-medium">Chưa có khóa học nào được mua</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="tab-pane fade" id="top-teachers" role="tabpanel">
                                <div class="mt-2">
                                    @foreach($topTeachers as $idx => $t)
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="top-rank-badge top-teacher-badge rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">{{ $idx + 1 }}</div>
                                            <a href="{{ route('teacher.public.show', ['locale' => app()->getLocale(), 'slug' => $t['slug'] ?? '#']) }}" target="_blank" class="fw-semibold text-truncate text-decoration-none text-dark" style="max-width: 150px;">{{ $t['name'] }}</a>
                                        </div>
                                        <div class="fw-bold text-primary">{{ $currency === 'ALL' ? money($t['revenue']) : number_format($t['revenue']) }}</div>
                                    </div>
                                    @endforeach
                                    @if(empty($topTeachers))
                                        <div class="text-center py-4">
                                            <i class="fa-solid fa-chalkboard-user text-muted mb-2 d-block" style="font-size: 1.2rem; opacity: 0.3;"></i>
                                            <div class="text-muted small fw-medium">Chưa có dữ liệu giảng viên</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="tab-pane fade" id="top-packages" role="tabpanel">
                                <div class="mt-2">
                                    @foreach($topPackages['labels'] as $idx => $label)
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="top-rank-badge top-package-badge rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">{{ $idx + 1 }}</div>
                                            <span class="fw-semibold text-truncate text-dark" style="max-width: 150px;">{{ $label }}</span>
                                        </div>
                                        <div class="fw-bold text-success">{{ $topPackages['data'][$idx] }} lượt mua</div>
                                    </div>
                                    @endforeach
                                    @if(empty($topPackages['labels']) || count($topPackages['labels']) === 0)
                                        <div class="text-center py-4">
                                            <i class="fa-solid fa-box-open text-muted mb-2 d-block" style="font-size: 1.2rem; opacity: 0.3;"></i>
                                            <div class="text-muted small fw-medium">Chưa có gói nào được mua</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
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
            padding-bottom: 2rem;
            background: #f8fafc;
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
            font-size: clamp(1.6rem, 2vw, 2.2rem);
            font-weight: 800;
            line-height: 1.2;
            color: #f8fafc !important;
        }

        .dashboard-hero__desc {
            max-width: 680px;
            color: rgba(255, 255, 255, 0.82) !important;
        }

        .dashboard-financial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .dashboard-highlight {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            padding: 1.25rem 1.1rem;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-highlight--gross::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; background:#60a5fa; }
        .dashboard-highlight--net::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; background:#34d399; }
        .dashboard-highlight--teacher::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; background:#fbbf24; }
        .dashboard-highlight--discount::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; background:#f87171; }

        .top-course-badge { background: #e0e7ff; color: #4f46e5; }
        .top-teacher-badge { background: #ffedd5; color: #ea580c; }
        .top-package-badge { background: #d1fae5; color: #059669; }

        html[data-theme='dark'] .top-course-badge { background: rgba(79, 70, 229, 0.2); color: #a5b4fc; }
        html[data-theme='dark'] .top-teacher-badge { background: rgba(234, 88, 12, 0.2); color: #fdba74; }
        html[data-theme='dark'] .top-package-badge { background: rgba(5, 150, 105, 0.2); color: #6ee7b7; }

        .dashboard-highlight strong {
            display: block;
            font-size: 1.45rem;
            line-height: 1.3;
            margin: 0.35rem 0;
            color: #f8fafc !important;
            font-weight: 800;
        }

        .dashboard-highlight small {
            color: rgba(255, 255, 255, 0.78) !important;
        }

        .custom-tab-btn {
            color: #64748b !important;
        }
        .custom-tab-btn.active {
            color: #0f172a !important;
        }
        html[data-theme='dark'] .custom-tab-btn.active {
            color: #f8fafc !important;
        }
        html[data-theme='dark'] .card-header.bg-white {
            background-color: transparent !important;
        }
        html[data-theme='dark'] .tab-content .text-dark {
            color: #e2e8f0 !important;
        }
        html[data-theme='dark'] .tab-content .border-bottom {
            border-color: rgba(148, 163, 184, 0.1) !important;
        }
        .dashboard-highlight__label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.66) !important;
        }

        .dashboard-toolbar {
            background: rgba(255,255,255,0.05);
            border-radius: 18px;
            padding: 1.25rem;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .dashboard-filter label {
            color: rgba(255, 255, 255, 0.78) !important;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .dashboard-select {
            background: rgba(255,255,255,0.9) !important;
            color: #0f172a !important;
            border: 0;
            min-height: 42px;
            border-radius: 10px;
            font-weight: 600;
        }

        .dashboard-range-buttons {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .dashboard-range-buttons .btn {
            min-height: 38px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.05);
            color: #f8fafc;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .dashboard-range-buttons .btn.is-active, .dashboard-range-buttons .btn:hover {
            background: #ffffff;
            color: #0f172a;
            border-color: #ffffff;
        }

        .dashboard-refresh {
            width: 100%;
            border-radius: 10px;
            font-weight: 700;
            padding: 0.6rem;
            background: rgba(255,255,255,0.1) !important;
            color: white !important;
            border: 1px solid rgba(255,255,255,0.2) !important;
        }
        .dashboard-refresh:hover {
            background: rgba(255,255,255,0.2) !important;
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
            font-weight: 600;
        }

        .dashboard-stat__value {
            margin-bottom: 0.35rem;
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
        }

        .dashboard-stat__meta {
            color: #64748b;
            font-size: 0.85rem;
        }

        .dashboard-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        #chartRevenue,
        #chartOrderStatus {
            width: 100% !important;
            min-height: 320px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            padding-bottom: 12px;
        }
        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #1d4ed8;
            color: #1d4ed8 !important;
            background: transparent;
        }

        html[data-theme="dark"] .dashboard-shell {
            background: var(--admin-bg);
        }

        html[data-theme="dark"] .dashboard-stat, html[data-theme="dark"] .card {
            background: linear-gradient(180deg, rgba(17, 24, 39, 0.96) 0%, rgba(15, 23, 42, 0.98) 100%);
            border-color: rgba(255,255,255,0.05) !important;
        }
        
        html[data-theme="dark"] .card-header {
            background: transparent !important;
        }

        html[data-theme="dark"] .dashboard-stat__label,
        html[data-theme="dark"] .dashboard-stat__meta,
        html[data-theme="dark"] .text-muted,
        html[data-theme="dark"] .table {
            color: #9fb0c7 !important;
        }

        html[data-theme="dark"] .dashboard-stat__value, html[data-theme="dark"] .fw-bold, html[data-theme="dark"] .nav-link.active {
            color: #f8fafc !important;
        }
        
        html[data-theme="dark"] .table-light {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }

        .action-item-done {
            color: rgba(220, 53, 69, 0.6);
        }

        html[data-theme="dark"] .top-rank-badge { 
            background: rgba(79, 70, 229, 0.2) !important; 
            color: #818cf8 !important; 
        }
        
        html[data-theme="dark"] .top-teacher-badge { 
            background: rgba(234, 88, 12, 0.2) !important; 
            color: #fb923c !important; 
        }

        html[data-theme="dark"] .action-items-wrapper .action-item-link {
            color: #ff8787 !important;
            text-shadow: 0 0 1px rgba(255, 135, 135, 0.3);
        }
        html[data-theme="dark"] .action-items-wrapper .action-item-link .badge {
            background-color: #fa5252 !important;
            color: #fff !important;
        }
        html[data-theme="dark"] .action-items-wrapper .action-item-done {
            color: rgba(255, 135, 135, 0.5) !important;
        }
        
        html[data-theme="dark"] .tab-pane a.text-dark {
            color: #f8fafc !important;
        }

        @keyframes pulse-red {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(0.98); }
            100% { opacity: 1; transform: scale(1); }
        }
        .pulse-alert {
            animation: pulse-red 2s infinite ease-in-out;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function setRange(val) {
            document.getElementById('dashboard-range').value = val;
            document.getElementById('dashboardFilterForm').submit();
        }
        
        function toggleCustomRange(val) {
            if(val === 'custom') {
                document.getElementById('custom-range-inputs').style.display = 'flex';
            } else {
                document.getElementById('custom-range-inputs').style.display = 'none';
                document.getElementById('dashboardFilterForm').submit();
            }
        }
        
        function submitCustomRange() {
            let start = document.getElementById('start_date').value;
            let end = document.getElementById('end_date').value;
            if(!start || !end) {
                alert('Vui lòng chọn đầy đủ Từ ngày và Đến ngày');
                return;
            }
            if(new Date(start) > new Date(end)) {
                alert('Ngày bắt đầu không được lớn hơn Ngày kết thúc!');
                return;
            }
            document.getElementById('dashboardFilterForm').submit();
        }

        const revenueLabels = @json($revenueLabels);
        const grossData = @json($revenueData['gross']);
        const netData = @json($revenueData['net']);
        const orderStatus = {!! json_encode($orderStatus, JSON_UNESCAPED_UNICODE) !!};

        new Chart(document.getElementById('chartRevenue'), {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [
                    {
                        label: 'Gross (Tổng GMV)',
                        data: grossData,
                        tension: 0.35,
                        fill: false,
                        borderColor: '#60a5fa',
                        borderDash: [5, 5],
                        pointBackgroundColor: '#60a5fa',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Net (Doanh thu Nền tảng)',
                        data: netData,
                        tension: 0.35,
                        fill: true,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, boxWidth: 8 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: (ctx) => {
                                const v = ctx.parsed.y ?? 0;
                                return ctx.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(v);
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
                    data: orderStatus.data,
                    backgroundColor: ['#2563eb', '#14b8a6', '#f59e0b', '#ef4444', '#8b5cf6', '#94a3b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        
        // Handle tab switching styling
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (e) {
                document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(b => {
                    b.classList.remove('text-dark', 'text-white');
                    b.classList.add('text-muted');
                });
                e.target.classList.remove('text-muted');
                e.target.classList.add(document.documentElement.getAttribute('data-theme') === 'dark' ? 'text-white' : 'text-dark');
            })
        });
    </script>
@endpush
