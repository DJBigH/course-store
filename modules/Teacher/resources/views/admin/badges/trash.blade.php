@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Thùng rác huy hiệu</h5>
                    <p class="text-muted mb-0">Khôi phục hoặc xóa vĩnh viễn các huy hiệu đã xóa.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('teacher.badges.index') }}" class="btn btn-light border">
                        <i class="fa-solid fa-arrow-left me-2"></i> Trở về danh sách
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
                            <th>Tên (VI)</th>
                            <th>Mã định danh</th>
                            <th>Khôi phục</th>
                            <th>Xóa vĩnh viễn</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
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
                ajax: "{{ route('teacher.badges.trash.data') }}",
                columns: [
                    { data: 'name' },
                    { data: 'code' },
                    { data: 'restore', orderable: false, searchable: false },
                    { data: 'force_delete', orderable: false, searchable: false }
                ],
                language: {
                    processing: 'Đang xử lý...',
                    search: 'Tìm kiếm:',
                    lengthMenu: 'Hiển thị _MENU_ bản ghi',
                    info: 'Hiển thị từ _START_ đến _END_ của _TOTAL_ bản ghi',
                    emptyTable: 'Thùng rác trống',
                    paginate: { previous: 'Trước', next: 'Tiếp' }
                }
            });
        });
    </script>
@endsection
