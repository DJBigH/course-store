@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">{{ $pageTitle ?? 'Lịch sử cấu hình Website' }}</h4>

            <a href="{{ route('settings.index') }}" class="btn btn-primary">
                <i class="fas fa-cog"></i> Quay lại cấu hình
            </a>
        </div>

        {{-- Bộ lọc --}}
        <div class="card mb-3">
            <div class="card-header fw-bold">Bộ lọc</div>
            <div class="card-body">
                <form method="GET" action="{{ route('settings.logs') }}">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Hành động</label>
                            <select name="action" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <option value="update_settings"
                                    {{ request('action') == 'update_settings' ? 'selected' : '' }}>
                                    Cập nhật cấu hình
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Từ khóa</label>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                placeholder="mô tả / log_name...">
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                        <a href="{{ route('settings.logs') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Danh sách logs --}}
        <div class="card">
            <div class="card-header fw-bold">
                Lịch sử (Tổng: {{ $logs->total() }})
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 160px">Thời gian</th>
                                <th style="width: 120px">Log name</th>
                                <th style="width: 140px">Hành động</th>
                                <th style="width: 160px">Người thực hiện</th>
                                <th>Mô tả</th>
                                <th>Module</th>
                                <th style="width: 120px" class="text-end">Dữ liệu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $log->log_name }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $log->action }}</span>
                                    </td>
                                    <td>{{ $log->causer_type ?? 'System' }}</td>
                                    <td>{{ $log->description }}</td>
                                    <td>
                                        <div class="fw-bold">{{ subjectLabel($log->subject_type) }}</div>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#propsModal{{ $log->id }}">
                                            Chi tiết
                                        </button>
                                    </td>
                                </tr>

                                {{-- Modal chi tiết --}}
                                <div class="modal fade" id="propsModal{{ $log->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Chi tiết (Log #{{ $log->id }})</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div style="white-space: pre-wrap; font-family: inherit; line-height: 1.6;">
                                                    {!! nl2br(e(presentLogProperties($log))) !!}
                                                </div>

                                                {{-- @if (!empty($log->ip))
                                                    <hr>
                                                    <div class="text-muted small">
                                                        IP: {{ $log->ip }} <br>
                                                        UA: {{ $log->user_agent }}
                                                    </div>
                                                @endif --}}
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Không có dữ liệu log
                                    </td>
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
