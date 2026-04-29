@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Gói giảng viên</h5>
                    <p class="text-muted mb-0">Quản lý bảng giá và commission cho flow onboarding giảng viên.</p>
                </div>
                <a href="{{ route('teacher-packages.add') }}" class="btn btn-primary">Thêm gói</a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            <div class="alert alert-info d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-up-down-left-right"></i>
                <span>Kéo thả dòng để đổi thứ tự hiển thị gói. Hệ thống sẽ lưu lại thứ tự mới ngay khi bạn thả chuột.</span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 56px;"></th>
                            <th>Mã</th>
                            <th>Tên gói</th>
                            <th>Quyền nổi bật</th>
                            <th>Badge quyền</th>
                            <th>Thứ tự</th>
                            <th>Giá</th>
                            <th>Commission</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody data-package-sortable data-reorder-url="{{ route('teacher-packages.reorder') }}" data-csrf="{{ csrf_token() }}">
                        @forelse ($packages as $package)
                            <tr draggable="true" data-package-id="{{ $package->id }}">
                                <td class="text-center text-muted package-sort-handle" title="Keo de sap xep">
                                    <i class="fa-solid fa-grip-vertical"></i>
                                </td>
                                <td>{{ strtoupper($package->code) }}</td>
                                <td>
                                    <strong>{{ $package->name }}</strong>
                                    <div class="text-muted small">{{ $package->description }}</div>
                                </td>
                                <td>
                                    <div class="package-feature-pills">
                                        @if ($package->can_view_student_progress)
                                            <span class="package-feature-pill is-progress">
                                                <i class="fa-solid fa-chart-line"></i>
                                                Xem tiến độ học viên
                                            </span>
                                        @endif
                                        @if ($package->can_manage_students)
                                            <span class="package-feature-pill is-students">
                                                <i class="fa-solid fa-users"></i>
                                                Quản lý học viên
                                            </span>
                                        @endif
                                        @if ($package->can_view_activity_logs)
                                            <span class="package-feature-pill is-activity">
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                                Nhật ký hoạt động
                                            </span>
                                        @endif
                                        @if ($package->can_manage_quizzes)
                                            <span class="package-feature-pill is-growth">
                                                <i class="fa-solid fa-square-check"></i>
                                                Quản lý quiz
                                            </span>
                                        @endif
                                        @if ($package->can_sell_bundles)
                                            <span class="package-feature-pill is-growth">
                                                <i class="fa-solid fa-layer-group"></i>
                                                Combo khóa học
                                            </span>
                                        @endif
                                        @if ($package->can_send_promotions)
                                            <span class="package-feature-pill is-growth">
                                                <i class="fa-solid fa-bullhorn"></i>
                                                Gửi khuyến mại
                                            </span>
                                        @endif
                                        @if ($package->can_issue_certificates)
                                            <span class="package-feature-pill is-growth">
                                                <i class="fa-solid fa-award"></i>
                                                Chứng chỉ
                                            </span>
                                        @endif
                                        @if (!$package->can_view_student_progress && !$package->can_manage_students && !$package->can_view_activity_logs && !$package->can_manage_quizzes && !$package->can_sell_bundles && !$package->can_send_promotions && !$package->can_issue_certificates)
                                            <span class="text-muted small">Chưa có quyền nổi bật</span>
                                        @endif
                                    </div>
                                </td>
                                <td>#{{ $package->sort_order }}</td>
                                <td>{{ money($package->price) }}</td>
                                <td>{{ rtrim(rtrim(number_format($package->commission_rate, 2, '.', ''), '0'), '.') }}%</td>
                                <td>
                                    <span class="badge bg-{{ $package->status ? 'success' : ($package->hidden_mode === 'available' ? 'warning text-dark' : 'secondary') }}">
                                        {{ $package->status ? 'Công khai' : ($package->hidden_mode === 'available' ? 'Ẩn nhưng vẫn dùng được' : 'Ẩn và khóa sử dụng') }}
                                    </span>
                                    @if ($package->is_featured)
                                        <div class="small text-info mt-1">Nổi bật</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('teacher-packages.edit', $package->id) }}" class="btn btn-sm btn-warning">Sua</a>
                                    <form action="{{ route('teacher-packages.delete', $package->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Xoa goi nay?')">Xoa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Chua co goi nao.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .package-sort-handle {
            cursor: grab;
            user-select: none;
        }

        .package-feature-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            min-width: 210px;
        }

        .package-feature-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.62rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .package-feature-pill i {
            font-size: 0.72rem;
        }

        .package-feature-pill.is-progress {
            color: #0f766e;
            background: rgba(20, 184, 166, 0.16);
            border: 1px solid rgba(20, 184, 166, 0.24);
        }

        .package-feature-pill.is-students {
            color: #1d4ed8;
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .package-feature-pill.is-activity {
            color: #7c2d12;
            background: rgba(251, 146, 60, 0.14);
            border: 1px solid rgba(249, 115, 22, 0.2);
        }

        .package-feature-pill.is-growth {
            color: #7c3aed;
            background: rgba(139, 92, 246, 0.12);
            border: 1px solid rgba(139, 92, 246, 0.18);
        }

        tr[data-package-id] {
            transition: background-color 0.18s ease, opacity 0.18s ease, transform 0.18s ease;
        }

        tr[data-package-id].is-dragging {
            opacity: 0.55;
        }

        tr[data-package-id].is-drag-over > * {
            background: rgba(59, 130, 246, 0.12) !important;
        }

        html[data-theme="dark"] tr[data-package-id].is-drag-over > * {
            background: rgba(96, 165, 250, 0.16) !important;
        }

        html[data-theme="dark"] .package-feature-pill.is-progress {
            color: #99f6e4;
            background: rgba(20, 184, 166, 0.18);
            border-color: rgba(45, 212, 191, 0.28);
        }

        html[data-theme="dark"] .package-feature-pill.is-students {
            color: #bfdbfe;
            background: rgba(59, 130, 246, 0.18);
            border-color: rgba(96, 165, 250, 0.28);
        }

        html[data-theme="dark"] .package-feature-pill.is-activity {
            color: #fdba74;
            background: rgba(249, 115, 22, 0.16);
            border-color: rgba(251, 146, 60, 0.24);
        }

        html[data-theme="dark"] .package-feature-pill.is-growth {
            color: #ddd6fe;
            background: rgba(139, 92, 246, 0.18);
            border-color: rgba(167, 139, 250, 0.24);
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const tbody = document.querySelector('[data-package-sortable]');
            if (!tbody) {
                return;
            }

            let draggingRow = null;

            const getRows = () => [...tbody.querySelectorAll('tr[data-package-id]')];

            const clearDragState = () => {
                getRows().forEach((row) => row.classList.remove('is-drag-over', 'is-dragging'));
            };

            const persistOrder = async () => {
                const ids = getRows().map((row) => Number(row.dataset.packageId));

                const response = await fetch(tbody.dataset.reorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': tbody.dataset.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ ids }),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Khong the cap nhat thu tu.');
                }

                getRows().forEach((row, index) => {
                    const orderCell = row.children[4];
                    if (orderCell) {
                        orderCell.textContent = `#${index + 1}`;
                    }
                });
            };

            getRows().forEach((row) => {
                row.addEventListener('dragstart', () => {
                    draggingRow = row;
                    row.classList.add('is-dragging');
                });

                row.addEventListener('dragend', async () => {
                    clearDragState();
                    if (!draggingRow) {
                        return;
                    }

                    try {
                        await persistOrder();
                    } catch (error) {
                        window.alert(error.message || 'Khong the cap nhat thu tu.');
                        window.location.reload();
                    } finally {
                        draggingRow = null;
                    }
                });

                row.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    if (!draggingRow || draggingRow === row) {
                        return;
                    }

                    const rect = row.getBoundingClientRect();
                    const offset = event.clientY - rect.top;
                    row.classList.add('is-drag-over');

                    if (offset > rect.height / 2) {
                        row.after(draggingRow);
                    } else {
                        row.before(draggingRow);
                    }
                });

                row.addEventListener('dragleave', () => {
                    row.classList.remove('is-drag-over');
                });

                row.addEventListener('drop', (event) => {
                    event.preventDefault();
                    row.classList.remove('is-drag-over');
                });
            });
        })();
    </script>
@endsection
