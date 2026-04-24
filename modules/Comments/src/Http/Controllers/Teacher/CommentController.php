<?php

namespace Modules\Comments\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseComment;
use App\Models\Scopes\ActiveScope;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;

class CommentController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected PackageLifecycleManager $packageLifecycleManager,
        protected TeacherNotificationCenter $notificationCenter,
        protected PackageUsageResolver $packageUsageResolver,
    ) {}

    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->has('comments')
            ->orderByDesc('id')
            ->get();

        $courseId = (int) $request->query('course_id', 0);
        $selectedCourse = $courseId > 0
            ? $courses->firstWhere('id', $courseId)
            : $courses->first();

        $threads = $selectedCourse
            ? courseCommentThreads($selectedCourse->id, true)
            : collect();

        // Calculate Stats for Premium UI
        $allTeacherComments = CourseComment::whereHas('course', function($q) use ($teacher) {
            $q->withoutGlobalScopes()->where('teacher_id', $teacher->id);
        });

        $stats = [
            'total' => (clone $allTeacherComments)->count(),
            'unread' => (clone $allTeacherComments)
                ->where('is_visible', true)
                ->where('student_id', '!=', $teacher->student_id)
                ->whereDoesntHave('replies', function($q) use ($teacher) {
                    $q->where('student_id', $teacher->student_id);
                })->count(),
            'flagged' => (clone $allTeacherComments)->where('is_flagged', true)->count(),
        ];

        $pageTitle = __('comments::teacher/messages.page_title');
        $pageName = $pageTitle;

        // Resolve Package Summary
        $packageSummary = $this->resolvePackageSummary($teacher);

        return view('comments::teacher.index', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'courses',
            'selectedCourse',
            'threads',
            'stats',
            'packageSummary'
        ));
    }

    public function reply(Request $request, int $commentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->commentErrorResponse($request, 'Unauthorized', 401);
        }

        $comment = CourseComment::query()
            ->whereHas('course', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->where('teacher_id', $teacher->id);
            })
            ->findOrFail($commentId);

        $payload = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $content = $this->sanitizeCommentContent($payload['content']);
        if (Str::length($content) < 2) {
            return $this->commentErrorResponse($request, __('comments::teacher/messages.flash.reply_too_short'), 422);
        }

        $moderation = courseCommentModeration($content);

        CourseComment::create([
            'course_id' => $comment->course_id,
            'parent_id' => $comment->id,
            'student_id' => auth('students')->id(),
            'content' => $content,
            'is_visible' => ($moderation['status'] ?? 1) == 1,
        ]);

        return $this->renderTeacherCommentThread($request, $comment->course_id, $teacher);
    }

    public function toggleVisibility(Request $request, int $commentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->commentErrorResponse($request, 'Unauthorized', 401);
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_comments')) {
            return $featureRedirect;
        }

        $comment = CourseComment::query()
            ->whereHas('course', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->where('teacher_id', $teacher->id);
            })
            ->findOrFail($commentId);

        $oldVisibility = $comment->is_visible;

        $comment->update([
            'is_visible' => !$comment->is_visible,
        ]);

        $comment->refresh();

        activity_log(
            action: 'toggle_visibility',
            subject: $comment,
            properties: [
                'old' => ['is_visible' => $oldVisibility],
                'new' => ['is_visible' => $comment->is_visible],
            ],
            logName: 'teacher_comment_management',
        );

        return $this->renderTeacherCommentThread($request, $comment->course_id, $teacher);
    }

    /**
     * Re-render original helper logic but with new view paths
     */
    protected function renderTeacherCommentThread(Request $request, int $courseId, $teacher)
    {
        if ($request->expectsJson()) {
            $course = Courses::query()->withoutGlobalScopes()->findOrFail($courseId);
            $threads = courseCommentThreads($courseId, true);

            $html = view('comments::teacher.thread', compact('course', 'threads', 'teacher'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        return redirect()->back();
    }
}
