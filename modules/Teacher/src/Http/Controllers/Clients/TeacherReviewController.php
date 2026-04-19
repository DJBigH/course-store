<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Models\Teacher;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseComment;
use App\Models\Scopes\ActiveScope;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\Teacher\src\Support\TeacherPackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;

class TeacherReviewController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected TeacherPackageLifecycleManager $packageLifecycleManager,
        protected TeacherNotificationCenter $notificationCenter,
        protected TeacherPackageUsageResolver $packageUsageResolver,
    ) {}

    public function comments(Request $request)
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

        $pageTitle = __('teacher::comments.page_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.comments', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'courses',
            'selectedCourse',
            'threads'
        ));
    }

    public function replyComment(Request $request, int $commentId)
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
            return $this->commentErrorResponse($request, __('teacher::comments.flash.reply_too_short'), 422);
        }

        $moderation = courseCommentModeration($content);

        CourseComment::create([
            'course_id' => $comment->course_id,
            'parent_id' => $comment->id,
            'student_id' => auth('students')->id(),
            'teacher_id' => $teacher->id,
            'content' => $content,
            'status' => $moderation['status'] ?? 1,
            'is_teacher_reply' => true,
        ]);

        return $this->renderTeacherCommentThread($request, $comment->course_id, $teacher);
    }

    public function toggleCommentVisibility(Request $request, int $commentId)
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

        $comment->update([
            'status' => (int) $comment->status === 1 ? 0 : 1,
        ]);

        return $this->renderTeacherCommentThread($request, $comment->course_id, $teacher);
    }
}
