@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
                    Quay lại danh sách
                </a>
            </div>
        </div>

        {{-- Flash message --}}
        @if (session('msg'))
            <div class="alert alert-success">{{ session('msg') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        {{-- User info --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="text-muted">Tên</div>
                        <div class="fw-bold">{{ $contacts->name ?? 'N/A' }}</div>
                    </div>
                    {{-- <div class="col-md-4">
                        <div class="text-muted">Kinh nghiệm</div>
                        <div class="fw-bold">{{ $contacts->exp ?? 'N/A' }}</div>
                    </div> --}}
                </div>
            </div>
        </div>

        {{-- Filters (optional) --}}
        <div class="card mb-3">
            <div class="card-header">
                <strong>Bộ lọc</strong>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Hành động</label>
                            <select name="action" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <option value="create" @selected(request('action') === 'create')>create</option>
                                <option value="update" @selected(request('action') === 'update')>update</option>
                                <option value="delete" @selected(request('action') === 'delete')>delete</option>
                                <option value="assign_coupon" @selected(request('action') === 'assign_coupon')>assign_coupon</option>
                                <option value="revoke_coupon" @selected(request('action') === 'revoke_coupon')>revoke_coupon</option>
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

                        <div class="col-12 d-flex gap-2 mt-2">
                            <button class="btn btn-primary" type="submit">Lọc</button>
                            <a class="btn btn-outline-secondary" href="{{ url()->current() }}">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Logs table --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <strong>Lịch sử</strong>
                <span class="text-muted" style="font-size: 13px;">
                    Tổng: {{ method_exists($logs, 'total') ? $logs->total() : (is_countable($logs) ? count($logs) : 0) }}
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 160px;">Thời gian</th>
                                <th style="width: 140px;">Log name</th>
                                <th style="width: 160px;">Hành động</th>
                                <th style="width: 220px;">Người thực hiện</th>
                                <th>Mô tả</th>
                                <th style="width: 140px;">Dữ liệu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                @php
                                    $props = $log->properties ?? [];
                                    $propsJson = json_encode($props, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            {{ optional($log->created_at)->format('d/m/Y') }}
                                        </div>
                                        <div class="text-muted" style="font-size: 13px;">
                                            {{ optional($log->created_at)->format('H:i:s') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $log->log_name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $log->action }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $log->causer_type ?? 'system' }}</div>
                                        <div class="text-muted" style="font-size: 13px;">
                                            #{{ $log->causer_id ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $log->description ?? '-' }}</div>
                                        <div class="text-muted" style="font-size: 13px;">
                                            IP: {{ $log->ip ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if (!empty($props))
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#propsModal{{ $log->id }}">
                                                Xem
                                            </button>

                                            {{-- Modal --}}
                                            <div class="modal fade" id="propsModal{{ $log->id }}" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Chi tiết dữ liệu (Log
                                                                #{{ $log->id }})</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <pre class="mb-0" style="white-space: pre-wrap;">{{ presentLogProperties($log) }}</pre>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Đóng</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center p-4">
                                        <div class="text-muted">Chưa có lịch sử cho người dùng này.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (method_exists($logs, 'links'))
                <div class="card-footer">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
