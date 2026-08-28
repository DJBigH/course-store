<?php

namespace Modules\Packages\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Packages\src\Models\PackageCategory;
use Modules\Packages\src\Http\Requests\PackageCategoryRequest;

class PackageCategoryController extends Controller
{
    public function index()
    {
        $pageTitle = "Quản lý danh mục gói";
        $categories = PackageCategory::query()->orderBy('sort_order')->get();

        return view('packages::admin.categories.index', compact('pageTitle', 'categories'));
    }

    public function create()
    {
        $pageTitle = "Thêm danh mục mới";
        $nextSortOrder = ((int) PackageCategory::query()->max('sort_order')) + 1;

        return view('packages::admin.categories.create', compact('pageTitle', 'nextSortOrder'));
    }

    public function store(PackageCategoryRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status', true);
        
        PackageCategory::create($data);

        return redirect()->route('teacher-packages.categories.index')->with('msg', 'Thêm danh mục thành công.');
    }

    public function edit($id)
    {
        $pageTitle = "Chỉnh sửa danh mục";
        $category = PackageCategory::findOrFail($id);

        return view('packages::admin.categories.edit', compact('pageTitle', 'category'));
    }

    public function update(PackageCategoryRequest $request, $id)
    {
        $category = PackageCategory::findOrFail($id);
        $data = $request->validated();
        $data['status'] = $request->boolean('status', true);

        $category->update($data);

        return redirect()->route('teacher-packages.categories.index')->with('msg', 'Cập nhật danh mục thành công.');
    }

    public function delete($id)
    {
        $category = PackageCategory::findOrFail($id);
        
        // Optional: Check if category has packages before deleting
        if ($category->packages()->exists()) {
            return back()->with('msg_danger', 'Không thể xóa danh mục đang có gói sử dụng.');
        }

        $category->delete();

        return redirect()->route('teacher-packages.categories.index')->with('msg', 'Xóa danh mục thành công.');
    }
}
