@extends('layouts.backend')

@section('content')
    <p class="text-end">
        <a href="{{ route('coupons.add') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Thêm mã khuyến mãi
        </a>
    </p>

    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif

    @if (session('msg_danger'))
        <div class="alert alert-danger">{{ session('msg_danger') }}</div>
    @endif

    <table id="datatable" class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Mã</th>
                <th>Loại giảm</th>
                <th>Giá trị</th>
                <th>Số lượng</th>
                <th>Thời gian</th>
                <th>Tối thiểu</th>
                <th>Sửa</th>
                <th>Xóa</th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th>Mã</th>
                <th>Loại giảm</th>
                <th>Giá trị</th>
                <th>Số lượng</th>
                <th>Thời gian</th>
                <th>Tối thiểu</th>
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
            $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 5,
                lengthMenu: [2,5,10, 25, 50],
                ajax: "{{ route('coupons.data') }}",
                columns: [{
                        data: 'code'
                    },
                    {
                        data: 'discount_type'
                    },
                    {
                        data: 'discount_value'
                    },
                    {
                        data: 'count'
                    },
                    {
                        data: 'time'
                    },
                    {
                        data: 'total_condition'
                    },
                    {
                        data: 'edit'
                    },
                    {
                        data: 'delete'
                    },
                ],
                language: {
                    processing: "Đang xử lý...",
                    search: "Tìm kiếm:",
                    lengthMenu: "Hiển thị _MENU_ bản ghi",
                    info: "Hiển thị từ _START_ đến _END_ của _TOTAL_ bản ghi",
                    infoEmpty: "Hiển thị 0 đến 0 của 0 bản ghi",
                    infoFiltered: "(lọc từ _MAX_ bản ghi)",
                    loadingRecords: "Đang tải...",
                    zeroRecords: "Không tìm thấy mã khuyến mãi",
                    emptyTable: "Chưa có mã khuyến mãi nào",
                    aria: {
                        sortAscending: ": sắp xếp tăng dần",
                        sortDescending: ": sắp xếp giảm dần"
                    }
                }
            });
        });
    </script>
@endsection
