@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1 fw-bold text-primary">{{ __('finances::admin.titles.earnings') }}</h5>
                    <p class="text-muted mb-0">Theo dõi doanh thu chi tiết, phân bổ chiết khấu và lợi nhuận nền tảng.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('teacher-finance.earnings.export', array_merge(['format' => 'csv'], request()->query())) }}" class="btn btn-outline-primary px-3 rounded-pill">
                        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                    </a>
                    <a href="{{ route('teacher-finance.earnings.export', array_merge(['format' => 'excel'], request()->query())) }}" class="btn btn-primary px-3 rounded-pill">
                        <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                    </a>
                </div>
            </div>

            <form method="GET" class="row g-3 mb-4 p-3 bg-light rounded-3 shadow-sm mx-1">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tiền tệ</label>
                    <select name="currency" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="ALL" @selected($currency === 'ALL')>Tất cả (Quy đổi Base)</option>
                        @foreach ($currencies as $c)
                            <option value="{{ $c }}" @selected($currency === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('finances::admin.filters.teacher') }}</label>
                    <select name="teacher_id" class="form-select rounded-pill">
                        <option value="">{{ __('finances::admin.filters.all_teachers') }}</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ (string) request('teacher_id') === (string) $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name_locale ?: $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('finances::admin.filters.from_date') }}</label>
                    <input type="date" name="from_date" class="form-control rounded-pill" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('finances::admin.filters.to_date') }}</label>
                    <input type="date" name="to_date" class="form-control rounded-pill" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100 rounded-pill"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                </div>
            </form>

            @php
                $fmt = function($val) use ($currency) {
                    return $currency === 'ALL' ? money($val) : number_format($val) . ' ' . $currency;
                };
            @endphp

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="teacher-dashboard-card p-3 bg-white border rounded-3 shadow-sm">
                        <div class="teacher-dashboard-card__label text-muted small fw-bold text-uppercase">Gross Revenue</div>
                        <div class="teacher-dashboard-card__value fs-4 fw-bold text-dark mt-1">{{ $fmt($summary['gross_amount'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="teacher-dashboard-card p-3 bg-white border rounded-3 shadow-sm">
                        <div class="teacher-dashboard-card__label text-muted small fw-bold text-uppercase">Allocated Discount</div>
                        <div class="teacher-dashboard-card__value fs-4 fw-bold text-danger mt-1">{{ $fmt($summary['allocated_discount'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="teacher-dashboard-card p-3 bg-white border rounded-3 shadow-sm">
                        <div class="teacher-dashboard-card__label text-muted small fw-bold text-uppercase">Net Revenue</div>
                        <div class="teacher-dashboard-card__value fs-4 fw-bold text-success mt-1">{{ $fmt($summary['net_revenue'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="teacher-dashboard-card p-3 bg-white border rounded-3 shadow-sm">
                        <div class="teacher-dashboard-card__label text-muted small fw-bold text-uppercase">Teacher Payout</div>
                        <div class="teacher-dashboard-card__value fs-4 fw-bold text-primary mt-1">{{ $fmt($summary['teacher_revenue'] ?? 0) }}</div>
                    </div>
                </div>
            </div>

            @if($teacherSummaries->isNotEmpty())
            <div class="card mb-4 border rounded-3 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="fa-solid fa-users me-1 text-primary"></i> Tổng hợp theo từng giảng viên</h6>
                        <span class="badge bg-secondary rounded-pill">{{ $teacherSummaries->count() }} giảng viên</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('finances::admin.table.teacher') }}</th>
                                <th>Số đơn</th>
                                <th class="text-end">Gross</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Net</th>
                                <th class="text-end">Teacher Share</th>
                                <th class="text-end pe-3">Platform Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teacherSummaries as $row)
                                <tr>
                                    <td class="ps-3 fw-bold">{{ $row['teacher']?->name_locale ?: '-' }}</td>
                                    <td>{{ $row['orders_count'] }}</td>
                                    <td class="text-end fw-semibold">{{ $fmt($row['gross_amount']) }}</td>
                                    <td class="text-end text-danger">-{{ $fmt($row['allocated_discount']) }}</td>
                                    <td class="text-end">{{ $fmt($row['net_revenue']) }}</td>
                                    <td class="text-end text-success fw-bold">{{ $fmt($row['teacher_revenue']) }}</td>
                                    <td class="text-end fw-bold pe-3 text-primary">{{ $fmt($row['platform_revenue']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <div class="card border rounded-3 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-list me-1 text-primary"></i> Danh sách giao dịch chi tiết</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Đơn hàng</th>
                                <th>{{ __('finances::admin.table.teacher') }}</th>
                                <th>Khóa học</th>
                                <th class="text-end">Gross</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Net</th>
                                <th class="text-center">Commission</th>
                                <th class="text-end">Teacher Share</th>
                                <th class="text-end pe-3">Platform Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php
                                    $itemCurrency = $item->order?->currency ?: 'VND';
                                    
                                    $grossAmount = $item->finance_breakdown['gross_amount'];
                                    $allocatedDiscount = $item->finance_breakdown['allocated_discount'];
                                    $netRevenue = $item->finance_breakdown['net_revenue'];
                                    $teacherRevenue = $item->finance_breakdown['teacher_revenue'];
                                    $platformRevenue = $item->finance_breakdown['platform_revenue'];
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold">#{{ $item->order?->code }}</div>
                                        <div class="small text-muted">{{ optional($item->order?->payment_complete_date ?: $item->order?->created_at)->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td>{{ $item->courses?->teacher?->name_locale ?: '-' }}</td>
                                    <td class="text-truncate" style="max-width: 200px;">{{ $item->courses?->name_locale ?: '-' }}</td>
                                    <td class="text-end">{{ $currency === 'ALL' ? money($grossAmount) : number_format($grossAmount) . ' ' . $itemCurrency }}</td>
                                    <td class="text-end text-danger">-{{ $currency === 'ALL' ? money($allocatedDiscount) : number_format($allocatedDiscount) . ' ' . $itemCurrency }}</td>
                                    <td class="text-end">{{ $currency === 'ALL' ? money($netRevenue) : number_format($netRevenue) . ' ' . $itemCurrency }}</td>
                                    <td class="text-center fw-bold text-secondary">{{ rtrim(rtrim(number_format($item->finance_breakdown['commission_rate'], 2, '.', ''), '0'), '.') }}%</td>
                                    <td class="text-end text-success fw-bold">{{ $currency === 'ALL' ? money($teacherRevenue) : number_format($teacherRevenue) . ' ' . $itemCurrency }}</td>
                                    <td class="text-end fw-bold text-primary pe-3">{{ $currency === 'ALL' ? money($platformRevenue) : number_format($platformRevenue) . ' ' . $itemCurrency }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">Chưa có giao dịch nào hợp lệ để đối soát.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($items->hasPages())
                    <div class="card-footer bg-white py-3">
                        {{ $items->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
