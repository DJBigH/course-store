<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Requests\TeacherCourseRequest;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use App\Models\Scopes\ActiveScope;
use Modules\Courses\src\Models\Courses;
use Illuminate\Pagination\LengthAwarePaginator;

class TeacherCourseController extends Controller
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

    public function courses(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = __('teacher::teacher/course/list.title');
        $pageName = __('teacher::teacher/course/list.title');
        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->withCount(['lessons', 'students', 'ratings'])
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $usage = $this->resolvePublishedCourseUsage($teacher);
        return view('teacher::teacher.course.course', compact('pageTitle', 'pageName', 'teacher', 'courses', 'usage'));
    }

    public function coursesTrash()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = __('teacher::teacher/course/list.trash_title');
        $pageName = __('teacher::teacher/course/list.trash_title');
        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->onlyTrashed()
            ->where('teacher_id', $teacher->id)
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('teacher::teacher.course.trash', compact('pageTitle', 'pageName', 'teacher', 'courses'));
    }

    public function createCourse()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $this->syncCourseLocks($teacher);
        $teacher->refresh();

        $pageTitle = __('teacher::teacher/course/add.create_title');
        $pageName = __('teacher::teacher/course/add.create_title');
        $categories = $this->getCourseCategories();
        $usage = $this->resolvePublishedCourseUsage($teacher);

        if (!($usage['can_create_draft'] ?? true)) {
            return redirect()
                ->route('teacher.dashboard.courses')
                ->with('msg_danger', __('packages::teacher.feature_locked'));
        }

        return view('teacher::teacher.course.create', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'categories' => $categories,
            'usage' => $usage,
            'course' => null,
            'selectedCategories' => [],
            'formAction' => route('teacher.dashboard.courses.store'),
            'submitLabel' => __('teacher::teacher/course/common.actions.create'),
        ]);
    }

    public function storeCourse(TeacherCourseRequest $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $usage = $this->resolvePublishedCourseUsage($teacher);
        if (!($usage['can_create_draft'] ?? true)) {
            return redirect()
                ->route('teacher.dashboard.courses')
                ->with('msg_danger', __('packages::teacher.feature_locked'));
        }

        $data = $request->validated();
        
        // Force draft if trying to publish but over limit
        if ((int) $data['status'] === 1 && !($usage['can_create'] ?? true)) {
            $data['status'] = 0;
            $limitMessage = __('teacher::teacher/course/common.flash.created_as_draft_due_limit', ['limit' => $usage['limit'] ?? 0]);
        }

        $course = Courses::query()->create($this->buildCoursePayload($data, $teacher));
        $this->syncCourseCategories($course, $data['categories'] ?? []);
        $this->logTeacherCourseActivity(
            $teacher,
            $course->fresh(),
            'course_created',
            __('teacher::teacher/course/common.history.course_created'),
            [
                'status' => (int) $course->status,
                'price' => (float) $course->price,
                'sale_price' => (float) $course->sale_price,
                'category_count' => count($data['categories'] ?? []),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', $limitMessage ?? __('teacher::teacher/course/common.flash.created'));
    }

    public function editCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $pageTitle = __('teacher::teacher/course/edit.edit_title');
        $pageName = __('teacher::teacher/course/edit.edit_title');

        return view('teacher::teacher.course.create', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'categories' => $this->getCourseCategories(),
            'usage' => $this->resolvePublishedCourseUsage($teacher),
            'course' => $course,
            'selectedCategories' => $course->categories()->pluck('categories.id')->all(),
            'formAction' => route('teacher.dashboard.courses.update', $course->id),
            'submitLabel' => __('teacher::teacher/course/common.actions.update'),
        ]);
    }

    public function updateCourse(TeacherCourseRequest $request, int $courseId)
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
        $before = $course->only(['name', 'price', 'sale_price', 'status', 'is_learning_locked']);

        $course->update($this->buildCoursePayload($data, $teacher, $course));
        $this->syncCourseCategories($course, $data['categories'] ?? []);
        $course->refresh();
        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_updated',
            __('teacher::teacher/course/common.history.course_updated'),
            [
                'before' => $before,
                'after' => $course->only(['name', 'price', 'sale_price', 'status', 'is_learning_locked']),
                'category_count' => count($data['categories'] ?? []),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __(
                ((int) $data['status'] === 1 && (int) $course->status !== 1)
                    ? 'teacher::teacher/course/common.flash.updated_as_draft_due_limit'
                    : 'teacher::teacher/course/common.flash.updated'
            ));
    }

    public function duplicateCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $usage = $this->resolvePublishedCourseUsage($teacher);
        if (!($usage['can_create_draft'] ?? true)) {
            return back()->with('msg_danger', __('packages::teacher.feature_locked'));
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $newCourse = $this->performCourseDuplicate($course);
        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_duplicated',
            __('teacher::teacher/course/common.history.course_duplicated'),
            [
                'duplicate_course_id' => $newCourse->id,
                'duplicate_course_name' => $newCourse->name_locale ?: $newCourse->name,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::teacher/course/common.flash.duplicated'));
    }

    public function updateCourseVisibility(Request $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $data = $request->validate([
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $targetStatus = (int) $data['status'];
        $previousStatus = (int) $course->status;
        if ($targetStatus === 1) {
            $this->activateCourseWithinLimit($teacher, $course);
        } else {
            $course->update([
                'status' => 0,
                'package_locked_at' => null,
                'package_lock_reason' => null,
            ]);
            $this->syncCourseLocks($teacher);
        }
        $course->refresh();
        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_visibility_updated',
            __('teacher::teacher/course/common.history.course_visibility_updated'),
            [
                'before' => $previousStatus,
                'after' => (int) $course->status,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::teacher/course/common.flash.visibility_updated'));
    }

    public function toggleCoursePriority(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_pin_courses')) {
            return $featureRedirect;
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $before = (int) ($course->is_package_priority ?? 0);
        $course->update([
            'is_package_priority' => !$before,
        ]);

        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_priority_toggled',
            __('teacher::teacher/course/common.history.course_priority_toggled'),
            [
                'before' => $before,
                'after' => (int) $course->is_package_priority,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::teacher/course/common.flash.priority_updated'));
    }

    public function deleteCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $course->delete();
        $this->syncCourseLocks($teacher);

        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_deleted',
            __('teacher::teacher/course/common.history.course_deleted')
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::teacher/course/common.flash.deleted'));
    }

    public function restoreCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        if (!$course->trashed()) {
            abort(404);
        }

        $course->restore();
        $this->syncCourseLocks($teacher);

        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_restored',
            __('teacher::teacher/course/common.history.course_restored')
        );

        return redirect()
            ->route('teacher.dashboard.courses.trash')
            ->with('msg_success', __('teacher::teacher/course/common.flash.restored'));
    }

    public function forceDeleteCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        if (!$course->trashed()) {
            abort(404);
        }

        $course->forceDelete();
        $this->syncCourseLocks($teacher);

        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_force_deleted',
            __('teacher::teacher/course/common.history.course_force_deleted')
        );

        return redirect()
            ->route('teacher.dashboard.courses.trash')
            ->with('msg_success', __('teacher::teacher/course/common.flash.force_deleted'));
    }
}
