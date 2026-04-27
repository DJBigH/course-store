@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Goi giang vien</h5>
                    <p class="text-muted mb-0">Quan ly bang gia va commission cho flow onboarding giang vien.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('teacher-package-features.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-tags me-1"></i> Quản lý nhãn tính năng
                    </a>
                    <a href="{{ route('teacher-packages.add') }}" class="btn btn-primary">Them goi</a>
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            <div class="alert alert-info d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid fa-up-down-left-right"></i>
                <span>Keo tha dong de doi thu tu hien thi package. He thong se luu lai sort order moi ngay khi ban tha chuot.</span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 56px;"></th>
                            <th>Code</th>
                            <th>Ten goi</th>
                            <th>Quyen noi bat</th>
                            <th>Badge quyen</th>
                            <th>Thu tu</th>
                            <th>Gia</th>
                            <th>Commission</th>
                            <th>Trang thai</th>
                            <th class="text-end">Thao tac</th>
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
                                                Xem tien do hoc vien
                                            </span>
                                        @endif
                                        @if ($package->can_manage_students)
                                            <span class="package-feature-pill is-students">
                                                <i class="fa-solid fa-users"></i>
                                                Quan ly hoc vien
                                            </span>
                                        @endif
                                        @if ($package->can_view_activity_logs)
                                            <span class="package-feature-pill is-activity">
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                                Nhat ky hoat dong
                                            </span>
                                        @endif
                                        @if ($package->can_manage_quizzes)
                                            <span class="package-feature-pill is-growth">
                                                <i class="fa-solid fa-square-check"></i>
                                                Quan ly quiz
                                            </span>
                                        @endif
                                        @if ($package->can_sell_bundles)
                                            <span class="package-feature-pill is-growth">
                                                <i class="fa-solid fa-layer-group"></i>
                                                Bundle khoa hoc
                                            </span>
                                        @endif
                                        @if ($package->can_send_promotions)
                                            <span class="package-feature-pill is-growth">
                                                <i class="fa-solid fa-bullhorn"></i>
                                                Gui khuyen mai
                                            </span>
                                        @endif
                                        @if ($package->can_issue_certificates)
                                            <span class="package-feature-pill is-growth">
                                                <i class="fa-solid fa-award"></i>
                                                Chung chi
                                            </span>
                                        @endif
                                        @if (!$package->can_view_student_progress && !$package->can_manage_students && !$package->can_view_activity_logs && !$package->can_manage_quizzes && !$package->can_sell_bundles && !$package->can_send_promotions && !$package->can_issue_certificates)
                                            <span class="text-muted small">Chua co quyen noi bat</span>
                                        @endif
                                    </div>
                                </td>
                                <td>#{{ $package->sort_order }}</td>
                                <td>{{ money($package->price) }}</td>
                                <td>{{ rtrim(rtrim(number_format($package->commission_rate, 2, '.', ''), '0'), '.') }}%</td>
                                <td>
                                    <span class="badge bg-{{ $package->status ? 'success' : ($package->hidden_mode === 'available' ? 'warning text-dark' : 'secondary') }}">
                                        {{ $package->status ? 'Cong khai' : ($package->hidden_mode === 'available' ? 'An nhung van dung duoc' : 'An va khoa su dung') }}
                                    </span>
                                    @if ($package->is_featured)
                                        <div class="small text-info mt-1">Featured</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#copyFeaturesModal" 
                                            data-package-id="{{ $package->id }}"
                                            data-package-name="{{ $package->name }}">
                                        Sao chép
                                    </button>
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

    <!-- Modal Sao chép tính năng -->
    <div class="modal fade" id="copyFeaturesModal" tabindex="-1" aria-labelledby="copyFeaturesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="copyFeaturesForm" action="{{ route('teacher-packages.copy-features') }}" method="POST">
                    @csrf
                    <input type="hidden" name="target_id" id="target_package_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="copyFeaturesModalLabel">Sao chép tính năng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn đang sao chép tính năng cho gói: <strong id="target_package_name_display"></strong></p>
                        <div class="mb-3">
                            <label for="source_id" class="form-label">Chọn gói nguồn (Gói muốn lấy tính năng)</label>
                            <select name="source_id" id="source_id" class="form-select" required>
                                <option value="">-- Chọn gói nguồn --</option>
                                @foreach ($packages as $pkg)
                                    <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ strtoupper($pkg->code) }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-danger mt-2">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                Lưu ý: Mọi thiết lập tính năng hiện tại của gói đích sẽ bị ghi đè.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-info text-white" id="btnConfirmCopy">
                            Xác nhận sao chép
                        </button>
                    </div>
                </form>
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

        // Handle Copy Features Modal
        $(document).ready(function() {
            const modal = document.getElementById('copyFeaturesModal');
            if (modal) {
                modal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const packageId = button.getAttribute('data-package-id');
                    const packageName = button.getAttribute('data-package-name');

                    document.getElementById('target_package_id').value = packageId;
                    document.getElementById('target_package_name_display').textContent = packageName;

                    // Hide the target package from source options
                    const select = document.getElementById('source_id');
                    for (let i = 0; i < select.options.length; i++) {
                        if (select.options[i].value === packageId) {
                            select.options[i].style.display = 'none';
                        } else {
                            select.options[i].style.display = '';
                        }
                    }
                });

                $('#copyFeaturesForm').on('submit', function(e) {
                    e.preventDefault();
                    const form = $(this);
                    const btn = $('#btnConfirmCopy');
                    
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...');

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Thành công',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Có lỗi xảy ra.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: msg
                            });
                            btn.prop('disabled', false).text('Xác nhận sao chép');
                        }
                    });
                });
            }
        });
    </script>
@endsection
