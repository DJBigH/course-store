<?php

namespace Modules\Certificates\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Modules\Courses\src\Models\Courses;
use Modules\Lessons\src\Models\Lesson;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Models\StudentLessonProgress;
use Modules\Teacher\src\Models\Teacher;
use Modules\Certificates\src\Models\Certificate;
use Modules\Students\src\Models\CourseGrant;
use Modules\Certificates\src\Support\CertificateIssuer;

class CertificateController extends Controller
{
    public function __construct(
        private CertificateIssuer $certificateIssuer
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
        $canExport = $teacher->packageHasFeature('can_import_export');

        $query = $this->getCertificateDataQuery($teacher, $request);
        $rows = $query->paginate(20)->withQueryString();

        $pageTitle = __('certificates::teacher/messages.certificates.title');
        $pageName = $pageTitle;

        return view('certificates::teacher.index', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'rows',
            'courseOptions',
            'selectedCourse',
            'search',
            'status',
            'canExport'
        ));
    }

    public function export(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            abort(401);
        }

        if (!$teacher->packageHasFeature('can_issue_certificates')) {
            return redirect()->route('teacher.dashboard.package.upgrade')->with('msg_danger', __('certificates::teacher/messages.package_features.feature_locked'));
        }

        if (!$teacher->packageHasFeature('can_import_export')) {
            return redirect()->back()->with('msg_danger', __('certificates::teacher/messages.package_features.import_export_locked'));
        }

        $selectedCourse = (int) $request->query('course_id', 0);
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $rows = $this->getCertificateDataQuery($teacher, $request)->get();

        $fileName = 'danh-sach-chung-chi-' . now()->format('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'STT',
            __('certificates::teacher/messages.certificates.mail.student_label'),
            'Email',
            __('certificates::teacher/messages.certificates.mail.course_label'),
            'Tiến độ (%)',
            'Số bài hoàn thành',
            'Trạng thái',
            __('certificates::teacher/messages.certificates.mail.code_label'),
            'Ngày cấp',
            'Ngày thu hồi',
        ];

        $callback = function () use ($rows, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); // Add BOM for Excel UTF-8
            fputcsv($file, $columns);

            foreach ($rows as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->student_name,
                    $row->student_email,
                    $row->course_name,
                    $row->progress_percent . '%',
                    $row->completed_lessons . '/' . $row->total_lessons,
                    $row->is_revoked ? 'Đã thu hồi' : ($row->is_issued ? 'Đã cấp' : 'Chưa cấp'),
                    $row->certificate_code ?? '',
                    $row->issued_at ? Carbon::parse($row->issued_at)->format('d/m/Y H:i') : '',
                    $row->revoked_at ? Carbon::parse($row->revoked_at)->format('d/m/Y H:i') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
            return back()->with('msg_danger', __('certificates::teacher/messages.certificates.issue_failed'));
        }

        activity_log(
            action: 'issue_certificate',
            subject: $certificate,
            properties: [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'student_id' => $certificate->student_id,
                'student_name' => $certificate->student_name_snapshot,
                'course_id' => $certificate->course_id,
                'course_name' => $certificate->course_name_snapshot,
                'certificate_code' => $certificate->code,
                'issue_mode' => 'manual',
            ],
            logName: 'teacher_student_management',
            description: "Cấp chứng chỉ hoàn thành khóa học cho học viên [{$certificate->student_name_snapshot}]"
        );

        return redirect()
            ->route('teacher.dashboard.certificates.show', $certificate->id)
            ->with('msg_success', __('certificates::teacher/messages.certificates.issue_success'));
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

        $certificate = Certificate::query()
            ->with(['teacher', 'student', 'course'])
            ->where('teacher_id', $teacher->id)
            ->findOrFail($id);

        return view('certificates::pdf.completion', [
            'certificate' => $certificate,
            'viewerMode' => 'teacher',
            'backUrl' => route('teacher.dashboard.certificates.index'),
            'backLabel' => __('certificates::teacher/messages.certificates.back_to_list'),
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

        $certificate = Certificate::query()
            ->where('teacher_id', $teacher->id)
            ->findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'revoke_reason' => ['required', 'string', 'max:500'],
            ],
            [
                'revoke_reason.required' => 'Vui lòng nhập lý do thu hồi.',
                'revoke_reason.max' => 'Lý do thu hồi không được vượt quá 500 ký tự.',
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
            __('certificates::teacher/messages.certificates.revoke_log_desc')
        );

        return redirect()
            ->route('teacher.dashboard.certificates.index')
            ->with('msg_success', __('certificates::teacher/messages.certificates.revoke_success'));
    }

    private function getCertificateDataQuery(Teacher $teacher, Request $request)
    {
        $selectedCourse = (int) $request->query('course_id', 0);
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        // 1. Get Access Pairs (Paid + Granted) using UNION for performance
        $paidAccess = $this->paidOrderDetailsQuery($teacher)
            ->selectRaw('orders_detail.course_id, orders.student_id, "paid" as access_type, orders_detail.id as source_id')
            ->join('orders', 'orders.id', '=', 'orders_detail.order_id');

        $grantedAccess = $this->teacherCourseGrantsQuery($teacher)
            ->selectRaw('course_id, student_id, "grant" as access_type, id as source_id');

        $accessUnion = $paidAccess->union($grantedAccess);

        // 2. Subqueries for progress and total lessons
        $lessonTotals = Lesson::query()
            ->whereNotNull('parent_id')
            ->where('status', 1)
            ->selectRaw('course_id, COUNT(*) as total_count')
            ->groupBy('course_id');

        $studentProgress = StudentLessonProgress::query()
            ->selectRaw('student_id, course_id, COUNT(DISTINCT lesson_id) as completed_count')
            ->groupBy('student_id', 'course_id');

        // 3. Main query with joins
        return \Illuminate\Support\Facades\DB::query()
            ->fromSub($accessUnion, 'access')
            ->join('students', 'students.id', '=', 'access.student_id')
            ->join('courses', 'courses.id', '=', 'access.course_id')
            ->whereNull('students.deleted_at')
            ->leftJoin('teacher_course_certificates as certs', function ($join) use ($teacher) {
                $join->on('certs.student_id', '=', 'access.student_id')
                    ->on('certs.course_id', '=', 'access.course_id')
                    ->where('certs.teacher_id', '=', $teacher->id);
            })
            ->leftJoinSub($lessonTotals, 'lt', 'lt.course_id', '=', 'access.course_id')
            ->leftJoinSub($studentProgress, 'sp', function ($join) {
                $join->on('sp.student_id', '=', 'access.student_id')
                    ->on('sp.course_id', '=', 'access.course_id');
            })
            ->select([
                'access.student_id',
                'access.course_id',
                'access.access_type',
                'students.name as student_name',
                'students.email as student_email',
                'courses.name as course_name',
                'certs.id as certificate_id',
                'certs.code as certificate_code',
                'certs.issued_at',
                'certs.revoked_at',
                'certs.note',
                \Illuminate\Support\Facades\DB::raw('COALESCE(lt.total_count, 0) as total_lessons'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(sp.completed_count, 0) as completed_lessons'),
                \Illuminate\Support\Facades\DB::raw('certs.id is not null as is_issued'),
                \Illuminate\Support\Facades\DB::raw('certs.revoked_at is not null as is_revoked'),
            ])
            ->when($selectedCourse > 0, fn ($q) => $q->where('access.course_id', $selectedCourse))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('students.name', 'like', "%$search%")
                        ->orWhere('students.email', 'like', "%$search%")
                        ->orWhere('courses.name', 'like', "%$search%");
                });
            })
            ->when($status === 'issued', fn ($q) => $q->whereNotNull('certs.id'))
            ->when($status === 'missing', fn ($q) => $q->whereNull('certs.id'))
            ->orderByRaw('certs.issued_at DESC, students.id DESC');
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
            ->with('msg_danger', __('certificates::teacher/messages.package_features.feature_locked'));
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
            ->latest('orders_detail.id');
    }

    private function teacherCourseGrantsQuery(Teacher $teacher)
    {
        return CourseGrant::query()
            ->where('teacher_id', $teacher->id)
            ->whereNull('revoked_at');
    }
}
