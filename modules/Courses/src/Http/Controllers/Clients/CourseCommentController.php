<?php

namespace Modules\Courses\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Courses\src\Models\CourseComment;
use Modules\Courses\src\Models\Courses;

class CourseCommentController extends Controller
{
    public function store(Request $request, $locale, $slug)
    {
        $course = $this->findCourse($slug);
        $student = Auth::guard('students')->user();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để bình luận.',
            ], 403);
        }

        $hasCourse = $student->courses()
            ->where('courses.id', $course->id)
            ->wherePivot('status', 1)
            ->exists();

        if (!$hasCourse) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần mua khóa học để bình luận.',
            ], 403);
        }

        $payload = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $moderation = courseCommentModeration($payload['content']);

        CourseComment::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'content' => trim($payload['content']),
            'is_visible' => true,
            'is_flagged' => $moderation['is_flagged'],
            'flagged_terms' => $moderation['is_flagged'] ? implode(', ', $moderation['matched_terms']) : null,
        ]);

        return $this->renderThreadResponse($course);
    }

    public function reply(Request $request, $locale, $slug, $commentId)
    {
        $course = $this->findCourse($slug);
        $admin = Auth::user();

        abort_if(!$admin, 403);

        $comment = CourseComment::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->findOrFail($commentId);

        $payload = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $moderation = courseCommentModeration($payload['content']);

        CourseComment::create([
            'course_id' => $course->id,
            'parent_id' => $comment->id,
            'user_id' => $admin->id,
            'content' => trim($payload['content']),
            'is_visible' => true,
            'is_flagged' => $moderation['is_flagged'],
            'flagged_terms' => $moderation['is_flagged'] ? implode(', ', $moderation['matched_terms']) : null,
        ]);

        return $this->renderThreadResponse($course);
    }

    public function toggleVisibility($locale, $slug, $commentId)
    {
        $course = $this->findCourse($slug);
        abort_if(!Auth::check(), 403);

        $comment = CourseComment::query()
            ->where('course_id', $course->id)
            ->findOrFail($commentId);

        $comment->update([
            'is_visible' => !$comment->is_visible,
        ]);

        return $this->renderThreadResponse($course);
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

    protected function renderThreadResponse(Courses $course)
    {
        $student = Auth::guard('students')->user();
        $viewerIsAdmin = Auth::check();
        $canComment = $student
            ? $student->courses()
                ->where('courses.id', $course->id)
                ->wherePivot('status', 1)
                ->exists()
            : false;

        $threads = courseCommentThreads($course->id, $viewerIsAdmin);

        $html = view('courses::clients.comments_thread', [
            'course' => $course,
            'threads' => $threads,
            'canComment' => $canComment,
            'viewerIsAdmin' => $viewerIsAdmin,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }
}
