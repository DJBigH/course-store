@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">{{ $pageTitle }}</h5>
                    <p class="text-muted mb-0">Quản lý nhãn và mô tả chi tiết của từng tính năng hiển thị trong bảng so sánh.</p>
                </div>
                <a href="{{ route('teacher-packages.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-1"></i> Danh sách gói
                </a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            <form id="bulk-action-form" action="{{ route('teacher-package-features.bulk-update') }}" method="POST">
                @csrf
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <select name="status" class="form-select form-select-sm" style="width: auto;">
                            <option value="1">Kích hoạt (Hoạt động)</option>
                            <option value="2">Bảo trì (Hiện bảng so sánh)</option>
                            <option value="3">Bảo trì (Ẩn bảng so sánh)</option>
                            <option value="0">Tạm khóa (Ẩn hoàn toàn)</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Áp dụng cho mục đã chọn</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th style="width: 40px"></th>
                                <th>Icon</th>
                                <th>Mã (Key)</th>
                                <th>Tên tính năng (VI)</th>
                                <th>Nhóm</th>
                                <th>Thứ tự</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-features">
                            @forelse ($features as $feature)
                                <tr data-id="{{ $feature->id }}" class="sortable-row">
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $feature->id }}" class="form-check-input select-item">
                                    </td>
                                    <td class="text-center">
                                        <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;"></i>
                                    </td>
                                    <td>
                                        <div class="feature-icon-circle">
                                            <i class="{{ $feature->icon ?: 'fas fa-check' }}"></i>
                                        </div>
                                    </td>
                                    <td><code>{{ $feature->key }}</code></td>
                                    <td><strong>{{ $feature->name_vi }}</strong></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $feature->group ?: 'Chưa phân nhóm' }}
                                        </span>
                                    </td>
                                    <td class="sort-order-text">#{{ $feature->sort_order }}</td>
                                    <td>
                                        @if($feature->is_enabled == 1)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @elseif($feature->is_enabled == 2)
                                            <span class="badge bg-warning text-dark">Bảo trì (Hiện)</span>
                                        @elseif($feature->is_enabled == 3)
                                            <span class="badge bg-warning text-dark border">Bảo trì (Ẩn)</span>
                                        @else
                                            <span class="badge bg-secondary">Tạm khóa</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('teacher-package-features.edit', $feature->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i> Sửa mô tả
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Chưa có tính năng nào được khởi tạo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select All logic
            const selectAll = document.getElementById('select-all');
            const selectItems = document.querySelectorAll('.select-item');
            
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    selectItems.forEach(item => {
                        item.checked = this.checked;
                    });
                });
            }

            // Sync select-all checkbox when individual items are changed
            selectItems.forEach(item => {
                item.addEventListener('change', function() {
                    if (!this.checked) {
                        selectAll.checked = false;
                    } else if (Array.from(selectItems).every(i => i.checked)) {
                        selectAll.checked = true;
                    }
                });
            });

            const el = document.getElementById('sortable-features');

            // Bulk action confirmation
            const bulkForm = document.getElementById('bulk-action-form');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    const checkedCount = document.querySelectorAll('.select-item:checked').length;
                    if (checkedCount === 0) {
                        e.preventDefault();
                        alert('Vui lòng chọn ít nhất một tính năng để thực hiện thao tác này.');
                        return;
                    }
                    
                    if (!confirm(`Bạn có chắc chắn muốn cập nhật trạng thái cho ${checkedCount} tính năng đã chọn?`)) {
                        e.preventDefault();
                    }
                });
            }

            if (el) {
                Sortable.create(el, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'bg-light',
                    onEnd: function() {
                        const ids = Array.from(el.querySelectorAll('.sortable-row')).map(row => row.dataset.id);
                        
                        fetch("{{ route('teacher-package-features.reorder') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ ids: ids })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Cập nhật lại số thứ tự hiển thị (tùy chọn)
                                el.querySelectorAll('.sortable-row').forEach((row, index) => {
                                    row.querySelector('.sort-order-text').textContent = '#' + (index + 1);
                                });
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                });
            }
        });
    </script>
@endsection

@section('stylesheets')
    <style>
        .feature-icon-circle {
            width: 36px;
            height: 36px;
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        code {
            background: #f1f5f9;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            color: #475569;
        }
    </style>
@endsection
