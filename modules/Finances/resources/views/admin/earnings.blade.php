@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">{{ __('finances::admin.titles.earnings') }}</h5>
                    <p class="text-muted mb-0">Tính net theo tỷ lệ discount phân bổ trên từng order detail và commission của từng giảng viên.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('teacher-finance.earnings.export', array_merge(['format' => 'csv'], request()->query())) }}" class="btn btn-outline-primary">
                        Export CSV
                    </a>
                    <a href="{{ route('teacher-finance.earnings.export', array_merge(['format' => 'excel'], request()->query())) }}" class="btn btn-primary">
                        Export Excel
                    </a>
                </div>
            </div>

            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">{{ __('finances::admin.filters.teacher') }}</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">{{ __('finances::admin.filters.all_teachers') }}</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ (string) request('teacher_id') === (string) $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name_locale ?: $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('finances::admin.filters.from_date') }}</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('finances::admin.filters.to_date') }}</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">{{ __('finances::admin.filters.filter_button') }}</button>
                </div>
            </form>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="teacher-dashboard-card">
                        <div class="teacher-dashboard-card__label">{{ __('finances::admin.summary.gross') }}</div>
                        <div class="teacher-dashboard-card__value">{{ money($summary['gross_amount']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="teacher-dashboard-card">
                        <div class="teacher-dashboard-card__label">{{ __('finances::admin.summary.discount') }}</div>
                        <div class="teacher-dashboard-card__value">{{ money($summary['allocated_discount']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="teacher-dashboard-card">
                        <div class="teacher-dashboard-card__label">{{ __('finances::admin.summary.net') }}</div>
                        <div class="teacher-dashboard-card__value">{{ money($summary['net_revenue']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="teacher-dashboard-card">
                        <div class="teacher-dashboard-card__label">{{ __('finances::admin.summary.teacher_revenue') }}</div>
                        <div class="teacher-dashboard-card__value">{{ money($summary['teacher_revenue']) }}</div>
                    </div>
                </div>
            </div>

            <div class="teacher-dashboard-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Tổng hợp theo từng giảng viên</h6>
                    <span class="text-muted small">{{ $teacherSummaries->count() }} giảng viên</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('finances::admin.table.teacher') }}</th>
                                <th>Số đơn</th>
                                <th>Gross</th>
                                <th>Discount</th>
                                <th>Net</th>
                                <th>Teacher</th>
                                <th>Platform</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teacherSummaries as $row)
                                <tr>
                                    <td>{{ $row['teacher']?->name_locale ?: '-' }}</td>
                                    <td>{{ $row['orders_count'] }}</td>
                                    <td>{{ money($row['gross_amount']) }}</td>
                                    <td class="text-danger">-{{ money($row['allocated_discount']) }}</td>
                                    <td>{{ money($row['net_revenue']) }}</td>
                                    <td class="text-success fw-semibold">{{ money($row['teacher_revenue']) }}</td>
                                    <td>{{ money($row['platform_revenue']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Đơn</th>
                            <th>{{ __('finances::admin.table.teacher') }}</th>
                            <th>Khóa học</th>
                            <th>Gross</th>
                            <th>Discount</th>
                            <th>Net</th>
                            <th>Commission</th>
                            <th>Teacher</th>
                            <th>Platform</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    #{{ $item->order?->code }}
                                    <div class="small text-muted">{{ optional($item->order?->payment_complete_date ?: $item->order?->created_at)->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>{{ $item->courses?->teacher?->name_locale ?: '-' }}</td>
                                <td>{{ $item->courses?->name_locale ?: '-' }}</td>
                                <td>{{ money($item->finance_breakdown['gross_amount']) }}</td>
                                <td class="text-danger">-{{ money($item->finance_breakdown['allocated_discount']) }}</td>
                                <td>{{ money($item->finance_breakdown['net_revenue']) }}</td>
                                <td>{{ rtrim(rtrim(number_format($item->finance_breakdown['commission_rate'], 2, '.', ''), '0'), '.') }}%</td>
                                <td class="text-success fw-semibold">{{ money($item->finance_breakdown['teacher_revenue']) }}</td>
                                <td>{{ money($item->finance_breakdown['platform_revenue']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Chưa có giao dịch nào hợp lệ để đối soát.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection
