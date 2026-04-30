<?php

namespace Modules\Packages\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Packages\src\Models\PackageFeature;

class PackageFeatureController extends Controller
{
    public function index()
    {
        $pageTitle = 'Quản lý tính năng gói';
        $features = PackageFeature::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get();

        return view('packages::admin.features.index', compact('pageTitle', 'features'));
    }

    public function edit($id)
    {
        $pageTitle = 'Chỉnh sửa tính năng';
        $feature = PackageFeature::query()->findOrFail($id);

        return view('packages::admin.features.edit', compact('pageTitle', 'feature'));
    }

    public function update(Request $request, $id)
    {
        $feature = PackageFeature::query()->findOrFail($id);

        $request->validate([
            'name_vi' => 'required',
            'sort_order' => 'required|integer',
        ]);

        $old = $feature->toArray();
        $feature->update([
            'name_vi' => $request->input('name_vi'),
            'name_en' => $request->input('name_en'),
            'name_ko' => $request->input('name_ko'),
            'name_ja' => $request->input('name_ja'),
            'name_zh' => $request->input('name_zh'),
            'description_vi' => $request->input('description_vi'),
            'description_en' => $request->input('description_en'),
            'description_ko' => $request->input('description_ko'),
            'description_ja' => $request->input('description_ja'),
            'description_zh' => $request->input('description_zh'),
            'icon' => $request->input('icon'),
            'group' => $request->input('group'),
            'sort_order' => (int) $request->input('sort_order'),
            'is_enabled' => (int) $request->input('is_enabled'),
        ]);

        activity_log(
            action: 'update',
            subject: $feature,
            properties: [
                'old' => $old,
                'new' => $feature->refresh()->toArray(),
            ],
            logName: 'admin_package_feature_management',
            description: 'Cập nhật tính năng gói: ' . $feature->name_vi
        );

        return redirect()->route('teacher-package-features.index')->with('msg', 'Cập nhật tính năng thành công');
    }

    public function reorder(Request $request)
    {
        $ids = $request->input('ids', []);
        
        foreach ($ids as $index => $id) {
            PackageFeature::query()
                ->where('id', (int) $id)
                ->update(['sort_order' => $index + 1]);
        }

        activity_log(
            action: 'reorder',
            subject: null,
            properties: ['ids' => $ids],
            logName: 'admin_package_feature_management',
            description: 'Sắp xếp lại các tính năng gói'
        );

        return response()->json(['success' => true]);
    }

    public function bulkUpdate(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = (int) $request->input('status', PackageFeature::STATUS_ACTIVE);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một tính năng');
        }

        PackageFeature::query()
            ->whereIn('id', $ids)
            ->update(['is_enabled' => $status]);

        activity_log(
            action: 'bulk_update',
            subject: null,
            properties: [
                'ids' => $ids,
                'status' => $status,
            ],
            logName: 'admin_package_feature_management',
            description: 'Cập nhật trạng thái hàng loạt tính năng gói'
        );

        $message = 'Đã cập nhật trạng thái hàng loạt thành công';
        if ($status === PackageFeature::STATUS_MAINTENANCE_VISIBLE) {
            $message = 'Đã đưa các tính năng đã chọn vào chế độ bảo trì';
        }

        return redirect()->route('teacher-package-features.index')->with('msg', $message);
    }

    public function syncFeatures()
    {
        $seeder = new \Modules\Packages\database\seeders\PackageFeatureSeeder();
        $seeder->run();

        activity_log(
            action: 'sync_features',
            subject: null,
            properties: [],
            logName: 'admin_package_feature_management',
            description: 'Đồng bộ hóa tính năng gói từ hệ thống'
        );

        return redirect()->route('teacher-package-features.index')->with('msg', 'Đồng bộ tính năng gói thành công!');
    }
}
