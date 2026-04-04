<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Modules\Teacher\src\Models\TeacherPackage;

class TeacherLandingController extends Controller
{
    public function index()
    {
        $content = trans('teacher::landing');
        $pageTitle = $content['page_title'] ?? __('teacher::portal.titles.apply');
        $pageName = $pageTitle;
        $packages = TeacherPackage::query()->visibleForListing()->get();

        return view('teacher::clients.landing', compact('pageTitle', 'pageName', 'packages', 'content'));
    }
}
