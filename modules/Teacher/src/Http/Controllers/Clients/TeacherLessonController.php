<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Requests\TeacherLessonRequest;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Modules\Lessons\src\Models\Lesson;
use Modules\Courses\src\Models\Courses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherLessonController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected PackageLifecycleManager $packageLifecycleManager,
        protected PackageUsageResolver $packageUsageResolver,
        protected TeacherNotificationCenter $notificationCenter,
    ) {}

    public function lessons(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $pageTitle = __('teacher::teacher/lesson/list.title', ['course' => $course->name_locale]);
        $pageName = __('teacher::teacher/lesson/list.title', ['course' => $course->name_locale]);
        $modules = Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->with(['subLessons' => fn ($query) => $query->orderBy('position')])
            ->orderBy('position')
            ->get();

        return view('teacher::teacher.lesson.lesson', compact('pageTitle', 'pageName', 'teacher', 'course', 'modules') + [
            'canImportExportLessons' => $teacher->packageHasFeature('can_import_export'),
            'existingModuleSelectors' => $this->buildExistingLessonModuleSelectors($course),
            'lessonImportColumns' => $this->lessonImportColumns(),
            'lessonImportPreview' => $this->getLessonImportPreview($course),
        ]);
    }

    public function lessonsTrash(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $pageTitle = __('teacher::teacher/lesson/list.trash_title', ['course' => $course->name_locale]);
        $pageName = __('teacher::teacher/lesson/list.trash_title', ['course' => $course->name_locale]);
        $lessons = Lesson::query()
            ->onlyTrashed()
            ->where('course_id', $course->id)
            ->orderByRaw('COALESCE(parent_id, 0)')
            ->orderBy('position')
            ->get();
        $trashedLessonRows = $this->flattenTrashedLessons($lessons);

        return view('teacher::teacher.lesson.trash', compact('pageTitle', 'pageName', 'teacher', 'course', 'trashedLessonRows'));
    }

    public function createLesson(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $pageTitle = __('teacher::teacher/lesson/add.create_title', ['course' => $course->name_locale]);
        $pageName = __('teacher::teacher/lesson/add.create_title', ['course' => $course->name_locale]);

        $defaultParentId = (int) request()->query('parent_id', 0);
        $lastPosition = Lesson::query()->where('course_id', $course->id)->max('position') ?: 0;

        return view('teacher::teacher.lesson.create', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'course' => $course,
            'lesson' => null,
            'modules' => Lesson::query()->where('course_id', $course->id)->whereNull('parent_id')->orderBy('position')->get(),
            'defaultParentId' => $defaultParentId,
            'position' => $lastPosition + 1,
            'schedule' => $this->summarizeLessonSchedule(null),
            'canScheduleContent' => $teacher->packageHasFeature('can_schedule_content'),
            'formAction' => route('teacher.dashboard.lessons.store', $course->id),
            'submitLabel' => __('teacher::teacher/lesson/common.actions.create'),
        ]);
    }

    public function storeLesson(TeacherLessonRequest $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $data = $request->validated();
        $lesson = Lesson::query()->create($this->buildLessonPayload(
            $data,
            $course,
            null,
            $teacher->packageHasFeature('can_schedule_content')
        ));
        $this->updateCourseDurations($course->id);

        $this->logTeacherLessonActivity(
            $teacher,
            $course,
            $lesson,
            'lesson_created',
            'teacher::teacher/lesson/common.history.lesson_created',
            [
                'type' => $lesson->parent_id ? 'lesson' : 'module',
                'has_video' => !empty($lesson->video_id),
                'has_document' => !empty($lesson->document_id),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::teacher/lesson/add.flash.created'));
    }

    public function editLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $pageTitle = __('teacher::teacher/lesson/edit.edit_title', ['lesson' => $lesson->name_locale]);
        $pageName = __('teacher::teacher/lesson/edit.edit_title', ['lesson' => $lesson->name_locale]);

        return view('teacher::teacher.lesson.create', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'course' => $course,
            'lesson' => $lesson,
            'modules' => Lesson::query()->where('course_id', $course->id)->where('id', '!=', $lesson->id)->whereNull('parent_id')->orderBy('position')->get(),
            'defaultParentId' => $lesson->parent_id ?: 0,
            'position' => $lesson->position,
            'schedule' => $this->summarizeLessonSchedule($lesson),
            'canScheduleContent' => $teacher->packageHasFeature('can_schedule_content'),
            'formAction' => route('teacher.dashboard.lessons.update', [$course->id, $lesson->id]),
            'submitLabel' => __('teacher::teacher/lesson/common.actions.update'),
        ]);
    }

    public function updateLesson(TeacherLessonRequest $request, int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $data = $request->validated();
        $before = $lesson->only(['name', 'parent_id', 'position', 'is_trial', 'status']);
        $lesson->update($this->buildLessonPayload(
            $data,
            $course,
            $lesson,
            $teacher->packageHasFeature('can_schedule_content')
        ));
        $this->updateCourseDurations($course->id);

        $this->logTeacherLessonActivity(
            $teacher,
            $course,
            $lesson,
            'lesson_updated',
            'teacher::teacher/lesson/common.history.lesson_updated',
            [
                'before' => $before,
                'after' => $lesson->only(['name', 'parent_id', 'position', 'is_trial', 'status']),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::teacher/lesson/edit.flash.updated'));
    }

    public function deleteLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $branchIds = $this->collectLessonBranchIds($lesson->id);
        Lesson::query()->whereIn('id', $branchIds)->delete();
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::teacher/lesson/common.flash.deleted'));
    }

    public function restoreLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $lesson = $this->resolveOwnedLesson($course, $lessonId, true);
        if (!$lesson->trashed()) {
            abort(404);
        }

        $branchIds = $this->collectLessonBranchIds($lesson->id);
        Lesson::query()->onlyTrashed()->whereIn('id', $branchIds)->restore();
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.trash', $course->id)
            ->with('msg_success', __('teacher::teacher/lesson/common.flash.restored'));
    }



    public function exportLessons(Request $request, int $courseId, string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        if ($format !== 'csv') {
            abort(404);
        }

        $rows = $this->buildLessonExportRows($course);
        $filename = 'teacher-lessons-' . $course->id . '-' . now()->format('Ymd-His') . '.csv';

        return $this->streamCsvDownload($filename, $this->lessonImportColumns(), $rows);
    }

    public function downloadLessonImportTemplate(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        $filename = 'lesson-import-template-' . $course->id . '.csv';
        $rows = $this->lessonImportTemplateRows();

        return $this->streamCsvDownload($filename, $this->lessonImportColumns(), $rows);
    }

    public function downloadLessonImportExample(int $courseId, ?string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        $format = Str::lower((string) $format);
        $rows = $this->lessonImportExampleRows();

        if ($format === 'xlsx') {
            $filename = 'lesson-import-example-' . $course->id . '.xlsx';

            return $this->streamXlsxDownload($filename, $this->lessonImportColumns(), $rows);
        }

        $filename = 'lesson-import-example-' . $course->id . '.csv';

        return $this->streamCsvDownload($filename, $this->lessonImportColumns(), $rows);
    }

    public function importLessons(Request $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        $payload = $request->validate([
            'lesson_import_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:4096'],
        ]);

        $result = $this->validateLessonImportFile(
            $payload['lesson_import_file']->getRealPath(),
            $payload['lesson_import_file']->getClientOriginalExtension(),
            $course,
            $teacher->packageHasFeature('can_schedule_content')
        );

        if (!empty($result['errors'])) {
            session()->forget($this->lessonImportPreviewSessionKey($course->id));

            return redirect()
                ->route('teacher.dashboard.lessons.index', $course->id)
                ->with('msg_danger', __('teacher::teacher/lesson/common.import.flash.validation_failed'))
                ->with('lesson_import_errors', $result['errors'])
                ->with('lesson_import_summary', $result['summary'] ?? null);
        }

        $createdRows = DB::transaction(function () use ($result, $teacher, $course) {
            $createdModules = [];
            $createdRows = [];

            foreach ($result['rows'] as $row) {
                $parentId = null;
                if ($row['type'] === 'lesson') {
                    $parentId = $this->resolveImportedLessonParentId($row['parent_selector'], $createdModules, $course);
                }

                $lesson = Lesson::query()->create($this->buildLessonPayload(
                    [
                        'name' => $row['name'],
                        'name_en' => $row['name_en'],
                        'name_ko' => $row['name_ko'],
                        'name_ja' => $row['name_ja'],
                        'name_zh' => $row['name_zh'],
                        'parent_id' => $parentId,
                        'is_trial' => $row['type'] === 'module' ? 0 : $row['is_trial'],
                        'position' => $row['position'],
                        'video' => $row['video'],
                        'document' => $row['document'],
                        'description' => $row['description'],
                        'description_en' => $row['description_en'],
                        'description_ko' => $row['description_ko'],
                        'description_ja' => $row['description_ja'],
                        'description_zh' => $row['description_zh'],
                        'status' => $row['status'],
                        'release_mode' => $row['release_mode'],
                        'release_at' => $row['release_at'],
                        'release_after_days' => $row['release_after_days'],
                    ],
                    $course,
                    null,
                    $teacher->packageHasFeature('can_schedule_content')
                ));

                if ($row['type'] === 'module' && $row['module_ref'] !== '') {
                    $createdModules[$row['module_ref']] = (int) $lesson->id;
                }

                $createdRows[] = [
                    'lesson' => $lesson,
                    'row' => $row,
                ];
            }

            return $createdRows;
        });

        foreach ($createdRows as $createdRow) {
            $lesson = $createdRow['lesson'];
            $row = $createdRow['row'];

            $this->logTeacherLessonActivity(
                $teacher,
                $course,
                $lesson,
                'import_lesson',
                'teacher::teacher/lesson/common.history.import_lesson',
                [
                    'import_line' => $row['line'],
                    'type' => $row['type'],
                ]
            );
        }

        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::teacher/lesson/common.import.flash.success', ['count' => count($createdRows)]));
    }

    public function toggleLessonVisibility(Request $request, int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return response()->json(['success' => false, 'message' => 'Course is locked'], 403);
        }

        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $before = (int) ($lesson->status ?? 0);
        $lesson->update([
            'status' => !$before,
        ]);

        $this->logTeacherLessonActivity(
            $teacher,
            $course,
            $lesson,
            'lesson_visibility_toggled',
            'teacher::teacher/lesson/common.history.lesson_visibility_toggled',
            ['before' => $before, 'after' => (int) $lesson->status]
        );

        return response()->json([
            'success' => true,
            'status' => (int) $lesson->status,
            'message' => __('teacher::teacher/lesson/common.flash.visibility_updated'),
        ]);
    }

    public function toggleLessonTrial(Request $request, int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return response()->json(['success' => false, 'message' => 'Course is locked'], 403);
        }

        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        if (!$lesson->parent_id) {
            return response()->json(['success' => false, 'message' => 'Cannot toggle trial for modules'], 422);
        }

        $before = (int) ($lesson->is_trial ?? 0);
        $lesson->update([
            'is_trial' => !$before,
        ]);

        $this->logTeacherLessonActivity(
            $teacher,
            $course,
            $lesson,
            'lesson_trial_toggled',
            'teacher::teacher/lesson/common.history.lesson_trial_toggled',
            ['before' => $before, 'after' => (int) $lesson->is_trial]
        );

        return response()->json([
            'success' => true,
            'is_trial' => (int) $lesson->is_trial,
            'message' => __('teacher::teacher/lesson/common.flash.trial_updated'),
        ]);
    }

    public function updateLessonPosition(Request $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return response()->json(['success' => false, 'message' => 'Course is locked'], 403);
        }

        $data = $request->validate([
            'positions' => ['required', 'array'],
            'positions.*.id' => ['required', 'integer'],
            'positions.*.position' => ['required', 'integer'],
            'positions.*.parent_id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($data, $course) {
            foreach ($data['positions'] as $pos) {
                Lesson::query()
                    ->where('course_id', $course->id)
                    ->where('id', $pos['id'])
                    ->update([
                        'position' => $pos['position'],
                        'parent_id' => !empty($pos['parent_id']) ? $pos['parent_id'] : null,
                    ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => __('teacher::teacher/lesson/common.flash.positions_updated'),
        ]);
    }

    public function getLessonPreviewData(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Course not found'], 404);
        }

        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        if (!$lesson || !$lesson->video) {
            return response()->json([
                'success' => false,
                'message' => __('teacher::teacher/lesson/common.modal.error_video')
            ]);
        }

        $video = $lesson->video;
        $videoUrl = $video->url;
        $type = 'file';

        if (Str::contains($videoUrl, ['youtube.com', 'youtu.be', 'vimeo.com'])) {
            $type = 'embed';
            // Simple transformation for YouTube/Vimeo into embed format if needed
            if (Str::contains($videoUrl, 'youtube.com/watch?v=')) {
                $videoUrl = str_replace('watch?v=', 'embed/', $videoUrl);
            } elseif (Str::contains($videoUrl, 'youtu.be/')) {
                $videoUrl = 'https://www.youtube.com/embed/' . explode('youtu.be/', $videoUrl)[1];
            } elseif (Str::contains($videoUrl, 'vimeo.com/')) {
                $videoUrl = 'https://player.vimeo.com/video/' . (explode('vimeo.com/', $videoUrl)[1] ?? '');
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $lesson->id,
                'name' => $lesson->name_locale,
                'video' => [
                    'type' => $type,
                    'url' => $videoUrl
                ]
            ]
        ]);
    }
}
