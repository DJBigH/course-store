<?php

namespace Modules\User\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\User\seeders\PermissionSeeder;
use Modules\User\src\Models\Group;
use Modules\User\src\Models\Permission;

class GroupController extends Controller
{
    public function index()
    {
        $pageTitle = 'Nhóm quyền';
        $groups = Group::query()->withCount(['permissions', 'users'])->orderBy('id')->get();
        $syncStatus = $this->getPermissionSyncStatus();

        return view('user::groups.index', compact('pageTitle', 'groups', 'syncStatus'));
    }

    public function create()
    {
        $pageTitle = 'Thêm nhóm quyền';
        [$permissions, $permissionMatrix, $matrixActions] = $this->buildPermissionData();
        $rolePresets = $this->getRolePresets();
        $syncStatus = $this->getPermissionSyncStatus();

        return view('user::groups.create', compact(
            'pageTitle',
            'permissions',
            'permissionMatrix',
            'matrixActions',
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

        return redirect()->route('groups.index')->with('msg', 'Đã tạo nhóm quyền thành công.');
    }

    public function edit($group)
    {
        $pageTitle = 'Cập nhật nhóm quyền';
        $group = Group::query()->with('permissions:id')->findOrFail($group);
        [$permissions, $permissionMatrix, $matrixActions] = $this->buildPermissionData();
        $syncStatus = $this->getPermissionSyncStatus();

        return view('user::groups.edit', compact(
            'pageTitle',
            'group',
            'permissions',
            'permissionMatrix',
            'matrixActions',
            'syncStatus'
        ));
    }

    public function update(Request $request, $group)
    {
        $group = Group::query()->findOrFail($group);

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

        if ($group->slug === 'super_admin') {
            return back()->withErrors(['group' => 'Không thể xóa nhóm quyền Super Admin.']);
        }

        if ((int) auth()->user()?->group_id === (int) $group->id) {
            return back()->withErrors(['group' => 'Không thể xóa nhóm quyền mà tài khoản hiện tại đang sử dụng.']);
        }

        if ($group->users_count > 0) {
            return back()->withErrors(['group' => 'Không thể xóa nhóm quyền đang có người dùng.']);
        }

        $group->permissions()->detach();
        $group->delete();

        return redirect()->route('groups.index')->with('msg', 'Đã xóa nhóm quyền thành công.');
    }

    private function buildPermissionData(): array
    {
        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy(fn($permission) => $permission->module ?: 'other');

        $actionOrder = collect(['view', 'create', 'edit', 'update', 'delete', 'soft_delete', 'force_delete', 'publish', 'logs', 'moderate', 'manage']);
        $derivedActions = $permissions->flatten()
            ->map(fn($permission) => $this->extractActionFromSlug($permission->slug))
            ->unique()
            ->sortBy(function ($action) use ($actionOrder) {
                $index = $actionOrder->search($action);

                return $index === false ? 999 : $index;
            })
            ->values();

        $permissionMatrix = $permissions->map(function ($modulePermissions) use ($derivedActions) {
            return $derivedActions->mapWithKeys(function ($action) use ($modulePermissions) {
                $permission = $modulePermissions->first(function ($item) use ($action) {
                    return $this->extractActionFromSlug($item->slug) === $action;
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
                'module' => $permission['module'] ?? 'other',
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
                'label' => 'Sale',
                'name' => 'Sale',
                'slug' => 'sale',
                'description' => 'Theo dõi đơn hàng, khách hàng, coupon và liên hệ.',
                'is_admin' => true,
                'permissions' => [
                    'dashboard.view',
                    'orders.view',
                    'orders.update',
                    'orders.delete',
                    'students.view',
                    'students.create',
                    'students.edit',
                    'students.delete',
                    'students.logs',
                    'coupons.view',
                    'coupons.logs',
                    'contacts.view',
                    'contacts.update',
                    'contacts.delete',
                    'contacts.logs',
                ],
            ],
            [
                'label' => 'Content',
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
                    'lessons.sort',
                    'categories.view',
                    'categories.create',
                    'categories.edit',
                    'categories.delete',
                    'categories.logs',
                    'teachers.view',
                    'teachers.create',
                    'teachers.edit',
                    'teachers.delete',
                    'teachers.logs',
                    'coupons.view',
                    'coupons.create',
                    'coupons.edit',
                    'coupons.delete',
                    'coupons.assign',
                    'coupons.logs',
                    'comments.moderate',
                ],
            ],
            [
                'label' => 'Support',
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
                'label' => 'Teacher Manager',
                'name' => 'Teacher Manager',
                'slug' => 'teacher_manager',
                'description' => 'Quản lý hồ sơ giảng viên và nội dung liên quan đến giảng viên.',
                'is_admin' => true,
                'permissions' => [
                    'dashboard.view',
                    'teachers.view',
                    'teachers.create',
                    'teachers.edit',
                    'teachers.delete',
                    'teachers.logs',
                    'courses.view',
                    'courses.soft_delete',
                    'lessons.view',
                    'lessons.create',
                    'lessons.edit',
                    'lessons.delete',
                    'lessons.sort',
                    'comments.moderate',
                ],
            ],
        ];
    }
}
