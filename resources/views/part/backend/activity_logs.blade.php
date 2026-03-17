@php
    $entityItems = $entityItems ?? [];
    $filterActions = $filterActions ?? [];
    $filterRoute = $filterRoute ?? url()->current();
@endphp

@once
    <style>
        .activity-log-item {
            border-left: 4px solid #d8dde6;
        }

        .activity-log-item.is-success {
            border-left-color: #198754;
        }

        .activity-log-item.is-primary {
            border-left-color: #0d6efd;
        }

        .activity-log-item.is-danger {
            border-left-color: #dc3545;
        }

        .activity-log-item.is-secondary {
            border-left-color: #6c757d;
        }

        .activity-log-item.is-dark {
            border-left-color: #212529;
        }

        .activity-log-summary {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #dbe3ec;
            border-radius: .75rem;
            padding: .9rem 1rem;
        }

        .activity-log-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: .75rem;
        }

        .activity-log-meta {
            font-size: 13px;
            color: #6c757d;
        }

        .activity-log-topline {
            font-size: 12px;
            color: #64748b;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .activity-log-detail-box {
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            background: #fff;
        }

        .activity-log-table th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #64748b;
            border-bottom-width: 1px;
        }

        .activity-log-table td {
            vertical-align: top;
        }

        .activity-log-cell {
            border-radius: .5rem;
            padding: .6rem .7rem;
            line-height: 1.45;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .activity-log-cell-old {
            background: #fff7ed;
            border: 1px solid #fed7aa;
        }

        .activity-log-cell-new {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        .activity-log-cell-single {
            background: #f8fafc;
            border: 1px solid #dbeafe;
        }

        .activity-log-empty {
            color: #94a3b8;
            font-style: italic;
        }

        .activity-log-description {
            padding: .85rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            background: #ffffff;
        }

        .activity-log-actions {
            border-top: 1px dashed #dbe3ec;
            padding-top: .9rem;
        }
    </style>
@endonce

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            @if (!empty($pageTitle))
                <h4 class="mb-1">{{ $pageTitle }}</h4>
            @endif
            <div class="text-muted small">Lịch sử thao tác và thay đổi dữ liệu</div>
        </div>

        @if (!empty($backUrl))
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                {{ $backLabel ?? 'Quay lại' }}
            </a>
        @endif
    </div>

    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    @if (session('msg_danger'))
        <div class="alert alert-danger">{{ session('msg_danger') }}</div>
    @endif

    @if (!empty($entityItems))
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-bold">{{ $entityTitle ?? 'Thông tin' }}</div>
            <div class="card-body">
                <div class="activity-log-grid">
                    @foreach ($entityItems as $label => $value)
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fw-semibold">{{ $value ?: '—' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header fw-bold">Bộ lọc</div>
        <div class="card-body">
            <form method="GET" action="{{ $filterRoute }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Hành động</label>
                        <select name="action" class="form-select">
                            <option value="">Tất cả</option>
                            @foreach ($filterActions as $value => $label)
                                <option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>
                            @endforeach
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
                            placeholder="Mô tả / log name...">
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" type="submit">Lọc</button>
                        <a class="btn btn-outline-secondary" href="{{ url()->current() }}">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-bold">Lịch sử thay đổi</div>
        <div class="card-body">
            @forelse ($logs as $log)
                @php
                    $badgeClass = logActionBadgeClass($log->action);
                    $detailRows = logDetailRows($log);
                    $diffCount = logDiffCount($log);
                    $itemClass = 'activity-log-item is-' . $badgeClass;
                @endphp

                <div class="card {{ $itemClass }} shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge text-bg-{{ $badgeClass }}">{{ logActionLabel($log->action) }}</span>
                                <span class="badge text-bg-light border">{{ $log->log_name ?: 'Không nhóm' }}</span>
                                <span class="activity-log-meta">#{{ $log->id }}</span>
                                @if ($diffCount > 0)
                                    <span class="badge text-bg-light border">{{ $diffCount }} thay đổi</span>
                                @endif
                            </div>

                            <div class="text-end">
                                <div class="activity-log-topline">Thời gian</div>
                                <div class="fw-semibold">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</div>
                                <div class="activity-log-meta">{{ subjectLabel($log->subject_type) }}</div>
                            </div>
                        </div>

                        <div class="activity-log-description mb-3">
                            <div class="activity-log-topline mb-1">Mô tả thao tác</div>
                            <div class="fw-semibold">{{ $log->description ?: 'Không có mô tả' }}</div>
                            <div class="activity-log-meta mt-1">
                                {{ $log->causer_type ?: 'System' }}
                                @if ($log->causer_id)
                                    · ID {{ $log->causer_id }}
                                @endif
                                @if ($log->ip)
                                    · IP {{ $log->ip }}
                                @endif
                            </div>
                        </div>

                        <div class="activity-log-summary mb-3">
                            <div class="activity-log-topline mb-1">Tóm tắt nhanh</div>
                            <div class="fw-semibold">{{ \Illuminate\Support\Str::limit(logSummaryText($log), 260) }}</div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 activity-log-actions">
                            @if (!empty($detailRows))
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#logDetail{{ $log->id }}" aria-expanded="false">
                                    Xem thay đổi chi tiết
                                </button>
                            @endif
                        </div>

                        @if (!empty($detailRows))
                            <div class="collapse mt-3" id="logDetail{{ $log->id }}">
                                <div class="activity-log-detail-box p-3">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                        <div>
                                            <div class="activity-log-topline">Phân tích thay đổi</div>
                                            <div class="fw-semibold">Chi tiết dễ đọc</div>
                                        </div>
                                        <span class="badge text-bg-light border">{{ $diffCount }} dòng</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm activity-log-table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width: 24%">Trường</th>
                                                    <th style="width: 38%">Giá trị cũ</th>
                                                    <th style="width: 38%">Giá trị mới</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($detailRows as $row)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $row['label'] }}</td>
                                                        @if (array_key_exists('old', $row) || array_key_exists('new', $row))
                                                            <td>
                                                                <div class="activity-log-cell activity-log-cell-old {{ ($row['old'] ?? '—') === '—' ? 'activity-log-empty' : '' }}">
                                                                    {{ $row['old'] ?? '—' }}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="activity-log-cell activity-log-cell-new {{ ($row['new'] ?? '—') === '—' ? 'activity-log-empty' : '' }}">
                                                                    {{ $row['new'] ?? '—' }}
                                                                </div>
                                                            </td>
                                                        @else
                                                            <td colspan="2">
                                                                <div class="activity-log-cell activity-log-cell-single {{ ($row['value'] ?? '—') === '—' ? 'activity-log-empty' : '' }}">
                                                                    {{ $row['value'] ?? '—' }}
                                                                </div>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if ($log->user_agent)
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="activity-log-topline mb-1">Thiết bị / trình duyệt</div>
                                            <div class="small text-muted">{{ $log->user_agent }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    Chưa có bản ghi log.
                </div>
            @endforelse
        </div>

        @if (method_exists($logs, 'links'))
            <div class="card-footer">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
