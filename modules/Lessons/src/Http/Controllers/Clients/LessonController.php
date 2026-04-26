<?php

namespace Modules\Lessons\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Courses\src\Models\Courses;
use Modules\Lessons\src\Models\Lesson;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Students\src\Models\StudentsCourses;
use Modules\Students\src\Models\StudentLessonProgress;
use Modules\Certificates\src\Models\Certificate;
use Modules\Certificates\src\Support\CertificateIssuer;

class LessonController extends Controller
{
    protected $lessonRepository;

    public function __construct(
        LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager
    ) {
        $this->lessonRepository = $lessonRepository;
    }

    public function index($locale, $slug)
    {
        $lesson = $this->lessonRepository->getLessonActive($slug);
        if (!$lesson) {
            abort(404);
        }

        $student = Auth::guard('students')->user();
        $course = Courses::query()->withoutGlobalScopes()->find($lesson->course_id);

        if (!$course) {
            abort(404);
        }

        $isAdmin = auth('web')->check() && auth('web')->user()->hasPermission('dashboard.view');
        $isImpersonating = session()->has('admin_impersonator');
        $hasCourse = $isAdmin || $isImpersonating || ($student && $student->courses()->where('courses.id', $course->id)->wherePivot('status', 1)->exists());

        if ((int) $course->status !== 1 && !$hasCourse) {
            abort(404);
        }

        if ((int) $course->is_learning_locked === 1) {
            abort(403, 'Khóa học này đang tạm thời bị khóa học tập.');
        }

        if (!$hasCourse && (int) $lesson->is_trial !== 1) {
            return redirect()->route('courses.detail', [
                'locale' => $locale,
                'slug' => $course->slug_locale,
            ]);
        }

        $enrolledAt = $this->resolveEnrollmentTime($student?->id, $course->id, $hasCourse);

        $pageTitle = $lesson->name_locale;
        $pageName = $lesson->name_locale;
        $index = 0;

        $lessons = $this->lessonRepository->getLessonByPosition($course);
        if (!$lessons) {
            abort(404);
        }

        $courseProgress = $this->buildCourseProgress($course->id, $student?->id, $hasCourse);
        $completedLessonIds = array_fill_keys($courseProgress['completed_lesson_ids'], true);
        $lessonContexts = $this->buildLessonContexts($lessons, $completedLessonIds);
        $lessonAvailabilityMap = $this->buildLessonAvailabilityMap($lessons, $hasCourse, $enrolledAt, $lessonContexts);

        $currentLessonIndex = null;
        foreach ($lessons as $key => $item) {
            if ((int) $item->id === (int) $lesson->id) {
                $currentLessonIndex = $key;
                break;
            }
        }

        $nextLesson = !empty($lessons[$currentLessonIndex + 1]) ? $lessons[$currentLessonIndex + 1] : null;
        $prevLesson = !empty($lessons[$currentLessonIndex - 1]) ? $lessons[$currentLessonIndex - 1] : null;

        $currentLessonAvailability = $lessonAvailabilityMap[(int) $lesson->id] ?? [
            'can_open' => true,
            'schedule_locked' => false,
            'message' => null,
            'available_at' => null,
        ];

        $lessonScheduleLocked = (bool) $currentLessonAvailability['schedule_locked'];
        $lessonScheduleMessage = $currentLessonAvailability['message'];

        $cacheKey = 'lesson_view_' . $lesson->id . '_' . request()->ip();
        if (!$lessonScheduleLocked && !Cache::has($cacheKey)) {
            $lesson->increment('view');
            Cache::put($cacheKey, true, now()->addMinutes(30));
        }

        if (!$lessonScheduleLocked) {
            $this->logLessonLearning($student, $course, $lesson);
        }

        $isCurrentLessonCompleted = !empty($completedLessonIds[$lesson->id]);
        $studentCertificate = $student && $hasCourse
            ? Certificate::query()
                ->where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->whereNull('revoked_at')
                ->first()
            : null;

        return view('lessons::clients.index', compact(
            'pageTitle',
            'pageName',
            'lesson',
            'course',
            'index',
            'nextLesson',
            'prevLesson',
            'hasCourse',
            'courseProgress',
            'completedLessonIds',
            'isCurrentLessonCompleted',
            'studentCertificate',
            'lessonScheduleLocked',
            'lessonScheduleMessage',
            'lessonAvailabilityMap'
        ));
    }

    public function toggleCompletion(Request $request, $locale, $slug)
    {
        $lesson = $this->lessonRepository->getLessonActive($slug);

        if (!$lesson) {
            return $this->completionErrorResponse($request, 404, __('lessons::clients/common.lesson_not_found'));
        }

        $student = Auth::guard('students')->user();
        $course = Courses::query()->withoutGlobalScopes()->find($lesson->course_id);

        if (!$student || !$course) {
            return $this->completionErrorResponse($request, 401, __('lessons::clients/common.login_required'));
        }

        $hasCourse = $student
            ->courses()
            ->where('courses.id', $course->id)
            ->wherePivot('status', 1)
            ->exists();

        if (!$hasCourse && session()->missing('admin_impersonator')) {
            return $this->completionErrorResponse($request, 403, __('courses::clients/common.lesson_purchase_required'));
        }

        if ((int) $course->is_learning_locked === 1) {
            return $this->completionErrorResponse($request, 403, 'Khóa học này đang tạm thời bị khóa học tập.');
        }

        $enrolledAt = $this->resolveEnrollmentTime($student->id, $course->id, true);
        $allLessons = $this->lessonRepository->getLessonByPosition($course);
        $completedLessonIds = StudentLessonProgress::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->pluck('lesson_id')
            ->unique()
            ->values()
            ->all();

        $lessonContexts = $this->buildLessonContexts($allLessons, array_fill_keys($completedLessonIds, true));
        $lessonContext = $lessonContexts[(int) $lesson->id] ?? [];

        if (!$this->lessonReleaseManager->isAvailableForStudent($lesson, true, $enrolledAt, $lessonContext)) {
            return $this->completionErrorResponse(
                $request,
                403,
                $this->lessonReleaseManager->buildLockedMessage($lesson, $enrolledAt, true, $lessonContext) ?? 'Bài học này chưa tới lịch mở.'
            );
        }

        $progress = StudentLessonProgress::query()->where([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ])->first();

        $isCompleted = true;

        if ($progress) {
            $progress->delete();
            $isCompleted = false;
        } else {
            $this->markLessonAsCompleted($student->id, $course->id, $lesson->id);
        }

        $courseProgress = $this->buildCourseProgress($course->id, $student->id, true);

        $issuedCertificate = null;
        if ($isCompleted) {
            $issuedCertificate = app(CertificateIssuer::class)->issueIfEligible(
                $student,
                $course,
                $courseProgress,
                $student->id,
                null,
                false,
                'auto_completion'
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'completed' => $isCompleted,
                'lesson_id' => $lesson->id,
                'course_progress' => [
                    'progress_percent' => $courseProgress['progress_percent'],
                    'completed_lessons' => $courseProgress['completed_lessons'],
                    'total_lessons' => $courseProgress['total_lessons'],
                ],
                'certificate' => $issuedCertificate
                    ? [
                        'id' => $issuedCertificate->id,
                        'url' => route('students.account.certificates.show', [
                            'locale' => app()->getLocale(),
                            'id' => $issuedCertificate->id,
                        ]),
                    ]
                    : null,
            ]);
        }

        $redirectUrl = $request->string('redirect')->toString();
        if ($redirectUrl !== '') {
            return redirect()->to($redirectUrl);
        }

        return redirect()->route('lessons.home', [
            'locale' => $locale,
            'slug' => $lesson->slug_locale,
        ]);
    }

    protected function completionErrorResponse(Request $request, int $status, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        abort($status, $message);
    }

    protected function logLessonLearning($student, $course, $lesson): void
    {
        if (!$student) {
            return;
        }

        $activityKey = sprintf('student-lesson-activity:%s:%s', $student->id, $lesson->id);

        if (Cache::has($activityKey)) {
            return;
        }

        Cache::put($activityKey, true, now()->addMinutes(30));

        activity_log(
            'lesson_learned',
            $student,
            [
                'course_name' => $this->buildTranslatedNames($course),
                'lesson_name' => $this->buildTranslatedNames($lesson),
                'lesson_id' => $lesson->id,
                'course_id' => $course->id,
            ],
            'student_learning',
            __('students::clients/account.activity_log.lesson_learned_desc')
        );
    }

    protected function buildTranslatedNames($model): array
    {
        return [
            'vi' => (string) ($model->name ?? ''),
            'en' => (string) ($model->name_en ?? $model->name ?? ''),
            'ko' => (string) ($model->name_ko ?? $model->name ?? $model->name_en ?? ''),
            'ja' => (string) ($model->name_ja ?? $model->name ?? $model->name_en ?? ''),
            'zh' => (string) ($model->name_zh ?? $model->name ?? $model->name_en ?? ''),
        ];
    }

    protected function markLessonAsCompleted(int $studentId, int $courseId, int $lessonId): void
    {
        StudentLessonProgress::query()->updateOrCreate(
            [
                'student_id' => $studentId,
                'course_id' => $courseId,
                'lesson_id' => $lessonId,
            ],
            [
                'completed_at' => now(),
            ]
        );
    }

    protected function buildCourseProgress(int $courseId, ?int $studentId, bool $hasCourse): array
    {
        $totalLessons = Lesson::query()
            ->active()
            ->where('course_id', $courseId)
            ->whereNotNull('parent_id')
            ->count();

        if (!$studentId || !$hasCourse) {
            return [
                'total_lessons' => $totalLessons,
                'completed_lessons' => 0,
                'progress_percent' => 0,
                'completed_lesson_ids' => [],
            ];
        }

        $completedLessonIds = StudentLessonProgress::query()
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->pluck('lesson_id')
            ->unique()
            ->values()
            ->all();

        $completedLessons = count($completedLessonIds);
        $progressPercent = $totalLessons > 0
            ? (int) round(($completedLessons * 100) / $totalLessons)
            : 0;

        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => min($completedLessons, $totalLessons),
            'progress_percent' => min($progressPercent, 100),
            'completed_lesson_ids' => $completedLessonIds,
        ];
    }

    protected function resolveEnrollmentTime(?int $studentId, int $courseId, bool $hasCourse): ?Carbon
    {
        if (!$studentId || !$hasCourse) {
            return null;
        }

        $enrollment = StudentsCourses::query()
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->where('status', 1)
            ->first();

        return $enrollment?->created_at ? Carbon::parse($enrollment->created_at) : null;
    }

    protected function buildLessonContexts($lessons, array $completedLessonIds): array
    {
        $contexts = [];
        $previousLesson = null;

        foreach ($lessons as $lesson) {
            $contexts[(int) $lesson->id] = [
                'has_previous' => $previousLesson !== null,
                'previous_completed' => $previousLesson ? !empty($completedLessonIds[(int) $previousLesson->id]) : false,
                'previous_lesson_name' => $previousLesson?->name_locale,
                'previous_lesson_id' => $previousLesson?->id,
            ];

            $previousLesson = $lesson;
        }

        return $contexts;
    }

    protected function buildLessonAvailabilityMap($lessons, bool $hasCourse, ?Carbon $enrolledAt, array $lessonContexts): array
    {
        $map = [];

        foreach ($lessons as $item) {
            $context = $lessonContexts[(int) $item->id] ?? [];
            $scheduleLocked = $hasCourse && !$this->lessonReleaseManager->isAvailableForStudent($item, true, $enrolledAt, $context) && session()->missing('admin_impersonator');
            $canOpenLesson = ($hasCourse || (int) $item->is_trial === 1) && !$scheduleLocked;

            $map[(int) $item->id] = [
                'can_open' => $canOpenLesson,
                'schedule_locked' => $scheduleLocked,
                'message' => $scheduleLocked
                    ? $this->lessonReleaseManager->buildLockedMessage($item, $enrolledAt, true, $context)
                    : null,
                'available_at' => $scheduleLocked
                    ? optional($this->lessonReleaseManager->resolveAvailableAt($item, $enrolledAt, true))->format('d/m/Y H:i')
                    : null,
            ];
        }

        return $map;
    }
}
