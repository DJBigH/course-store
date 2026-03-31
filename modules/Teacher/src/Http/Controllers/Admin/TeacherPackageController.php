<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Teacher\src\Http\Requests\TeacherPackageRequest;
use Modules\Teacher\src\Models\TeacherPackage;

class TeacherPackageController extends Controller
{
    public function index()
    {
        $pageTitle = 'Goi dang ky giang vien';
        $packages = TeacherPackage::query()->orderBy('sort_order')->paginate(12);

        return view('teacher::packages.lists', compact('pageTitle', 'packages'));
    }

    public function create()
    {
        $pageTitle = 'Them goi giang vien';

        return view('teacher::packages.create', compact('pageTitle'));
    }

    public function store(TeacherPackageRequest $request)
    {
        TeacherPackage::query()->create($this->payload($request));

        return redirect()->route('teacher-packages.index')->with('msg', 'Da tao goi giang vien thanh cong.');
    }

    public function edit($id)
    {
        $pageTitle = 'Cap nhat goi giang vien';
        $package = TeacherPackage::query()->findOrFail($id);

        return view('teacher::packages.edit', compact('pageTitle', 'package'));
    }

    public function update(TeacherPackageRequest $request, $id)
    {
        $package = TeacherPackage::query()->findOrFail($id);
        $package->update($this->payload($request));

        return redirect()->route('teacher-packages.edit', $package->id)->with('msg', 'Da cap nhat goi giang vien.');
    }

    public function delete($id)
    {
        $package = TeacherPackage::query()->findOrFail($id);
        $package->delete();

        return redirect()->route('teacher-packages.index')->with('msg', 'Da xoa goi giang vien.');
    }

    private function payload(TeacherPackageRequest $request): array
    {
        return [
            'code' => $request->string('code')->toString(),
            'name' => $request->string('name')->toString(),
            'description' => $request->string('description')->toString() ?: null,
            'price' => $request->input('price', 0),
            'billing_cycle' => $request->string('billing_cycle')->toString(),
            'course_limit' => $request->filled('course_limit') ? $request->integer('course_limit') : null,
            'commission_rate' => $request->input('commission_rate', 50),
            'priority_review' => $request->boolean('priority_review'),
            'support_level' => $request->string('support_level')->toString() ?: null,
            'status' => $request->boolean('status', true),
            'sort_order' => $request->integer('sort_order', 0),
        ];
    }
}
