@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        {{-- Bộ lọc --}}
        <div class="card mb-3">
            <div class="card-header fw-bold">Bộ lọc</div>
            <div class="card-body">
                <form method="GET" action="{{ route('activelogs.index') }}">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Tên (log_name)</label>
                            <select name="log_name" class="form-select">
                                <option value="">-- Tất cả --</option>
                                @foreach ($logNames as $name)
                                    <option value="{{ $name }}"
                                        {{ request('log_name') == $name ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Hành động (action)</label>
                            <select name="action" class="form-select">
                                <option value="">-- Tất cả --</option>
                                @foreach ($actions as $action)
                                    <option value="{{ $action }}"
                                        {{ request('action') == $action ? 'selected' : '' }}>
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
                                    <option value="{{ $type }}"
                                        {{ request('subject_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Subject ID</label>
                            <input type="number" name="subject_id" value="{{ request('subject_id') }}"
                                class="form-control" placeholder="VD: 12">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Causer ID</label>
                            <input type="number" name="causer_id" value="{{ request('causer_id') }}" class="form-control"
                                placeholder="VD: 3">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">IP</label>
                            <input type="text" name="ip" value="{{ request('ip') }}" class="form-control"
                                placeholder="VD: 127.0.0.1">
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
                            <label class="form-label">Từ khóa</label>
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                placeholder="mô tả / module / action / người thực hiện / IP...">
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <a href="{{ route('activelogs.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                        <button class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bảng dữ liệu --}}
        <div class="card">
            <div class="card-header fw-bold">
                Danh sách log (Tổng: {{ $logs->total() }})
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:160px;">Thời gian</th>
                                <th style="width:140px;">Log name</th>
                                <th style="width:140px;">Hành động</th>
                                <th style="width:180px;">Người thực hiện</th>
                                <th>Mô tả</th>
                                <th style="width:170px;">Module</th>
                                <th style="width:120px;" class="text-end">Chi tiết</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $log->log_name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $log->action ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $log->causer_type ?? 'System' }}</div>
                                        <div class="text-muted small">ID: {{ $log->subject_id ?? '—' }}</div>
                                    </td>
                                    <td>{{ $log->description ?? '—' }}</td>
                                    <td class="text-muted small">
                                        {{ subjectLabel($log->subject_type) }}
                                        <br>
                                        {{-- #{{ $log->subject_id ?? '—' }} --}}
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#propsModal{{ $log->id }}">
                                            Xem
                                        </button>
                                    </td>
                                </tr>

                                {{-- Modal --}}
                                <div class="modal fade" id="propsModal{{ $log->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Chi tiết Log #{{ $log->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-2">
                                                    <span class="badge bg-secondary">{{ $log->log_name ?? '—' }}</span>
                                                    <span class="badge bg-info">{{ $log->action ?? '—' }}</span>
                                                </div>

                                                <div
                                                    style="white-space: pre-wrap; font-family: inherit; line-height: 1.6;">
                                                    {!! nl2br(e(presentLogProperties($log))) !!}
                                                </div>

                                                {{-- <hr>
                                                <div class="text-muted small">
                                                    <div><b>Time:</b>
                                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                                                    </div>
                                                    <div><b>IP:</b> {{ $log->ip ?? '—' }}</div>
                                                    <div><b>User Agent:</b> {{ $log->user_agent ?? '—' }}</div>
                                                    <div><b>Subject:</b> {{ $log->subject_type ?? '—' }}
                                                        #{{ $log->subject_id ?? '—' }}</div>
                                                </div> --}}
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
                                    <td colspan="7" class="text-center py-4 text-muted">
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
