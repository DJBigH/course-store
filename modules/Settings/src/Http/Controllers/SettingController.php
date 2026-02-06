<?php

namespace Modules\Settings\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Settings\src\Models\Setting;

class SettingController extends Controller
{

    public function __construct() {}

    public function index()
    {
        $pageTitle = 'Cấu hình Website';
        $pageName = 'Cấu hình Website';
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('settings::index', compact('settings', 'pageName', 'pageTitle'));
    }

    public function update(Request $request)
    {
        // 1) Lấy tất cả input trừ file + token
        $textInputs = $request->except('_token', 'banner_slider', 'banner_right', 'banner_full', 'logo');

        // 2) Lấy old settings theo các key gửi lên
        $old = Setting::whereIn('key', array_keys($textInputs))
            ->pluck('value', 'key')
            ->toArray();

        // 3) Lưu text settings
        foreach ($textInputs as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 4) Lưu file settings + chuẩn bị newValues để log
        $new = $textInputs; // new của text

        if ($request->hasFile('banner_slider')) {
            $paths = [];
            foreach ($request->file('banner_slider') as $file) {
                $paths[] = $file->store('banners/slider', 'public');
            }
            Setting::updateOrCreate(['key' => 'banner_slider'], ['value' => json_encode($paths)]);
            $new['banner_slider'] = $paths; // log dạng array cho dễ đọc
        }

        if ($request->hasFile('banner_right')) {
            $paths = [];
            foreach ($request->file('banner_right') as $file) {
                $paths[] = $file->store('banners/right', 'public');
            }
            Setting::updateOrCreate(['key' => 'banner_right'], ['value' => json_encode($paths)]);
            $new['banner_right'] = $paths;
        }

        if ($request->hasFile('banner_full')) {
            $path = $request->file('banner_full')->store('banners/full', 'public');
            Setting::updateOrCreate(['key' => 'banner_full'], ['value' => $path]);
            $new['banner_full'] = $path;
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('banners/logo', 'public');
            Setting::updateOrCreate(['key' => 'logo'], ['value' => $path]);
            $new['logo'] = $path;
        }

        // 5) Lấy lại new values trong DB cho chắc (bao gồm key file)
        $allKeys = array_unique(array_merge(array_keys($textInputs), ['banner_slider', 'banner_right', 'banner_full', 'logo']));
        $newDb = Setting::whereIn('key', $allKeys)->pluck('value', 'key')->toArray();

        // 6) Tính changed (chỉ log cái thay đổi)
        $changed = [];
        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $newDb[$key] ?? null;

            if ((string)$oldVal !== (string)$newVal) {
                $changed[] = $key;
            }
        }

        // 7) Tạo log (1 record duy nhất cho mỗi lần update)
        if (!empty($changed)) {
            activity_log(
                action: 'update_settings',
                subject: null, // settings không cần subject_id
                properties: [
                    'changed_keys' => $changed,
                    'old' => Arr::only($old, $changed),
                    'new' => Arr::only($newDb, $changed),
                ],
                logName: 'Cập nhập',
                description: 'Cập nhật cấu hình website'
            );
        }

        return back()->with('msg', 'Cập nhật cấu hình thành công');
    }

    public function logs(Request $request)
    {
        $pageTitle = "Lịch sử cấu hình Website";

        $query = ActiveLog::query()
            ->where('log_name', 'Cập nhập')
            ->withoutGlobalScopes();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%");
            });
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('settings::logs', compact('pageTitle', 'logs'));
    }
}
