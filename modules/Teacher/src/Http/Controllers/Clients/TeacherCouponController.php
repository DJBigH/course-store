<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherCourseGrant;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;

class TeacherCouponController extends Controller
{
    public function __construct(
        protected TeacherPackageLifecycleManager $packageLifecycleManager,
    ) {}

    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $this->syncCouponLocks($teacher);
        $teacher->refresh();

        $couponLimit = $this->resolveCouponLimit($teacher);
        $couponCount = $this->resolveTeacherCouponCount($teacher);
        $activeCouponCount = $this->resolveTeacherCouponCount($teacher, false);
        $lockedCouponCount = $this->resolveTeacherLockedCouponCount($teacher);
        $canManageCoupons = $teacher->packageHasFeature('can_manage_coupons');
        $canCreateCoupons = $canManageCoupons && !$this->isCouponOverLimit($couponLimit, $lockedCouponCount);

        $coupons = Coupons::query()
            ->with(['courses', 'students'])
            ->withCount('usagescoupon')
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(12)
            ->withQueryString();
        $this->attachCouponHistoryPreview($coupons, $teacher);

        $pageTitle = __('teacher::coupons.page_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.coupons.index', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'coupons',
            'couponLimit',
            'couponCount',
            'activeCouponCount',
            'lockedCouponCount',
            'canManageCoupons',
            'canCreateCoupons'
        ));
    }

    public function create()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureCouponFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $this->syncCouponLocks($teacher);
        $teacher->refresh();

        if ($limitRedirect = $this->ensureCouponUnderVisibleLimit($teacher)) {
            return $limitRedirect;
        }

        if ($limitRedirect = $this->ensureCouponCreationAllowed($teacher)) {
            return $limitRedirect;
        }

        $students = $this->resolveTeacherStudents($teacher);
        $courses = Courses::query()
            ->withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();
        $assignedStudentIds = [];
        $assignedCourseIds = [];
        $pageTitle = __('teacher::coupons.create_title');
        $pageName = $pageTitle;
        $coupon = null;

        return view('teacher::clients.dashboard.coupons.form', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'coupon',
            'students',
            'courses',
            'assignedStudentIds',
            'assignedCourseIds'
        ));
    }

    public function store(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureCouponFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $this->syncCouponLocks($teacher);
        $teacher->refresh();

        if ($limitRedirect = $this->ensureCouponUnderVisibleLimit($teacher)) {
            return $limitRedirect;
        }

        if ($limitRedirect = $this->ensureCouponCreationAllowed($teacher)) {
            return $limitRedirect;
        }

        $data = $this->validateCoupon($request);
        $assignment = $this->validateAssignment($request, $teacher);
        if ($assignment === null) {
            return back()->withErrors([
                'students' => __('teacher::coupons.validation.assign_required'),
            ])->withInput();
        }

        $coupon = Coupons::query()->create(array_merge($data, [
            'teacher_id' => $teacher->id,
        ]));

        $coupon->students()->sync($assignment['students']);
        $coupon->courses()->sync($assignment['courses']);
        $this->syncCouponLocks($teacher);
        $coupon->load(['students', 'courses']);
        $this->logTeacherCouponActivity(
            $teacher,
            $coupon,
            'coupon_created',
            'Da tao ma giam gia moi',
            [
                'discount_type' => $coupon->discount_type,
                'discount_value' => (int) $coupon->discount_value,
                'student_count' => $coupon->students->count(),
                'course_count' => $coupon->courses->count(),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.coupons.edit', $coupon->id)
            ->with('msg_success', __('teacher::coupons.flash.created'));
    }

    public function edit(int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureCouponFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $coupon = $this->resolveTeacherCoupon($teacher, $id);
        if ($lockedRedirect = $this->ensureCouponEditable($coupon)) {
            return $lockedRedirect;
        }
        $students = $this->resolveTeacherStudents($teacher);
        $courses = Courses::query()
            ->withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();
        $assignedStudentIds = $coupon->students()->pluck('students.id')->map(fn ($sid) => (int) $sid)->all();
        $assignedCourseIds = $coupon->courses()->pluck('courses.id')->map(fn ($cid) => (int) $cid)->all();
        $pageTitle = __('teacher::coupons.edit_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.coupons.form', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'coupon',
            'students',
            'courses',
            'assignedStudentIds',
            'assignedCourseIds'
        ));
    }

    public function update(Request $request, int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureCouponFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $coupon = $this->resolveTeacherCoupon($teacher, $id);
        if ($lockedRedirect = $this->ensureCouponEditable($coupon)) {
            return $lockedRedirect;
        }
        $data = $this->validateCoupon($request, $coupon->id);
        $assignment = $this->validateAssignment($request, $teacher);
        if ($assignment === null) {
            return back()->withErrors([
                'students' => __('teacher::coupons.validation.assign_required'),
            ])->withInput();
        }

        $before = $coupon->only([
            'code',
            'discount_type',
            'discount_value',
            'total_condition',
            'count',
            'per_student_once',
            'start_date',
            'end_date',
        ]);
        $coupon->update($data);
        $coupon->students()->sync($assignment['students']);
        $coupon->courses()->sync($assignment['courses']);
        $this->syncCouponLocks($teacher);
        $coupon->load(['students', 'courses']);
        $this->logTeacherCouponActivity(
            $teacher,
            $coupon,
            'coupon_updated',
            'Da cap nhat ma giam gia',
            [
                'before' => $before,
                'after' => $coupon->only([
                    'code',
                    'discount_type',
                    'discount_value',
                    'total_condition',
                    'count',
                    'per_student_once',
                    'start_date',
                    'end_date',
                ]),
                'student_count' => $coupon->students->count(),
                'course_count' => $coupon->courses->count(),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.coupons.edit', $coupon->id)
            ->with('msg_success', __('teacher::coupons.flash.updated'));
    }

    public function delete(int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $coupon = $this->resolveTeacherCoupon($teacher, $id);
        $couponName = $coupon->code;
        $couponId = $coupon->id;
        $coupon->delete();
        $this->syncCouponLocks($teacher);
        activity_log(
            'coupon_deleted',
            null,
            [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'coupon_id' => $couponId,
                'coupon_code' => $couponName,
            ],
            'teacher_coupon_management',
            'Da xoa ma giam gia'
        );

        return redirect()
            ->route('teacher.dashboard.coupons.index')
            ->with('msg_success', __('teacher::coupons.flash.deleted'));
    }

    public function togglePriority(int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $coupon = $this->resolveTeacherCoupon($teacher, $id);
        $wasPriority = (bool) $coupon->is_package_priority;
        $coupon->update([
            'is_package_priority' => !$coupon->is_package_priority,
        ]);

        $this->syncCouponLocks($teacher);
        $coupon->refresh();
        $this->logTeacherCouponActivity(
            $teacher,
            $coupon,
            $coupon->is_package_priority ? 'coupon_priority_enabled' : 'coupon_priority_disabled',
            $coupon->is_package_priority ? 'Da bat uu tien goi cho ma giam gia' : 'Da tat uu tien goi cho ma giam gia',
            [
                'before_priority' => $wasPriority,
                'after_priority' => (bool) $coupon->is_package_priority,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.coupons.index')
            ->with('msg_success', __(
                $coupon->fresh()->is_package_priority
                    ? 'teacher::coupons.flash.priority_enabled'
                    : 'teacher::coupons.flash.priority_disabled'
            ));
    }

    public function students(int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureCouponFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $coupon = $this->resolveTeacherCoupon($teacher, $id);
        if ($lockedRedirect = $this->ensureCouponEditable($coupon)) {
            return $lockedRedirect;
        }
        $students = $this->resolveTeacherStudents($teacher);
        $assignedStudentIds = $coupon->students()->pluck('students.id')->map(fn ($sid) => (int) $sid)->all();

        $pageTitle = __('teacher::coupons.assign_students_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.coupons.assign_students', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'coupon',
            'students',
            'assignedStudentIds'
        ));
    }

    public function updateStudents(Request $request, int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureCouponFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $coupon = $this->resolveTeacherCoupon($teacher, $id);
        if ($lockedRedirect = $this->ensureCouponEditable($coupon)) {
            return $lockedRedirect;
        }
        $allowedStudents = $this->resolveTeacherStudents($teacher)->pluck('id')->map(fn ($sid) => (int) $sid)->all();
        $studentIds = collect($request->input('students', []))
            ->map(fn ($sid) => (int) $sid)
            ->filter(fn ($sid) => in_array($sid, $allowedStudents, true))
            ->unique()
            ->values();

        $now = now();
        $syncData = [];
        foreach ($studentIds as $studentId) {
            $syncData[$studentId] = [
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $coupon->students()->sync($syncData);
        $this->syncCouponLocks($teacher);
        $this->logTeacherCouponActivity(
            $teacher,
            $coupon->fresh(),
            'coupon_students_updated',
            'Da cap nhat hoc vien ap dung ma giam gia',
            [
                'student_count' => count($syncData),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.coupons.students', $coupon->id)
            ->with('msg_success', __('teacher::coupons.flash.students_updated'));
    }

    public function courses(int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureCouponFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $coupon = $this->resolveTeacherCoupon($teacher, $id);
        if ($lockedRedirect = $this->ensureCouponEditable($coupon)) {
            return $lockedRedirect;
        }
        $courses = Courses::query()
            ->withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();
        $assignedCourseIds = $coupon->courses()->pluck('courses.id')->map(fn ($cid) => (int) $cid)->all();

        $pageTitle = __('teacher::coupons.assign_courses_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.coupons.assign_courses', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'coupon',
            'courses',
            'assignedCourseIds'
        ));
    }

    public function updateCourses(Request $request, int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureCouponFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $coupon = $this->resolveTeacherCoupon($teacher, $id);
        if ($lockedRedirect = $this->ensureCouponEditable($coupon)) {
            return $lockedRedirect;
        }
        $allowedCourses = Courses::query()
            ->withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->pluck('id')
            ->map(fn ($cid) => (int) $cid)
            ->all();

        $courseIds = collect($request->input('courses', []))
            ->map(fn ($cid) => (int) $cid)
            ->filter(fn ($cid) => in_array($cid, $allowedCourses, true))
            ->unique()
            ->values();

        $now = now();
        $syncData = [];
        foreach ($courseIds as $courseId) {
            $syncData[$courseId] = [
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $coupon->courses()->sync($syncData);
        $this->syncCouponLocks($teacher);
        $this->logTeacherCouponActivity(
            $teacher,
            $coupon->fresh(),
            'coupon_courses_updated',
            'Da cap nhat khoa hoc ap dung ma giam gia',
            [
                'course_count' => count($syncData),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.coupons.courses', $coupon->id)
            ->with('msg_success', __('teacher::coupons.flash.courses_updated'));
    }

    private function validateCoupon(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:100', 'unique:coupons,code,' . (int) $id],
            'discount_type' => ['required', 'in:percent,value'],
            'discount_value' => ['required', 'integer', 'min:1'],
            'total_condition' => ['nullable', 'integer', 'min:0'],
            'count' => ['nullable', 'integer', 'min:0'],
            'per_student_once' => ['required', 'boolean'],
            'start_date' => ['nullable', 'date', 'required_with:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_with:start_date'],
        ]);

        if (empty($data['code'])) {
            $data['code'] = $this->generateUniqueCode($id);
        }

        if ($data['discount_type'] === 'percent' && (int) $data['discount_value'] > 100) {
            $data['discount_value'] = 100;
        }

        if (!empty($data['start_date']) && !$id) {
            $start = Carbon::parse($data['start_date']);
            if ($start->isPast()) {
                $data['start_date'] = now();
            }
        }

        $data['code'] = strtoupper(trim((string) $data['code']));

        return $data;
    }

    private function validateAssignment(Request $request, Teacher $teacher): ?array
    {
        $request->validate([
            'students' => ['nullable', 'array'],
            'students.*' => ['integer'],
            'courses' => ['nullable', 'array'],
            'courses.*' => ['integer'],
        ]);

        $allowedStudents = $this->resolveTeacherStudents($teacher)
            ->pluck('id')
            ->map(fn ($sid) => (int) $sid)
            ->all();
        $allowedCourses = Courses::query()
            ->withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->pluck('id')
            ->map(fn ($cid) => (int) $cid)
            ->all();

        $studentIds = collect($request->input('students', []))
            ->map(fn ($sid) => (int) $sid)
            ->filter(fn ($sid) => in_array($sid, $allowedStudents, true))
            ->unique()
            ->values()
            ->all();

        $courseIds = collect($request->input('courses', []))
            ->map(fn ($cid) => (int) $cid)
            ->filter(fn ($cid) => in_array($cid, $allowedCourses, true))
            ->unique()
            ->values()
            ->all();

        if (empty($studentIds) && empty($courseIds)) {
            return null;
        }

        $now = now();
        $studentSync = [];
        foreach ($studentIds as $studentId) {
            $studentSync[$studentId] = [
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $courseSync = [];
        foreach ($courseIds as $courseId) {
            $courseSync[$courseId] = [
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return [
            'students' => $studentSync,
            'courses' => $courseSync,
        ];
    }

    private function generateUniqueCode(?int $ignoreId = null): string
    {
        $attempts = 0;

        do {
            $attempts++;
            $code = strtoupper(Str::random(8));

            $exists = Coupons::query()
                ->where('code', $code)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists();
        } while ($exists && $attempts < 5);

        if ($exists ?? false) {
            $code = strtoupper(Str::random(10));
        }

        return $code;
    }

    private function attachCouponHistoryPreview($coupons, Teacher $teacher): void
    {
        $couponIds = collect($coupons->items())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $historyMap = ActiveLog::query()
            ->where('log_name', 'teacher_coupon_management')
            ->where(function ($query) use ($couponIds) {
                $query->where(function ($subjectQuery) use ($couponIds) {
                    $subjectQuery->where('subject_type', Coupons::class)
                        ->whereIn('subject_id', $couponIds ?: [0]);
                })->orWhere(function ($propertyQuery) use ($couponIds) {
                    $propertyQuery->whereNull('subject_id')
                        ->whereIn('properties->coupon_id', $couponIds ?: [0]);
                });
            })
            ->where('properties->teacher_id', $teacher->id)
            ->latest('id')
            ->get()
            ->groupBy(function ($log) {
                return (int) ($log->subject_id ?: data_get($log->properties, 'coupon_id', 0));
            });

        foreach ($coupons->items() as $coupon) {
            $coupon->teacher_activity_preview = ($historyMap->get((int) $coupon->id, collect()) ?? collect())
                ->take(3)
                ->values();
        }
    }

    private function logTeacherCouponActivity(
        Teacher $teacher,
        Coupons $coupon,
        string $action,
        string $description,
        array $properties = []
    ): void {
        activity_log(
            $action,
            $coupon,
            array_merge($properties, [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'coupon_id' => $coupon->id,
                'coupon_code' => $coupon->code,
            ]),
            'teacher_coupon_management',
            $description
        );
    }

    private function resolveTeacher(): ?Teacher
    {
        $student = auth('students')->user();
        if (!$student) {
            return null;
        }

        return Teacher::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();
    }

    private function redirectToStatus()
    {
        return redirect()->route('teacher.account.status', ['locale' => session('locale', app()->getLocale())]);
    }

    private function ensureCouponFeatureAllowed(Teacher $teacher)
    {
        if ($teacher->packageHasFeature('can_manage_coupons')) {
            return null;
        }

        return redirect()
            ->route('teacher.dashboard.index')
            ->with('msg_danger', __('teacher::dashboard.package_features.feature_locked'));
    }

    private function ensureCouponCreationAllowed(Teacher $teacher)
    {
        if (!$teacher->packageHasFeature('can_manage_coupons')) {
            return redirect()
                ->route('teacher.dashboard.coupons.index')
                ->with('msg_danger', __('teacher::dashboard.package_features.feature_locked'));
        }

        if ($this->resolveCouponLimit($teacher) === null) {
            return null;
        }

        return null;
    }

    private function resolveCouponLimit(Teacher $teacher): ?int
    {
        if (!$teacher->packageHasFeature('can_manage_coupons')) {
            return 0;
        }

        return $teacher->currentPackage()?->effective_coupon_limit;
    }

    private function resolveTeacherCouponCount(Teacher $teacher, bool $includeLocked = true): int
    {
        return Coupons::query()
            ->where('teacher_id', $teacher->id)
            ->when(!$includeLocked, fn ($query) => $query->whereNull('package_locked_at'))
            ->count();
    }

    private function resolveTeacherLockedCouponCount(Teacher $teacher): int
    {
        return Coupons::query()
            ->where('teacher_id', $teacher->id)
            ->whereNotNull('package_locked_at')
            ->count();
    }

    private function isCouponOverLimit(?int $couponLimit, int $lockedCouponCount): bool
    {
        return $couponLimit !== null && $lockedCouponCount > 0;
    }

    private function resolveTeacherCoupon(Teacher $teacher, int $id): Coupons
    {
        return Coupons::query()
            ->where('teacher_id', $teacher->id)
            ->findOrFail($id);
    }

    private function ensureCouponEditable(Coupons $coupon)
    {
        if (!$coupon->package_locked_at) {
            return null;
        }

        return redirect()
            ->route('teacher.dashboard.coupons.index')
            ->with('msg_danger', __('teacher::coupons.flash.locked_manage_only'));
    }

    private function syncCouponLocks(Teacher $teacher): void
    {
        $this->packageLifecycleManager->syncCouponLocks($teacher->fresh(['application.package']));
    }

    private function ensureCouponUnderVisibleLimit(Teacher $teacher)
    {
        $couponLimit = $this->resolveCouponLimit($teacher);
        $lockedCouponCount = $this->resolveTeacherLockedCouponCount($teacher);

        if (!$this->isCouponOverLimit($couponLimit, $lockedCouponCount)) {
            return null;
        }

        return redirect()
            ->route('teacher.dashboard.coupons.index')
            ->with('msg_danger', __('teacher::coupons.flash.limit_reached', ['limit' => $couponLimit]));
    }

    private function resolveTeacherStudents(Teacher $teacher)
    {
        $orderDetails = $this->paidOrderDetailsQuery($teacher)->get();
        $grants = $this->teacherCourseGrantsQuery($teacher, ['pending', 'accepted'])
            ->with(['student'])
            ->get();

        $studentIds = $orderDetails->pluck('order.student_id')
            ->concat($grants->pluck('student_id'))
            ->filter()
            ->unique()
            ->values();

        return Student::query()
            ->withTrashed()
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get();
    }

    private function paidOrderDetailsQuery(Teacher $teacher)
    {
        return OrderDetail::query()
            ->with(['courses', 'order.status', 'order.students'])
            ->whereHas('courses', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->where('teacher_id', $teacher->id);
            })
            ->whereHas('order', function ($query) {
                $query->where('status_id', 2);
            })
            ->latest('id');
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
}
