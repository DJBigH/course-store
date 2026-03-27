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

        Permission::query()->create($data);

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

        $permission->update($data);

        return back()->with('msg', 'Đã cập nhật quyền thành công.');
    }

    public function destroy($permission)
    {
        $permission = Permission::query()->withCount('groups')->findOrFail($permission);

        if ($permission->groups_count > 0) {
            return back()->with('msg_danger', 'Không thể xóa quyền đang được gán cho nhóm quyền.');
        }

        $permission->delete();

        return redirect()->route('permissions.index')->with('msg', 'Đã xóa quyền thành công.');
    }
}
