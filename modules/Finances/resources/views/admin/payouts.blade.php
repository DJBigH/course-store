@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">{{ __('finances::admin.titles.payouts') }}</h5>
                    <p class="text-muted mb-0">Cập nhật payout request và duyệt yêu cầu thay đổi tài khoản ngân hàng.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('teacher-finance.payouts.export', array_merge(['format' => 'csv'], request()->query())) }}" class="btn btn-outline-primary">
                        Export CSV
                    </a>
                    <a href="{{ route('teacher-finance.payouts.export', array_merge(['format' => 'excel'], request()->query())) }}" class="btn btn-primary">
                        Export Excel
                    </a>
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">{{ __('finances::admin.filters.payout_status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('finances::admin.filters.all') }}</option>
                        @foreach (['requested' => 'Requested', 'processing' => 'Processing', 'paid' => 'Paid', 'rejected' => 'Rejected'] as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('finances::admin.filters.account_change_status') }}</label>
                    <select name="account_change_status" class="form-select">
                        <option value="">{{ __('finances::admin.filters.all') }}</option>
                        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                            <option value="{{ $value }}" {{ request('account_change_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">{{ __('finances::admin.filters.filter_button') }}</button>
                </div>
            </form>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="teacher-dashboard-card">
                        <div class="teacher-dashboard-card__label">{{ __('finances::admin.summary.requested') }}</div>
                        <div class="teacher-dashboard-card__value">{{ money($summary['requested']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="teacher-dashboard-card">
                        <div class="teacher-dashboard-card__label">{{ __('finances::admin.summary.processing') }}</div>
                        <div class="teacher-dashboard-card__value">{{ money($summary['processing']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="teacher-dashboard-card">
                        <div class="teacher-dashboard-card__label">{{ __('finances::admin.summary.paid') }}</div>
                        <div class="teacher-dashboard-card__value">{{ money($summary['paid']) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="teacher-dashboard-card">
                        <div class="teacher-dashboard-card__label">{{ __('finances::admin.summary.account_change_pending') }}</div>
                        <div class="teacher-dashboard-card__value">{{ $summary['account_change_pending'] }}</div>
                    </div>
                </div>
            </div>

            <div class="teacher-dashboard-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Tổng hợp payout theo từng giảng viên</h6>
                    <span class="text-muted small">{{ $teacherSummaries->count() }} giảng viên</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('finances::admin.table.teacher') }}</th>
                                <th>Requested</th>
                                <th>Processing</th>
                                <th>Paid</th>
                                <th>Rejected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teacherSummaries as $row)
                                <tr>
                                    <td>{{ $row['teacher']?->name_locale ?: '-' }}</td>
                                    <td>{{ money($row['requested']) }}</td>
                                    <td>{{ money($row['processing']) }}</td>
                                    <td>{{ money($row['paid']) }}</td>
                                    <td>{{ money($row['rejected']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Yêu cầu đổi tài khoản ngân hàng</h6>
                        <span class="text-muted small">{{ $accountChangeRequests->total() }} yêu cầu</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('finances::admin.table.id') }}</th>
                                    <th>{{ __('finances::admin.table.teacher') }}</th>
                                    <th>{{ __('finances::admin.table.replace_account') }}</th>
                                    <th>{{ __('finances::admin.table.new_account') }}</th>
                                    <th>{{ __('finances::admin.table.status') }}</th>
                                    <th>{{ __('finances::admin.table.note') }}</th>
                                    <th class="text-end">{{ __('finances::admin.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($accountChangeRequests as $item)
                                    <tr>
                                        <td>#{{ $item->id }}</td>
                                        <td>
                                            {{ $item->teacher?->name_locale ?: '-' }}
                                            <div class="small text-muted">{{ $item->teacher?->student?->email ?: '-' }}</div>
                                        </td>
                                        <td>
                                            @if ($item->replace_bank_name)
                                                {{ $item->replace_bank_name }}<br>
                                                <small class="text-muted">{{ $item->replace_bank_account_name }} - {{ $item->replace_bank_account_number }}</small>
                                            @else
                                                <span class="text-muted">Không còn tài khoản gốc</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $item->bank_name }}<br>
                                            <small class="text-muted">{{ $item->bank_account_name }} - {{ $item->bank_account_number }}</small>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($item->status) }}</span></td>
                                        <td style="min-width: 260px;">
                                            <form method="POST" action="{{ route('teacher-finance.payout-account-change-requests.update', $item->id) }}">
                                                @csrf
                                                <select name="status" class="form-select form-select-sm mb-2">
                                                    @foreach (['approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                                                        <option value="{{ $value }}" {{ $item->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <textarea name="admin_note" class="form-control form-control-sm" rows="2"
                                                    placeholder="{{ __('finances::admin.placeholders.admin_note') }}">{{ $item->admin_note }}</textarea>
                                        </td>
                                        <td class="text-end align-top">
                                                <button class="btn btn-sm btn-primary" {{ $item->status !== 'pending' ? 'disabled' : '' }}>Lưu</button>
                                                <div class="small text-muted mt-2">
                                                    {{ optional($item->processed_at)->format('d/m/Y H:i') ?: optional($item->created_at)->format('d/m/Y H:i') }}
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Chưa có yêu cầu đổi tài khoản nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $accountChangeRequests->links() }}
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('finances::admin.table.id') }}</th>
                            <th>{{ __('finances::admin.table.teacher') }}</th>
                            <th>{{ __('finances::admin.table.amount') }}</th>
                            <th>{{ __('finances::admin.table.bank_info') }}</th>
                            <th>{{ __('finances::admin.table.status') }}</th>
                            <th>{{ __('finances::admin.table.note') }}</th>
                            <th class="text-end">{{ __('finances::admin.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payouts as $payout)
                            <tr>
                                <td>#{{ $payout->id }}</td>
                                <td>
                                    {{ $payout->teacher?->name_locale ?: '-' }}
                                    <div class="small text-muted">{{ $payout->teacher?->student?->email ?: '-' }}</div>
                                </td>
                                <td class="fw-semibold">{{ money($payout->amount) }}</td>
                                <td>
                                    {{ $payout->bank_name }}<br>
                                    <small class="text-muted">{{ $payout->bank_account_name }} - {{ $payout->bank_account_number }}</small>
                                </td>
                                <td><span class="badge bg-info">{{ ucfirst($payout->status) }}</span></td>
                                <td style="min-width: 260px;">
                                    <form method="POST" action="{{ route('teacher-finance.payouts.update', $payout->id) }}">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm mb-2">
                                            @foreach (['processing' => 'Processing', 'paid' => 'Paid', 'rejected' => 'Rejected'] as $value => $label)
                                                <option value="{{ $value }}" {{ $payout->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="admin_note" class="form-control form-control-sm" rows="2"
                                            placeholder="{{ __('finances::admin.placeholders.admin_note') }}">{{ $payout->admin_note }}</textarea>
                                </td>
                                <td class="text-end align-top">
                                        <button class="btn btn-sm btn-primary">Lưu</button>
                                        <div class="small text-muted mt-2">
                                            {{ optional($payout->processed_at)->format('d/m/Y H:i') ?: optional($payout->created_at)->format('d/m/Y H:i') }}
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Chưa có yêu cầu rút tiền nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payouts->links() }}
            </div>
        </div>
    </div>
@endsection
