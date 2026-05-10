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
        
        $packages = Package::query()
            ->with('packageCategory')
            ->visibleForListing()
            ->get();

        // Sort by category sort_order, then by package sort_order
        $packages = $packages->sort(function ($a, $b) {
            $aCatOrder = $a->packageCategory?->sort_order ?? 9999;
            $bCatOrder = $b->packageCategory?->sort_order ?? 9999;

            if ($aCatOrder !== $bCatOrder) {
                return $aCatOrder <=> $bCatOrder;
            }

            return $a->sort_order <=> $b->sort_order;
        })->values();

        $groupedPackages = $packages->groupBy(fn($package) => $package->category_locale ?: 'Khác');

        return view('teacher::clients.landing', compact('pageTitle', 'pageName', 'packages', 'groupedPackages', 'content'));
    }
}
