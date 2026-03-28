@extends('layouts.backend')

@section('content')
    @php
        $isSuperAdmin = optional($currentUser?->group)->slug === 'super_admin';
    @endphp

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Nhóm quyền</h5>
                    <p class="text-muted mb-0">Quản lý vai trò nội bộ và mở nhanh màn gán quyền cho từng nhóm.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if (auth()->user()?->hasPermission('permissions.manage'))
                        <form action="{{ route('groups.sync-permissions') }}" method="post" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-light border">
                                <i class="fa-solid fa-rotate me-2"></i>
                                Đồng bộ permission
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('groups.create') }}" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-2"></i>
                        Thêm nhóm quyền
                    </a>
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif

            @if ($errors->has('group'))
                <div class="alert alert-danger border-0 rounded-4">{{ $errors->first('group') }}</div>
            @endif

            @if (!empty($syncStatus['has_missing']))
                <div class="alert alert-warning border-0 rounded-4 d-flex flex-wrap justify-content-between gap-3 align-items-start">
                    <div>
                        <strong>Database đang thiếu {{ $syncStatus['missing_count'] }} permission mới so với code.</strong>
                        <div class="small mt-1">
                            Ví dụ:
                            {{ collect($syncStatus['missing_permissions'])->take(6)->pluck('slug')->implode(', ') }}
                            @if ($syncStatus['missing_count'] > 6)
                                ...
                            @endif
                        </div>
                    </div>
                    @if (auth()->user()?->hasPermission('permissions.manage'))
                        <form action="{{ route('groups.sync-permissions') }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                Đồng bộ ngay
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="row g-3">
                @forelse ($groups as $group)
                    @php
                        $isCurrentGroup = (int) ($currentUser?->group_id ?? 0) === (int) $group->id;
                        $isProtectedSuperAdmin = $group->slug === 'super_admin' && !$isSuperAdmin;
                        $canManageThisGroup = $isSuperAdmin || (!$isProtectedSuperAdmin && !$isCurrentGroup);
                    @endphp

                    <div class="col-12 col-lg-6">
                        <article class="group-card {{ !$canManageThisGroup ? 'group-card--locked' : '' }}">
                            <div class="group-card__header">
                                <div>
                                    <h6 class="mb-1">{{ $group->name }}</h6>
                                    <code>{{ $group->slug }}</code>
                                </div>
                                <span class="group-card__badge {{ $group->is_admin ? 'is-admin' : '' }}">
                                    {{ $group->is_admin ? 'Có admin panel' : 'Không vào admin' }}
                                </span>
                            </div>

                            <p class="text-muted mb-3">
                                {{ $group->description ?: 'Chưa có mô tả cho nhóm quyền này.' }}
                            </p>

                            <div class="group-card__meta">
                                <div class="group-card__stat">
                                    <strong>{{ $group->permissions_count }}</strong>
                                    <span>permission</span>
                                </div>
                                <div class="group-card__stat">
                                    <strong>#{{ $group->id }}</strong>
                                    <span>ID nhóm</span>
                                </div>
                                <div class="group-card__stat">
                                    <strong>{{ $group->users_count }}</strong>
                                    <span>người dùng</span>
                                </div>
                            </div>

                            @if (!$canManageThisGroup)
                                <div class="group-card__note mt-3">
                                    @if ($isProtectedSuperAdmin)
                                        Chỉ tài khoản thuộc nhóm Super Admin mới được chỉnh quyền của Super Admin.
                                    @elseif ($isCurrentGroup)
                                        Bạn không thể tự chỉnh nhóm quyền mà tài khoản hiện tại đang sử dụng.
                                    @endif
                                </div>
                            @endif

                            <div class="mt-3 d-flex flex-wrap gap-2">
                                @if ($canManageThisGroup)
                                    <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-warning">
                                        Sửa quyền
                                    </a>
                                    <form action="{{ route('groups.delete', $group->id) }}" method="post"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa nhóm quyền này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger"
                                            @disabled($group->users_count > 0 || $group->slug === 'super_admin' || $isCurrentGroup)>
                                            Xóa nhóm
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-light border" disabled>Không được phép chỉnh</button>
                                @endif
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-4">Chưa có nhóm quyền nào.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .group-card {
            height: 100%;
            border: 1px solid #dbe4f0;
            border-radius: 24px;
            padding: 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .group-card--locked {
            border-color: #fde68a;
            background: linear-gradient(180deg, #fffdf5 0%, #fffaf0 100%);
        }

        .group-card__header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .group-card__badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            background: #eef2f7;
            color: #475569;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .group-card__badge.is-admin {
            background: #dcfce7;
            color: #166534;
        }

        .group-card__meta {
            display: flex;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .group-card__stat {
            min-width: 120px;
            padding: 0.85rem 1rem;
            border-radius: 18px;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .group-card__stat strong {
            display: block;
            color: #0f172a;
            font-size: 1.1rem;
        }

        .group-card__stat span {
            color: #64748b;
            font-size: 0.88rem;
        }

        .group-card__note {
            padding: 0.8rem 1rem;
            border-radius: 16px;
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #92400e;
            font-size: 0.92rem;
        }
    </style>
@endsection
