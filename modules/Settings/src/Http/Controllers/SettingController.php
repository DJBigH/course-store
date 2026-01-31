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
        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('msg', 'Cập nhật cấu hình thành công');
    }
}
