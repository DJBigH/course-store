<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Courses\src\Models\Courses;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherRating;

class TeacherPublicController extends Controller
{
    public function show($locale, string $slug)
    {
        $teacher = $this->findTeacher($slug);
        $student = Auth::guard('students')->user();
        $canRateTeacher = $student ? $this->studentCanRateTeacher($student->id, $teacher->id) : false;

        $teacher->loadCount('ratings');
        $teacher->loadAvg('ratings', 'rating');
        $courses = Courses::query()
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->where('teacher_id', $teacher->id)
            ->where('status', 1)
            ->latest('id')
            ->get();

        $pageTitle = $teacher->name_locale;
        $pageName = $pageTitle;
        $viewerTeacherRating = $student
            ? TeacherRating::query()
                ->where('teacher_id', $teacher->id)
                ->where('student_id', $student->id)
                ->value('rating')
            : null;

        return view('teacher::clients.public_show', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'courses',
            'canRateTeacher',
            'viewerTeacherRating'
        ));
    }

    public function rate(Request $request, $locale, string $slug)
    {
        $teacher = $this->findTeacher($slug);
        $student = Auth::guard('students')->user();

        if (!$student) {
            return $this->errorResponse($request, __('courses::clients/common.rating_login_required'), 403);
        }

        if (!$this->studentCanRateTeacher($student->id, $teacher->id)) {
            return $this->errorResponse($request, __('teacher::public.rating_need_purchase'), 403);
        }

        $existingRating = TeacherRating::query()
            ->where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->value('rating');

        if ($existingRating !== null) {
            return $this->errorResponse($request, __('teacher::public.rating_already_submitted'), 422);
        }

        $payload = $request->validate([
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
        ]);

        $rating = $this->normalizeHalfStarRating($payload['rating']);
        if ($rating === null) {
            return $this->errorResponse($request, __('courses::clients/common.rating_invalid'), 422);
        }

        TeacherRating::query()->create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'rating' => $rating,
        ]);

        $teacher->loadCount('ratings');
        $teacher->loadAvg('ratings', 'rating');

        $html = view('teacher::clients.partials.rating_panel', [
            'teacher' => $teacher,
            'canRateTeacher' => true,
            'viewerTeacherRating' => $rating,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    private function findTeacher(string $slug): Teacher
    {
        return Teacher::query()
            ->where('status', 'active')
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug_en', $slug)
                    ->orWhere('slug_ko', $slug)
                    ->orWhere('slug_ja', $slug)
                    ->orWhere('slug_zh', $slug);
            })
            ->firstOrFail();
    }

    private function studentCanRateTeacher(int $studentId, int $teacherId): bool
    {
        return Courses::query()
            ->where('teacher_id', $teacherId)
            ->whereHas('students', function ($query) use ($studentId) {
                $query->where('students.id', $studentId)
                    ->where('students_courses.status', 1);
            })
            ->exists();
    }

    private function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->with('msg_danger', $message);
    }

    private function normalizeHalfStarRating(mixed $value): ?float
    {
        $rating = round(((float) $value) * 2) / 2;

        if ($rating < 1 || $rating > 5) {
            return null;
        }

        $validRatings = [1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5];

        return in_array($rating, $validRatings, true) ? $rating : null;
    }
}
