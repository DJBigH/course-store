@extends('layouts.backend')
@section('content')
    <p class="text-end">
        <a href="{{ route('courses.index') }}" class="btn btn-info text-white">Quay lại</a>
        <a href="{{ route('lessons.sort', $courses) }}" class="btn btn-success">Sắp xếp bài giảng</a>
        <a href="{{ route('lessons.add', $courses) }}" class="btn btn-primary">Thêm mới</a>
    </p>
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    <table id="datatable" class="table table-bordered">
        <thead>
            <tr>
                <th>Tên</th>
                <th>Học thử</th>
                <th>Lượt xem</th>
                <th>Thời lượng</th>
                <th>Thêm</th>
                <th>Sửa</th>
                <th>Xóa</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>Tên</th>
                <th>Học thử</th>
                <th>Lượt xem</th>
                <th>Thời lượng</th>
                <th>Thêm</th>
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
                lengthMenu: [2, 5, 10, 25, 50],
                ajax: "{{ route('lessons.data', $courses->id) }}",
                columns: [{
                        data: 'name',
                    },
                    {
                        data: 'is_trial',
                    },
                    {
                        data: 'view',
                    },
                    {
                        data: 'durations',
                    },
                    {
                        data: 'add',
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
                    // paginate: {
                    //     first:      "Đầu",
                    //     previous:   "Trước",
                    //     next:       "Tiếp",
                    //     last:       "Cuối"
                    // },
                    aria: {
                        sortAscending: ": sắp xếp tăng dần",
                        sortDescending: ": sắp xếp giảm dần"
                    }
                }
            });
        });
    </script>
@endsection
