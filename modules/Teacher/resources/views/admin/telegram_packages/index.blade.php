@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Quản lý gói Telegram</h5>
                    <p class="text-muted mb-0">Tạo và quản lý các gói đăng ký tính năng Telegram cho giảng viên.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('teacher.telegram-packages.create') }}" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-2"></i> Thêm gói mới
                    </a>
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif

            <div class="table-responsive">
                <table id="datatable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Tên gói</th>
                            <th>Giá tiền</th>
                            <th>Thời lượng</th>
                            <th>Thứ tự</th>
                            <th>Trạng thái</th>
                            <th style="width: 80px;">Sửa</th>
                            <th style="width: 80px;">Xóa</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @include('part.backend.delete')
@endsection

@section('stylesheets')
    <style>
        .admin-page-actions {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }
        html[data-theme="dark"] .card { background-color: #1e293b; color: #f1f5f9; }
        html[data-theme="dark"] .admin-page-actions { border-bottom-color: #334155; }
        html[data-theme="dark"] .table { color: #f1f5f9; }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "{{ route('teacher.telegram-packages.data') }}",
                columns: [
                    { data: 'name' },
                    { data: 'price' },
                    { data: 'duration' },
                    { data: 'sort_order' },
                    { data: 'is_active', searchable: false },
                    { data: 'edit', orderable: false, searchable: false },
                    { data: 'delete', orderable: false, searchable: false }
                ],
                language: {
                    processing: 'Đang xử lý...',
                    search: 'Tìm kiếm:',
                    lengthMenu: 'Hiển thị _MENU_ bản ghi',
                    info: 'Hiển thị từ _START_ đến _END_ của _TOTAL_ bản ghi',
                    emptyTable: 'Không có dữ liệu',
                    paginate: { previous: 'Trước', next: 'Tiếp' }
                }
            });
        });
    </script>
@endsection
