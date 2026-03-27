<?php

namespace Modules\Settings\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Settings\src\Http\Requests\SettingRequest;
use Modules\Settings\src\Models\Setting;

class SettingController extends Controller
{
    public function __construct() {}

    public function index()
    {
        $pageTitle = 'Cấu hình website';
        $pageName = 'Cấu hình website';
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('settings::index', compact('settings', 'pageName', 'pageTitle'));
    }

    public function update(SettingRequest $request)
    {
        $textInputs = array_merge(
            $request->except('_token', 'banner_slider', 'banner_right', 'banner_full', 'logo'),
            [
                'global_notice_enabled' => $request->boolean('global_notice_enabled') ? '1' : '0',
                'popup_notice_enabled' => $request->boolean('popup_notice_enabled') ? '1' : '0',
                'popup_notice_snooze_minutes' => (string) ($request->input('popup_notice_snooze_minutes') ?: '60'),
            ]
        );

        $old = Setting::whereIn('key', array_keys($textInputs))
            ->pluck('value', 'key')
            ->toArray();

        foreach ($textInputs as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        if ($request->hasFile('banner_slider')) {
            $paths = [];

            foreach ($request->file('banner_slider') as $file) {
                $paths[] = $file->store('banners/slider', 'public');
            }

            Setting::updateOrCreate(['key' => 'banner_slider'], ['value' => json_encode($paths)]);
        }

        if ($request->hasFile('banner_right')) {
            $paths = [];

            foreach ($request->file('banner_right') as $file) {
                $paths[] = $file->store('banners/right', 'public');
            }

            Setting::updateOrCreate(['key' => 'banner_right'], ['value' => json_encode($paths)]);
        }

        if ($request->hasFile('banner_full')) {
            $path = $request->file('banner_full')->store('banners/full', 'public');
            Setting::updateOrCreate(['key' => 'banner_full'], ['value' => $path]);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('banners/logo', 'public');
            Setting::updateOrCreate(['key' => 'logo'], ['value' => $path]);
        }

        $allKeys = array_unique(array_merge(array_keys($textInputs), ['banner_slider', 'banner_right', 'banner_full', 'logo']));
        $newDb = Setting::whereIn('key', $allKeys)->pluck('value', 'key')->toArray();

        $changed = [];
        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $newDb[$key] ?? null;

            if ((string) $oldVal !== (string) $newVal) {
                $changed[] = $key;
            }
        }

        if (!empty($changed)) {
            activity_log(
                action: 'update_settings',
                subject: null,
                properties: [
                    'changed_keys' => $changed,
                    'old' => Arr::only($old, $changed),
                    'new' => Arr::only($newDb, $changed),
                ],
                logName: 'Cập nhật',
                description: 'Cập nhật cấu hình website'
            );
        }

        return back()->with('msg', 'Cập nhật cấu hình thành công');
    }

    public function logs(Request $request)
    {
        $pageTitle = 'Lịch sử cấu hình website';

        $query = ActiveLog::query()
            ->where('log_name', 'Cập nhật')
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

        $logs = $query->latest()->paginate(config('paginate.log_limit'))->withQueryString();

        return view('settings::logs', compact('pageTitle', 'logs'));
    }
}
