@extends('layouts.backend')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">Vui lòng kiểm tra lại dữ liệu đã nhập.</div>
    @endif

    <form action="{{ route('permissions.store') }}" method="post" class="admin-form permission-form">
        @csrf
        <div class="admin-form__header">
            <div>
                <h5 class="mb-1">Thêm quyền mới</h5>
                <p class="text-muted mb-0">Tạo quyền chi tiết để gán linh hoạt cho từng nhóm nội bộ theo module.</p>
            </div>
        </div>

        <div class="row p-4 g-4">
            <div class="col-xl-8">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tên quyền</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                            placeholder="Ví dụ: Xem báo cáo doanh thu">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}"
                            placeholder="Ví dụ: reports.view">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Module</label>
                        <input type="text" name="module" class="form-control" value="{{ old('module') }}"
                            placeholder="Ví dụ: reports">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mô tả</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}"
                            placeholder="Ghi chú ngắn để người quản trị dễ hiểu">
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <aside class="permission-form__aside">
                    <h6 class="mb-3">Gợi ý đặt quyền</h6>
                    <div class="permission-form__tip">
                        <strong>Mẫu nên dùng</strong>
                        <span>`module.action` như `courses.publish`, `orders.update`, `logs.view`.</span>
                    </div>
                    <div class="permission-form__tip">
                        <strong>Module</strong>
                        <span>Dùng tên ngắn, nhất quán để gom quyền theo nhóm chức năng.</span>
                    </div>
                    <div class="permission-form__tip">
                        <strong>Tên hiển thị</strong>
                        <span>Viết rõ nghĩa để khi gán quyền người dùng nhìn là hiểu ngay.</span>
                    </div>
                </aside>
            </div>
        </div>

        <div class="admin-form__footer">
            <button type="submit" class="btn btn-success">Lưu quyền</button>
            <a href="{{ route('permissions.index') }}" class="btn btn-light border">Trở về</a>
        </div>
    </form>
@endsection

@section('stylesheets')
    <style>
        .permission-form__aside {
            border: 1px solid #dbe4f0;
            border-radius: 24px;
            padding: 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            height: 100%;
        }

        .permission-form__tip {
            display: grid;
            gap: 0.35rem;
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .permission-form__tip + .permission-form__tip {
            margin-top: 0.75rem;
        }

        .permission-form__tip strong {
            color: #0f172a;
        }

        .permission-form__tip span {
            color: #64748b;
            font-size: 0.92rem;
        }
    </style>
@endsection
