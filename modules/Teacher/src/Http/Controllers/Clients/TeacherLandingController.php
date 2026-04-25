<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Modules\Packages\src\Models\Package;

class TeacherLandingController extends Controller
{
    public function index()
    {
        $content = trans('teacher::landing');
        $pageTitle = $content['page_title'] ?? __('teacher::portal.titles.apply');
        $pageName = $pageTitle;
        $packages = Package::query()->visibleForListing()->get();

        return view('teacher::clients.landing', compact('pageTitle', 'pageName', 'packages', 'content'));
    }
}
