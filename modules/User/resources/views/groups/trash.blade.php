@extends('layouts.backend')

@section('content')
    @php
        $isSuperAdmin = optional($currentUser?->group)->slug === 'super_admin';
    @endphp

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-trash-can text-danger me-2"></i>Thùng rác Nhóm quyền</h4>
                    <p class="text-muted mb-0">Khôi phục hoặc xóa vĩnh viễn các nhóm quyền hạn đã loại bỏ trước đó.</p>
                </div>
                <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary px-3 rounded-pill shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay về
                </a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4 shadow-sm py-3 mb-4 d-flex align-items-center">
                    <i class="fas fa-check-circle fs-5 me-2"></i> {{ session('msg') }}
                </div>
            @endif

            @if ($errors->has('group'))
                <div class="alert alert-danger border-0 rounded-4 shadow-sm py-3 mb-4 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fs-5 me-2"></i> {{ $errors->first('group') }}
                </div>
            @endif

            <div class="row g-4">
                @forelse ($groups as $group)
                    @php
                        $isCurrentGroup = (int) ($currentUser?->group_id ?? 0) === (int) $group->id;
                        $isProtectedSuperAdmin = $group->slug === 'super_admin' && !$isSuperAdmin;
                        $canManageThisGroup = $isSuperAdmin || (!$isProtectedSuperAdmin && !$isCurrentGroup);
                    @endphp

                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 bg-light-subtle" 
                             style="border: 1px solid #eef2f7 !important;">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark">{{ $group->name }}</h5>
                                        <span class="badge bg-white text-secondary border font-monospace px-2 py-1" style="font-size: 0.75rem;">{{ $group->slug }}</span>
                                    </div>
                                    <span class="badge text-bg-{{ $group->is_admin ? 'success' : 'secondary' }} rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.75rem;">
                                        {{ $group->is_admin ? 'Quyền Admin' : 'Học viên' }}
                                    </span>
                                </div>

                                <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.5;">
                                    {{ $group->description ?: 'Chưa có mô tả cho nhóm quyền này.' }}
                                </p>

                                <div class="row g-2 text-center mb-3">
                                    <div class="col-4">
                                        <div class="p-2 border rounded-3 bg-white">
                                            <div class="fw-bold text-dark fs-6">{{ $group->permissions_count }}</div>
                                            <div class="text-muted" style="font-size: 0.65rem;">Quyền hạn</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 border rounded-3 bg-white">
                                            <div class="fw-bold text-dark fs-6">{{ $group->users_count }}</div>
                                            <div class="text-muted" style="font-size: 0.65rem;">Nhân sự</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 border rounded-3 bg-white">
                                            <div class="fw-bold text-dark fs-6 text-truncate" style="font-size: 0.85rem;">{{ optional($group->deleted_at)->format('d/m') }}</div>
                                            <div class="text-muted" style="font-size: 0.65rem;">Ngày xóa</div>
                                        </div>
                                    </div>
                                </div>

                                @if (!$canManageThisGroup)
                                    <div class="alert alert-light border border-warning-subtle text-warning-emphasis p-2 rounded-3 mb-3 small" style="font-size: 0.75rem;">
                                        <i class="fas fa-lock me-1"></i>
                                        Cần tài khoản Super Admin để phục hồi.
                                    </div>
                                @endif

                                <div class="d-flex gap-2 mt-auto pt-2">
                                    @if ($canManageThisGroup)
                                        <form action="{{ route('groups.restore', $group->id) }}" method="post" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill w-100">
                                                <i class="fas fa-undo me-1"></i> Khôi phục
                                            </button>
                                        </form>
                                        <form action="{{ route('groups.force-delete', $group->id) }}" method="post" class="flex-grow-1"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn nhóm quyền này không?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger px-3 rounded-pill w-100"
                                                @disabled($group->users_count > 0 || $group->slug === 'super_admin' || $isCurrentGroup)>
                                                <i class="far fa-trash-alt me-1"></i> Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill w-100" disabled>
                                            <i class="fas fa-shield-alt me-1"></i> Được bảo vệ
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-trash-alt fs-1 text-light mb-3"></i>
                            <div>Thùng rác nhóm quyền đang trống.</div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
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

        html[data-theme="dark"] .group-card {
            border-color: #2b3b53;
            background: linear-gradient(180deg, #162033 0%, #111827 100%);
        }

        html[data-theme="dark"] .group-card--locked {
            border-color: rgba(245, 158, 11, 0.34);
            background: linear-gradient(180deg, rgba(245, 158, 11, 0.12) 0%, rgba(17, 24, 39, 0.98) 100%);
        }

        html[data-theme="dark"] .group-card__badge {
            background: #1e293b;
            color: #cbd5e1;
        }

        html[data-theme="dark"] .group-card__badge.is-admin {
            background: rgba(34, 197, 94, 0.16);
            color: #86efac;
        }

        html[data-theme="dark"] .group-card__stat {
            background: #0f172a;
            border-color: #2b3b53;
        }

        html[data-theme="dark"] .group-card__stat strong,
        html[data-theme="dark"] .group-card h6,
        html[data-theme="dark"] .group-card code {
            color: #f8fafc;
        }

        html[data-theme="dark"] .group-card__stat span,
        html[data-theme="dark"] .group-card p.text-muted,
        html[data-theme="dark"] .group-card .text-muted {
            color: #9fb0c7 !important;
        }

        html[data-theme="dark"] .group-card__note {
            border-color: rgba(245, 158, 11, 0.34);
            background: rgba(245, 158, 11, 0.14);
            color: #fde68a;
        }
    </style>
@endsection
