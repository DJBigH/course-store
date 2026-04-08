<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Notifications\NewContactNotification;
use App\Notifications\TeacherCourseGiftInvitationNotification;
use App\Models\Scopes\ActiveScope;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Contacts\src\Models\Contacts;
use Modules\Categories\src\Models\Category;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseComment;
use Modules\Students\src\Models\Coupons;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Modules\Lessons\src\Models\Lesson;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Models\StudentLessonProgress;
use Modules\Students\src\Models\StudentsCourses;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Http\Requests\TeacherCourseRequest;
use Modules\Teacher\src\Http\Requests\TeacherLessonRequest;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Teacher\src\Models\TeacherCourseGrant;
use Modules\Teacher\src\Models\TeacherPayoutAccount;
use Modules\Teacher\src\Models\TeacherPayoutAccountChangeRequest;
use Modules\Teacher\src\Models\TeacherPayoutRequest;
use Modules\Teacher\src\Models\TeacherPackage;
use Modules\Teacher\src\Models\TeacherStudentNote;
use Modules\Teacher\src\Support\TeacherFinanceCalculator;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\User\src\Models\User;
use Modules\Video\src\Repositories\VideoRepositoryInterface;

class TeacherDashboardController extends Controller
{
    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected TeacherPackageLifecycleManager $packageLifecycleManager,
    ) {}

    public function index()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $coursesQuery = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id);

        $orderDetails = $this->paidOrderDetailsQuery($teacher)->get();
        $summary = TeacherFinanceCalculator::summarize(
            $orderDetails,
            fn () => (float) $teacher->commission_rate
        );
        $payoutRequested = $this->resolveCommittedPayoutAmount($teacher);

        $pageTitle = __('teacher::dashboard.pages.overview');
        $pageName = __('teacher::dashboard.pages.overview');
        $stats = [
            'courses' => (clone $coursesQuery)->count(),
            'active_courses' => (clone $coursesQuery)->where('status', 1)->count(),
            'students' => $orderDetails->pluck('order.student_id')->filter()->unique()->count(),
            'gross_revenue' => $summary['gross_amount'],
            'allocated_discount' => $summary['allocated_discount'],
            'estimated_revenue' => $summary['teacher_revenue'],
            'platform_revenue' => $summary['platform_revenue'],
            'available_balance' => max($summary['teacher_revenue'] - $payoutRequested, 0),
        ];
        $recentCourses = $coursesQuery->latest('id')->take(4)->get();
        $recentSales = TeacherFinanceCalculator::decorate($orderDetails->sortByDesc('created_at')->take(6)->values(), fn () => (float) $teacher->commission_rate);
        $currentPackage = $teacher->application?->package;
        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        $pendingUpgradeStartsAt = $pendingUpgrade?->activates_at;
        $pendingUpgradeIsQueued = $pendingUpgrade?->status === 'approved'
            && $pendingUpgradeStartsAt !== null
            && $pendingUpgrade?->activated_at === null;
        $nextPackage = $this->resolveNextPackage($currentPackage);
        $availablePackageChanges = $this->resolveAvailablePackageChanges($currentPackage);
        $packageSummary = $currentPackage ? [
            'name' => $currentPackage->name_locale ?: $currentPackage->name,
            'badge' => $currentPackage->badge_text_locale ?: strtoupper((string) $currentPackage->code),
            'price' => (float) $currentPackage->price,
            'billing_cycle' => $currentPackage->billing_cycle,
            'course_limit' => $currentPackage->effective_course_limit,
            'commission_rate' => (float) $currentPackage->commission_rate,
            'support' => $currentPackage->support_level_locale ?: '',
            'started_at' => $teacher->package_started_at,
            'expires_at' => $teacher->package_expires_at,
            'days_left' => $this->packageLifecycleManager->daysLeft($teacher),
            'can_upgrade' => $availablePackageChanges->isNotEmpty() && $pendingUpgrade === null,
            'has_higher_package' => $nextPackage !== null,
            'upgrade_name' => $nextPackage?->name_locale ?: $nextPackage?->name,
            'upgrade_url' => route('teacher.dashboard.package.upgrade'),
            'pending_upgrade' => $pendingUpgrade !== null,
            'pending_upgrade_status' => $pendingUpgrade?->display_status,
            'pending_upgrade_url' => $pendingUpgrade ? route('teacher.dashboard.package.upgrade.status') : null,
            'pending_upgrade_name' => $pendingUpgrade?->package?->name_locale ?: $pendingUpgrade?->package?->name,
            'pending_upgrade_starts_at' => $pendingUpgradeStartsAt,
            'pending_upgrade_days_until_activation' => $pendingUpgradeStartsAt
                ? max(now()->startOfDay()->diffInDays($pendingUpgradeStartsAt->copy()->startOfDay(), false), 0)
                : null,
            'pending_upgrade_is_queued' => $pendingUpgradeIsQueued,
        ] : null;

        return view('teacher::clients.dashboard.index', compact('pageTitle', 'pageName', 'teacher', 'stats', 'recentCourses', 'recentSales', 'packageSummary'));
    }

    public function upgradePackage()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        if ($pendingUpgrade) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $currentPackage = $teacher->application?->package;
        $upgradePackages = $this->resolveAvailablePackageChanges($currentPackage);
        if ($upgradePackages->isEmpty()) {
            return redirect()->route('teacher.dashboard.index')
                ->with('msg_danger', __('teacher::dashboard.package.flash.no_upgrade_available'));
        }

        $pageTitle = __('teacher::dashboard.package.upgrade_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.package_upgrade', compact('pageTitle', 'pageName', 'teacher', 'currentPackage', 'upgradePackages'));
    }

    public function storeUpgradePackage(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($this->resolveOpenPackageChangeRequest($teacher)) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $currentPackage = $teacher->application?->package;
        $upgradePackages = $this->resolveAvailablePackageChanges($currentPackage);
        $allowedPackageIds = $upgradePackages->pluck('id')->all();

        $data = $request->validate([
            'package_id' => ['required', 'integer'],
            'payment_method' => ['nullable', 'string', 'in:bank_transfer,vnpay,momo'],
        ]);

        if (!in_array((int) $data['package_id'], $allowedPackageIds, true)) {
            return back()->withErrors([
                'package_id' => __('teacher::dashboard.package.flash.invalid_target'),
            ])->withInput();
        }

        $targetPackage = $upgradePackages->firstWhere('id', (int) $data['package_id']);
        $paymentMethod = (float) $targetPackage->price > 0
            ? ($data['payment_method'] ?: 'bank_transfer')
            : null;

        $sourceApplication = $teacher->application;
        $student = auth('students')->user();

        $upgradeRequest = TeacherApplication::query()->create([
            'student_id' => $student?->id,
            'teacher_id' => $teacher->id,
            'applicant_type' => $student ? 'student' : 'guest',
            'package_id' => $targetPackage->id,
            'payment_method' => $paymentMethod,
            'coupon_code' => null,
            'discount_amount' => 0,
            'status' => (float) $targetPackage->price > 0 ? 'pending_payment' : 'approved',
            'full_name' => $sourceApplication?->full_name ?: $teacher->name,
            'display_name' => $sourceApplication?->display_name ?: $teacher->name,
            'headline' => $sourceApplication?->headline,
            'bio' => $sourceApplication?->bio ?: $teacher->description,
            'experience_years' => $sourceApplication?->experience_years ?: $teacher->exp,
            'specialties' => $sourceApplication?->specialties ?? [],
            'phone' => $sourceApplication?->phone ?: $student?->phone,
            'email' => $sourceApplication?->email ?: $student?->email,
            'locale' => $sourceApplication?->locale ?: session('locale', app()->getLocale()),
            'portfolio_url' => $sourceApplication?->portfolio_url,
            'facebook_url' => $sourceApplication?->facebook_url,
            'youtube_url' => $sourceApplication?->youtube_url,
            'linkedin_url' => $sourceApplication?->linkedin_url,
            'intro_video_url' => $sourceApplication?->intro_video_url,
            'cv_file' => $sourceApplication?->cv_file,
            'identity_file' => $sourceApplication?->identity_file,
            'submitted_at' => now(),
            'admin_note' => 'package_upgrade',
        ]);

        if ((float) $targetPackage->price <= 0) {
            $action = $this->finalizePackageChange($teacher, $upgradeRequest);

            return $this->redirectAfterPackageChange($action);
        }

        return redirect()->route('teacher.dashboard.package.upgrade.status')
            ->with('msg_success', __('teacher::dashboard.package.flash.created'));
    }

    public function upgradePackageStatus()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.package.upgrade');
        }

        $pageTitle = __('teacher::dashboard.package.status_title');
        $pageName = $pageTitle;
        $currentPackage = $teacher->application?->package;
        $overLimitWarnings = $this->resolvePackageOverLimitWarnings($teacher, $upgradeRequest);
        $featureLossWarnings = $this->resolvePackageFeatureLossWarnings($teacher, $upgradeRequest);

        return view('teacher::clients.dashboard.package_upgrade_status', compact('pageTitle', 'pageName', 'teacher', 'currentPackage', 'upgradeRequest', 'overLimitWarnings', 'featureLossWarnings'));
    }

    public function markUpgradePaid()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.package.upgrade');
        }

        if ($upgradeRequest->status !== 'pending_payment') {
            return back()->with('msg_danger', __('teacher::dashboard.package.flash.invalid_payment_status'));
        }

        if ($upgradeRequest->payment_method === 'bank_transfer') {
            $upgradeRequest->update([
                'status' => 'pending_review',
                'submitted_at' => now(),
            ]);

            return back()->with('msg_success', __('teacher::dashboard.package.flash.bank_transfer_waiting_confirmation'));
        }

        $action = $this->finalizePackageChange($teacher, $upgradeRequest);

        return $this->redirectAfterPackageChange($action);
    }

    public function cancelUpgradePackage()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.package.upgrade');
        }

        if (!in_array($upgradeRequest->status, ['pending_payment', 'pending_review'], true)) {
            return back()->with('msg_danger', 'Yêu cầu đổi gói này không còn có thể hủy.');
        }

        $upgradeRequest->update([
            'status' => 'cancelled',
            'activates_at' => null,
            'package_started_at' => null,
            'package_expires_at' => null,
            'activated_at' => null,
        ]);

        return redirect()
            ->route('teacher.dashboard.package.upgrade')
            ->with('msg_success', 'Đã hủy yêu cầu đổi gói.');
    }

    public function courses()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = __('teacher::dashboard.pages.courses');
        $pageName = __('teacher::dashboard.pages.courses');
        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->withCount(['lessons', 'students', 'ratings'])
            ->withAvg('ratings', 'rating')
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $courseLimit = $this->resolveCourseLimit($teacher);
        $courseCount = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->count();
        $usage = [
            'used' => $courseCount,
            'limit' => $courseLimit,
            'limit_label' => $courseLimit === null ? __('teacher::dashboard.courses.unlimited') : $courseLimit,
            'remaining' => $courseLimit === null ? null : max($courseLimit - $courseCount, 0),
            'can_create' => $courseLimit === null || $courseCount < $courseLimit,
        ];

        return view('teacher::clients.dashboard.courses', compact('pageTitle', 'pageName', 'teacher', 'courses', 'usage'));
    }

    public function coursesTrash()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = __('teacher::dashboard.courses.trash_title');
        $pageName = __('teacher::dashboard.courses.trash_title');
        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->onlyTrashed()
            ->withCount(['lessons', 'students'])
            ->where('teacher_id', $teacher->id)
            ->latest('deleted_at')
            ->paginate(12)
            ->withQueryString();

        return view('teacher::clients.dashboard.courses_trash', compact('pageTitle', 'pageName', 'teacher', 'courses'));
    }

    public function createCourse()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $limitCheck = $this->ensureCourseCreationAllowed($teacher);
        if ($limitCheck !== null) {
            return $limitCheck;
        }

        $pageTitle = __('teacher::dashboard.courses.create_title');
        $pageName = __('teacher::dashboard.courses.create_title');
        $categories = $this->getCourseCategories();

        $courseLimit = $this->resolveCourseLimit($teacher);
        $courseCount = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->count();
        $usage = [
            'used' => $courseCount,
            'limit' => $courseLimit,
            'limit_label' => $courseLimit === null ? __('teacher::dashboard.courses.unlimited') : $courseLimit,
        ];

        return view('teacher::clients.dashboard.create_course', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'categories' => $categories,
            'usage' => $usage,
            'course' => null,
            'selectedCategories' => [],
            'formAction' => route('teacher.dashboard.courses.store'),
            'submitLabel' => __('teacher::dashboard.courses.actions.create'),
        ]);
    }

    public function storeCourse(TeacherCourseRequest $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $limitCheck = $this->ensureCourseCreationAllowed($teacher);
        if ($limitCheck !== null) {
            return $limitCheck;
        }

        $data = $request->validated();
        $course = Courses::query()->create($this->buildCoursePayload($data, $teacher));
        $this->syncCourseCategories($course, $data['categories'] ?? []);

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::dashboard.courses.flash.created'));
    }

    public function editCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $pageTitle = __('teacher::dashboard.courses.edit_title');
        $pageName = __('teacher::dashboard.courses.edit_title');

        return view('teacher::clients.dashboard.create_course', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'categories' => $this->getCourseCategories(),
            'usage' => null,
            'course' => $course,
            'selectedCategories' => $course->categories()->pluck('categories.id')->all(),
            'formAction' => route('teacher.dashboard.courses.update', $course->id),
            'submitLabel' => __('teacher::dashboard.courses.actions.update'),
        ]);
    }

    public function updateCourse(TeacherCourseRequest $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $data = $request->validated();

        $course->update($this->buildCoursePayload($data, $teacher, $course));
        $this->syncCourseCategories($course, $data['categories'] ?? []);

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::dashboard.courses.flash.updated'));
    }

    public function duplicateCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_duplicate_courses', 'teacher.dashboard.courses')) {
            return $featureRedirect;
        }

        $limitCheck = $this->ensureCourseCreationAllowed($teacher);
        if ($limitCheck !== null) {
            return $limitCheck;
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $this->performCourseDuplicate($course);

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::dashboard.courses.flash.duplicated'));
    }

    public function deleteCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $course->delete();
        Lesson::query()->where('course_id', $course->id)->delete();

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::dashboard.courses.flash.deleted'));
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

        $limit = $this->resolveCourseLimit($teacher);
        $activeCourses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->count();
        if ($limit !== null && $activeCourses >= $limit) {
            return redirect()
                ->route('teacher.dashboard.courses.trash')
                ->with('msg_danger', __('teacher::dashboard.courses.flash.restore_limit_reached', ['limit' => $limit]));
        }

        $course->restore();
        Lesson::query()->onlyTrashed()->where('course_id', $course->id)->restore();

        return redirect()
            ->route('teacher.dashboard.courses.trash')
            ->with('msg_success', __('teacher::dashboard.courses.flash.restored'));
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

        Lesson::query()->withTrashed()->where('course_id', $course->id)->forceDelete();
        $course->categories()->detach();
        $course->forceDelete();

        return redirect()
            ->route('teacher.dashboard.courses.trash')
            ->with('msg_success', __('teacher::dashboard.courses.flash.force_deleted'));
    }

    public function lessons(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $pageTitle = __('teacher::dashboard.lessons.title', ['course' => $course->name_locale]);
        $pageName = __('teacher::dashboard.lessons.title_short');
        $modules = Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->with(['subLessons' => fn ($query) => $query->orderBy('position')])
            ->orderBy('position')
            ->get();

        return view('teacher::clients.dashboard.lessons', compact('pageTitle', 'pageName', 'teacher', 'course', 'modules'));
    }

    public function lessonsTrash(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        $pageTitle = __('teacher::dashboard.lessons.trash_title', ['course' => $course->name_locale]);
        $pageName = __('teacher::dashboard.lessons.trash_short');
        $lessons = Lesson::query()
            ->onlyTrashed()
            ->where('course_id', $course->id)
            ->orderByRaw('COALESCE(parent_id, 0)')
            ->orderBy('position')
            ->get();
        $trashedLessonRows = $this->flattenTrashedLessons($lessons);

        return view('teacher::clients.dashboard.lessons_trash', compact('pageTitle', 'pageName', 'teacher', 'course', 'trashedLessonRows'));
    }

    public function createLesson(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $pageTitle = __('teacher::dashboard.lessons.create_title', ['course' => $course->name_locale]);
        $pageName = __('teacher::dashboard.lessons.create_short');
        $defaultParentId = (int) request()->query('module', 0);
        $modules = Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();

        return view('teacher::clients.dashboard.lesson_form', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'course' => $course,
            'lesson' => null,
            'modules' => $modules,
            'formAction' => route('teacher.dashboard.lessons.store', $course->id),
            'submitLabel' => __('teacher::dashboard.lessons.actions.create'),
            'position' => $this->nextLessonPosition($course, $defaultParentId),
            'defaultParentId' => $defaultParentId,
        ]);
    }

    public function storeLesson(TeacherLessonRequest $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $data = $request->validated();

        Lesson::query()->create($this->buildLessonPayload($data, $course));
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.created'));
    }

    public function editLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $pageTitle = __('teacher::dashboard.lessons.edit_title', ['lesson' => $lesson->name_locale]);
        $pageName = __('teacher::dashboard.lessons.edit_short');
        $modules = Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->where('id', '!=', $lesson->id)
            ->orderBy('position')
            ->get();

        return view('teacher::clients.dashboard.lesson_form', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'course' => $course,
            'lesson' => $lesson,
            'modules' => $modules,
            'formAction' => route('teacher.dashboard.lessons.update', [$course->id, $lesson->id]),
            'submitLabel' => __('teacher::dashboard.lessons.actions.update'),
            'position' => $lesson->position,
            'defaultParentId' => $lesson->parent_id ?? 0,
        ]);
    }

    public function updateLesson(TeacherLessonRequest $request, int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $data = $request->validated();

        $lesson->update($this->buildLessonPayload($data, $course, $lesson));
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.updated'));
    }

    public function deleteLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $branchIds = $this->collectLessonBranchIds($lesson->id);
        Lesson::query()->whereIn('id', $branchIds)->delete();
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.deleted'));
    }

    public function restoreLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        $lesson = $this->resolveOwnedLesson($course, $lessonId, true);
        if (!$lesson->trashed()) {
            abort(404);
        }

        $branchIds = $this->collectLessonBranchIds($lesson->id);
        Lesson::query()->onlyTrashed()->whereIn('id', $branchIds)->restore();
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.trash', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.restored'));
    }

    public function forceDeleteLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        $lesson = $this->resolveOwnedLesson($course, $lessonId, true);
        if (!$lesson->trashed()) {
            abort(404);
        }

        $branchIds = $this->collectLessonBranchIds($lesson->id);
        Lesson::query()->onlyTrashed()->whereIn('id', $branchIds)->forceDelete();
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.trash', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.force_deleted'));
    }

    public function earnings()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = __('teacher::dashboard.pages.earnings');
        $pageName = __('teacher::dashboard.pages.earnings');
        $items = $this->paidOrderDetailsQuery($teacher)->paginate(12)->withQueryString();
        $summary = TeacherFinanceCalculator::summarize(
            $this->paidOrderDetailsQuery($teacher)->get(),
            fn () => (float) $teacher->commission_rate
        );
        $items->setCollection(TeacherFinanceCalculator::decorate(
            $items->getCollection(),
            fn () => (float) $teacher->commission_rate
        ));

        return view('teacher::clients.dashboard.earnings', compact('pageTitle', 'pageName', 'teacher', 'items', 'summary'));
    }

    public function payouts()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = __('teacher::dashboard.pages.payouts');
        $pageName = __('teacher::dashboard.pages.payouts');
        $summary = TeacherFinanceCalculator::summarize(
            $this->paidOrderDetailsQuery($teacher)->get(),
            fn () => (float) $teacher->commission_rate
        );
        $requestedAmount = $this->resolveCommittedPayoutAmount($teacher);
        $availableBalance = max($summary['teacher_revenue'] - $requestedAmount, 0);
        $payoutAccounts = $teacher->payoutAccounts()->orderBy('id')->get();
        $payoutAccountLimit = $this->resolvePayoutAccountLimit($teacher);
        $pendingAccountChangeRequests = $teacher->payoutAccountChangeRequests()
            ->with('replaceAccount')
            ->latest('id')
            ->take(10)
            ->get();
        $payouts = TeacherPayoutRequest::query()
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('teacher::clients.dashboard.payouts', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'payouts',
            'summary',
            'requestedAmount',
            'availableBalance',
            'payoutAccounts',
            'payoutAccountLimit',
            'pendingAccountChangeRequests'
        ));
    }

    public function support()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = 'Góp ý / Báo cáo';
        $pageName = $pageTitle;
        $items = Contacts::query()
            ->where('teacher_id', $teacher->id)
            ->where('source', 'teacher_portal')
            ->whereIn('submission_type', [Contacts::TYPE_FEEDBACK, Contacts::TYPE_REPORT])
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('teacher::clients.dashboard.support', compact('pageTitle', 'pageName', 'teacher', 'items'));
    }

    public function orders(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $directory = $this->buildTeacherOrderDirectory($teacher, $request);
        $groupedOrders = $directory['orders'];

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $orders = new LengthAwarePaginator(
            $groupedOrders->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $groupedOrders->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $pageTitle = __('teacher::dashboard.pages.orders');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.orders', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'orders',
            'directory'
        ));
    }

    public function exportOrders(Request $request, string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_export_orders')) {
            return $featureRedirect;
        }

        $directory = $this->buildTeacherOrderDirectory($teacher, $request);
        $orders = $directory['orders'];

        if ($format === 'excel') {
            $filename = 'teacher-orders-' . now()->format('Ymd-His') . '.xls';
            $html = view('teacher::clients.dashboard.exports.orders_excel', compact('orders'))->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $filename = 'teacher-orders-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                __('teacher::dashboard.orders.export.order'),
                __('teacher::dashboard.orders.export.student'),
                __('teacher::dashboard.orders.export.email'),
                __('teacher::dashboard.orders.export.phone'),
                __('teacher::dashboard.orders.export.payment_method'),
                __('teacher::dashboard.orders.export.paid_at'),
                __('teacher::dashboard.orders.export.course_count'),
                __('teacher::dashboard.orders.export.courses'),
                __('teacher::dashboard.orders.export.gross'),
                __('teacher::dashboard.orders.export.discount'),
                __('teacher::dashboard.orders.export.net'),
                __('teacher::dashboard.orders.export.revenue'),
            ]);

            foreach ($orders as $item) {
                fputcsv($handle, [
                    $item->order?->code ?: $item->order?->id,
                    $item->order?->customer_name_display ?: '',
                    $item->order?->customer_email_display ?: '',
                    $item->order?->customer_phone_display ?: '',
                    $item->order?->payment_method_label ?: '',
                    optional($item->payment_at)->format('Y-m-d H:i:s'),
                    $item->item_count,
                    $item->details->pluck(fn ($detail) => $detail->courses?->name_locale ?: $detail->courses?->name ?: '')->implode(' | '),
                    $item->gross_amount,
                    $item->allocated_discount,
                    $item->net_revenue,
                    $item->teacher_revenue,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function showOrder(int $orderId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $details = $this->paidOrderDetailsQuery($teacher)
            ->where('order_id', $orderId)
            ->get();

        if ($details->isEmpty()) {
            abort(404);
        }

        $details = TeacherFinanceCalculator::decorate(
            $details,
            fn() => (float) $teacher->commission_rate
        );

        $order = $details->first()?->order;
        if (!$order) {
            abort(404);
        }

        $summary = [
            'gross_amount' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
            'allocated_discount' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.allocated_discount', 0)),
            'net_revenue' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.net_revenue', 0)),
            'teacher_revenue' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
            'item_count' => $details->count(),
        ];

        $pageTitle = __('teacher::dashboard.orders.show.title', ['code' => $order->code ?: $order->id]);
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.order_show', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'order',
            'details',
            'summary'
        ));
    }

    public function students(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $directory = $this->buildTeacherStudentDirectory($teacher, $request);
        $search = $directory['search'];
        $summary = $directory['summary'];
        $courseOptions = $directory['courseOptions'];
        $selectedCourse = $directory['selectedCourse'];
        $tag = $directory['tag'];
        $sort = $directory['sort'];

        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $students = new LengthAwarePaginator(
            $directory['students']->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $directory['students']->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $pageTitle = 'Học viên của tôi';
        $pageName = $pageTitle;
        return view('teacher::clients.dashboard.students', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'students',
            'directory'
        ));
    }

    public function comments(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        $selectedCourseId = (int) $request->query('course_id', 0);
        $selectedCourse = $selectedCourseId > 0
            ? $courses->firstWhere('id', $selectedCourseId)
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
            return $this->redirectToStatus();
        }

        $comment = CourseComment::query()
            ->whereNull('parent_id')
            ->whereHas('course', function ($query) use ($teacher) {
                $query->withoutGlobalScope(ActiveScope::class)
                    ->where('teacher_id', $teacher->id);
            })
            ->findOrFail($commentId);

        $payload = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $content = $this->sanitizeCommentContent($payload['content']);

        if (mb_strlen($content) < 2) {
            return $this->commentErrorResponse($request, __('teacher::comments.flash.reply_too_short'), 422);
        }

        $moderation = courseCommentModeration($content);

        CourseComment::create([
            'course_id' => $comment->course_id,
            'parent_id' => $comment->id,
            'student_id' => $teacher->student_id ?? auth('students')->id(),
            'content' => $content,
            'is_visible' => true,
            'is_flagged' => $moderation['is_flagged'],
            'flagged_terms' => $moderation['is_flagged'] ? implode(', ', $moderation['matched_terms']) : null,
        ]);

        return $this->renderTeacherCommentThread($request, $comment->course_id, $teacher);
    }

    public function toggleCommentVisibility(Request $request, int $commentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_comments')) {
            return $featureRedirect;
        }

        $comment = CourseComment::query()
            ->whereHas('course', function ($query) use ($teacher) {
                $query->withoutGlobalScope(ActiveScope::class)
                    ->where('teacher_id', $teacher->id);
            })
            ->findOrFail($commentId);

        $comment->update([
            'is_visible' => !$comment->is_visible,
        ]);

        return $this->renderTeacherCommentThread($request, $comment->course_id, $teacher);
    }

    public function exportStudents(Request $request, string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_export_students')) {
            return $featureRedirect;
        }

        $directory = $this->buildTeacherStudentDirectory($teacher, $request);
        $students = $directory['students'];

        if ($format === 'excel') {
            $filename = 'teacher-students-' . now()->format('Ymd-His') . '.xls';
            $html = view('teacher::clients.dashboard.exports.students_excel', compact('students'))->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $filename = 'teacher-students-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student', 'Email', 'Phone', 'Address', 'Tag', 'Courses Bought', 'Orders Paid', 'Spent', 'Last Purchase', 'Recent Learning']);

            foreach ($students as $student) {
                fputcsv($handle, [
                    $student->name,
                    $student->email,
                    $student->phone,
                    $student->address,
                    $this->normalizeStudentTagLabel($student->teacher_tag),
                    $student->teacher_courses_all->pluck(fn ($course) => $course->name_locale ?: $course->name)->implode(' | '),
                    $student->teacher_order_count,
                    (float) $student->teacher_total_spent,
                    optional($student->teacher_last_purchase_at)->format('Y-m-d H:i:s'),
                    optional($student->teacher_last_learning_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function createStudentGrant(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_grant_courses', 'teacher.dashboard.students')) {
            return $featureRedirect;
        }

        $selectedStudent = Student::query()
            ->where('id', (int) $request->query('student_id', 0))
            ->first();
        $selectedEmail = old('student_email', $selectedStudent?->email ?? '');
        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();
        $recentGrants = $this->teacherCourseGrantsQuery($teacher, ['pending', 'accepted'])
            ->with(['student', 'course'])
            ->latest('id')
            ->take(8)
            ->get();

        $pageTitle = __('teacher::gifts.teacher.page_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.student_grant', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'courses',
            'selectedEmail',
            'recentGrants'
        ));
    }

    public function storeStudentGrant(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_grant_courses', 'teacher.dashboard.students')) {
            return $featureRedirect;
        }

        $data = $request->validate([
            'student_email' => ['required', 'email:rfc'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'reason' => ['required', 'in:gift,support,special_trial,compensation'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $email = Str::lower(trim((string) $data['student_email']));
        $student = Student::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$student || $student->deleted_at) {
            return back()
                ->withErrors(['student_email' => __('teacher::gifts.flash.student_not_found')])
                ->withInput();
        }

        $course = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->findOrFail((int) $data['course_id']);

        $alreadyHasAccess = StudentsCourses::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyHasAccess) {
            return back()->with('msg_danger', __('teacher::gifts.flash.already_has_access'));
        }

        $locale = $student->preferredLocale();
        $grant = TeacherCourseGrant::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
            ],
            [
                'reason' => $data['reason'],
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
                'status' => 'pending',
                'token' => (string) Str::uuid(),
                'locale' => $locale,
                'invited_at' => now(),
                'accepted_at' => null,
                'revoked_at' => null,
            ]
        );

        $student->notify(new TeacherCourseGiftInvitationNotification(
            $grant->fresh(['teacher', 'course']),
            $locale
        ));

        return redirect()
            ->route('teacher.dashboard.students.grants.create', ['student_id' => $student->id])
            ->with('msg_success', __('teacher::gifts.flash.invitation_sent'));
    }

    public function revokeStudentGrant(int $studentId, int $grantId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_grant_courses', 'teacher.dashboard.students')) {
            return $featureRedirect;
        }

        [$student, $details, $grants] = $this->resolveOwnedStudentContext($teacher, $studentId);
        $grant = $grants->firstWhere('id', $grantId);

        if (!$grant) {
            abort(404);
        }

        $grant->delete();

        $hasPaidAccess = $details->contains(function ($detail) use ($grant) {
            return (int) $detail->course_id === (int) $grant->course_id;
        });

        if (!$hasPaidAccess) {
            StudentsCourses::query()
                ->where('student_id', $student->id)
                ->where('course_id', $grant->course_id)
                ->delete();
        }

        return redirect()
            ->route('teacher.dashboard.students.show', $student->id)
            ->with('msg_success', 'Da thu hoi suat cap quyen hoc.');
    }

    public function showStudent(int $studentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        [$student, $details, $grants] = $this->resolveOwnedStudentContext($teacher, $studentId);
        $orders = $details->pluck('order')->filter()->unique('id')->sortByDesc('id')->values();
        $paidCourses = $details->pluck('courses')->filter()->unique('id')->values();
        $grantedCourses = $grants->pluck('course')->filter()->unique('id')->values();
        $courses = $paidCourses->concat($grantedCourses)->unique('id')->values();
        $note = TeacherStudentNote::query()->firstWhere([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);
        $courseIds = $courses->pluck('id')->map(fn ($id) => (int) $id)->all();
        $courseLessonTotals = collect();
        if (!empty($courseIds)) {
            $courseLessonTotals = \Modules\Lessons\src\Models\Lesson::query()
                ->whereIn('course_id', $courseIds)
                ->whereNotNull('parent_id')
                ->where('status', 1)
                ->selectRaw('course_id, COUNT(*) as total_lessons')
                ->groupBy('course_id')
                ->pluck('total_lessons', 'course_id');
        }
        $courseProgressMap = StudentLessonProgress::query()
            ->where('student_id', $student->id)
            ->whereIn('course_id', $courseIds ?: [0])
            ->selectRaw('course_id, COUNT(DISTINCT lesson_id) as completed_lessons, MAX(completed_at) as last_completed_at')
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');
        $learningTimeline = StudentLessonProgress::query()
            ->with(['course', 'lesson'])
            ->where('student_id', $student->id)
            ->whereIn('course_id', $courseIds ?: [0])
            ->latest('completed_at')
            ->take(20)
            ->get();

        $courses = $courses->map(function ($course) use ($courseLessonTotals, $courseProgressMap) {
            $totalLessons = (int) ($courseLessonTotals[$course->id] ?? 0);
            $completedLessons = (int) ($courseProgressMap->get($course->id)?->completed_lessons ?? 0);
            $progressPercent = $totalLessons > 0
                ? min((int) round(($completedLessons * 100) / $totalLessons), 100)
                : 0;

            $course->teacher_progress_total_lessons = $totalLessons;
            $course->teacher_progress_completed_lessons = min($completedLessons, $totalLessons);
            $course->teacher_progress_percent = $progressPercent;
            $course->teacher_progress_last_completed_at = $courseProgressMap->get($course->id)?->last_completed_at;

            return $course;
        });

        $pageTitle = 'Chi tiết học viên';
        $pageName = $pageTitle;
        $summary = [
            'orders' => $orders->count(),
            'courses' => $courses->count(),
            'grants' => $grants->count(),
            'spent' => (float) $details->sum(fn ($detail) => (float) ($detail->price ?? 0)),
            'last_purchase_at' => optional(
                $details->sortByDesc(function ($detail) {
                    return optional($detail->order?->payment_complete_date ?: $detail->order?->payment_date ?: $detail->created_at)->timestamp ?? 0;
                })->first()
            )->order?->payment_complete_date
                ?: optional(
                    $details->sortByDesc(function ($detail) {
                        return optional($detail->order?->payment_complete_date ?: $detail->order?->payment_date ?: $detail->created_at)->timestamp ?? 0;
                    })->first()
                )->order?->payment_date
                ?: optional($details->sortByDesc('created_at')->first())->created_at,
        ];

        return view('teacher::clients.dashboard.student_show', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'student',
            'orders',
            'courses',
            'details',
            'grants',
            'note',
            'summary',
            'learningTimeline'
        ));
    }

    public function saveStudentNote(Request $request, int $studentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        [$student] = $this->resolveOwnedStudentContext($teacher, $studentId);

        $data = $request->validate([
            'tag' => ['nullable', 'in:potential,support_needed,vip'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        TeacherStudentNote::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
            ],
            [
                'tag' => $data['tag'] ?? null,
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.students.show', $student->id)
            ->with('msg_success', 'Đã lưu ghi chú nội bộ cho học viên.');
    }

    public function storePayout(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'account_mode' => ['required', 'in:saved,new'],
            'payout_account_id' => ['nullable', 'integer'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:120'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
        ]);

        $summary = TeacherFinanceCalculator::summarize(
            $this->paidOrderDetailsQuery($teacher)->get(),
            fn () => (float) $teacher->commission_rate
        );
        $requestedAmount = $this->resolveCommittedPayoutAmount($teacher);
        $availableBalance = max($summary['teacher_revenue'] - $requestedAmount, 0);

        if ((float) $data['amount'] > $availableBalance) {
            return back()->with('msg_danger', __('teacher::dashboard.payouts.flash.amount_exceeds_balance'));
        }

        $payoutAccounts = $teacher->payoutAccounts()->orderBy('id')->get();
        $bankData = $this->resolvePayoutBankData($request, $teacher, $data, $payoutAccounts);
        if ($bankData instanceof \Illuminate\Http\RedirectResponse) {
            return $bankData;
        }

        TeacherPayoutRequest::query()->create([
            'teacher_id' => $teacher->id,
            'amount' => $data['amount'],
            'bank_name' => $bankData['bank_name'],
            'bank_account_name' => $bankData['bank_account_name'],
            'bank_account_number' => $bankData['bank_account_number'],
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
            'status' => 'requested',
        ]);

        return redirect()->route('teacher.dashboard.payouts')
            ->with('msg_success', __('teacher::dashboard.payouts.flash.request_sent'));
    }

    public function storeSupport(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $data = $request->validate([
            'submission_type' => ['required', 'in:' . Contacts::TYPE_FEEDBACK . ',' . Contacts::TYPE_REPORT],
            'category' => ['required', 'in:' . implode(',', array_filter(Contacts::categories(), fn ($item) => $item !== 'general_contact'))],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ]);

        $student = auth('students')->user();

        $contact = Contacts::query()->create([
            'name' => $student?->name ?: ($teacher->name_locale ?: $teacher->name),
            'phone' => $student?->phone,
            'email' => $student?->email,
            'subject' => $data['subject'],
            'submission_type' => $data['submission_type'],
            'category' => $data['category'],
            'message' => $data['message'],
            'status' => 0,
            'workflow_status' => Contacts::STATUS_NEW,
            'source' => 'teacher_portal',
            'page_url' => $data['page_url'] ?: route('teacher.dashboard.support'),
            'student_id' => $student?->id,
            'teacher_id' => $teacher->id,
        ]);

        $admins = User::query()->inGroup('super_admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewContactNotification($contact));
        }

        return redirect()
            ->route('teacher.dashboard.support')
            ->with('msg_success', 'Bạn đã gửi góp ý / báo cáo thành công cho admin.');
    }

    public function storePayoutAccountChangeRequest(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $accounts = $teacher->payoutAccounts()->orderBy('id')->get();
        $accountLimit = $this->resolvePayoutAccountLimit($teacher);
        if ($accounts->count() < $accountLimit) {
            return back()->with('msg_danger', __('teacher::dashboard.payouts.flash.change_request_not_required', [
                'limit' => $accountLimit,
            ]));
        }

        $data = $request->validate([
            'replace_payout_account_id' => ['required', 'integer'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:120'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
        ]);

        $replaceAccount = $accounts->firstWhere('id', (int) $data['replace_payout_account_id']);
        if (!$replaceAccount) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.invalid_saved_account'));
        }

        $bankData = $this->sanitizeBankData($data);
        if ($this->findMatchingPayoutAccount($accounts, $bankData)) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.account_already_saved'));
        }

        $duplicatePendingRequest = $teacher->payoutAccountChangeRequests()
            ->where('status', 'pending')
            ->get()
            ->first(fn (TeacherPayoutAccountChangeRequest $item) => $item->replace_payout_account_id === (int) $replaceAccount->id
                && $this->bankPayloadMatches($item, $bankData));

        if ($duplicatePendingRequest) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.change_request_exists'));
        }

        TeacherPayoutAccountChangeRequest::query()->create([
            'teacher_id' => $teacher->id,
            'replace_payout_account_id' => $replaceAccount->id,
            'replace_bank_name' => $replaceAccount->bank_name,
            'replace_bank_account_name' => $replaceAccount->bank_account_name,
            'replace_bank_account_number' => $replaceAccount->bank_account_number,
            'bank_name' => $bankData['bank_name'],
            'bank_account_name' => $bankData['bank_account_name'],
            'bank_account_number' => $bankData['bank_account_number'],
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.dashboard.payouts')
            ->with('msg_success', __('teacher::dashboard.payouts.flash.change_request_sent'));
    }

    private function resolveTeacher(): ?Teacher
    {
        $student = auth('students')->user();
        $teacher = Teacher::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$teacher) {
            return null;
        }

        return $this->packageLifecycleManager->sync($teacher);
    }

    private function redirectToStatus()
    {
        return redirect()->route('teacher.account.status', ['locale' => session('locale', app()->getLocale())])
            ->with('msg_danger', __('teacher::dashboard.payouts.flash.inactive_teacher'));
    }

    private function ensurePackageFeatureAllowed(Teacher $teacher, string $feature, string $fallbackRoute = 'teacher.dashboard.index')
    {
        if ($teacher->packageHasFeature($feature)) {
            return null;
        }

        return redirect()
            ->route($fallbackRoute)
            ->with('msg_danger', __('teacher::dashboard.package_features.feature_locked'));
    }

    private function resolveCommittedPayoutAmount(Teacher $teacher): float
    {
        return (float) TeacherPayoutRequest::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('status', ['requested', 'processing', 'paid'])
            ->sum('amount');
    }

    private function resolvePayoutBankData(Request $request, Teacher $teacher, array $data, Collection $payoutAccounts)
    {
        if (($data['account_mode'] ?? null) === 'saved') {
            $selectedAccount = $payoutAccounts->firstWhere('id', (int) ($data['payout_account_id'] ?? 0));
            if (!$selectedAccount) {
                return back()
                    ->withInput()
                    ->with('msg_danger', __('teacher::dashboard.payouts.flash.invalid_saved_account'));
            }

            return [
                'bank_name' => $selectedAccount->bank_name,
                'bank_account_name' => $selectedAccount->bank_account_name,
                'bank_account_number' => $selectedAccount->bank_account_number,
            ];
        }

        $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:120'],
            'bank_account_number' => ['required', 'string', 'max:50'],
        ]);

        $bankData = $this->sanitizeBankData($data);
        $matchedAccount = $this->findMatchingPayoutAccount($payoutAccounts, $bankData);
        if ($matchedAccount) {
            return [
                'bank_name' => $matchedAccount->bank_name,
                'bank_account_name' => $matchedAccount->bank_account_name,
                'bank_account_number' => $matchedAccount->bank_account_number,
            ];
        }

        $accountLimit = $this->resolvePayoutAccountLimit($teacher);
        if ($payoutAccounts->count() >= $accountLimit) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.limit_reached_use_change_request', [
                    'limit' => $accountLimit,
                ]));
        }

        TeacherPayoutAccount::query()->create(array_merge($bankData, [
            'teacher_id' => $teacher->id,
        ]));

        return $bankData;
    }

    private function sanitizeBankData(array $data): array
    {
        return [
            'bank_name' => trim((string) ($data['bank_name'] ?? '')),
            'bank_account_name' => trim((string) ($data['bank_account_name'] ?? '')),
            'bank_account_number' => preg_replace('/\s+/', '', trim((string) ($data['bank_account_number'] ?? ''))),
        ];
    }

    private function findMatchingPayoutAccount(Collection $accounts, array $bankData): ?TeacherPayoutAccount
    {
        return $accounts->first(fn (TeacherPayoutAccount $account) => $this->bankPayloadMatches($account, $bankData));
    }

    private function bankPayloadMatches($accountLike, array $bankData): bool
    {
        return $this->normalizeBankText($accountLike->bank_name) === $this->normalizeBankText($bankData['bank_name'])
            && $this->normalizeBankText($accountLike->bank_account_name) === $this->normalizeBankText($bankData['bank_account_name'])
            && $this->normalizeBankAccountNumber($accountLike->bank_account_number) === $this->normalizeBankAccountNumber($bankData['bank_account_number']);
    }

    private function normalizeBankText(?string $value): string
    {
        return Str::upper(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    private function normalizeBankAccountNumber(?string $value): string
    {
        return preg_replace('/\s+/', '', trim((string) $value));
    }

    private function resolveCourseLimit(Teacher $teacher): ?int
    {
        return $teacher->application?->package?->effective_course_limit;
    }

    private function resolvePayoutAccountLimit(Teacher $teacher): int
    {
        return $teacher->application?->package?->effective_payout_account_limit ?? 3;
    }

    private function resolveNextPackage(?TeacherPackage $currentPackage): ?TeacherPackage
    {
        if (!$currentPackage) {
            return null;
        }

        return TeacherPackage::query()
            ->selectable()
            ->where('sort_order', '>', (int) $currentPackage->sort_order)
            ->orderBy('sort_order')
            ->first();
    }

    private function resolveAvailablePackageChanges(?TeacherPackage $currentPackage)
    {
        if (!$currentPackage) {
            return collect();
        }

        $query = TeacherPackage::query()
            ->selectable()
            ->orderBy('sort_order');

        if (!$this->packageLifecycleManager->isRecurring($currentPackage)) {
            $query->where('id', '!=', (int) $currentPackage->id);
        }

        return $query->get();
    }

    private function resolveOpenPackageChangeRequest(Teacher $teacher): ?TeacherApplication
    {
        return TeacherApplication::query()
            ->with(['package'])
            ->where('teacher_id', $teacher->id)
            ->where('id', '!=', (int) $teacher->application_id)
            ->where(function ($query) {
                $query->whereIn('status', ['pending_payment', 'pending_review'])
                    ->orWhere(function ($approvedQuery) {
                        $approvedQuery->where('status', 'approved')
                            ->whereNotNull('activates_at')
                            ->whereNull('activated_at');
                    });
            })
            ->latest('id')
            ->first();
    }

    private function finalizePackageChange(Teacher $teacher, TeacherApplication $upgradeRequest): string
    {
        $upgradeRequest->forceFill([
            'status' => 'approved',
            'submitted_at' => $upgradeRequest->submitted_at ?: now(),
            'reviewed_at' => now(),
        ])->save();

        $action = $this->packageLifecycleManager->applyApprovedChange($teacher, $upgradeRequest->fresh(['package']));

        $teacher->refresh();

        return $action;
    }

    private function redirectAfterPackageChange(string $action)
    {
        $flashKey = match ($action) {
            'extended' => 'teacher::dashboard.package.flash.auto_extended',
            'queued' => 'teacher::dashboard.package.flash.auto_queued',
            default => 'teacher::dashboard.package.flash.auto_activated',
        };

        $route = $action === 'queued'
            ? route('teacher.dashboard.package.upgrade.status')
            : route('teacher.dashboard.index');

        return redirect($route)->with('msg_success', __($flashKey));
    }

    private function resolvePackageOverLimitWarnings(Teacher $teacher, TeacherApplication $upgradeRequest): array
    {
        $currentPackage = $teacher->application?->package;
        $targetPackage = $upgradeRequest->package;

        if (
            !$currentPackage ||
            !$targetPackage ||
            (int) $targetPackage->sort_order >= (int) $currentPackage->sort_order
        ) {
            return [];
        }

        $warnings = [];

        $courseCount = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->count();
        $targetCourseLimit = $targetPackage->effective_course_limit;
        if ($targetCourseLimit !== null && $courseCount > $targetCourseLimit) {
            $warnings[] = __('teacher::dashboard.package.over_limit.course_limit', [
                'used' => $courseCount,
                'limit' => $targetCourseLimit,
            ]);
        }

        $couponCount = Coupons::query()
            ->where('teacher_id', $teacher->id)
            ->count();
        $targetCouponLimit = $targetPackage->hasFeature('can_manage_coupons')
            ? $targetPackage->effective_coupon_limit
            : 0;
        if ($targetCouponLimit !== null && $couponCount > $targetCouponLimit) {
            $warnings[] = __('teacher::dashboard.package.over_limit.coupon_limit', [
                'used' => $couponCount,
                'limit' => $targetCouponLimit,
            ]);
        }

        $payoutAccountCount = TeacherPayoutAccount::query()
            ->where('teacher_id', $teacher->id)
            ->count();
        $targetPayoutLimit = $targetPackage->effective_payout_account_limit;
        if ($payoutAccountCount > $targetPayoutLimit) {
            $warnings[] = __('teacher::dashboard.package.over_limit.payout_account_limit', [
                'used' => $payoutAccountCount,
                'limit' => $targetPayoutLimit,
            ]);
        }

        return $warnings;
    }

    private function resolvePackageFeatureLossWarnings(Teacher $teacher, TeacherApplication $upgradeRequest): array
    {
        $currentPackage = $teacher->application?->package;
        $targetPackage = $upgradeRequest->package;

        if (
            !$currentPackage ||
            !$targetPackage ||
            (int) $targetPackage->sort_order >= (int) $currentPackage->sort_order
        ) {
            return [];
        }

        $featureKeys = [
            'can_duplicate_courses',
            'can_manage_comments',
            'can_manage_coupons',
            'can_grant_courses',
            'can_export_orders',
            'can_export_students',
        ];

        $warnings = [];
        foreach ($featureKeys as $featureKey) {
            if ($currentPackage->hasFeature($featureKey) && !$targetPackage->hasFeature($featureKey)) {
                $warnings[] = __('teacher::dashboard.package_features.labels.' . $featureKey);
            }
        }

        return $warnings;
    }

    private function resolveOwnedCourse(Teacher $teacher, int $courseId, bool $withTrashed = false): Courses
    {
        $query = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->where('id', $courseId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->firstOrFail();
    }

    private function resolveOwnedLesson(Courses $course, int $lessonId, bool $withTrashed = false): Lesson
    {
        $query = Lesson::query()
            ->where('course_id', $course->id)
            ->where('id', $lessonId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->firstOrFail();
    }

    private function duplicateTitle(?string $value, string $suffix): ?string
    {
        if (!$value) {
            return $value;
        }

        return trim($value . ' (' . $suffix . ')');
    }

    private function duplicateSlug(?string $value, string $suffix = 'copy'): ?string
    {
        if (!$value) {
            return $value;
        }

        return trim($value . '-' . $suffix . '-' . Str::lower(Str::random(4)), '-');
    }

    private function duplicateCode(?string $value): string
    {
        $base = $value ?: 'COURSE';

        return $base . '-COPY-' . strtoupper(Str::random(4));
    }

    private function performCourseDuplicate(Courses $course): Courses
    {
        return DB::transaction(function () use ($course) {
            $courseData = $course->getAttributes();

            unset($courseData['id'], $courseData['created_at'], $courseData['updated_at'], $courseData['deleted_at']);
            $courseData['name'] = $this->duplicateTitle($course->name, 'Bản sao');
            $courseData['name_ko'] = $this->duplicateTitle($course->name_ko, '복제본');
            $courseData['name_ja'] = $this->duplicateTitle($course->name_ja, '複製版');
            $courseData['name_zh'] = $this->duplicateTitle($course->name_zh, '复制版');
            $courseData['slug'] = $this->duplicateSlug($course->slug, 'copy');
            $courseData['slug_en'] = $this->duplicateSlug($course->slug_en, 'copy');
            $courseData['slug_ko'] = $this->duplicateSlug($course->slug_ko, 'copy');
            $courseData['slug_ja'] = $this->duplicateSlug($course->slug_ja, 'copy');
            $courseData['slug_zh'] = $this->duplicateSlug($course->slug_zh, 'copy');
            $courseData['code'] = $this->duplicateCode($course->code);
            $courseData['status'] = 0;
            $courseData['is_learning_locked'] = 0;
            $courseData['view'] = 0;

            $newCourse = $this->courseRepository->create($courseData);

            $categoryIds = $this->courseRepository->getRelatedCategories($course);
            if (!empty($categoryIds)) {
                $newCourse->categories()->attach($this->categoriesPivotPayload($categoryIds));
            }

            $lessonMap = [];
            $lessons = Lesson::query()
                ->where('course_id', $course->id)
                ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
                ->orderBy('parent_id')
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            foreach ($lessons as $lesson) {
                $lessonData = $lesson->getAttributes();

                unset($lessonData['id'], $lessonData['created_at'], $lessonData['updated_at']);

                $oldParentId = $lessonData['parent_id'] ?? null;
                $lessonData['course_id'] = $newCourse->id;
                $lessonData['name'] = $this->duplicateTitle($lesson->name, 'Bản sao');
                $lessonData['name_ko'] = $this->duplicateTitle($lesson->name_ko, '복제본');
                $lessonData['name_ja'] = $this->duplicateTitle($lesson->name_ja, '複製版');
                $lessonData['name_zh'] = $this->duplicateTitle($lesson->name_zh, '复制版');
                $lessonData['slug'] = $this->duplicateSlug($lesson->slug, 'copy');
                $lessonData['slug_en'] = $this->duplicateSlug($lesson->slug_en, 'copy');
                $lessonData['slug_ko'] = $this->duplicateSlug($lesson->slug_ko, 'copy');
                $lessonData['slug_ja'] = $this->duplicateSlug($lesson->slug_ja, 'copy');
                $lessonData['slug_zh'] = $this->duplicateSlug($lesson->slug_zh, 'copy');
                $lessonData['status'] = 0;
                $lessonData['view'] = 0;
                $lessonData['parent_id'] = null;

                $newLesson = Lesson::query()->create($lessonData);
                $lessonMap[$lesson->id] = [
                    'id' => $newLesson->id,
                    'parent_id' => $oldParentId,
                ];
            }

            foreach ($lessonMap as $map) {
                if (!empty($map['parent_id']) && isset($lessonMap[$map['parent_id']])) {
                    Lesson::query()
                        ->whereKey($map['id'])
                        ->update(['parent_id' => $lessonMap[$map['parent_id']]['id']]);
                }
            }

            return $newCourse;
        });
    }

    private function ensureCourseCreationAllowed(Teacher $teacher)
    {
        $limit = $this->resolveCourseLimit($teacher);
        if ($limit === null) {
            return null;
        }

        $currentCount = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->count();

        if ($currentCount < $limit) {
            return null;
        }

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_danger', __('teacher::dashboard.courses.flash.limit_reached', ['limit' => $limit]));
    }

    private function getCourseCategories()
    {
        return Category::query()
            ->where(function ($query) {
                $query->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->with([
                'children' => fn ($query) => $query
                    ->orderBy('id')
                    ->with([
                        'children' => fn ($childQuery) => $childQuery
                            ->orderBy('id')
                            ->with([
                                'children' => fn ($grandChildQuery) => $grandChildQuery->orderBy('id'),
                            ]),
                    ]),
            ])
            ->orderBy('id')
            ->get();
    }

    private function buildCoursePayload(array $data, Teacher $teacher, ?Courses $course = null): array
    {
        return [
            'teacher_id' => $teacher->id,
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'name_ko' => $data['name_ko'] ?? null,
            'name_ja' => $data['name_ja'] ?? null,
            'name_zh' => $data['name_zh'] ?? null,
            'slug' => $this->generateCourseSlug($data['name'], 'slug', $course?->id),
            'slug_en' => $this->generateOptionalCourseSlug($data['name_en'] ?? null, 'slug_en', $course?->id),
            'slug_ko' => $this->generateOptionalCourseSlug($data['name_ko'] ?? null, 'slug_ko', $course?->id),
            'slug_ja' => $this->generateOptionalCourseSlug($data['name_ja'] ?? null, 'slug_ja', $course?->id),
            'slug_zh' => $this->generateOptionalCourseSlug($data['name_zh'] ?? null, 'slug_zh', $course?->id),
            'detail' => $data['detail'],
            'detail_en' => $data['detail_en'] ?? null,
            'detail_ko' => $data['detail_ko'] ?? null,
            'detail_ja' => $data['detail_ja'] ?? null,
            'detail_zh' => $data['detail_zh'] ?? null,
            'supports' => $data['supports'],
            'supports_en' => $data['supports_en'] ?? null,
            'supports_ko' => $data['supports_ko'] ?? null,
            'supports_ja' => $data['supports_ja'] ?? null,
            'supports_zh' => $data['supports_zh'] ?? null,
            'thumbnail' => $data['thumbnail'],
            'price' => (float) ($data['price'] ?? 0),
            'sale_price' => (float) ($data['sale_price'] ?? 0),
            'code' => $this->generateCourseCode($data['code'] ?? null, $course?->id),
            'is_document' => (int) $data['is_document'],
            'status' => (int) $data['status'],
            'is_learning_locked' => (int) $data['is_learning_locked'],
        ];
    }

    private function syncCourseCategories(Courses $course, array $categories): void
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $normalized = collect($categories)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $course->categories()->sync(
            $normalized->mapWithKeys(fn ($categoryId) => [
                $categoryId => ['created_at' => $timestamp, 'updated_at' => $timestamp],
            ])->all()
        );
    }

    private function categoriesPivotPayload(array $categories): array
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');

        return collect($categories)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($categoryId) => [
                $categoryId => ['created_at' => $timestamp, 'updated_at' => $timestamp],
            ])->all();
    }

    private function buildLessonPayload(array $data, Courses $course, ?Lesson $lesson = null): array
    {
        $parentId = $this->normalizeLessonParentId($course, (int) ($data['parent_id'] ?? 0), $lesson?->id);
        $videoUrl = trim((string) ($data['video'] ?? ''));
        $documentUrl = trim((string) ($data['document'] ?? ''));
        $videoId = $lesson?->video_id;
        $documentId = $lesson?->document_id;
        $durations = $lesson?->getRawOriginal('durations') ?? 0;

        if ($documentUrl !== '') {
            $documentInfo = getFileInfo($documentUrl);
            $document = $this->documentRepository->createDocument([
                'name' => $documentInfo['name'] ?? $data['name'],
                'url' => $documentUrl,
                'size' => $documentInfo['size'] ?? 0,
            ], $documentUrl);
            $documentId = $document?->id;
        } elseif (($data['remove_document'] ?? 0) == 1) {
            $documentId = null;
        }

        if ($videoUrl !== '') {
            $host = strtolower((string) parse_url($videoUrl, PHP_URL_HOST));
            $isExternal = $host && (
                str_contains($host, 'youtube.com') ||
                str_contains($host, 'youtu.be') ||
                str_contains($host, 'vimeo.com')
            );

            if ($isExternal) {
                $video = $this->videoRepository->createVideo([
                    'url' => $videoUrl,
                    'name' => $data['name'],
                    'size' => 0,
                ], $videoUrl);
                $videoId = $video?->id;
                $durations = externalVideoDuration($videoUrl);
            } else {
                $videoInfo = getVideoInfo($videoUrl);
                $video = $this->videoRepository->createVideo([
                    'url' => $videoUrl,
                    'name' => $videoInfo['filename'] ?? $data['name'],
                    'size' => $videoInfo['playtime_seconds'] ?? 0,
                ], $videoUrl);
                $videoId = $video?->id;
                $durations = $videoInfo['playtime_seconds'] ?? 0;
            }
        } elseif (($data['remove_video'] ?? 0) == 1) {
            $videoId = null;
            $durations = 0;
        }

        return [
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'name_ko' => $data['name_ko'] ?? null,
            'name_ja' => $data['name_ja'] ?? null,
            'name_zh' => $data['name_zh'] ?? null,
            'slug' => $this->generateLessonSlug($data['name'], 'slug', $lesson?->id),
            'slug_en' => $this->generateOptionalLessonSlug($data['name_en'] ?? null, 'slug_en', $lesson?->id),
            'slug_ko' => $this->generateOptionalLessonSlug($data['name_ko'] ?? null, 'slug_ko', $lesson?->id),
            'slug_ja' => $this->generateOptionalLessonSlug($data['name_ja'] ?? null, 'slug_ja', $lesson?->id),
            'slug_zh' => $this->generateOptionalLessonSlug($data['name_zh'] ?? null, 'slug_zh', $lesson?->id),
            'video_id' => $videoId,
            'course_id' => $course->id,
            'document_id' => $documentId,
            'parent_id' => $parentId > 0 ? $parentId : null,
            'is_trial' => (int) ($data['is_trial'] ?? 0),
            'position' => (int) ($data['position'] ?? $this->nextLessonPosition($course, $parentId)),
            'durations' => $durations,
            'description' => $data['description'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'description_ko' => $data['description_ko'] ?? null,
            'description_ja' => $data['description_ja'] ?? null,
            'description_zh' => $data['description_zh'] ?? null,
            'status' => (int) ($data['status'] ?? 0),
        ];
    }

    private function nextLessonPosition(Courses $course, int $parentId = 0): int
    {
        return (int) Lesson::query()
            ->where('course_id', $course->id)
            ->where('parent_id', $parentId > 0 ? $parentId : null)
            ->max('position') + 1;
    }

    private function normalizeLessonParentId(Courses $course, int $parentId, ?int $lessonId = null): int
    {
        if ($parentId <= 0) {
            return 0;
        }

        $query = Lesson::query()
            ->where('course_id', $course->id)
            ->where('id', $parentId)
            ->whereNull('parent_id');

        if ($lessonId) {
            $query->where('id', '!=', $lessonId);
        }

        return $query->exists() ? $parentId : 0;
    }

    private function generateCourseSlug(string $value, string $column, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        if ($baseSlug === '') {
            $baseSlug = 'course-' . Str::lower(Str::random(6));
        }

        $slug = $baseSlug;
        $counter = 2;
        while (Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where($column, $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateOptionalCourseSlug(?string $value, string $column, ?int $ignoreId = null): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return $this->generateCourseSlug($value, $column, $ignoreId);
    }

    private function generateCourseCode(?string $value, ?int $ignoreId = null): string
    {
        $base = trim((string) $value);
        if (
            $base !== '' &&
            !Courses::query()
                ->withoutGlobalScope(ActiveScope::class)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('code', $base)
                ->exists()
        ) {
            return $base;
        }

        do {
            $candidate = 'KH' . random_int(100000, 999999);
        } while (Courses::query()->withoutGlobalScope(ActiveScope::class)->where('code', $candidate)->exists());

        return $candidate;
    }

    private function generateLessonSlug(string $value, string $column, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        if ($baseSlug === '') {
            $baseSlug = 'lesson-' . Str::lower(Str::random(6));
        }

        $slug = $baseSlug;
        $counter = 2;
        while (Lesson::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where($column, $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateOptionalLessonSlug(?string $value, string $column, ?int $ignoreId = null): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return $this->generateLessonSlug($value, $column, $ignoreId);
    }

    private function buildTeacherStudentDirectory(Teacher $teacher, Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $courseId = (int) $request->query('course_id', 0);
        $tag = trim((string) $request->query('tag', ''));
        $accessType = trim((string) $request->query('access_type', 'all'));
        $sort = trim((string) $request->query('sort', 'recent_purchase'));
        $allowedAccessTypes = ['all', 'paid', 'granted', 'both'];
        $allowedSorts = ['recent_purchase', 'highest_spent', 'recent_learning'];
        if (!in_array($accessType, $allowedAccessTypes, true)) {
            $accessType = 'all';
        }
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'recent_purchase';
        }

        $baseOrderDetails = $this->paidOrderDetailsQuery($teacher)->get();
        $baseGrants = $this->teacherCourseGrantsQuery($teacher)->with(['course', 'student'])->get();
        $courseOptions = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        $validCourseIds = $courseOptions->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selectedCourse = in_array($courseId, $validCourseIds, true) ? $courseId : 0;
        $orderDetails = $selectedCourse > 0
            ? $baseOrderDetails->where('course_id', $selectedCourse)->values()
            : $baseOrderDetails;
        $grants = $selectedCourse > 0
            ? $baseGrants->where('course_id', $selectedCourse)->values()
            : $baseGrants;

        $studentIds = $orderDetails->pluck('order.student_id')
            ->concat($grants->pluck('student_id'))
            ->filter()
            ->unique()
            ->values();
        $notesMap = TeacherStudentNote::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        $students = Student::query()
            ->withTrashed()
            ->whereIn('id', $studentIds)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->get()
            ->values();

        if (in_array($tag, ['potential', 'support_needed', 'vip'], true)) {
            $students = $students
                ->filter(fn (Student $student) => $notesMap->get((int) $student->id)?->tag === $tag)
                ->values();
        }

        $filteredStudentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();
        $detailsByStudent = $orderDetails->groupBy(fn ($detail) => (int) ($detail->order->student_id ?? 0));
        $grantsByStudent = $grants->groupBy(fn ($grant) => (int) $grant->student_id);
        $teacherCourseIds = $orderDetails->pluck('course_id')
            ->concat($grants->pluck('course_id'))
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->all();
        $recentLearningMap = StudentLessonProgress::query()
            ->when(!empty($filteredStudentIds), fn ($query) => $query->whereIn('student_id', $filteredStudentIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->when(!empty($teacherCourseIds), fn ($query) => $query->whereIn('course_id', $teacherCourseIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->selectRaw('student_id, MAX(completed_at) as last_learning_at')
            ->groupBy('student_id')
            ->pluck('last_learning_at', 'student_id');

        $students = $students->map(function (Student $student) use ($detailsByStudent, $grantsByStudent, $notesMap, $recentLearningMap) {
            $details = $detailsByStudent->get((int) $student->id, collect());
            $grantItems = $grantsByStudent->get((int) $student->id, collect());
            $courses = $details->pluck('courses')
                ->concat($grantItems->pluck('course'))
                ->filter()
                ->unique('id')
                ->values();
            $lastPurchase = $details->sortByDesc(function ($detail) {
                return optional($detail->order?->payment_complete_date ?: $detail->order?->payment_date ?: $detail->created_at)->timestamp ?? 0;
            })->first();
            $noteItem = $notesMap->get((int) $student->id);
            $lastLearningAt = $recentLearningMap->get((int) $student->id);

            $student->teacher_course_count = $courses->count();
            $student->teacher_order_count = $details->pluck('order_id')->filter()->unique()->count();
            $student->teacher_grant_count = $grantItems->count();
            $student->teacher_total_spent = (float) $details->sum(fn ($detail) => (float) ($detail->price ?? 0));
            $student->teacher_last_purchase_at = $lastPurchase?->order?->payment_complete_date
                ?: $lastPurchase?->order?->payment_date
                ?: $lastPurchase?->created_at;
            $student->teacher_courses_all = $courses;
            $student->teacher_courses_preview = $courses->take(4);
            $student->teacher_courses_remaining = max($courses->count() - $student->teacher_courses_preview->count(), 0);
            $student->teacher_note_preview = $noteItem?->note;
            $student->teacher_tag = $noteItem?->tag;
            $student->teacher_last_learning_at = $lastLearningAt ? Carbon::parse($lastLearningAt) : null;

            return $student;
        });

        $students = $students->filter(function (Student $student) use ($accessType) {
            $hasPaid = (int) $student->teacher_order_count > 0;
            $hasGranted = (int) $student->teacher_grant_count > 0;

            return match ($accessType) {
                'paid' => $hasPaid,
                'granted' => $hasGranted,
                'both' => $hasPaid && $hasGranted,
                default => true,
            };
        })->values();

        $filteredStudentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();

        $students = match ($sort) {
            'highest_spent' => $students->sortByDesc(fn (Student $student) => sprintf('%015.2f-%015d-%010d', $student->teacher_total_spent, optional($student->teacher_last_purchase_at)->timestamp ?? 0, (int) $student->id)),
            'recent_learning' => $students->sortByDesc(fn (Student $student) => sprintf('%015d-%015d-%010d', optional($student->teacher_last_learning_at)->timestamp ?? 0, optional($student->teacher_last_purchase_at)->timestamp ?? 0, (int) $student->id)),
            default => $students->sortByDesc(fn (Student $student) => sprintf('%015d-%015d-%010d', optional($student->teacher_last_purchase_at)->timestamp ?? 0, optional($student->teacher_last_learning_at)->timestamp ?? 0, (int) $student->id)),
        };
        $students = $students->values();

        $filteredDetails = $orderDetails->filter(fn ($detail) => in_array((int) ($detail->order->student_id ?? 0), $filteredStudentIds, true));
        $filteredGrants = $grants->filter(fn ($grant) => in_array((int) $grant->student_id, $filteredStudentIds, true));

        return [
            'students' => $students,
            'search' => $search,
            'courseOptions' => $courseOptions,
            'selectedCourse' => $selectedCourse,
            'tag' => $tag,
            'access_type' => $accessType,
            'sort' => $sort,
            'summary' => [
                'total_students' => $students->count(),
                'total_orders' => $filteredDetails->pluck('order_id')->filter()->unique()->count(),
                'total_courses' => $filteredDetails->pluck('course_id')->concat($filteredGrants->pluck('course_id'))->filter()->unique()->count(),
            ],
        ];
    }

    private function paidOrderDetailsQuery(Teacher $teacher)
    {
        return OrderDetail::query()
            ->with(['courses', 'order.status', 'order.students'])
            ->whereHas('courses', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->whereHas('order', function ($query) {
                $query->where('status_id', 2);
            })
            ->latest('id');
    }

    private function buildTeacherOrderDirectory(Teacher $teacher, Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $courseId = (int) $request->query('course_id', 0);
        $paymentMethod = trim((string) $request->query('payment_method', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $courseOptions = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('id')
            ->get(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh']);

        $details = $this->paidOrderDetailsQuery($teacher)
            ->when($courseId > 0, fn($query) => $query->where('course_id', $courseId))
            ->when($paymentMethod !== '', fn($query) => $query->whereHas('order', fn($orderQuery) => $orderQuery->where('payment_method', $paymentMethod)))
            ->when($dateFrom !== '', fn($query) => $query->whereHas('order', fn($orderQuery) => $orderQuery->whereDate('payment_complete_date', '>=', $dateFrom)))
            ->when($dateTo !== '', fn($query) => $query->whereHas('order', fn($orderQuery) => $orderQuery->whereDate('payment_complete_date', '<=', $dateTo)))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('order', function ($orderQuery) use ($search) {
                    $orderQuery->where(function ($nestedQuery) use ($search) {
                        $nestedQuery->where('code', 'like', '%' . $search . '%')
                            ->orWhere('customer_name_snapshot', 'like', '%' . $search . '%')
                            ->orWhere('customer_email_snapshot', 'like', '%' . $search . '%')
                            ->orWhere('customer_phone_snapshot', 'like', '%' . $search . '%')
                            ->orWhereHas('students', function ($studentQuery) use ($search) {
                                $studentQuery->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%')
                                    ->orWhere('phone', 'like', '%' . $search . '%');
                            });
                    });
                });
            })
            ->get();

        $decoratedDetails = TeacherFinanceCalculator::decorate(
            $details,
            fn() => (float) $teacher->commission_rate
        );

        $orders = $decoratedDetails
            ->groupBy('order_id')
            ->map(function ($orderDetails) {
                $firstDetail = $orderDetails->first();
                $order = $firstDetail?->order;

                return (object) [
                    'order' => $order,
                    'details' => $orderDetails->values(),
                    'item_count' => $orderDetails->count(),
                    'gross_amount' => (float) $orderDetails->sum(fn($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
                    'allocated_discount' => (float) $orderDetails->sum(fn($item) => data_get($item, 'finance_breakdown.allocated_discount', 0)),
                    'net_revenue' => (float) $orderDetails->sum(fn($item) => data_get($item, 'finance_breakdown.net_revenue', 0)),
                    'teacher_revenue' => (float) $orderDetails->sum(fn($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
                    'payment_at' => $order?->payment_complete_date ?: $order?->payment_date ?: $order?->created_at,
                ];
            })
            ->sortByDesc(fn($item) => optional($item->payment_at)->timestamp ?? 0)
            ->values();

        return [
            'search' => $search,
            'courseId' => $courseId,
            'paymentMethod' => $paymentMethod,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'courseOptions' => $courseOptions,
            'orders' => $orders,
            'summary' => [
                'orders' => $orders->count(),
                'students' => $orders->pluck('order.student_id')->filter()->unique()->count(),
                'courses' => $decoratedDetails->pluck('course_id')->filter()->unique()->count(),
                'gross_amount' => (float) $decoratedDetails->sum(fn($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
                'allocated_discount' => (float) $decoratedDetails->sum(fn($item) => data_get($item, 'finance_breakdown.allocated_discount', 0)),
                'teacher_revenue' => (float) $decoratedDetails->sum(fn($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
            ],
        ];
    }

    private function teacherCourseGrantsQuery(Teacher $teacher, ?array $statuses = ['accepted'])
    {
        $query = TeacherCourseGrant::query()
            ->where('teacher_id', $teacher->id)
            ->whereNull('revoked_at');

        if ($statuses !== null) {
            $query->whereIn('status', $statuses);
        }

        return $query;
    }

    private function resolveOwnedStudentContext(Teacher $teacher, int $studentId): array
    {
        $student = Student::query()->withTrashed()->findOrFail($studentId);
        $details = $this->paidOrderDetailsQuery($teacher)
            ->whereHas('order', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->get();
        $grants = $this->teacherCourseGrantsQuery($teacher)
            ->with(['course', 'student'])
            ->where('student_id', $studentId)
            ->get();

        if ($details->isEmpty() && $grants->isEmpty()) {
            abort(404);
        }

        return [$student, $details, $grants];
    }

    private function normalizeStudentTagLabel(?string $tag): string
    {
        return match ($tag) {
            'potential' => 'Tiềm năng',
            'support_needed' => 'Cần hỗ trợ',
            'vip' => 'VIP',
            default => '',
        };
    }

    private function renderTeacherCommentThread(Request $request, int $courseId, Teacher $teacher)
    {
        $course = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->findOrFail($courseId);

        $threads = courseCommentThreads($course->id, true);

        $html = view('teacher::clients.dashboard.comments_thread', [
            'course' => $course,
            'threads' => $threads,
            'teacher' => $teacher,
        ])->render();

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        return redirect()->route('teacher.dashboard.comments', ['course_id' => $course->id]);
    }

    private function commentErrorResponse(Request $request, string $message, int $status = 422)
    {
        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()
            ->withInput()
            ->with('msg_danger', $message);
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function sanitizeCommentContent(string $content): string
    {
        $plainText = strip_tags(str_replace('&nbsp;', ' ', $content));
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $plainText) ?? '');
    }

    private function updateCourseDurations(?int $courseId): void
    {
        if (!$courseId) {
            return;
        }

        $lessons = $this->lessonRepository->getAllLessions($courseId);
        $durations = $lessons->reduce(fn ($prev, $item) => $prev + (float) $item->getRawOriginal('durations'), 0);
        $this->courseRepository->updateCourse($courseId, ['durations' => $durations]);
    }

    private function collectLessonBranchIds(int $lessonId): array
    {
        $ids = [$lessonId];
        $childIds = Lesson::query()
            ->withTrashed()
            ->where('parent_id', $lessonId)
            ->pluck('id');

        foreach ($childIds as $childId) {
            $ids = array_merge($ids, $this->collectLessonBranchIds((int) $childId));
        }

        return array_values(array_unique($ids));
    }

    private function flattenTrashedLessons($lessons, ?int $parentId = null, string $prefix = '', array &$rows = []): array
    {
        $items = $lessons->where('parent_id', $parentId)->sortBy('position');

        foreach ($items as $lesson) {
            $rows[] = [
                'id' => $lesson->id,
                'name' => $prefix . $lesson->name_locale,
                'is_trial' => $lesson->parent_id ? ((int) $lesson->is_trial === 1 ? __('teacher::dashboard.common.yes') : __('teacher::dashboard.common.no')) : '',
                'has_document' => $lesson->document_id ? __('teacher::dashboard.common.yes') : __('teacher::dashboard.common.no'),
                'status' => (int) $lesson->status === 1 ? __('teacher::dashboard.courses.status.published') : __('teacher::dashboard.courses.status.draft'),
                'deleted_at' => optional($lesson->deleted_at)?->format('d/m/Y H:i'),
            ];

            $this->flattenTrashedLessons($lessons, $lesson->id, $prefix . '|-- ', $rows);
        }

        return $rows;
    }
}


