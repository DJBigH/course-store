@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Gói giảng viên</h5>
                    <p class="text-muted mb-0">Quản lý bảng giá và hoa hồng cho flow onboarding giảng viên.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('teacher-package-features.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-tags me-1"></i> Quản lý nhãn tính năng
                    </a>
                    <a href="{{ route('teacher-packages.add') }}" class="btn btn-primary">Thêm gói</a>
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="fa-solid fa-lightbulb"></i>
                <span>Giao diện trực quan: Xem trước tối đa 5 gói hiển thị trên trang bán hàng/nâng cấp. Sử dụng thao tác kéo thả hoặc nút bật/tắt nhanh tiện lợi.</span>
            </div>

            <!-- VISUAL PRICING GRID -->
            <h6 class="fw-bold text-uppercase text-secondary mb-3 small" style="letter-spacing: 1px;">Giao diện hiển thị (Tối đa 5 gói)</h6>
            <div class="row g-4 mb-5 pricing-visual-grid">
                @foreach ($packages->take(5) as $package)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm pricing-card position-relative" 
                             style="border-top: 5px solid {{ $package->badge_tone ?: '#2563eb' }} !important; border-radius: 12px; transition: transform 0.2s ease;">
                            
                            @if ($package->is_featured)
                                <span class="hot-ribbon position-absolute bg-danger text-white px-3 py-1 rounded-pill shadow-sm" 
                                      style="top: 15px; right: 15px; font-size: 0.75rem; font-weight: bold; z-index: 10;">
                                    <i class="fa-solid fa-fire me-1"></i> HOT
                                </span>
                            @endif
                            
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-dark text-white rounded-pill px-3 py-1 font-monospace" style="font-size: 0.75rem;">
                                        Thứ tự #{{ $package->sort_order }}
                                    </span>
                                    @if (!$package->status)
                                        <span class="badge bg-secondary">Đang ẩn</span>
                                    @endif
                                </div>
                                <h4 class="fw-bold mt-2 mb-1 text-dark">{{ $package->name }}</h4>
                                <span class="badge mb-3 text-uppercase" style="background-color: {{ $package->badge_tone ?: '#2563eb' }}; width: fit-content; font-size: 0.7rem;">
                                    {{ $package->code }}
                                </span>
                                <h3 class="fw-bold text-primary mb-3">{{ money($package->price) }}</h3>
                                <p class="text-muted small mb-4 flex-grow-1">{{ $package->description }}</p>
                                
                                <ul class="list-unstyled mb-4 small text-secondary">
                                    @if ($package->can_view_student_progress)
                                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> Xem tiến độ học viên</li>
                                    @endif
                                    @if ($package->can_manage_students)
                                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> Quản lý học viên</li>
                                    @endif
                                    @if ($package->can_view_activity_logs)
                                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> Nhật ký hoạt động</li>
                                    @endif
                                    @if ($package->can_manage_quizzes)
                                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> Quản lý quiz</li>
                                    @endif
                                    @if ($package->can_sell_bundles)
                                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> Combo khóa học</li>
                                    @endif
                                    @if ($package->can_send_promotions)
                                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> Gửi khuyến mại</li>
                                    @endif
                                    @if ($package->can_issue_certificates)
                                        <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> Cấp chứng chỉ</li>
                                    @endif
                                </ul>

                                <div class="d-flex gap-2 mt-auto">
                                    <a href="{{ route('teacher-packages.edit', $package->id) }}" class="btn btn-sm btn-light flex-grow-1 border">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Sửa
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#copyFeaturesModal" 
                                            data-package-id="{{ $package->id }}"
                                            data-package-name="{{ $package->name }}">
                                        <i class="fa-solid fa-clone"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- MODERN DATA TABLE -->
            <h6 class="fw-bold text-uppercase text-secondary mb-3 small" style="letter-spacing: 1px;">Danh sách tất cả gói</h6>
            <div class="table-responsive">
                <table class="table align-middle table-hover border-top">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 56px;"></th>
                            <th style="width: 80px;">Thứ tự</th>
                            <th>Tên gói & Code</th>
                            <th>Quyền nổi bật</th>
                            <th style="width: 120px;">Gói Hot</th>
                            <th style="width: 120px;">Công khai</th>
                            <th>Giá / Commission</th>
                            <th class="text-end" style="width: 160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody data-package-sortable data-reorder-url="{{ route('teacher-packages.reorder') }}" data-csrf="{{ csrf_token() }}">
                        @forelse ($packages as $package)
                            <tr draggable="true" data-package-id="{{ $package->id }}" class="package-row">
                                <td class="text-center text-muted package-sort-handle" style="cursor: grab;" title="Kéo để sắp xếp">
                                    <i class="fa-solid fa-grip-vertical"></i>
                                </td>
                                <td>
                                    <span class="badge bg-dark font-monospace order-rank-badge" id="rank-badge-{{ $package->id }}">#{{ $package->sort_order }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <strong>{{ $package->name }}</strong>
                                        <span class="badge font-monospace text-uppercase" style="background-color: {{ $package->badge_tone ?: '#6c757d' }}; font-size: 0.65rem;">
                                            {{ $package->code }}
                                        </span>
                                    </div>
                                    <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $package->description }}</div>
                                </td>
                                <td>
                                    <div class="package-feature-pills">
                                        @if ($package->can_view_student_progress)
                                            <span class="package-feature-pill is-progress"><i class="fa-solid fa-chart-line"></i></span>
                                        @endif
                                        @if ($package->can_manage_students)
                                            <span class="package-feature-pill is-students"><i class="fa-solid fa-users"></i></span>
                                        @endif
                                        @if ($package->can_view_activity_logs)
                                            <span class="package-feature-pill is-activity"><i class="fa-solid fa-clock-rotate-left"></i></span>
                                        @endif
                                        @if ($package->can_manage_quizzes)
                                            <span class="package-feature-pill is-growth"><i class="fa-solid fa-square-check"></i></span>
                                        @endif
                                        @if ($package->can_sell_bundles)
                                            <span class="package-feature-pill is-growth"><i class="fa-solid fa-layer-group"></i></span>
                                        @endif
                                        @if ($package->can_send_promotions)
                                            <span class="package-feature-pill is-growth"><i class="fa-solid fa-bullhorn"></i></span>
                                        @endif
                                        @if ($package->can_issue_certificates)
                                            <span class="package-feature-pill is-growth"><i class="fa-solid fa-award"></i></span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ajax-toggle-featured" type="checkbox" role="switch" 
                                               data-url="{{ route('teacher-packages.toggle-featured', $package->id) }}"
                                               {{ $package->is_featured ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ajax-toggle-status" type="checkbox" role="switch"
                                               data-url="{{ route('teacher-packages.toggle-status', $package->id) }}"
                                               {{ $package->status ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ money($package->price) }}</div>
                                    <div class="text-muted small">Commission: {{ rtrim(rtrim(number_format($package->commission_rate, 2, '.', ''), '0'), '.') }}%</div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('teacher-packages.edit', $package->id) }}" class="btn btn-sm btn-outline-warning" title="Sửa gói">
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#copyFeaturesModal" 
                                                data-package-id="{{ $package->id }}"
                                                data-package-name="{{ $package->name }}"
                                                title="Sao chép tính năng">
                                            <i class="fa-solid fa-clone"></i>
                                        </button>
                                        <form action="{{ route('teacher-packages.delete', $package->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa gói này?')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>
                                    Chưa có gói nào.
                                </td>
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
                    throw new Error(data.message || 'Không thể cập nhật thứ tự.');
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
                        window.alert(error.message || 'Không thể cập nhật thứ tự.');
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

            // Handle AJAX Fast Toggles
            $('.ajax-toggle-featured, .ajax-toggle-status').on('change', function() {
                const checkbox = $(this);
                const url = checkbox.data('url');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            const toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });
                            toast.fire({
                                icon: 'success',
                                title: response.message
                            });

                            // Optionally reload after 1.5s to see the visual changes in the pricing grid above
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        checkbox.prop('checked', !checkbox.prop('checked'));
                        const msg = xhr.responseJSON?.message || 'Có lỗi xảy ra.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: msg
                        });
                    }
                });
            });
        });
    </script>
@endsection
