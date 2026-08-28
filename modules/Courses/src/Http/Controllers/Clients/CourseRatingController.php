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
            'rating' => ['required', 'numeric', 'min:0.5', 'max:5'],
        ]);

        $rating = $this->normalizeHalfStarRating($payload['rating']);
        if ($rating === null) {
            return $this->errorResponse($request, __('courses::clients/common.rating_invalid'), 422);
        }

        $newRating = CourseRating::query()->create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'rating' => $rating,
        ]);

        // Notify Teacher
        if ($course->teacher && $course->teacher->student) {
            $course->teacher->student->notify(new \App\Notifications\RatingNotification($newRating));

            // Telegram Notification for Teacher
            $teacher = $course->teacher;
            if ($teacher && $teacher->hasTelegramFeature() && $teacher->telegram_chat_id && $teacher->is_telegram_notifications_enabled) {
                $courseName = $course->name_locale ?: $course->name;
                $studentName = $student->name ?: 'Học viên';
                $stars = str_repeat('⭐', floor($newRating->rating)) . ($newRating->rating - floor($newRating->rating) > 0 ? '✨' : '');

                $text = "⭐ <b>[ĐÁNH GIÁ MỚI]</b>\n\n";
                $text .= "Giảng viên <b>{$teacher->name}</b> ơi, học viên vừa đánh giá khóa học của bạn:\n";
                $text .= "📚 <b>{$courseName}</b>\n\n";
                $text .= "👤 <b>Học viên:</b> {$studentName}\n";
                $text .= "🌟 <b>Đánh giá:</b> {$newRating->rating} / 5 {$stars}\n";
                $text .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i:s d/m/Y');

                \App\Jobs\SendTelegramTeacherNotification::dispatch($teacher, $text);
            }
        }

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
        $course->loadCount(['ratings' => function ($query) {
            $query->where('status', 1);
        }]);
        $course->loadAvg(['ratings' => function ($query) {
            $query->where('status', 1);
        }], 'rating');

        $ratingBreakdown = \Illuminate\Support\Facades\DB::table('course_ratings')
            ->where('course_id', $course->id)
            ->where('status', 1)
            ->select('rating', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $html = view('courses::clients.partials.rating_panel', [
            'course' => $course,
            'canRate' => $canRate,
            'viewerCourseRating' => CourseRating::query()
                ->where('course_id', $course->id)
                ->where('student_id', $studentId)
                ->value('rating'),
            'ratingBreakdown' => $ratingBreakdown,
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

        if ($rating < 0.5 || $rating > 5) {
            return null;
        }

        $validRatings = [0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5];

        return in_array($rating, $validRatings, true) ? $rating : null;
    }
}
