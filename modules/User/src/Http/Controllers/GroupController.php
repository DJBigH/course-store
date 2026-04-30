<?php

namespace Modules\User\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\User\seeders\PermissionSeeder;
use Modules\User\src\Models\Group;
use Modules\User\src\Models\Permission;
use Modules\ActiveLogs\src\Models\ActiveLog;

class GroupController extends Controller
{
    public function index()
    {
        $pageTitle = 'Nhóm quyền';
        $groups = Group::query()->withCount(['permissions', 'users'])->orderBy('id')->get();
        $syncStatus = $this->getPermissionSyncStatus();
        $currentUser = auth()->user();

        return view('user::groups.index', compact('pageTitle', 'groups', 'syncStatus', 'currentUser'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác nhóm quyền';
        $groups = Group::query()
            ->onlyTrashed()
            ->withCount(['permissions', 'users'])
            ->orderByDesc('deleted_at')
            ->get();
        $currentUser = auth()->user();

        return view('user::groups.trash', compact('pageTitle', 'groups', 'currentUser'));
    }

    public function create()
    {
        $pageTitle = 'Thêm nhóm quyền';
        [$permissions, $permissionMatrix, $matrixActions] = $this->buildPermissionData();
        $matrixActionLabels = $this->getMatrixActionLabels();
        $rolePresets = $this->getRolePresets();
        $syncStatus = $this->getPermissionSyncStatus();

        return view('user::groups.create', compact(
            'pageTitle',
            'permissions',
            'permissionMatrix',
            'matrixActions',
            'matrixActionLabels',
            'rolePresets',
            'syncStatus'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:groups,slug'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $group = Group::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_admin' => (bool) ($data['is_admin'] ?? true),
        ]);

        $group->permissions()->sync($data['permissions'] ?? []);

        ActiveLog::log(
            action: 'create',
            subject: $group,
            properties: [
                'data' => $data,
                'permissions' => $data['permissions'] ?? [],
            ],
            logName: 'Nhóm quyền',
            description: 'Tạo mới nhóm quyền'
        );

        return redirect()->route('groups.index')->with('msg', 'Đã tạo nhóm quyền thành công.');
    }

    public function edit($group)
    {
        $pageTitle = 'Cập nhật nhóm quyền';
        $group = Group::query()->with('permissions:id')->findOrFail($group);
        $this->authorizeGroupAccess($group, 'chỉnh sửa');
        [$permissions, $permissionMatrix, $matrixActions] = $this->buildPermissionData();
        $matrixActionLabels = $this->getMatrixActionLabels();
        $syncStatus = $this->getPermissionSyncStatus();

        return view('user::groups.edit', compact(
            'pageTitle',
            'group',
            'permissions',
            'permissionMatrix',
            'matrixActions',
            'matrixActionLabels',
            'syncStatus'
        ));
    }

    public function update(Request $request, $group)
    {
        $group = Group::query()->findOrFail($group);
        $this->authorizeGroupAccess($group, 'cập nhật');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:groups,slug,' . $group->id],
            'description' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $group->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);

        $group->permissions()->sync($data['permissions'] ?? []);

        ActiveLog::log(
            action: 'update',
            subject: $group,
            properties: [
                'data' => $data,
                'permissions' => $data['permissions'] ?? [],
            ],
            logName: 'Nhóm quyền',
            description: 'Cập nhật nhóm quyền'
        );

        return back()->with('msg', 'Đã cập nhật nhóm quyền thành công.');
    }

    public function syncPermissions()
    {
        $syncStatus = $this->getPermissionSyncStatus();
        $createdCount = $syncStatus['missing_count'];

        app(PermissionSeeder::class)->run();

        return back()->with('msg', $createdCount > 0
            ? 'Đã đồng bộ permission thành công. Đã bổ sung ' . $createdCount . ' quyền còn thiếu.'
            : 'Đã đồng bộ permission. Database đã ở trạng thái mới nhất.');
    }

    public function destroy($group)
    {
        $group = Group::query()->withCount('users')->findOrFail($group);
        $this->authorizeGroupAccess($group, 'xóa');

        if ($group->slug === 'super_admin') {
            return back()->withErrors(['group' => 'Không thể xóa nhóm quyền Super Admin.']);
        }

        if ((int) auth()->user()?->group_id === (int) $group->id) {
            return back()->withErrors(['group' => 'Không thể xóa nhóm quyền mà tài khoản hiện tại đang sử dụng.']);
        }

        if ($group->users_count > 0) {
            return back()->withErrors(['group' => 'Không thể xóa nhóm quyền đang có người dùng.']);
        }

        $snapshot = method_exists($group, 'toArray') ? $group->toArray() : (array) $group;
        $group->delete();

        ActiveLog::log(
            action: 'delete',
            subject: $group,
            properties: ['data' => $snapshot],
            logName: 'Nhóm quyền',
            description: 'Xóa mềm nhóm quyền'
        );

        return redirect()->route('groups.index')->with('msg', 'Đã xóa nhóm quyền thành công.');
    }

    public function restore($group)
    {
        $group = Group::query()->onlyTrashed()->withCount('users')->findOrFail($group);
        $this->authorizeGroupAccess($group, 'khôi phục');
        $group->restore();

        ActiveLog::log(
            action: 'restore',
            subject: $group->fresh(),
            properties: ['restored_from_trash' => true],
            logName: 'Nhóm quyền',
            description: 'Khôi phục nhóm quyền'
        );

        return redirect()->route('groups.trash')->with('msg', 'Đã khôi phục nhóm quyền thành công.');
    }

    public function forceDelete($group)
    {
        $group = Group::query()->onlyTrashed()->withCount('users')->findOrFail($group);
        $this->authorizeGroupAccess($group, 'xóa vĩnh viễn');

        if ($group->slug === 'super_admin') {
            return back()->withErrors(['group' => 'Không thể xóa vĩnh viễn nhóm quyền Super Admin.']);
        }

        if ((int) auth()->user()?->group_id === (int) $group->id) {
            return back()->withErrors(['group' => 'Không thể xóa vĩnh viễn nhóm quyền mà tài khoản hiện tại đang sử dụng.']);
        }

        if ($group->users_count > 0) {
            return back()->withErrors(['group' => 'Không thể xóa vĩnh viễn nhóm quyền đang có người dùng.']);
        }

        $group->permissions()->detach();
        
        $snapshot = method_exists($group, 'toArray') ? $group->toArray() : (array) $group;
        $group->forceDelete();

        ActiveLog::log(
            action: 'force_delete',
            subject: $group,
            properties: [
                'data' => $snapshot,
                'deleted_permanently' => true,
            ],
            logName: 'Nhóm quyền',
            description: 'Xóa vĩnh viễn nhóm quyền'
        );

        return redirect()->route('groups.trash')->with('msg', 'Đã xóa vĩnh viễn nhóm quyền thành công.');
    }

    public function canManageGroup(?Group $group): bool
    {
        $user = auth()->user();

        if (!$user || !$group) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($group->slug === 'super_admin') {
            return false;
        }

        if ((int) $user->group_id === (int) $group->id) {
            return false;
        }

        return true;
    }

    private function buildPermissionData(): array
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->map(function ($permission) {
                $permission->display_module = $this->resolvePermissionModule($permission);

                return $permission;
            })
            ->groupBy(fn($permission) => $permission->display_module ?: 'other');

        $moduleOrder = collect([
            'dashboard', 
            'courses', 'lessons', 'categories', 'certificates',
            'users', 'groups', 'permissions', 'teachers', 'students',
            'orders', 'coupons', 'packages', 
            'contacts', 'comments', 'promotions', 'announcements', 
            'chatbot', 'settings', 'logs', 'reports'
        ]);

        $permissions = $permissions->sortBy(function ($items, $module) use ($moduleOrder) {
            $index = $moduleOrder->search($module);

            return $index === false ? 999 : $index;
        });

        $actionOrder = collect(['manage', 'view', 'create', 'edit', 'update', 'payment', 'captcha', 'delete', 'restore', 'force_delete', 'publish', 'logs', 'moderate']);
        $derivedActions = $permissions->flatten()
            ->map(fn($permission) => $this->normalizeMatrixAction($this->extractActionFromSlug($permission->slug)))
            ->unique()
            ->sortBy(function ($action) use ($actionOrder) {
                $index = $actionOrder->search($action);

                return $index === false ? 999 : $index;
            })
            ->values();

        $permissionMatrix = $permissions->map(function ($modulePermissions) use ($derivedActions) {
            return $derivedActions->mapWithKeys(function ($action) use ($modulePermissions) {
                $permission = $modulePermissions
                    ->sortBy(fn($item) => $this->extractActionFromSlug($item->slug) === 'delete' ? 0 : 1)
                    ->first(function ($item) use ($action) {
                        return $this->normalizeMatrixAction($this->extractActionFromSlug($item->slug)) === $action;
                    });

                return [$action => $permission];
            });
        });

        return [$permissions, $permissionMatrix, $derivedActions];
    }

    private function extractActionFromSlug(string $slug): string
    {
        return Str::contains($slug, '.') ? Str::afterLast($slug, '.') : $slug;
    }

    private function normalizeMatrixAction(string $action): string
    {
        return $action === 'soft_delete' ? 'delete' : $action;
    }

    private function getPermissionSyncStatus(): array
    {
        $definedPermissions = collect(PermissionSeeder::definitions());
        $definedSlugs = $definedPermissions->pluck('slug')->values();
        $dbSlugs = Permission::query()->pluck('slug');

        $missingSlugs = $definedSlugs->diff($dbSlugs)->values();
        $missingPermissions = $definedPermissions
            ->whereIn('slug', $missingSlugs)
            ->map(fn($permission) => [
                'slug' => $permission['slug'],
                'name' => $permission['name'],
                'module' => $this->resolveDefinitionModule($permission),
            ])
            ->values();

        return [
            'has_missing' => $missingPermissions->isNotEmpty(),
            'missing_count' => $missingPermissions->count(),
            'missing_permissions' => $missingPermissions,
        ];
    }

    private function getRolePresets(): array
    {
        return [
            [
                'label' => 'Nhân viên Bán hàng',
                'name' => 'Sale',
                'slug' => 'sale',
                'description' => 'Theo dõi đơn hàng, khách hàng, coupon và liên hệ.',
                'is_admin' => true,
                'permissions' => [
                    'dashboard.view',
                    'orders.view',
                    'orders.update',
                    'orders.delete',
                    'orders.soft_delete',
                    'orders.force_delete',
                    'students.view',
                    'students.create',
                    'students.edit',
                    'students.delete',
                    'students.soft_delete',
                    'students.force_delete',
                    'students.logs',
                    'coupons.view',
                    'coupons.logs',
                    'contacts.view',
                    'contacts.update',
                    'contacts.delete',
                    'contacts.soft_delete',
                    'contacts.force_delete',
                    'contacts.logs',
                ],
            ],
            [
                'label' => 'Biên tập viên Nội dung',
                'name' => 'Content',
                'slug' => 'content',
                'description' => 'Quản lý nội dung khóa học, danh mục và bình luận.',
                'is_admin' => true,
                'permissions' => [
                    'dashboard.view',
                    'courses.view',
                    'courses.create',
                    'courses.edit',
                    'courses.publish',
                    'courses.soft_delete',
                    'courses.force_delete',
                    'lessons.view',
                    'lessons.create',
                    'lessons.edit',
                    'lessons.delete',
                    'lessons.soft_delete',
                    'lessons.restore',
                    'lessons.force_delete',
                    'lessons.sort',
                    'categories.view',
                    'categories.create',
                    'categories.edit',
                    'categories.delete',
                    'categories.soft_delete',
                    'categories.force_delete',
                    'categories.logs',
                    'teachers.view',
                    'teachers.create',
                    'teachers.edit',
                    'teachers.delete',
                    'teachers.soft_delete',
                    'teachers.force_delete',
                    'teachers.logs',
                    'coupons.view',
                    'coupons.create',
                    'coupons.edit',
                    'coupons.delete',
                    'coupons.soft_delete',
                    'coupons.force_delete',
                    'coupons.assign',
                    'coupons.logs',
                    'comments.moderate',
                ],
            ],
            [
                'label' => 'Chăm sóc khách hàng',
                'name' => 'Support',
                'slug' => 'support',
                'description' => 'Chăm sóc khách hàng, xử lý liên hệ và hỗ trợ học viên.',
                'is_admin' => true,
                'permissions' => [
                    'dashboard.view',
                    'students.view',
                    'students.logs',
                    'contacts.view',
                    'contacts.update',
                    'contacts.logs',
                    'comments.moderate',
                ],
            ],
            [
                'label' => 'Giảng viên',
                'name' => 'Teacher',
                'slug' => 'teacher',
                'description' => 'Giáo viên phụ trách nội dung khóa học và bài giảng.',
                'is_admin' => true,
                'permissions' => [
                    'dashboard.view',
                    'teachers.view',
                    'teachers.create',
                    'teachers.edit',
                    'teachers.delete',
                    'teachers.soft_delete',
                    'teachers.force_delete',
                    'teachers.logs',
                    'courses.view',
                    'courses.soft_delete',
                    'lessons.view',
                    'lessons.create',
                    'lessons.edit',
                    'lessons.delete',
                    'lessons.soft_delete',
                    'lessons.restore',
                    'lessons.force_delete',
                    'lessons.sort',
                    'comments.moderate',
                ],
            ],
        ];
    }

    private function resolvePermissionModule(Permission $permission): string
    {
        return $this->resolveModuleFromSlug($permission->slug, $permission->module);
    }

    private function getMatrixActionLabels(): array
    {
        return [
            'manage' => 'Toàn quyền',
            'view' => 'Xem',
            'create' => 'Thêm',
            'edit' => 'Sửa',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'restore' => 'Khôi phục',
            'force_delete' => 'Xóa vĩnh viễn',
            'publish' => 'Xuất bản',
            'logs' => 'Nhật ký',
            'moderate' => 'Kiểm duyệt',
            'approve' => 'Duyệt',
            'reject' => 'Từ chối',
            'lock' => 'Khóa/Mở',
            'send' => 'Gửi tin',
            'grant_course' => 'Cấp khóa học',
            'resolve' => 'Xử lý',
            'assign' => 'Gán',
        ];
    }

    private function resolveDefinitionModule(array $permission): string
    {
        return $this->resolveModuleFromSlug(
            $permission['slug'] ?? '',
            $permission['module'] ?? 'other'
        );
    }

    private function resolveModuleFromSlug(string $slug, ?string $fallback = 'other'): string
    {
        return match (true) {
            Str::startsWith($slug, 'groups.') => 'groups',
            Str::startsWith($slug, 'permissions.') => 'permissions',
            default => $fallback ?: 'other',
        };
    }

    private function authorizeGroupAccess(Group $group, string $action): void
    {
        if ($this->canManageGroup($group)) {
            return;
        }

        abort(403, 'Bạn không có quyền ' . $action . ' nhóm quyền này.');
    }

    private function isSuperAdmin($user): bool
    {
        return optional($user->group)->slug === 'super_admin';
    }
}






