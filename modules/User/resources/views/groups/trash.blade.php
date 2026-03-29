@extends('layouts.backend')

@section('content')
    @php
        $isSuperAdmin = optional($currentUser?->group)->slug === 'super_admin';
    @endphp

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Thùng rác nhóm quyền</h5>
                    <p class="text-muted mb-0">Khôi phục hoặc xóa vĩnh viễn các nhóm quyền đã đưa vào thùng rác.</p>
                </div>
                <a href="{{ route('groups.index') }}" class="btn btn-light border">
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    Quay lại
                </a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif

            @if ($errors->has('group'))
                <div class="alert alert-danger border-0 rounded-4">{{ $errors->first('group') }}</div>
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
                                {{ $group->description ?: 'Chưa có mô tả cho quyền này' }}
                            </p>

                            <div class="group-card__meta">
                                <div class="group-card__stat">
                                    <strong>{{ $group->permissions_count }}</strong>
                                    <span>permission</span>
                                </div>
                                <div class="group-card__stat">
                                    <strong>{{ optional($group->deleted_at)?->format('d/m/Y H:i') }}</strong>
                                    <span>Thời gian xóa</span>
                                </div>
                                <div class="group-card__stat">
                                    <strong>{{ $group->users_count }}</strong>
                                    <span>Người dùng</span>
                                </div>
                            </div>

                            @if (!$canManageThisGroup)
                                <div class="group-card__note mt-3">
                                    @if ($isProtectedSuperAdmin)
                                        Chỉ tài khoản thuộc nhóm Super Admin mới được thao tác với nhóm Super Admin.
                                    @elseif ($isCurrentGroup)
                                        Bạn không thể thao tác với nhóm quyền mà tài khoản hiện tại đang sử dụng.
                                    @endif
                                </div>
                            @endif

                            <div class="mt-3 d-flex flex-wrap gap-2">
                                @if ($canManageThisGroup)
                                    <form action="{{ route('groups.restore', $group->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Khôi phục</button>
                                    </form>

                                    <form action="{{ route('groups.force-delete', $group->id) }}" method="post"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn nhóm quyền này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger"
                                            @disabled($group->users_count > 0 || $group->slug === 'super_admin' || $isCurrentGroup)>
                                            Xóa vĩnh viễn
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
                        <div class="text-center text-muted py-4">Thúng rác nhóm quyền đang trống.</div>
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
