@extends('layouts.backend')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">Vui lòng kiểm tra lại dữ liệu đã nhập.</div>
    @endif

    @if (!empty($syncStatus['has_missing']))
        <div class="alert alert-warning permission-sync-alert">
            <div>
                <strong>Database đang thiếu {{ $syncStatus['missing_count'] }} permission mới.</strong>
                <div class="small mt-1">
                    Một số quyền trong code chưa được đồng bộ vào database, ví dụ:
                    {{ collect($syncStatus['missing_permissions'])->take(4)->pluck('slug')->implode(', ') }}
                    @if ($syncStatus['missing_count'] > 4)
                        ...
                    @endif
                </div>
            </div>
            @if (auth()->user()?->hasPermission('permissions.manage'))
                <form action="{{ route('groups.sync-permissions') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-rotate me-2"></i>
                        Đồng bộ permission
                    </button>
                </form>
            @endif
        </div>
    @endif

    <form action="{{ route('groups.store') }}" method="post" class="admin-form permission-builder">
        @csrf
        <div class="admin-form__header permission-builder__header">
            <div>
                <h5 class="mb-1">Thêm nhóm quyền</h5>
                <p class="text-muted mb-0">Tạo role mới, gán nhanh bằng ma trận quyền rồi tinh chỉnh sâu ở danh sách chi tiết.</p>
            </div>
            <div class="permission-builder__actions">
                <input type="text" class="form-control" id="permission-search" placeholder="Tìm permission theo tên hoặc slug...">
            </div>
        </div>

        <div class="row p-4 g-4">
            <div class="col-xl-8">
                <div class="preset-panel mb-4">
                    <div class="preset-panel__header">
                        <div>
                            <h6 class="mb-1">Preset role nhanh</h6>
                            <p class="text-muted mb-0">Chọn mẫu có sẵn để tự điền tên nhóm và bật bộ quyền khởi tạo.</p>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($rolePresets as $preset)
                            <button type="button" class="btn btn-light border preset-role-trigger"
                                data-preset='@json($preset, JSON_UNESCAPED_UNICODE)'>
                                {{ $preset['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Tên nhóm</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="vd: support">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1" @checked(old('is_admin', 1))>
                            <label class="form-check-label" for="is_admin">Cho phép truy cập admin panel</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="permission-builder__admin-section" id="admin-permission-section">
            <div class="row px-4 pb-4 g-4">
                <div class="col-xl-8">
                    <section class="permission-matrix">
                        <div class="permission-matrix__header">
                            <div>
                                <h6 class="mb-1">Ma trận quyền nhanh</h6>
                                <p class="text-muted mb-0">Tick trực tiếp theo module x action để gán quyền nhanh.</p>
                            </div>
                            <button type="button" class="btn btn-light border btn-sm permission-matrix__collapse-toggle"
                                id="permission-matrix-toggle" aria-expanded="true" aria-controls="permission-matrix-body">
                                Thu gọn ma trận
                            </button>
                        </div>

                        <div class="permission-matrix__body" id="permission-matrix-body">
                        <div class="table-responsive">
                            <table class="table permission-matrix__table align-middle">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        @foreach ($matrixActions as $action)
                                            <th class="text-center text-capitalize">{{ $action }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissionMatrix as $module => $actionPermissions)
                                        <tr>
                                            <td class="fw-semibold text-capitalize">{{ $module }}</td>
                                            @foreach ($matrixActions as $action)
                                                @php($permission = $actionPermissions[$action] ?? null)
                                                <td class="text-center">
                                                    @if ($permission)
                                                        <button type="button"
                                                            class="btn btn-sm permission-matrix__toggle {{ in_array($permission->id, old('permissions', [])) ? 'is-active' : '' }}"
                                                            data-permission-id="{{ $permission->id }}"
                                                            data-module="{{ strtolower($module ?: 'other') }}"
                                                            data-action="{{ $action }}">
                                                            {{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $action)) }}
                                                        </button>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </section>

                    <div class="permission-builder__list mt-4">
                        @foreach ($permissions as $module => $modulePermissions)
                            <section class="permission-module" data-module="{{ strtolower($module ?: 'other') }}">
                                <div class="permission-module__header">
                                    <div>
                                        <h6 class="mb-1 text-capitalize">{{ $module ?: 'other' }}</h6>
                                        <p class="text-muted mb-0">{{ $modulePermissions->count() }} permission</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-light border btn-sm permission-select-module">Chọn hết</button>
                                        <button type="button" class="btn btn-light border btn-sm permission-clear-module">Bỏ chọn</button>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    @foreach ($modulePermissions as $permission)
                                        <div class="col-lg-6 permission-item" data-filter="{{ strtolower($permission->name . ' ' . $permission->slug) }}">
                                            <label class="permission-card">
                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    data-module="{{ strtolower($module ?: 'other') }}"
                                                    data-slug="{{ $permission->slug }}"
                                                    @checked(in_array($permission->id, old('permissions', [])))>
                                                <span class="permission-card__body">
                                                    <strong>{{ $permission->name }}</strong>
                                                    <small>{{ $permission->slug }}</small>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>

                <div class="col-xl-4">
                    <aside class="permission-builder__aside">
                        <h6 class="mb-3">Tóm tắt quyền</h6>
                        <div class="permission-summary">
                            <div class="permission-summary__number" id="selected-permission-count">0</div>
                            <div class="text-muted">permission đang được chọn</div>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button type="button" class="btn btn-light border" id="select-all-permissions">Chọn tất cả</button>
                            <button type="button" class="btn btn-light border" id="clear-all-permissions">Bỏ chọn tất cả</button>
                        </div>
                    </aside>
                </div>
            </div>
        </div>

        <div class="admin-form__footer">
            <button type="submit" class="btn btn-success">Lưu</button>
            <a href="{{ route('groups.index') }}" class="btn btn-light border">Trở về</a>
        </div>
    </form>
@endsection

@include('user::groups.partials.builder-styles')
@include('user::groups.partials.builder-scripts')
