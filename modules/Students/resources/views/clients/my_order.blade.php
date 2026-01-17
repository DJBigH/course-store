@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-4">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                {{-- Content --}}
                <div class="col-lg-9">
                    <div class="account-content card shadow-sm border-0">
                        <div class="card-body p-4">

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h2 class="fw-semibold mb-0">
                                    Khóa học của tôi
                                </h2>
                            </div>

                            <div class="table-responsive" style="overflow-x: unset;">
                                <form method="GET" action="#" class="card shadow-sm border-0 mb-4">
                                    <div class="card-body">
                                        <div class="row g-3 align-items-end">

                                            <!-- Trạng thái -->
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-flag me-1 text-primary"></i> Trạng thái
                                                </label>
                                                <select name="status_id" class="form-select js-select2">
                                                    <option value="">Tất cả trạng thái</option>
                                                    @if (!empty($ordersStatus))
                                                        @foreach ($ordersStatus as $item)
                                                            <option
                                                                value="{{ $item->id }} {{ request()->status_id == $item->id ? 'selected' : '' }}">
                                                                {{ $item->name }}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="">Không có cứ liệu</option>
                                                    @endif
                                                </select>
                                            </div>

                                            <!-- Thời gian bắt đầu -->
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-calendar-event me-1 text-success"></i> Từ ngày
                                                </label>
                                                <input type="date" name="start_date" class="form-control"
                                                    value="{{ request()->start_date }}">
                                            </div>

                                            <!-- Thời gian kết thúc -->
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-calendar-check me-1 text-danger"></i> Đến ngày
                                                </label>
                                                <input type="date" name="end_date" class="form-control"
                                                    value="{{ request()->end_date }}">
                                            </div>

                                            <!-- Tổng tiền -->
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-cash-coin me-1 text-warning"></i> Tổng tiền
                                                </label>

                                                <!-- input hiển thị -->
                                                <input type="text" id="total_display" class="form-control"
                                                    placeholder="Nhập tổng tiền..."
                                                    value="{{ number_format(request()->total) }}">

                                                <!-- input gửi về backend -->
                                                <input type="hidden" name="total" id="total"
                                                    value="{{ request()->total }}">
                                            </div>

                                            <!-- Nút lọc -->
                                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                                </a>
                                                <button type="submit" class="btn btn-primary px-4">
                                                    <i class="bi bi-funnel me-1"></i> Lọc
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </form>


                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-uppercase small">
                                        <tr>
                                            <th class="text-center" style="width: 50px;">#</th>
                                            <th>Mã đơn hàng</th>
                                            <th class="text-end">Tổng tiền</th>
                                            <th class="text-center">Trạng thái</th>
                                            <th class="text-center">Thời gian</th>
                                            <th class="text-center">Hành động</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($orders as $item)
                                            <tr>
                                                <td class="text-center text-muted">
                                                    {{ $loop->iteration }}
                                                </td>

                                                <td class="fw-semibold text-primary">
                                                    {{ $item->code }}
                                                </td>

                                                <td class="text-end fw-semibold text-success">
                                                    {{ money($item->total) }}
                                                </td>

                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-{{ $item->status->color }}-subtle text-{{ $item->status->color }} px-3">
                                                        {{ $item->status->name }}
                                                    </span>
                                                    {{-- @if ($item->status == 'paid')
                                                        <span class="badge bg-success-subtle text-success px-3">
                                                            Đã thanh toán
                                                        </span>
                                                    @elseif ($item->status == 'pending')
                                                        <span class="badge bg-warning-subtle text-warning px-3">
                                                            Chờ xử lý
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger px-3">
                                                            Đã hủy
                                                        </span>
                                                    @endif --}}
                                                </td>

                                                <td class="text-center text-muted small">
                                                    {{ format_date_dmy($item->created_at) }}
                                                </td>

                                                <td class="text-center">
                                                    <a href="#" class="btn btn-outline-primary btn-sm px-3">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                    Bạn chưa có đơn hàng nào
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>
                                <div class="mt-2">
                                    {{ $orders->links('students::clients.pagination.boostrap') }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection

@section('scripts')
    <script>
        const displayInput = document.getElementById('total_display');
        const hiddenInput = document.getElementById('total');

        displayInput.addEventListener('input', function() {
            // Lấy số sạch (chỉ giữ số)
            let rawValue = this.value.replace(/[^\d]/g, '');

            // Gán vào hidden input để submit
            hiddenInput.value = rawValue;

            // Format hiển thị
            if (rawValue) {
                this.value = Number(rawValue).toLocaleString('en-US');
            } else {
                this.value = '';
            }
        });
    </script>
@endsection
