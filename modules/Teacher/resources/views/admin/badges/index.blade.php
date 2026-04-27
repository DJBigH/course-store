@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Quản lý huy hiệu giảng viên</h5>
                    <p class="text-muted mb-0">Thiết kế và quản lý các huy hiệu định danh cho giảng viên.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('teacher.badges.trash') }}" class="btn btn-light border">
                        <i class="fa-solid fa-trash-can me-2"></i> Thùng rác
                    </a>
                    <a href="{{ route('teacher.badges.create') }}" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-2"></i> Thêm huy hiệu
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
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-records" class="form-check-input">
                            </th>
                            <th>Tên (VI)</th>
                            <th>Mã định danh</th>
                            <th>Preview</th>
                            <th>Sửa</th>
                            <th>Xóa</th>
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
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Dark Mode Support */
        html[data-theme="dark"] .card {
            background-color: #1e293b;
            color: #f1f5f9;
        }

        html[data-theme="dark"] .admin-page-actions {
            border-bottom-color: #334155;
        }

        html[data-theme="dark"] .btn-light {
            background-color: #334155;
            border-color: #475569;
            color: #f1f5f9;
        }

        html[data-theme="dark"] .btn-light:hover {
            background-color: #475569;
        }

        html[data-theme="dark"] .table {
            color: #f1f5f9;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "{{ route('teacher.badges.data') }}",
                columns: [
                    { data: 'select', orderable: false, searchable: false },
                    { data: 'name' },
                    { data: 'code' },
                    { data: 'preview', orderable: false, searchable: false },
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
