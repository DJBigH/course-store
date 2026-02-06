@extends('layouts.backend')
@section('content')
    <p class="text-end"><a href="{{ route('teacher.add') }}" class="btn btn-primary">Thêm mới</a></p>
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    <table id="datatable" class="table table-bordered">
        <thead>
            <tr>
                <th>Ảnh</th>
                <th>Tên</th>
                <th>Kinh nghiệm</th>
                <th>Thời gian</th>
                <th>Lịch sử</th>
                <th>Sửa</th>
                <th>Xóa</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>Ảnh</th>
                <th>Tên</th>
                <th>Kinh nghiệm</th>
                <th>Thời gian</th>
                <th>Lịch sử</th>
                <th>Sửa</th>
                <th>Xóa</th>
            </tr>
        </tfoot>
    </table>
    @include('part.backend.delete')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#datatable").DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50],
                ajax: "{{ route('teacher.data') }}",
                columns: [{
                        data: 'image',
                    },
                    {
                        data: 'name',
                    },
                    {
                        data: 'exp',
                    },
                    {
                        data: 'created_at',
                    },
                    {
                        data: 'logs',
                    },
                    {
                        data: 'edit',
                    },
                    {
                        data: 'delete',
                    }
                ],
                language: {
                    processing: "Đang xử lý...",
                    search: "Tìm kiếm:",
                    lengthMenu: "Hiển thị _MENU_ bản ghi",
                    info: "Hiển thị từ _START_ đến _END_ của _TOTAL_ bản ghi",
                    infoEmpty: "Hiển thị 0 đến 0 của 0 bản ghi",
                    infoFiltered: "(lọc từ _MAX_ bản ghi)",
                    infoPostFix: "",
                    loadingRecords: "Đang tải...",
                    zeroRecords: "Không tìm thấy bản ghi nào",
                    emptyTable: "Không có dữ liệu trong bảng",
                    aria: {
                        sortAscending: ": sắp xếp tăng dần",
                        sortDescending: ": sắp xếp giảm dần"
                    }
                }
            });
        });
    </script>
@endsection
