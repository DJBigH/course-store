@extends('layouts.backend')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="permission-stat__label">Tổng số quyền</div>
                    <div class="permission-stat__value">{{ number_format($stats['total'] ?? 0) }}</div>
                    <div class="permission-stat__meta">Các quyền chi tiết đang có trong hệ thống</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="permission-stat__label">Module có quyền</div>
                    <div class="permission-stat__value">{{ number_format($stats['modules'] ?? 0) }}</div>
                    <div class="permission-stat__meta">Nhóm chức năng đã được chia theo module</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="permission-stat__label">Đang được gán</div>
                    <div class="permission-stat__value">{{ number_format($stats['assigned'] ?? 0) }}</div>
                    <div class="permission-stat__meta">Các quyền đã được ít nhất một nhóm sử dụng</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sách quyền</h5>
                    <p class="text-muted mb-0">Tạo, chỉnh sửa và kiểm soát các quyền chi tiết để gán cho từng nhóm nội bộ.</p>
                </div>
                <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-2"></i>
                    Thêm quyền
                </a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif

            @if (session('msg_danger'))
                <div class="alert alert-danger border-0 rounded-4">{{ session('msg_danger') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle permission-table">
                    <thead>
                        <tr>
                            <th>Quyền</th>
                            <th>Slug</th>
                            <th>Module</th>
                            <th>Mô tả</th>
                            <th>Nhóm đang dùng</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr>
                                <td>
                                    <div class="permission-row__title">{{ $permission->name }}</div>
                                </td>
                                <td><code>{{ $permission->slug }}</code></td>
                                <td>
                                    <span class="permission-module-badge">
                                        {{ $permission->module ?: 'other' }}
                                    </span>
                                </td>
                                <td>{{ $permission->description ?: 'Chưa có mô tả.' }}</td>
                                <td>
                                    <span class="permission-usage {{ $permission->groups_count > 0 ? 'is-active' : '' }}">
                                        {{ $permission->groups_count }} nhóm
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-wrap gap-2">
                                        <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-light border btn-sm">
                                            Sửa
                                        </a>
                                        <form action="{{ route('permissions.delete', $permission->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Xóa quyền này khỏi hệ thống?')"
                                                @disabled($permission->groups_count > 0)>
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chưa có quyền nào trong hệ thống.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $permissions->links() }}
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .permission-stat__label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 0.35rem;
        }

        .permission-stat__value {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
            margin-bottom: 0.35rem;
        }

        .permission-stat__meta {
            color: #64748b;
            font-size: 0.92rem;
        }

        .permission-table td {
            padding-top: 1rem;
            padding-bottom: 1rem;
            vertical-align: middle;
        }

        .permission-row__title {
            font-weight: 700;
            color: #0f172a;
        }

        .permission-module-badge,
        .permission-usage {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .permission-usage.is-active {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }
    </style>
@endsection
