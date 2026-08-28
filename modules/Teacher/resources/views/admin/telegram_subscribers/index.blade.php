@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">{{ $pageTitle }}</h5>
                    <p class="text-muted mb-0">Quản lý danh sách giảng viên đang sử dụng tính năng Telegram.</p>
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif

            <div class="table-responsive">
                <table id="subscribers-table" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Giảng viên</th>
                            <th>Gói đăng ký</th>
                            <th>Số tiền</th>
                            <th>Ngày hết hạn</th>
                            <th>Trạng thái</th>
                            <th style="width: 80px;">Thao tác</th>
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
        html[data-theme="dark"] .card { background-color: #1e293b; color: #f1f5f9; }
        html[data-theme="dark"] .admin-page-actions { border-bottom-color: #334155; }
        html[data-theme="dark"] .table { color: #f1f5f9; }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#subscribers-table').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "{{ route('teacher.telegram-subscribers.data') }}",
                columns: [
                    { data: 'teacher_name' },
                    { data: 'package_name' },
                    { data: 'amount' },
                    { data: 'expires_at' },
                    { data: 'status' },
                    { data: 'actions', orderable: false, searchable: false }
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

            $(document).on('click', '.cancel-subscriber', function() {
                var id = $(this).data('id');
                if (confirm('Bạn có chắc chắn muốn hủy gói Telegram của người dùng này?')) {
                    $.ajax({
                        url: "{{ route('teacher.telegram-subscribers.cancel', '') }}/" + id,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                table.ajax.reload();
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection
