@extends('layouts.backend')

@section('content')
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
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

            <button type="submit" class="btn btn-warning">
                <i class="fa-solid fa-rotate me-2"></i>
                Đồng bộ permission
            </button>
        </div>
    @endif

    @php
        $selectedPermissions = old('permissions', $group->permissions->pluck('id')->all());
    @endphp

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-user-shield text-primary me-2"></i>Cập Nhật Nhóm Quyền</h4>
                    <p class="text-muted mb-0">Hiệu chỉnh quyền lực nội bộ và phân bổ chuẩn xác hành động.</p>
                </div>
                <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary px-3 rounded-pill">
                    <i class="fas fa-arrow-left me-1"></i> Quay về
                </a>
            </div>

            <form action="{{ route('groups.update', $group->id) }}" method="post" class="admin-form permission-builder">
                @csrf
                
                <div class="row g-4 mb-4">
                    <div class="col-xl-8">
                        <div class="card border-0 shadow-sm rounded-4 p-4" style="border: 1px solid #eef2f7 !important;">
                            <div class="fw-bold text-dark mb-3 pb-2 border-bottom"><i class="fas fa-info-circle text-primary me-1"></i> Thông tin vai trò</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary">Tên nhóm vai trò <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control border-2 rounded-3" value="{{ old('name', $group->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary">Slug (Mã định danh) <span class="text-danger">*</span></label>
                                    <input type="text" name="slug" class="form-control border-2 rounded-3" value="{{ old('slug', $group->slug) }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary">Mô tả ngắn</label>
                                    <textarea name="description" class="form-control border-2 rounded-3" rows="2">{{ old('description', $group->description) }}</textarea>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="form-check form-switch bg-light p-3 rounded-3 border d-inline-flex align-items-center gap-3" style="width: 100%;">
                                        <input class="form-check-input ms-0 mt-0 border-primary" type="checkbox" id="is_admin" name="is_admin" value="1" @checked(old('is_admin', $group->is_admin)) style="width: 40px; height: 20px;">
                                        <label class="form-check-label fw-semibold text-dark mb-0" for="is_admin">
                                            <i class="fas fa-shield-alt text-success me-1"></i> Cho phép tài khoản này truy cập bảng điều khiển Admin Panel
                                        </label>
                                    </div>
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
                                <p class="text-muted mb-0">Tick trực tiếp theo module x action để chỉnh quyền nhanh cho role
                                    này.</p>
                            </div>
                            <button type="button" class="btn btn-light border btn-sm permission-matrix__collapse-toggle"
                                id="permission-matrix-toggle" aria-expanded="true" aria-controls="permission-matrix-body">
                                Thu gọn ma trận
                            </button>
                        </div>

                        <div class="permission-matrix__body" id="permission-matrix-body">
                            <div class="row g-3">
                                @foreach ($permissionMatrix as $module => $actionPermissions)
                                    @php
                                        $moduleLabels = [
                                            'dashboard' => 'Bảng điều khiển',
                                            'courses' => 'Khóa học',
                                            'lessons' => 'Bài học',
                                            'categories' => 'Danh mục',
                                            'teachers' => 'Giảng viên',
                                            'orders' => 'Đơn hàng',
                                            'students' => 'Học viên',
                                            'users' => 'Quản trị viên',
                                            'groups' => 'Nhóm quyền',
                                            'permissions' => 'Quyền chi tiết',
                                            'coupons' => 'Mã giảm giá',
                                            'contacts' => 'Liên hệ',
                                            'comments' => 'Bình luận',
                                            'settings' => 'Cấu hình hệ thống',
                                            'chatbot' => 'Chatbot AI',
                                            'logs' => 'Nhật ký',
                                            'packages' => 'Gói giảng viên',
                                            'promotions' => 'Khuyến mại',
                                            'reports' => 'Báo cáo/Góp ý',
                                            'certificates' => 'Chứng chỉ',
                                            'announcements' => 'Thông báo hệ thống',
                                        ];
                                    @endphp
                                    <div class="col-md-6 col-lg-4 permission-matrix__module-card" data-module="{{ strtolower($module ?: 'other') }}">
                                        <div class="card h-100 border shadow-sm" style="border-radius: 12px; overflow: hidden;">
                                            <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 px-3" style="border-bottom: 1px solid rgba(0,0,0,.05);">
                                                <h6 class="mb-0 fw-bold text-dark">
                                                    {{ $moduleLabels[$module] ?? \Illuminate\Support\Str::headline($module) }}
                                                </h6>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 permission-select-module" title="Chọn tất cả">
                                                        <i class="fas fa-check-double" style="font-size: 11px;"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1 permission-clear-module" title="Bỏ chọn">
                                                        <i class="fas fa-times" style="font-size: 11px;"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($matrixActions as $action)
                                                        @php($permission = $actionPermissions[$action] ?? null)
                                                        @if ($permission)
                                                            <button type="button"
                                                                class="btn btn-sm permission-matrix__toggle {{ in_array($permission->id, $selectedPermissions) ? 'is-active' : '' }}"
                                                                data-permission-id="{{ $permission->id }}"
                                                                data-module="{{ strtolower($module ?: 'other') }}"
                                                                data-action="{{ $action }}">
                                                                {{ $matrixActionLabels[$action] ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', $action)) }}
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                    <div class="permission-builder__list d-none" aria-hidden="true">
                        @foreach ($permissions as $module => $modulePermissions)
                            <section class="permission-module" data-module="{{ strtolower($module ?: 'other') }}">
                                @foreach ($modulePermissions as $permission)
                                    <div class="permission-item"
                                        data-filter="{{ strtolower($permission->name . ' ' . $permission->slug) }}">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="{{ $permission->id }}"
                                            data-module="{{ strtolower($module ?: 'other') }}"
                                            data-slug="{{ $permission->slug }}" @checked(in_array($permission->id, $selectedPermissions))>
                                    </div>
                                @endforeach
                            </section>
                        @endforeach
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 2rem; border: 1px solid #eef2f7 !important;">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-tasks text-primary me-1"></i> Tóm tắt phân quyền</h6>
                        <div class="p-3 bg-light rounded-3 text-center mb-3">
                            <div class="fw-bold text-primary display-6 mb-1" id="selected-permission-count">0</div>
                            <div class="text-muted small">Quyền hạn đã kích hoạt</div>
                        </div>
                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="select-all-permissions">
                                <i class="fas fa-check-double me-1"></i> Chọn tất cả
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" id="clear-all-permissions">
                                <i class="fas fa-times me-1"></i> Hủy chọn tất cả
                            </button>
                        </div>
                        <div class="border-top pt-3">
                            <button type="submit" class="btn btn-success rounded-pill w-100 shadow-sm py-2">
                                <i class="fas fa-save me-1"></i> Lưu cập nhật vai trò
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</div>
@endsection

@include('user::groups.partials.builder-styles')
@include('user::groups.partials.builder-scripts')
