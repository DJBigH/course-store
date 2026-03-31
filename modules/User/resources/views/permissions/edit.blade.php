@extends('layouts.backend')

@section('content')
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">Vui lòng kiểm tra lại dữ liệu đã nhập.</div>
    @endif

    <form action="{{ route('permissions.update', $permission->id) }}" method="post" class="admin-form permission-form">
        @csrf
        <div class="admin-form__header">
            <div>
                <h5 class="mb-1">Cập nhật quyền</h5>
                <p class="text-muted mb-0">Chỉnh sửa tên hiển thị, slug và module của quyền này.</p>
            </div>
        </div>

        <div class="row p-4 g-4">
            <div class="col-xl-8">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tên quyền</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $permission->name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $permission->slug) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Module</label>
                        <input type="text" name="module" class="form-control" value="{{ old('module', $permission->module) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mô tả</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $permission->description) }}">
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <aside class="permission-form__aside">
                    <h6 class="mb-3">Thông tin nhanh</h6>
                    <div class="permission-form__tip">
                        <strong>Slug hiện tại</strong>
                        <span><code>{{ $permission->slug }}</code></span>
                    </div>
                    <div class="permission-form__tip">
                        <strong>Module</strong>
                        <span>{{ $permission->module ?: 'other' }}</span>
                    </div>
                    <div class="permission-form__tip">
                        <strong>Lưu ý</strong>
                        <span>Nếu đổi `slug`, hãy rà lại route, middleware và các nút đang dùng quyền này.</span>
                    </div>
                </aside>
            </div>
        </div>

        <div class="admin-form__footer">
            <button type="submit" class="btn btn-success">Lưu thay đổi</button>
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

        html[data-theme='dark'] .permission-form__aside {
            border-color: rgba(148, 163, 184, 0.18);
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.96) 0%, rgba(17, 24, 39, 0.92) 100%);
            box-shadow: 0 18px 36px rgba(2, 6, 23, 0.24);
        }

        html[data-theme='dark'] .permission-form__aside h6 {
            color: #e2e8f0;
        }

        html[data-theme='dark'] .permission-form__tip {
            background: rgba(15, 23, 42, 0.84);
            border-color: rgba(148, 163, 184, 0.18);
        }

        html[data-theme='dark'] .permission-form__tip strong {
            color: #f8fafc;
        }

        html[data-theme='dark'] .permission-form__tip span,
        html[data-theme='dark'] .permission-form__tip code {
            color: #94a3b8;
        }
    </style>
@endsection
