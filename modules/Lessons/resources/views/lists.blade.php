@extends('layouts.backend')

@section('content')
    <style>
        .lesson-name-link {
            display: inline-block;
            position: relative;
            z-index: 2;
            color: #0f172a;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .lesson-name-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>

    <p class="text-end">
        <a href="{{ route('courses.index') }}" class="btn btn-info text-white">Quay lại</a>
        @if (auth()->user()?->hasPermission('lessons.sort'))
            <a href="{{ route('lessons.sort', $courses) }}" class="btn btn-success">Sắp xếp bài giảng</a>
        @endif
        @if (auth()->user()?->hasPermission('lessons.create'))
            <a href="{{ route('lessons.add', $courses) }}" class="btn btn-primary">Thêm mới</a>
        @endif
    </p>

    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif

    <table id="datatable" class="table table-bordered">
        <thead>
            <tr>
                <th>Tên</th>
                <th>Học thử</th>
                <th>Tài liệu</th>
                <th>Lượt xem</th>
                <th>Thời lượng</th>
                <th>Trạng thái</th>
                <th>Thêm</th>
                <th>Sửa</th>
                <th>Xóa</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>Tên</th>
                <th>Học thử</th>
                <th>Tài liệu</th>
                <th>Lượt xem</th>
                <th>Thời lượng</th>
                <th>Trạng thái</th>
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
                        data: 'document_id',
                    },
                    {
                        data: 'view',
                    },
                    {
                        data: 'durations',
                    },
                    {
                        data: 'status',
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
                    aria: {
                        sortAscending: ": sắp xếp tăng dần",
                        sortDescending: ": sắp xếp giảm dần"
                    }
                }
            });
        });
    </script>
@endsection
