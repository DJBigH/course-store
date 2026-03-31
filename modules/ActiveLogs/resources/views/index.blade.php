@extends('layouts.backend')

@section('stylesheets')
    <style>
        html[data-theme='dark'] .table-hover > tbody > tr:hover > * {
            color: #e5eefc !important;
            --bs-table-color-state: #e5eefc;
            --bs-table-bg-state: rgba(37, 99, 235, 0.08);
        }

        html[data-theme='dark'] .table-hover > tbody > tr:hover .text-muted,
        html[data-theme='dark'] .table-hover > tbody > tr:hover .small {
            color: #b9cae3 !important;
        }

        html[data-theme='dark'] .table-hover > tbody > tr:hover .badge {
            color: #fff !important;
        }

        @media (max-width: 767.98px) {
            .card-body form .text-end {
                text-align: left !important;
            }

            .card-body form .btn {
                width: 100%;
            }

            .card-body form .btn + .btn,
            .card-body form .btn + button,
            .card-body form a + .btn {
                margin-top: 0.5rem;
            }

            .table-responsive table {
                min-width: 980px;
            }

            .modal-dialog {
                margin: 0.75rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card mb-3">
            <div class="card-header fw-bold">Bo loc</div>
            <div class="card-body">
                <form method="GET" action="{{ route('activelogs.index') }}">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Tên nhóm log</label>
                            <select name="log_name" class="form-select">
                                <option value="">-- Tất cả --</option>
                                @foreach ($logNames as $name)
                                    <option value="{{ $name }}" {{ request('log_name') == $name ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Hành động</label>
                            <select name="action" class="form-select">
                                <option value="">-- Tất cả --</option>
                                @foreach ($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ $action }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Module</label>
                            <select name="subject_type" class="form-select">
                                <option value="">-- Tất cả --</option>
                                @foreach ($subjectTypes as $type)
                                    <option value="{{ $type['value'] }}" {{ request('subject_type') == $type['value'] ? 'selected' : '' }}>
                                        {{ $type['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- <div class="col-md-3">
                            <label class="form-label">Subject ID</label>
                            <input type="number" name="subject_id" value="{{ request('subject_id') }}" class="form-control" placeholder="VD: 12">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Causer ID</label>
                            <input type="number" name="causer_id" value="{{ request('causer_id') }}" class="form-control" placeholder="VD: 3">
                        </div> --}}

                        {{-- <div class="col-md-3">
                            <label class="form-label">Tim subject</label>
                            <input type="text" name="subject" value="{{ request('subject') }}" class="form-control" placeholder="Module / Subject ID">
                        </div> --}}

                        <div class="col-md-3">
                            <label class="form-label">Tìm người thực hiện</label>
                            <input type="text" name="causer" value="{{ request('causer') }}" class="form-control" placeholder="Tên / quyền / ID">
                        </div>

                        {{-- <div class="col-md-3">
                            <label class="form-label">Property</label>
                            <input type="text" name="property" value="{{ request('property') }}" class="form-control" placeholder="Khoa / gia tri trong properties">
                        </div> --}}

                        <div class="col-md-3">
                            <label class="form-label">IP</label>
                            <input type="text" name="ip" value="{{ request('ip') }}" class="form-control" placeholder="VD: 127.0.0.1">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Từ khóa tổng hợp</label>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Mô tả / module / action / người thực hiện / IP...">
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <a href="{{ route('activelogs.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
                        <button class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-bold">Danh sách log (Tổng: {{ $logs->total() }})</div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:155px;">Thời gian</th>
                                <th style="width:135px;">Log ID</th>
                                <th style="width:150px;">Nhóm log</th>
                                <th style="width:140px;">Hành động</th>
                                <th style="width:220px;">Người thực hiện</th>
                                <th style="width:200px;">Đối tượng</th>
                                <th>Mô tả</th>
                                <th style="width:130px;">IP</th>
                                <th style="width:120px;" class="text-end">Chi tiết</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <div class="fw-semibold">#{{ $log->id }}</div>
                                        <div class="text-muted small">{{ $log->subject_id ? 'Subject #' . $log->subject_id : '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $log->log_name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $log->action ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ logCauserDisplay($log) }}</div>
                                        <div class="text-muted small">Causer ID: {{ $log->causer_id ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ subjectLabel($log->subject_type) }}</div>
                                        <div class="text-muted small">{{ class_basename($log->subject_type) ?: '-' }}</div>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($log->description ?? '-', 110) }}</td>
                                    <td>
                                        <div>{{ $log->ip ?? '-' }}</div>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#propsModal{{ $log->id }}">
                                            Xem
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="propsModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Chi tiet log #{{ $log->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-3">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="text-muted small">Thoi gian</div>
                                                            <div class="fw-semibold">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="text-muted small">Log</div>
                                                            <div class="fw-semibold">{{ $log->log_name ?? '-' }}</div>
                                                            <div class="small text-muted">Action: {{ $log->action ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="text-muted small">Nguoi thuc hien</div>
                                                            <div class="fw-semibold">{{ logCauserDisplay($log) }}</div>
                                                            <div class="small text-muted">Causer ID: {{ $log->causer_id ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="text-muted small">IP</div>
                                                            <div class="fw-semibold">{{ $log->ip ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="text-muted small">Doi tuong bi tac dong</div>
                                                            <div class="fw-semibold">{{ subjectLabel($log->subject_type) }}</div>
                                                            <div class="small text-muted">Class: {{ $log->subject_type ?? '-' }}</div>
                                                            <div class="small text-muted">Subject ID: {{ $log->subject_id ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="text-muted small">Mo ta</div>
                                                            <div class="fw-semibold">{{ $log->description ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="fw-semibold mb-2">Chi tiet da doc</div>
                                                    <div style="white-space: pre-wrap; font-family: inherit; line-height: 1.6;">{!! nl2br(e(presentLogProperties($log))) !!}</div>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="fw-semibold mb-2">Raw properties</div>
                                                    <pre class="p-3 border rounded small mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($log->properties ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>

                                                <div class="border-top pt-3 text-muted small">
                                                    <div><b>User Agent:</b> {{ $log->user_agent ?? '-' }}</div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dong</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">Khong co du lieu log</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($logs->hasPages())
                <div class="card-footer">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
