@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Thùng rác quyền</h5>
                    <p class="text-muted mb-0">Khôi phục hoặc xóa vĩnh viễn các quyền đã đưa vào thúng rác.</p>
                </div>
                <a href="{{ route('permissions.index') }}" class="btn btn-light border">
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    Quay lại
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
                            <th>Nhóm đang dùng</th>
                            <th>Thời gian xóa</th>
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
                                <td>
                                    <span class="permission-usage {{ $permission->groups_count > 0 ? 'is-active' : '' }}">
                                        {{ $permission->groups_count }} nhóm
                                    </span>
                                </td>
                                <td>{{ optional($permission->deleted_at)?->format('d/m/Y H:i:s') }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-wrap gap-2">
                                        <form action="{{ route('permissions.restore', $permission->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Khôi phục</button>
                                        </form>
                                        <form action="{{ route('permissions.force-delete', $permission->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Xóa vĩnh viễn quyền này khỏi hệ thốngg?')"
                                                @disabled($permission->groups_count > 0)>
                                                Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Thùng rác quyền đang trống.</td>
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
