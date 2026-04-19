@extends('layouts.backend')

@section('title', 'Danh sách yêu cầu hủy hợp tác')

@section('content')
    @if (session('msg_success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('msg_success') }}
        </div>
    @endif

    @if (session('msg_danger'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('msg_danger') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom">
                    <h3 class="card-title fw-bold">Danh sách yêu cầu</h3>
                    <div class="card-tools">
                        <form action="" method="GET" class="input-group input-group-sm" style="width: 250px;">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Đang chờ</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã chấp nhận</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                            </select>
                        </form>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">#ID</th>
                                <th>Giảng viên</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                                <th>Thời gian gửi</th>
                                <th>Người xử lý</th>
                                <th class="text-end pe-3">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                <tr>
                                    <td class="ps-3">{{ $request->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img src="{{ $request->teacher->image ? asset($request->teacher->image) : asset('resources/assets/teacher.png') }}" alt="" class="rounded-circle shadow-sm" width="40" height="40" style="object-fit: cover;">
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-bold text-body">{{ $request->teacher->name }}</div>
                                                <small class="text-muted">{{ $request->teacher->student->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block text-body" style="max-width: 250px;" title="{{ $request->reason }}">
                                            {{ $request->reason }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($request->status === 'pending')
                                            <span class="badge bg-warning text-dark px-2">Đang chờ</span>
                                        @elseif($request->status === 'approved')
                                            <span class="badge bg-success px-2">Đã chấp nhận</span>
                                        @else
                                            <span class="badge bg-danger px-2">Đã từ chối</span>
                                        @endif
                                    </td>
                                    <td><span class="text-body">{{ $request->created_at->format('d/m/Y H:i') }}</span></td>
                                    <td>
                                        @if ($request->processor)
                                            <div class="small">
                                                <div class="fw-bold text-body">{{ $request->processor->name }}</div>
                                                <div class="text-muted small">{{ $request->processed_at->format('d/m/Y H:i') }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        @if ($request->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#modal-action" 
                                                data-id="{{ $request->id }}" 
                                                data-teacher="{{ $request->teacher->name }}"
                                                data-reason="{{ $request->reason }}"
                                                data-action="approve">
                                                <i class="fas fa-check me-1"></i>Duyệt
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modal-action" 
                                                data-id="{{ $request->id }}" 
                                                data-teacher="{{ $request->teacher->name }}"
                                                data-reason="{{ $request->reason }}"
                                                data-action="reject">
                                                <i class="fas fa-times me-1"></i>Từ chối
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-view" 
                                                data-teacher="{{ $request->teacher->name }}"
                                                data-reason="{{ $request->reason }}"
                                                data-note="{{ $request->admin_note }}"
                                                data-status="{{ $request->status }}">
                                                <i class="fas fa-eye me-1"></i>Chi tiết
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center p-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                                        Không có yêu cầu nào cần xử lý.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                @if($requests->hasPages())
                <div class="card-footer bg-transparent border-top text-end">
                    {{ $requests->links() }}
                </div>
                @endif
            </div>
            <!-- /.card -->
        </div>
    </div>

    <!-- Modal Action -->
    <div class="modal fade" id="modal-action" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <form action="" method="POST" id="form-action">
                    @csrf
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title fw-bold" id="modal-action-title">Xử lý yêu cầu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="alert alert-info border-0 shadow-sm mb-4" style="background: rgba(var(--bs-info-rgb), 0.1); color: var(--admin-text) !important;">
                            <div class="mb-2"><strong>Giảng viên:</strong> <span id="span-teacher" class="fw-bold"></span></div>
                            <div class="mb-0"><strong>Lý do:</strong> <span id="span-reason" class="font-italic"></span></div>
                        </div>
                        <div class="mb-3">
                            <label for="admin_note" class="form-label fw-bold">Ghi chú của Admin <span class="text-danger">*</span></label>
                            <textarea name="admin_note" id="admin_note" rows="4" class="form-control" required placeholder="Nhập lý do chấp nhận hoặc từ chối để giảng viên được biết..."></textarea>
                        </div>
                        <div id="approve-warning" class="alert alert-warning border-0 small d-none shadow-sm mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i> <strong>Lưu ý:</strong> Hành động này sẽ khóa quyền giảng viên và ẩn toàn bộ bài giảng ngay lập tức.
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btn-action-submit">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal View -->
    <div class="modal fade" id="modal-view" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Chi tiết yêu cầu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Giảng viên:</label>
                        <div id="view-teacher" class="h6 fw-bold text-body"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Lý do từ giảng viên:</label>
                        <div class="p-3 bg-secondary bg-opacity-10 rounded-3 text-body" id="view-reason"></div>
                    </div>
                    <div class="pt-3 border-top">
                        <label class="form-label fw-bold text-muted small text-uppercase" id="view-note-label">Phản hồi của Admin:</label>
                        <div class="p-3 border-start border-4 bg-secondary bg-opacity-10 rounded-2 text-body" id="view-note"></div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('stylesheets')
<style>
    /* Fix hover text disappearing in Dark Mode */
    html[data-theme="dark"] .table-hover tbody tr:hover * {
        color: var(--admin-text) !important;
    }
    html[data-theme="dark"] .table-hover tbody tr:hover {
        background-color: var(--admin-hover-bg) !important;
    }
    .badge { font-weight: 600; letter-spacing: 0.02em; }
    .card-tools .form-select { height: 35px; padding-top: 2px; padding-bottom: 2px; border-radius: 8px; }
    .table thead th { border-top: 0; }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Modal Action (Approve/Reject)
        const modalAction = document.getElementById('modal-action');
        if (modalAction) {
            modalAction.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const teacher = button.getAttribute('data-teacher');
                const reason = button.getAttribute('data-reason');
                const action = button.getAttribute('data-action');
                
                const modal = this;
                modal.querySelector('#span-teacher').textContent = teacher;
                modal.querySelector('#span-reason').textContent = reason;
                modal.querySelector('#admin_note').value = '';

                const form = modal.querySelector('#form-action');
                const title = modal.querySelector('#modal-action-title');
                const submitBtn = modal.querySelector('#btn-action-submit');
                const warning = modal.querySelector('#approve-warning');

                if (action === 'approve') {
                    title.textContent = 'Chấp nhận yêu cầu hủy hợp tác';
                    submitBtn.className = 'btn btn-success px-4 shadow-sm';
                    submitBtn.textContent = 'Duyệt ngay';
                    warning.classList.remove('d-none');
                    form.setAttribute('action', "{{ url('admin/teacher-finance/cancellations') }}/" + id + "/approve");
                } else {
                    title.textContent = 'Từ chối yêu cầu hủy hợp tác';
                    submitBtn.className = 'btn btn-danger px-4 shadow-sm';
                    submitBtn.textContent = 'Từ chối';
                    warning.classList.add('d-none');
                    form.setAttribute('action', "{{ url('admin/teacher-finance/cancellations') }}/" + id + "/reject");
                }
            });
        }

        // Handle Modal View (Details)
        const modalView = document.getElementById('modal-view');
        if (modalView) {
            modalView.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const teacher = button.getAttribute('data-teacher');
                const reason = button.getAttribute('data-reason');
                const note = button.getAttribute('data-note');
                const status = button.getAttribute('data-status');
                
                const modal = this;
                modal.querySelector('#view-teacher').textContent = teacher;
                modal.querySelector('#view-reason').textContent = reason;
                modal.querySelector('#view-note').textContent = note || '(Trống)';
                
                const noteLabel = modal.querySelector('#view-note-label');
                const noteBox = modal.querySelector('#view-note');
                
                if (status === 'approved') {
                    noteLabel.textContent = 'Ghi chú chấp nhận:';
                    noteBox.className = 'p-3 border-start border-4 border-success bg-light rounded-2 text-body';
                } else {
                    noteLabel.textContent = 'Lý do từ chối:';
                    noteBox.className = 'p-3 border-start border-4 border-danger bg-light rounded-2 text-body';
                }
            });
        }
    });
</script>
@endsection
