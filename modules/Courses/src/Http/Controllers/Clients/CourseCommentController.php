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
            return $this->errorResponse($request, __('courses::clients/common.comment_login_required'), 403);
        }

        $hasCourse = $student->courses()
            ->where('courses.id', $course->id)
            ->wherePivot('status', 1)
            ->exists();

        if (!$hasCourse) {
            return $this->errorResponse($request, __('courses::clients/common.comment_need_purchase'), 403);
        }

        $payload = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $content = $this->sanitizeCommentContent($payload['content']);

        if (mb_strlen($content) < 2) {
            return $this->errorResponse($request, __('courses::clients/common.comment_too_short'), 422);
        }

        $moderation = courseCommentModeration($content);

        CourseComment::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'content' => $content,
            'is_visible' => true,
            'is_flagged' => $moderation['is_flagged'],
            'flagged_terms' => $moderation['is_flagged'] ? implode(', ', $moderation['matched_terms']) : null,
        ]);

        return $this->renderThreadResponse($request, $course);
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

        $content = $this->sanitizeCommentContent($payload['content']);

        if (mb_strlen($content) < 2) {
            return $this->errorResponse($request, __('courses::clients/common.comment_reply_too_short'), 422);
        }

        $moderation = courseCommentModeration($content);

        CourseComment::create([
            'course_id' => $course->id,
            'parent_id' => $comment->id,
            'user_id' => $admin->id,
            'content' => $content,
            'is_visible' => true,
            'is_flagged' => $moderation['is_flagged'],
            'flagged_terms' => $moderation['is_flagged'] ? implode(', ', $moderation['matched_terms']) : null,
        ]);

        return $this->renderThreadResponse($request, $course);
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

        return $this->renderThreadResponse(request(), $course);
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

    protected function renderThreadResponse(Request $request, Courses $course)
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

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        return redirect()->back()->withFragment('evaluate');
    }

    protected function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()
            ->withInput()
            ->with('msg_danger', $message)
            ->withFragment('evaluate');
    }

    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    protected function sanitizeCommentContent(string $content): string
    {
        $plainText = strip_tags(str_replace('&nbsp;', ' ', $content));
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $plainText) ?? '');
    }
}
