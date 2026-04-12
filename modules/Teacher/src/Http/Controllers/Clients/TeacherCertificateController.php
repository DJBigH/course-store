<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Courses\src\Models\Courses;
use Modules\Lessons\src\Models\Lesson;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Models\StudentLessonProgress;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherCourseCertificate;
use Modules\Teacher\src\Models\TeacherCourseGrant;
use Modules\Teacher\src\Support\TeacherCertificateIssuer;

class TeacherCertificateController extends Controller
{
    public function __construct(
        private TeacherCertificateIssuer $certificateIssuer
    ) {
    }

    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()]);
        }

        if ($featureRedirect = $this->ensureFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $courseOptions = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('id')
            ->get(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh']);

        $selectedCourse = (int) $request->query('course_id', 0);
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $rows = $this->buildCertificateDirectory($teacher)
            ->when($selectedCourse > 0, fn (Collection $items) => $items->where('course_id', $selectedCourse))
            ->when($search !== '', function (Collection $items) use ($search) {
                return $items->filter(function ($item) use ($search) {
                    return str_contains(mb_strtolower($item->student_name), mb_strtolower($search))
                        || str_contains(mb_strtolower($item->course_name), mb_strtolower($search))
                        || str_contains(mb_strtolower($item->student_email), mb_strtolower($search));
                });
            })
            ->when($status === 'issued', fn (Collection $items) => $items->where('is_issued', true))
            ->when($status === 'missing', fn (Collection $items) => $items->where('is_issued', false))
            ->sortByDesc(function ($item) {
                return sprintf(
                    '%015d-%015d-%010d',
                    optional($item->certificate?->issued_at)->timestamp ?? 0,
                    optional($item->last_learning_at)->timestamp ?? 0,
                    (int) $item->student_id
                );
            })
            ->values();

        $pageTitle = 'Chung chi hoan thanh';
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.certificates.index', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'rows',
            'courseOptions',
            'selectedCourse',
            'search',
            'status'
        ));
    }

    public function issue(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()]);
        }

        if ($featureRedirect = $this->ensureFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $payload = $request->validate([
            'student_id' => ['required', 'integer'],
            'course_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $course = Courses::query()
            ->withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->findOrFail((int) $payload['course_id']);

        $student = Student::query()->withTrashed()->findOrFail((int) $payload['student_id']);

        $hasPaidAccess = $this->paidOrderDetailsQuery($teacher)
            ->where('course_id', $course->id)
            ->whereHas('order', fn ($query) => $query->where('student_id', $student->id))
            ->exists();

        $hasGrantedAccess = $this->teacherCourseGrantsQuery($teacher)
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$hasPaidAccess && !$hasGrantedAccess) {
            abort(404);
        }

        $progress = $this->certificateIssuer->resolveProgress($student->id, $course->id);
        $certificate = $this->certificateIssuer->issueIfEligible(
            $student,
            $course,
            [
                'completed_lessons' => $progress['completed_lessons'],
                'total_lessons' => $progress['total_lessons'],
                'progress_percent' => max(100, $progress['progress_percent']),
            ],
            auth('students')->id(),
            $payload['note'] ?? null,
            true,
            'manual'
        );

        if (!$certificate) {
            return back()->with('msg_danger', 'Chua the cap chung chi cho hoc vien nay.');
        }

        return redirect()
            ->route('teacher.dashboard.certificates.show', $certificate->id)
            ->with('msg_success', 'Da cap chung chi thanh cong.');
    }

    public function show(int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()]);
        }

        if ($featureRedirect = $this->ensureFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $certificate = TeacherCourseCertificate::query()
            ->with(['teacher', 'student', 'course'])
            ->where('teacher_id', $teacher->id)
            ->findOrFail($id);

        return view('certificates.course_completion', [
            'certificate' => $certificate,
            'viewerMode' => 'teacher',
            'backUrl' => route('teacher.dashboard.certificates.index'),
            'backLabel' => 'Quay lai danh sach chung chi',
            'autoPrint' => request()->boolean('print'),
        ]);
    }

    public function revoke(Request $request, int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()]);
        }

        if ($featureRedirect = $this->ensureFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $certificate = TeacherCourseCertificate::query()
            ->where('teacher_id', $teacher->id)
            ->findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'revoke_reason' => ['required', 'string', 'max:500'],
            ],
            [
                'revoke_reason.required' => 'Vui long nhap ly do thu hoi.',
                'revoke_reason.max' => 'Ly do thu hoi khong duoc vuot qua 500 ky tu.',
            ]
        );

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('certificate_modal', 'revoke')
                ->with('certificate_modal_payload', [
                    'action' => route('teacher.dashboard.certificates.revoke', $certificate->id),
                    'student_name' => $certificate->student_name_snapshot,
                    'course_name' => $certificate->course_name_snapshot,
                    'certificate_code' => $certificate->code,
                ]);
        }

        $payload = $validator->validated();

        $certificate->update([
            'revoked_at' => now(),
            'revoked_by_student_id' => auth('students')->id(),
            'revoke_reason' => trim((string) $payload['revoke_reason']),
        ]);

        activity_log(
            'certificate_revoked',
            $certificate->student,
            [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'student_id' => $certificate->student_id,
                'student_name' => $certificate->student_name_snapshot,
                'course_id' => $certificate->course_id,
                'course_name' => $certificate->course_name_snapshot,
                'certificate_id' => $certificate->id,
                'certificate_code' => $certificate->code,
                'revoke_reason' => $certificate->revoke_reason,
                'is_revoked' => true,
            ],
            'teacher_student_management',
            'Da thu hoi chung chi cua hoc vien.'
        );

        return redirect()
            ->route('teacher.dashboard.certificates.index')
            ->with('msg_success', 'Da thu hoi chung chi.');
    }

    private function buildCertificateDirectory(Teacher $teacher): Collection
    {
        $rows = collect();
        $paidDetails = $this->paidOrderDetailsQuery($teacher)->get();
        $grants = $this->teacherCourseGrantsQuery($teacher)->with(['course', 'student'])->get();

        foreach ($paidDetails as $detail) {
            $course = $detail->courses;
            $student = $detail->order?->students;

            if (!$course || !$student) {
                continue;
            }

            $key = $student->id . ':' . $course->id;
            if ($rows->has($key)) {
                continue;
            }

            $rows->put($key, (object) [
                'student_id' => (int) $student->id,
                'student_name' => (string) $student->name,
                'student_email' => (string) $student->email,
                'course_id' => (int) $course->id,
                'course_name' => (string) ($course->name_locale ?: $course->name),
                'access_type' => 'paid',
            ]);
        }

        foreach ($grants as $grant) {
            $course = $grant->course;
            $student = $grant->student;

            if (!$course || !$student) {
                continue;
            }

            $key = $student->id . ':' . $course->id;
            if ($rows->has($key)) {
                continue;
            }

            $rows->put($key, (object) [
                'student_id' => (int) $student->id,
                'student_name' => (string) $student->name,
                'student_email' => (string) $student->email,
                'course_id' => (int) $course->id,
                'course_name' => (string) ($course->name_locale ?: $course->name),
                'access_type' => 'grant',
            ]);
        }

        $studentIds = $rows->pluck('student_id')->unique()->values()->all();
        $courseIds = $rows->pluck('course_id')->unique()->values()->all();

        $lessonTotals = Lesson::query()
            ->whereIn('course_id', $courseIds ?: [0])
            ->whereNotNull('parent_id')
            ->where('status', 1)
            ->selectRaw('course_id, COUNT(*) as total_lessons')
            ->groupBy('course_id')
            ->pluck('total_lessons', 'course_id');

        $progressRows = StudentLessonProgress::query()
            ->whereIn('student_id', $studentIds ?: [0])
            ->whereIn('course_id', $courseIds ?: [0])
            ->selectRaw('student_id, course_id, COUNT(DISTINCT lesson_id) as completed_lessons, MAX(completed_at) as last_learning_at')
            ->groupBy('student_id', 'course_id')
            ->get()
            ->keyBy(fn ($item) => $item->student_id . ':' . $item->course_id);

        $certificateMap = TeacherCourseCertificate::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('student_id', $studentIds ?: [0])
            ->whereIn('course_id', $courseIds ?: [0])
            ->get()
            ->keyBy(fn ($item) => $item->student_id . ':' . $item->course_id);

        return $rows->map(function ($row) use ($lessonTotals, $progressRows, $certificateMap) {
            $key = $row->student_id . ':' . $row->course_id;
            $progress = $progressRows->get($key);
            $totalLessons = (int) ($lessonTotals[$row->course_id] ?? 0);
            $completedLessons = min((int) ($progress->completed_lessons ?? 0), $totalLessons);
            $progressPercent = $totalLessons > 0 ? min((int) round(($completedLessons * 100) / $totalLessons), 100) : 0;

            $row->total_lessons = $totalLessons;
            $row->completed_lessons = $completedLessons;
            $row->progress_percent = $progressPercent;
            $row->last_learning_at = $progress?->last_learning_at;
            $row->certificate = $certificateMap->get($key);
            $row->is_issued = (bool) $row->certificate;
            $row->is_revoked = (bool) $row->certificate?->revoked_at;

            return $row;
        });
    }

    private function resolveTeacher(): ?Teacher
    {
        $student = auth('students')->user();
        $teacher = $student?->teacher;

        if (!$teacher || $teacher->status !== 'active') {
            return null;
        }

        return $teacher;
    }

    private function ensureFeatureAllowed(Teacher $teacher)
    {
        if ($teacher->packageHasFeature('can_issue_certificates')) {
            return null;
        }

        return redirect()
            ->route('teacher.dashboard.package.upgrade')
            ->with('msg_danger', __('teacher::dashboard.package_features.feature_locked'));
    }

    private function paidOrderDetailsQuery(Teacher $teacher)
    {
        return OrderDetail::query()
            ->with(['courses', 'order.students'])
            ->whereHas('courses', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->whereHas('order', function ($query) {
                $query->where('status_id', 2);
            })
            ->latest('id');
    }

    private function teacherCourseGrantsQuery(Teacher $teacher)
    {
        return TeacherCourseGrant::query()
            ->where('teacher_id', $teacher->id)
            ->whereNull('revoked_at')
            ->whereIn('status', ['accepted']);
    }
}
