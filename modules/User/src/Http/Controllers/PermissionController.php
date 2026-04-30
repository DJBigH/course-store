<?php

namespace Modules\User\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\src\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $pageTitle = 'Danh sách quyền';
        $permissions = Permission::query()
            ->withCount('groups')
            ->orderBy('module')
            ->orderBy('name')
            ->paginate(30);

        $stats = [
            'total' => Permission::query()->count(),
            'modules' => Permission::query()->whereNotNull('module')->distinct('module')->count('module'),
            'assigned' => Permission::query()->has('groups')->count(),
        ];

        return view('user::permissions.index', compact('pageTitle', 'permissions', 'stats'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác quyền';
        $permissions = Permission::query()
            ->onlyTrashed()
            ->withCount('groups')
            ->orderByDesc('deleted_at')
            ->paginate(30);

        return view('user::permissions.trash', compact('pageTitle', 'permissions'));
    }

    public function create()
    {
        $pageTitle = 'Thêm quyền';

        return view('user::permissions.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug'],
            'module' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $permission = Permission::query()->create($data);

        activity_log(
            action: 'create',
            subject: $permission,
            properties: [
                'data' => $data,
            ],
            logName: 'admin_user_management',
            description: "Tạo mới quyền: {$permission->name}"
        );

        return redirect()->route('permissions.index')->with('msg', 'Đã tạo quyền mới thành công.');
    }

    public function edit($permission)
    {
        $pageTitle = 'Cập nhật quyền';
        $permission = Permission::query()->findOrFail($permission);

        return view('user::permissions.edit', compact('pageTitle', 'permission'));
    }

    public function update(Request $request, $permission)
    {
        $permission = Permission::query()->findOrFail($permission);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug,' . $permission->id],
            'module' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $old = $permission->toArray();
        $permission->update($data);
        $new = $permission->fresh()->toArray();

        activity_log(
            action: 'update',
            subject: $permission,
            properties: [
                'old' => $old,
                'new' => $new,
            ],
            logName: 'admin_user_management',
            description: "Cập nhật quyền: {$permission->name}"
        );

        return back()->with('msg', 'Đã cập nhật quyền thành công.');
    }

    public function destroy($permission)
    {
        $permission = Permission::query()->withCount('groups')->findOrFail($permission);

        if ($permission->groups_count > 0) {
            return back()->with('msg_danger', 'Không thể xóa quyền đang được gán cho nhóm quyền.');
        }

        $snapshot = $permission->toArray();
        $permission->delete();

        activity_log(
            action: 'delete',
            subject: $permission,
            properties: [
                'data' => $snapshot,
            ],
            logName: 'admin_user_management',
            description: "Xóa tạm quyền: {$permission->name}"
        );

        return redirect()->route('permissions.index')->with('msg', 'Đã xóa quyền thành công.');
    }
    public function restore($permission)
    {
        $permission = Permission::query()->onlyTrashed()->findOrFail($permission);
        $permission->restore();

        activity_log(
            action: 'restore',
            subject: $permission->fresh(),
            properties: [
                'restored_from_trash' => true,
            ],
            logName: 'admin_user_management',
            description: "Khôi phục quyền từ thùng rác: {$permission->name}"
        );

        return redirect()->route('permissions.trash')->with('msg', 'Đã khôi phục quyền thành công.');
    }

    public function forceDelete($permission)
    {
        $permission = Permission::query()->onlyTrashed()->withCount('groups')->findOrFail($permission);

        if ($permission->groups_count > 0) {
            return back()->with('msg_danger', 'Không thể xóa vĩnh viễn quyền đang được gán cho nhóm quyền.');
        }

        $snapshot = $permission->toArray();
        $permission->groups()->detach();
        $permission->forceDelete();

        activity_log(
            action: 'force_delete',
            subject: $permission,
            properties: [
                'data' => $snapshot,
                'deleted_permanently' => true,
            ],
            logName: 'admin_user_management',
            description: "Xóa vĩnh viễn quyền: " . ($snapshot['name'] ?? 'N/A')
        );

        return redirect()->route('permissions.trash')->with('msg', 'Đã xóa vĩnh viễn quyền thành công.');
    }
}
