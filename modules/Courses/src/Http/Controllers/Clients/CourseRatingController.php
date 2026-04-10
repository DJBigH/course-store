<?php

namespace Modules\Courses\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Courses\src\Models\CourseRating;
use Modules\Courses\src\Models\Courses;

class CourseRatingController extends Controller
{
    public function store(Request $request, $locale, $slug)
    {
        $course = $this->findCourse($slug);
        $student = Auth::guard('students')->user();

        if (!$student) {
            return $this->errorResponse($request, __('courses::clients/common.rating_login_required'), 403);
        }

        $hasCourse = $student->courses()
            ->where('courses.id', $course->id)
            ->wherePivot('status', 1)
            ->exists();

        if (!$hasCourse) {
            return $this->errorResponse($request, __('courses::clients/common.rating_need_purchase'), 403);
        }

        $existingRating = CourseRating::query()
            ->where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->value('rating');

        if ($existingRating !== null) {
            return $this->errorResponse($request, __('courses::clients/common.rating_already_submitted'), 422);
        }

        $payload = $request->validate([
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
        ]);

        $rating = $this->normalizeHalfStarRating($payload['rating']);
        if ($rating === null) {
            return $this->errorResponse($request, __('courses::clients/common.rating_invalid'), 422);
        }

        CourseRating::query()->create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'rating' => $rating,
        ]);

        return $this->renderRatingResponse($request, $course, $student->id, $hasCourse);
    }

    protected function findCourse(string $slug): Courses
    {
        return Courses::query()
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug_en', $slug)
                    ->orWhere('slug_ko', $slug)
                    ->orWhere('slug_ja', $slug)
                    ->orWhere('slug_zh', $slug);
            })
            ->firstOrFail();
    }

    protected function renderRatingResponse(Request $request, Courses $course, int $studentId, bool $canRate)
    {
        $course->loadCount('ratings');
        $course->loadAvg('ratings', 'rating');

        $html = view('courses::clients.partials.rating_panel', [
            'course' => $course,
            'canRate' => $canRate,
            'viewerCourseRating' => CourseRating::query()
                ->where('course_id', $course->id)
                ->where('student_id', $studentId)
                ->value('rating'),
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    protected function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->with('msg_danger', $message)->withFragment('evaluate');
    }

    protected function normalizeHalfStarRating(mixed $value): ?float
    {
        $rating = round(((float) $value) * 2) / 2;

        if ($rating < 1 || $rating > 5) {
            return null;
        }

        $validRatings = [1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5];

        return in_array($rating, $validRatings, true) ? $rating : null;
    }
}
