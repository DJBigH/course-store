<?php

namespace Modules\Lessons\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Lessons\src\Models\Lesson;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Students\src\Models\StudentLessonProgress;

class LessonController extends Controller
{
    protected $lessonRepository;
    public function __construct(LessonsRepositoryInterface $lessonRepository)
    {
       
        $this->lessonRepository = $lessonRepository;
    }

    public function index($locale, $slug)
    {
        $lesson = $this->lessonRepository->getLessonActive($slug);
        if (!$lesson) {
            abort(404);
        }

        $student = Auth::guard('students')->user();
        $course = $lesson->course;

        if (!$course) {
            abort(404);
        }

        $hasCourse = $student
            ? $student
                ->courses()
                ->where('courses.id', $course->id)
                ->wherePivot('status', 1)
                ->exists()
            : false;

        if (!$hasCourse && (int) $lesson->is_trial !== 1) {
            return redirect()->route('courses.detail', [
                'locale' => $locale,
                'slug' => $course->slug_locale,
            ]);
        }

        $cacheKey = 'lesson_view_' . $lesson->id . '_' . request()->ip();

        if (!Cache::has($cacheKey)) {
            $lesson->increment('view');
            Cache::put($cacheKey, true, now()->addMinutes(30));
        }

        $this->logLessonLearning($student, $course, $lesson);

        $pageTitle = $lesson->name_locale;
        $pageName = $lesson->name_locale;
        $index = 0;

        $lessons = $this->lessonRepository->getLessonByPosition($course);
        if (!$lessons) {
            abort(404);
        }

        $currentLessonIndex = null;

        foreach ($lessons as $key => $item) {
            if ($item->id == $lesson->id) {
                $currentLessonIndex = $key;
                break;
            }
        }

        $nextLesson = null;
        $prevLesson = null;

        if (!empty($lessons[$currentLessonIndex + 1])) {
            $nextLesson = $lessons[$currentLessonIndex + 1];
        }

        if (!empty($lessons[$currentLessonIndex - 1])) {
            $prevLesson = $lessons[$currentLessonIndex - 1];
        }

        $courseProgress = $this->buildCourseProgress($course->id, $student?->id, $hasCourse);
        $completedLessonIds = array_fill_keys($courseProgress['completed_lesson_ids'], true);
        $isCurrentLessonCompleted = !empty($completedLessonIds[$lesson->id]);

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
            'isCurrentLessonCompleted'
        ));
    }

    public function toggleCompletion(Request $request, $locale, $slug)
    {
        $lesson = $this->lessonRepository->getLessonActive($slug);

        if (!$lesson) {
            return $this->completionErrorResponse($request, 404, __('lessons::clients/common.lesson_not_found'));
        }

        $student = Auth::guard('students')->user();
        $course = $lesson->course;

        if (!$student || !$course) {
            return $this->completionErrorResponse($request, 401, __('lessons::clients/common.login_required'));
        }

        $hasCourse = $student
            ->courses()
            ->where('courses.id', $course->id)
            ->wherePivot('status', 1)
            ->exists();

        if (!$hasCourse) {
            return $this->completionErrorResponse($request, 403, __('courses::clients/common.lesson_purchase_required'));
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
}
