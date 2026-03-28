@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sách đơn hàng</h5>
                    <p class="text-muted mb-0">Theo dõi mã đơn, tổng tiền, trạng thái, phương thức thanh toán và truy cập chi
                        tiết nhanh.</p>
                </div>
                @if (auth()->user()
                        ?->canAnyPermission(['orders.soft_delete', 'orders.delete', 'orders.force_delete']))
                    <a href="{{ route('orders.trash') }}" class="btn btn-light border">
                        <i class="fa-solid fa-trash-can me-2"></i>
                        Thùng rác
                    </a>
                @endif
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger border-0 rounded-4">{{ session('msg_danger') }}</div>
            @endif
            @if ($errors->has('bulk_action'))
                <div class="alert alert-danger border-0 rounded-4">{{ $errors->first('bulk_action') }}</div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label">Phương thức thanh toán</label>
                    <select id="payment-method-filter" class="form-select">
                        <option value="">Tất cả phương thức</option>
                        <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                        <option value="vnpay">VNPay</option>
                        <option value="momo">MoMo</option>
                        <option value="free">Miễn phí</option>
                        <option value="unknown">Chưa xác định</option>
                    </select>
                </div>
            </div>

            @if (auth()->user()
                    ?->canAnyPermission(['orders.update', 'orders.soft_delete', 'orders.delete']))
                <form id="bulk-action-form" action="{{ route('orders.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-ids">
                    <input type="hidden" name="bulk_action" id="bulk-action-input">

                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-count">0</span> Đơn hàng được chọn
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()?->hasPermission('orders.update'))
                                <button type="button" class="btn btn-light border bulk-action-trigger"
                                    data-action="cancel">Hủy đơn</button>
                            @endif
                            @if (auth()->user()
                                    ?->canAnyPermission(['orders.soft_delete', 'orders.delete']))
                                <button type="button" class="btn btn-outline-danger bulk-action-trigger"
                                    data-action="delete">Xóa</button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table id="datatable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-records" class="form-check-input">
                            </th>
                            <th>Mã đơn hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Phương thức thanh toán</th>
                            <th>Ngày tạo đơn</th>
                            <th>Chi tiết</th>
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
        .bulk-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.1rem;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .bulk-toolbar__summary {
            font-weight: 600;
            color: #334155;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const selectedIds = new Set();

            const table = $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                ajax: {
                    url: "{{ route('orders.data') }}",
                    data: function(d) {
                        d.payment_method_filter = $('#payment-method-filter').val();
                    }
                },
                columns: [{
                        data: 'select',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'code'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'status_id'
                    },
                    {
                        data: 'payment_method',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'detail'
                    },
                    {
                        data: 'delete'
                    }
                ],
                language: {
                    processing: 'Đang xử lý...',
                    search: 'Tìm kiếm:',
                    lengthMenu: 'Hiển thị _MENU_ bản ghi',
                    info: 'Hiển thị từ _START_ đến _END_ của _TOTAL_ bản ghi',
                    infoEmpty: 'Hiển thị 0 đến 0 của 0 bản ghi',
                    infoFiltered: '(lọc từ _MAX_ bản ghi)',
                    loadingRecords: 'Đang tải...',
                    zeroRecords: 'Không tìm thấy bản ghi nào',
                    emptyTable: 'Không có dữ liệu trong bảng',
                    paginate: {
                        previous: 'Trước',
                        next: 'Tiếp'
                    },
                    aria: {
                        sortAscending: ': sắp xếp tăng dần',
                        sortDescending: ': sắp xếp giảm dần'
                    }
                },
                drawCallback: function() {
                    syncCheckboxState();
                }
            });

            function syncCheckboxState() {
                $('.bulk-row-checkbox').each(function() {
                    $(this).prop('checked', selectedIds.has($(this).val()));
                });

                $('#selected-count').text(selectedIds.size);

                const visibleCheckboxes = $('.bulk-row-checkbox');
                const checkedVisible = visibleCheckboxes.filter(':checked').length;
                $('#select-all-records').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes
                    .length === checkedVisible);
            }

            $('#datatable').on('change', '.bulk-row-checkbox', function() {
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }

                syncCheckboxState();
            });

            $('#select-all-records').on('change', function() {
                $('.bulk-row-checkbox').each(function() {
                    const id = $(this).val();

                    if ($('#select-all-records').is(':checked')) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });

                syncCheckboxState();
            });

            $('.bulk-action-trigger').on('click', function() {
                if (selectedIds.size === 0) {
                    alert('Vui lòng chọn ít nhất một đơn hàng.');
                    return;
                }

                $('#selected-ids').val(Array.from(selectedIds).join(','));
                $('#bulk-action-input').val($(this).data('action'));
                $('#bulk-action-form').trigger('submit');
            });

            $('#payment-method-filter').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
