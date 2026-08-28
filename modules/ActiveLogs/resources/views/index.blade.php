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
        <div class="card mb-4 border-0 shadow-sm rounded-4">
            <div class="card-header fw-bold bg-primary text-white d-flex align-items-center gap-2 rounded-top-4 py-3">
                <i class="fas fa-filter"></i> <span style="font-size: 1.05rem;">Bộ lọc tìm kiếm nâng cao</span>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('activelogs.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Nhóm Log</label>
                            <select name="log_name" class="form-select border-2">
                                <option value="">-- Tất cả --</option>
                                @foreach ($logNames as $name)
                                    <option value="{{ $name }}" {{ request('log_name') == $name ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Hành động</label>
                            <select name="action" class="form-select border-2">
                                <option value="">-- Tất cả --</option>
                                @foreach ($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ logActionLabel($action) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Module tác động</label>
                            <select name="subject_type" class="form-select border-2">
                                <option value="">-- Tất cả --</option>
                                @foreach ($subjectTypes as $type)
                                    <option value="{{ $type['value'] }}" {{ request('subject_type') == $type['value'] ? 'selected' : '' }}>
                                        {{ $type['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Người thực hiện</label>
                            <input type="text" name="causer" value="{{ request('causer') }}" class="form-control border-2" placeholder="Tên / Quyền / ID">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Địa chỉ IP</label>
                            <input type="text" name="ip" value="{{ request('ip') }}" class="form-control border-2" placeholder="VD: 127.0.0.1">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Từ ngày</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control border-2">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Đến ngày</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control border-2">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Từ khóa tổng hợp</label>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control border-2" placeholder="Mô tả / Module / IP...">
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('activelogs.index') }}" class="btn btn-outline-secondary px-4 rounded-pill me-2">
                            <i class="fas fa-undo me-1"></i> Đặt lại
                        </a>
                        <button class="btn btn-primary px-4 rounded-pill shadow-sm">
                            <i class="fas fa-search me-1"></i> Lọc dữ liệu
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
                                <th style="width:100px;" class="text-center">Chi tiết</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="text-muted" style="font-size: 0.9rem;">
                                        <i class="far fa-clock me-1"></i>{{ optional($log->created_at)->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">#{{ $log->id }}</div>
                                        @if($log->subject_id)
                                            <span class="badge text-bg-light border text-muted small mt-1" style="font-size: 0.75rem;">Sub #{{ $log->subject_id }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary px-2 py-1 rounded">{{ $log->log_name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ logActionBadgeClass($log->action) }} px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                                            {{ logActionLabel($log->action) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">{{ logCauserDisplay($log) }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">ID: {{ $log->causer_id ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-secondary">{{ subjectLabel($log->subject_type) }}</div>
                                        <div class="text-muted small" style="font-size: 0.72rem; word-break: break-all;">{{ class_basename($log->subject_type) ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-wrap" style="max-width: 350px; font-size: 0.9rem; line-height: 1.4;">
                                            {{ \Illuminate\Support\Str::limit($log->description ?? '-', 110) }}
                                        </div>
                                    </td>
                                    <td class="small font-monospace text-muted">
                                        <i class="fas fa-network-wired me-1"></i>{{ $log->ip ?? '-' }}
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary px-3 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#propsModal{{ $log->id }}">
                                            <i class="far fa-eye me-1"></i> Xem
                                        </button>
                                    </td>
                                </tr>

                                <div class="modal fade" id="propsModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-dark text-white rounded-top-4">
                                                <h5 class="modal-title"><i class="fas fa-info-circle me-1"></i> Chi tiết hoạt động #{{ $log->id }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body bg-light-subtle p-4">
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-3">
                                                        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                                                            <div class="card-body p-3 text-center">
                                                                <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.75rem;">Thời gian</div>
                                                                <div class="fw-bold text-dark"><i class="far fa-calendar-alt me-1 text-primary"></i> {{ optional($log->created_at)->format('d/m/Y H:i:s') }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                                                            <div class="card-body p-3 text-center">
                                                                <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.75rem;">Nhóm Log & Hành động</div>
                                                                <div class="fw-bold text-dark mb-1">{{ $log->log_name ?? '-' }}</div>
                                                                <span class="badge text-bg-{{ logActionBadgeClass($log->action) }} px-2 py-1 rounded-pill">{{ logActionLabel($log->action) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                                                            <div class="card-body p-3 text-center">
                                                                <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.75rem;">Người thực hiện</div>
                                                                <div class="fw-bold text-primary">{{ logCauserDisplay($log) }}</div>
                                                                <div class="small text-muted mt-1">Causer ID: {{ $log->causer_id ?? '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                                                            <div class="card-body p-3 text-center">
                                                                <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.75rem;">Địa chỉ IP</div>
                                                                <div class="fw-bold text-dark"><i class="fas fa-globe me-1 text-info"></i> {{ $log->ip ?? '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                                                            <div class="card-body p-3">
                                                                <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.75rem;">Đối tượng tác động</div>
                                                                <div class="fw-bold text-dark mb-1"><i class="fas fa-cube me-1 text-warning"></i> {{ subjectLabel($log->subject_type) }}</div>
                                                                <div class="small text-muted font-monospace" style="font-size: 0.75rem;">Class: {{ $log->subject_type ?? '-' }}</div>
                                                                <div class="small text-muted font-monospace" style="font-size: 0.75rem;">Subject ID: {{ $log->subject_id ?? '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                                                            <div class="card-body p-3">
                                                                <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.75rem;">Mô tả hoạt động</div>
                                                                <div class="text-dark fw-normal" style="font-size: 0.95rem; line-height: 1.5;">{{ $log->description ?? '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card border-0 shadow-sm rounded-3 bg-white mb-3">
                                                    <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-stream me-1"></i> Chi tiết thay đổi</div>
                                                    <div class="card-body p-3" style="white-space: pre-wrap; font-family: inherit; line-height: 1.8; font-size: 0.95rem;">{!! nl2br(e(presentLogProperties($log))) !!}</div>
                                                </div>

                                                <div class="card border-0 shadow-sm rounded-3 bg-white mb-3">
                                                    <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-code me-1"></i> Dữ liệu thô (Raw Properties JSON)</div>
                                                    <div class="card-body p-0">
                                                        <pre class="p-3 bg-dark text-light small mb-0 font-monospace" style="white-space: pre-wrap; word-break: break-word; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">{{ json_encode($log->properties ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                                    </div>
                                                </div>

                                                <div class="text-muted small ps-1">
                                                    <i class="fas fa-user-shield me-1"></i> <b>User Agent (Thiết bị):</b> {{ $log->user_agent ?? '-' }}
                                                </div>
                                            </div>

                                            <div class="modal-footer bg-light border-top-0 rounded-bottom-4">
                                                <button type="button" class="btn btn-secondary px-4 rounded-pill shadow-sm" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">Không có dữ liệu log</td>
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
