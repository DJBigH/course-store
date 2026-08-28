@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-4">
            <!-- Header -->
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1 fw-bold text-primary">{{ __('finances::admin.titles.payouts') }}</h5>
                    <p class="text-muted mb-0">Quản lý yêu cầu rút tiền và phê duyệt thay đổi thông tin ngân hàng của giảng viên.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('teacher-finance.payouts.export', array_merge(['format' => 'csv'], request()->query())) }}" class="btn btn-outline-primary rounded-pill px-3">
                        <i class="fa-solid fa-file-csv me-1"></i> Export CSV
                    </a>
                    <a href="{{ route('teacher-finance.payouts.export', array_merge(['format' => 'excel'], request()->query())) }}" class="btn btn-primary rounded-pill px-3">
                        <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                    </a>
                </div>
            </div>

            <!-- Summary Cards Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="p-3 bg-white border rounded-3 shadow-sm d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-hourglass-half fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">{{ __('finances::admin.summary.requested') }}</div>
                            <div class="fs-5 fw-bold text-dark mt-1">{{ $summary['requested'] > 0 ? money($summary['requested']) : '0 đ' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-white border rounded-3 shadow-sm d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-spinner fa-spin fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">{{ __('finances::admin.summary.processing') }}</div>
                            <div class="fs-5 fw-bold text-dark mt-1">{{ $summary['processing'] > 0 ? money($summary['processing']) : '0 đ' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-white border rounded-3 shadow-sm d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-check-double fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">{{ __('finances::admin.summary.paid') }}</div>
                            <div class="fs-5 fw-bold text-dark mt-1">{{ $summary['paid'] > 0 ? money($summary['paid']) : '0 đ' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-white border rounded-3 shadow-sm d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-id-card fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Đổi ngân hàng (Chờ)</div>
                            <div class="fs-5 fw-bold text-dark mt-1">{{ $summary['account_change_pending'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB CONTROLS -->
            <ul class="nav nav-pills mb-4 gap-2 bg-light p-2 rounded-pill shadow-sm" id="payoutTabs" role="tablist" style="width: fit-content;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-bold" id="payout-list-tab" data-bs-toggle="tab" data-bs-target="#payout-list-pane" type="button" role="tab" aria-controls="payout-list-pane" aria-selected="true">
                        <i class="fa-solid fa-wallet me-1"></i> Yêu cầu rút tiền
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="bank-changes-tab" data-bs-toggle="tab" data-bs-target="#bank-changes-pane" type="button" role="tab" aria-controls="bank-changes-pane" aria-selected="false">
                        <i class="fa-solid fa-university me-1"></i> Đổi tài khoản ngân hàng
                        @if($summary['account_change_pending'] > 0)
                            <span class="badge bg-danger ms-1 rounded-pill">{{ $summary['account_change_pending'] }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics-pane" type="button" role="tab" aria-controls="analytics-pane" aria-selected="false">
                        <i class="fa-solid fa-chart-pie me-1"></i> Thống kê Giảng viên
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="payoutTabsContent">
                <!-- TAB 1: YÊU CẦU RÚT TIỀN -->
                <div class="tab-pane fade show active" id="payout-list-pane" role="tabpanel" aria-labelledby="payout-list-tab">
                    <form method="GET" class="row g-3 mb-4 p-3 bg-light rounded-3 shadow-sm mx-1">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Trạng thái Payout</label>
                            <select name="status" class="form-select rounded-pill">
                                <option value="">{{ __('finances::admin.filters.all') }}</option>
                                @foreach (['requested' => 'Chờ duyệt (Requested)', 'processing' => 'Đang xử lý (Processing)', 'paid' => 'Đã chi trả (Paid)', 'rejected' => 'Từ chối (Rejected)'] as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100 rounded-pill"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                        </div>
                    </form>

                    <div class="card border rounded-3 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list me-1 text-primary"></i> Danh sách yêu cầu rút tiền</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">{{ __('finances::admin.table.id') }}</th>
                                        <th>{{ __('finances::admin.table.teacher') }}</th>
                                        <th>{{ __('finances::admin.table.amount') }}</th>
                                        <th>{{ __('finances::admin.table.bank_info') }}</th>
                                        <th>{{ __('finances::admin.table.status') }}</th>
                                        <th>{{ __('finances::admin.table.note') }}</th>
                                        <th class="text-end pe-3">{{ __('finances::admin.table.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payouts as $payout)
                                        <tr>
                                            <td class="ps-3">#{{ $payout->id }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $payout->teacher?->name_locale ?: '-' }}</div>
                                                <div class="small text-muted">{{ $payout->teacher?->student?->email ?: '-' }}</div>
                                            </td>
                                            <td class="fw-bold text-success">{{ money($payout->amount) }}</td>
                                            <td>
                                                <div class="fw-semibold text-primary">{{ $payout->bank_name }}</div>
                                                <div class="small text-muted">{{ $payout->bank_account_name }}</div>
                                                <div class="small fw-bold text-dark">{{ $payout->bank_account_number }}</div>
                                            </td>
                                            <td>
                                                <span class="badge @if($payout->status === 'requested') bg-info @elseif($payout->status === 'processing') bg-warning text-dark @elseif($payout->status === 'paid') bg-success @else bg-danger @endif rounded-pill">
                                                    @if($payout->status === 'requested') Chờ duyệt @elseif($payout->status === 'processing') Đang xử lý @elseif($payout->status === 'paid') Đã chi trả @else Từ chối @endif
                                                </span>
                                            </td>
                                            <td style="min-width: 260px;">
                                                <form method="POST" action="{{ route('teacher-finance.payouts.update', $payout->id) }}">
                                                    @csrf
                                                    <div class="input-group input-group-sm mb-1">
                                                        <span class="input-group-text bg-light"><i class="fa-solid fa-pen-nib"></i></span>
                                                        <select name="status" class="form-select">
                                                            @foreach (['processing' => 'Đang xử lý', 'paid' => 'Đã chi trả', 'rejected' => 'Từ chối'] as $value => $label)
                                                                <option value="{{ $value }}" {{ $payout->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <textarea name="admin_note" class="form-control form-control-sm" rows="2" placeholder="Ghi chú phản hồi cho giảng viên...">{{ $payout->admin_note }}</textarea>
                                            </td>
                                            <td class="text-end align-top pe-3">
                                                    <button class="btn btn-sm btn-primary px-3 rounded-pill">Cập nhật</button>
                                                    <div class="small text-muted mt-2">
                                                        <i class="fa-solid fa-clock me-1"></i>{{ optional($payout->processed_at ?: $payout->created_at)->format('d/m/Y H:i') }}
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">Chưa có yêu cầu rút tiền nào phù hợp.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($payouts->hasPages())
                            <div class="card-footer bg-white py-3">
                                {{ $payouts->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- TAB 2: ĐỔI TÀI KHOẢN NGÂN HÀNG -->
                <div class="tab-pane fade" id="bank-changes-pane" role="tabpanel" aria-labelledby="bank-changes-tab">
                    <form method="GET" class="row g-3 mb-4 p-3 bg-light rounded-3 shadow-sm mx-1">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Trạng thái Đổi ngân hàng</label>
                            <select name="account_change_status" class="form-select rounded-pill">
                                <option value="">{{ __('finances::admin.filters.all') }}</option>
                                @foreach (['pending' => 'Chờ xử lý (Pending)', 'approved' => 'Đã duyệt (Approved)', 'rejected' => 'Từ chối (Rejected)'] as $value => $label)
                                    <option value="{{ $value }}" {{ request('account_change_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100 rounded-pill"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                        </div>
                    </form>

                    <div class="card border rounded-3 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-university me-1 text-primary"></i> Danh sách yêu cầu thay đổi tài khoản</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">{{ __('finances::admin.table.id') }}</th>
                                        <th>{{ __('finances::admin.table.teacher') }}</th>
                                        <th>Tài khoản hiện tại</th>
                                        <th>Tài khoản mới đề xuất</th>
                                        <th>Trạng thái</th>
                                        <th>Ghi chú admin</th>
                                        <th class="text-end pe-3">{{ __('finances::admin.table.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($accountChangeRequests as $item)
                                        <tr>
                                            <td class="ps-3">#{{ $item->id }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $item->teacher?->name_locale ?: '-' }}</div>
                                                <div class="small text-muted">{{ $item->teacher?->student?->email ?: '-' }}</div>
                                            </td>
                                            <td>
                                                @if ($item->replace_bank_name)
                                                    <div class="text-muted">{{ $item->replace_bank_name }}</div>
                                                    <div class="small text-muted">{{ $item->replace_bank_account_name }}</div>
                                                    <div class="small text-muted fw-bold">{{ $item->replace_bank_account_number }}</div>
                                                @else
                                                    <span class="text-muted">Không có thông tin</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-primary">{{ $item->bank_name }}</div>
                                                <div class="small text-muted">{{ $item->bank_account_name }}</div>
                                                <div class="small fw-bold text-dark">{{ $item->bank_account_number }}</div>
                                            </td>
                                            <td>
                                                <span class="badge @if($item->status === 'pending') bg-warning text-dark @elseif($item->status === 'approved') bg-success @else bg-danger @endif rounded-pill">
                                                    @if($item->status === 'pending') Chờ xử lý @elseif($item->status === 'approved') Đã duyệt @else Từ chối @endif
                                                </span>
                                            </td>
                                            <td style="min-width: 260px;">
                                                <form method="POST" action="{{ route('teacher-finance.payout-account-change-requests.update', $item->id) }}">
                                                    @csrf
                                                    <div class="input-group input-group-sm mb-1">
                                                        <span class="input-group-text bg-light"><i class="fa-solid fa-pen-nib"></i></span>
                                                        <select name="status" class="form-select">
                                                            @foreach (['approved' => 'Phê duyệt', 'rejected' => 'Từ chối'] as $value => $label)
                                                                <option value="{{ $value }}" {{ $item->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <textarea name="admin_note" class="form-control form-control-sm" rows="2" placeholder="Phản hồi cho giảng viên...">{{ $item->admin_note }}</textarea>
                                            </td>
                                            <td class="text-end align-top pe-3">
                                                    <button class="btn btn-sm btn-primary px-3 rounded-pill" {{ $item->status !== 'pending' ? 'disabled' : '' }}>Phê duyệt</button>
                                                    <div class="small text-muted mt-2">
                                                        <i class="fa-solid fa-clock me-1"></i>{{ optional($item->processed_at ?: $item->created_at)->format('d/m/Y H:i') }}
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">Chưa có yêu cầu đổi tài khoản nào phù hợp.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($accountChangeRequests->hasPages())
                            <div class="card-footer bg-white py-3">
                                {{ $accountChangeRequests->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- TAB 3: THỐNG KÊ GIẢNG VIÊN -->
                <div class="tab-pane fade" id="analytics-pane" role="tabpanel" aria-labelledby="analytics-tab">
                    <div class="card border rounded-3 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-chart-bar me-1 text-primary"></i> Tổng hợp payout theo từng giảng viên</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">{{ __('finances::admin.table.teacher') }}</th>
                                        <th class="text-end">Chờ duyệt (Requested)</th>
                                        <th class="text-end">Đang xử lý (Processing)</th>
                                        <th class="text-end">Đã chi trả (Paid)</th>
                                        <th class="text-end pe-3">Bị từ chối (Rejected)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($teacherSummaries as $row)
                                        <tr>
                                            <td class="ps-3 fw-bold">{{ $row['teacher']?->name_locale ?: '-' }}</td>
                                            <td class="text-end text-info fw-semibold">{{ $row['requested'] > 0 ? money($row['requested']) : '0 đ' }}</td>
                                            <td class="text-end text-warning fw-semibold">{{ $row['processing'] > 0 ? money($row['processing']) : '0 đ' }}</td>
                                            <td class="text-end text-success fw-bold">{{ $row['paid'] > 0 ? money($row['paid']) : '0 đ' }}</td>
                                            <td class="text-end text-danger pe-3">{{ $row['rejected'] > 0 ? money($row['rejected']) : '0 đ' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
