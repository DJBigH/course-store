<?php

namespace Modules\Courses\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Courses\src\Models\Courses;
use App\Models\Scopes\ActiveScope;

class CourseSearchController extends Controller
{
    public function suggest(Request $request)
    {
        $query = $request->get('q');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $locale = app()->getLocale();
        $nameField = $locale === 'vi' ? 'name' : 'name_' . $locale;

        $courses = Courses::query()
            ->where(function($q) use ($query, $nameField) {
                $q->where($nameField, 'LIKE', "%{$query}%")
                  ->orWhere('code', 'LIKE', "%{$query}%");
            })
            ->where('status', 1)
            ->limit(8)
            ->get();

        $results = $courses->map(function($course) use ($locale) {
            $priceField = $locale === 'vi' ? 'price' : 'price_' . $locale;
            $salePriceField = $locale === 'vi' ? 'sale_price' : 'sale_price_' . $locale;
            
            return [
                'id' => $course->id,
                'name' => $course->name_locale,
                'slug' => $course->slug_locale,
                'thumbnail' => $course->thumbnail ? (str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : asset($course->thumbnail)) : asset('assets/clients/images/course-placeholder.png'),
                'price' => number_format($course->{$priceField} ?? 0),
                'sale_price' => $course->{$salePriceField} ? number_format($course->{$salePriceField}) : null,
                'currency' => $course->currency_symbol,
                'url' => route('courses.detail', ['locale' => $locale, 'slug' => $course->slug_locale]),
            ];
        });

        return response()->json($results);
    }
}
