@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sách bình luận khóa học</h5>
                    <p class="text-muted mb-0">Lọc theo khóa học, vai trò người gửi, trạng thái hiển thị và xử lý hàng loạt.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="btn btn-light border">Về danh sách khóa học</a>
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

            <form method="GET" class="mb-4 admin-filter-panel">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Khóa học</label>
                        <select name="course_id" class="form-select">
                            <option value="">Tất cả</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hiển thị</label>
                        <select name="visibility" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="visible" @selected(request('visibility') === 'visible')>Đang hiện</option>
                            <option value="hidden" @selected(request('visibility') === 'hidden')>Đang ẩn</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Người gửi</label>
                        <select name="role" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="student" @selected(request('role') === 'student')>Học viên</option>
                            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cảnh báo</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="flagged" name="flagged"
                                @checked(request()->boolean('flagged'))>
                            <label class="form-check-label" for="flagged">Chỉ hiện comment bị flag</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Từ khóa</label>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                            placeholder="Nhập nội dung cần tìm...">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary">Lọc</button>
                    <a href="{{ route('courses.comments.admin') }}" class="btn btn-light border">Xóa lọc</a>
                </div>
            </form>

            <form method="POST" action="{{ route('courses.comments.admin-bulk') }}" id="comment-bulk-form">
                @csrf
                <input type="hidden" name="selected_ids" id="selected-comment-ids">
                <input type="hidden" name="bulk_action" id="comment-bulk-action">

                <div class="bulk-toolbar mb-3">
                    <div class="bulk-toolbar__summary">
                        <span id="selected-count">0</span> bình luận được chọn
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-success comment-bulk-trigger" data-action="show">Hiện đã chọn</button>
                        <button type="button" class="btn btn-light border comment-bulk-trigger" data-action="hide">Ẩn đã chọn</button>
                        <button type="button" class="btn btn-outline-danger comment-bulk-trigger" data-action="delete">Xóa đã chọn</button>
                    </div>
                </div>

                <div class="bulk-toolbar bulk-toolbar--soft mb-3">
                    <div class="bulk-toolbar__summary">
                        Tác vụ theo vai trò trong vùng chọn
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-success comment-bulk-trigger" data-action="show_student">Hiện comment học viên</button>
                        <button type="button" class="btn btn-outline-secondary comment-bulk-trigger" data-action="hide_student">Ẩn comment học viên</button>
                        <button type="button" class="btn btn-outline-danger comment-bulk-trigger" data-action="delete_student">Xóa comment học viên</button>
                        <button type="button" class="btn btn-outline-primary comment-bulk-trigger" data-action="show_admin">Hiện phản hồi admin</button>
                        <button type="button" class="btn btn-outline-secondary comment-bulk-trigger" data-action="hide_admin">Ẩn phản hồi admin</button>
                        <button type="button" class="btn btn-outline-danger comment-bulk-trigger" data-action="delete_admin">Xóa phản hồi admin</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 48px;">
                                    <input type="checkbox" id="select-all-comments" class="form-check-input">
                                </th>
                                <th>#</th>
                                <th>Khóa học</th>
                                <th>Người gửi</th>
                                <th>Nội dung</th>
                                <th>Trạng thái</th>
                                <th>Flag</th>
                                <th>Thời gian</th>
                                <th>Xử lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($comments as $comment)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input comment-row-checkbox" value="{{ $comment->id }}">
                                    </td>
                                    <td>{{ $comment->id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $comment->course?->name ?? 'N/A' }}</div>
                                        @if ($comment->parent_id)
                                            <small class="text-muted">Trả lời cho comment #{{ $comment->parent_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $comment->user_id ? 'bg-primary' : 'bg-success' }}">
                                            {{ $comment->user_id ? 'Admin' : 'Học viên' }}
                                        </span>
                                        <div class="mt-1">{{ $comment->author_name }}</div>
                                    </td>
                                    <td style="min-width: 320px;">
                                        <div>{{ $comment->content }}</div>
                                        @if ($comment->flagged_terms)
                                            <div class="small text-danger mt-1">Từ khóa: {{ $comment->flagged_terms }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $comment->is_visible ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $comment->is_visible ? 'Đang hiện' : 'Đang ẩn' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($comment->is_flagged)
                                            <span class="badge bg-danger">Bị flag</span>
                                        @else
                                            <span class="badge bg-light text-dark">Bình thường</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($comment->created_at)->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <div class="d-grid gap-2">
                                            <form method="POST" action="{{ route('courses.comments.admin-toggle', ['commentId' => $comment->id]) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-secondary w-100">
                                                    {{ $comment->is_visible ? 'Ẩn' : 'Hiện' }}
                                                </button>
                                            </form>
                                            @if ($comment->course)
                                                <a href="{{ route('courses.detail', ['locale' => 'vi', 'slug' => $comment->course->slug]) }}#evaluate"
                                                    target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                                    Xem ngoài client
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Chưa có bình luận nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-3">
        {{ $comments->links() }}
    </div>
@endsection

@section('stylesheets')
    <style>
        .admin-filter-panel {
            padding: 1.1rem;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

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

        .bulk-toolbar--soft {
            background: #fff;
        }

        .bulk-toolbar__summary {
            font-weight: 600;
            color: #334155;
        }

        html[data-theme="dark"] .admin-filter-panel {
            background: linear-gradient(180deg, #162033 0%, #111827 100%);
            border-color: #2b3b53;
        }

        html[data-theme="dark"] .bulk-toolbar {
            background: #162033;
            border-color: #2b3b53;
        }

        html[data-theme="dark"] .bulk-toolbar--soft {
            background: #111827;
        }

        html[data-theme="dark"] .bulk-toolbar__summary {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .table tbody td,
        html[data-theme="dark"] .table tbody div,
        html[data-theme="dark"] .table tbody a {
            color: #e2e8f0;
        }

        html[data-theme="dark"] .badge.bg-light.text-dark {
            background: #f8fafc !important;
            color: #0f172a !important;
        }

        html[data-theme="dark"] .btn-light.border {
            background: #1e293b;
            color: #f8fafc;
            border-color: #334155 !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectedIds = new Set();

            function syncCheckboxState() {
                document.querySelectorAll('.comment-row-checkbox').forEach((checkbox) => {
                    checkbox.checked = selectedIds.has(checkbox.value);
                });

                document.getElementById('selected-count').textContent = selectedIds.size;

                const rowCheckboxes = Array.from(document.querySelectorAll('.comment-row-checkbox'));
                const checkedVisible = rowCheckboxes.filter((checkbox) => checkbox.checked).length;
                document.getElementById('select-all-comments').checked = rowCheckboxes.length > 0 && rowCheckboxes.length === checkedVisible;
            }

            document.querySelectorAll('.comment-row-checkbox').forEach((checkbox) => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        selectedIds.add(this.value);
                    } else {
                        selectedIds.delete(this.value);
                    }

                    syncCheckboxState();
                });
            });

            document.getElementById('select-all-comments').addEventListener('change', function() {
                document.querySelectorAll('.comment-row-checkbox').forEach((checkbox) => {
                    if (this.checked) {
                        selectedIds.add(checkbox.value);
                    } else {
                        selectedIds.delete(checkbox.value);
                    }
                });

                syncCheckboxState();
            });

            document.querySelectorAll('.comment-bulk-trigger').forEach((button) => {
                button.addEventListener('click', function() {
                    if (selectedIds.size === 0) {
                        alert('Vui lòng chọn ít nhất một bình luận.');
                        return;
                    }

                    if (this.dataset.action.includes('delete') && !confirm('Xóa các bình luận phù hợp trong vùng chọn?')) {
                        return;
                    }

                    document.getElementById('selected-comment-ids').value = Array.from(selectedIds).join(',');
                    document.getElementById('comment-bulk-action').value = this.dataset.action;
                    document.getElementById('comment-bulk-form').submit();
                });
            });
        });
    </script>
@endsection
