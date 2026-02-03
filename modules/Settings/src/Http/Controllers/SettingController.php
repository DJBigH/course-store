<?php

namespace Modules\Settings\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        /* =====================
     | 1. LƯU TEXT SETTING
     ===================== */
        foreach ($request->except('_token', 'banner_slider', 'banner_right', 'banner_full') as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        /* =====================
     | 2. BANNER SLIDER (NHIỀU ẢNH)
     ===================== */
        if ($request->hasFile('banner_slider')) {
            $paths = [];

            foreach ($request->file('banner_slider') as $file) {
                $paths[] = $file->store('banners/slider', 'public');
            }

            Setting::updateOrCreate(
                ['key' => 'banner_slider'],
                ['value' => json_encode($paths)]
            );
        }

        /* =====================
     | 3. BANNER PHẢI (NHIỀU ẢNH)
     ===================== */
        if ($request->hasFile('banner_right')) {
            $paths = [];

            foreach ($request->file('banner_right') as $file) {
                $paths[] = $file->store('banners/right', 'public');
            }

            Setting::updateOrCreate(
                ['key' => 'banner_right'],
                ['value' => json_encode($paths)]
            );
        }

        /* =====================
     | 4. BANNER FULL (1 ẢNH)
     ===================== */
        if ($request->hasFile('banner_full')) {
            $path = $request->file('banner_full')->store('banners/full', 'public');

            Setting::updateOrCreate(
                ['key' => 'banner_full'],
                ['value' => $path]
            );
        }
        /* =====================
        | 4. BANNER FULL (1 ẢNH)
     ===================== */
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('banners/logo', 'public');

            Setting::updateOrCreate(
                ['key' => 'logo'],
                ['value' => $path]
            );
        }

        return back()->with('msg', 'Cập nhật cấu hình thành công');
    }
}
