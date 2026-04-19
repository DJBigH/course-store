<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherCourseGrant;
use Modules\Teacher\src\Models\TeacherStudentNote;
use Modules\Students\src\Models\Student;
use Modules\Courses\src\Models\Courses;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\Teacher\src\Support\TeacherPackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TeacherStudentController extends Controller
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

    public function students(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $directory = $this->buildTeacherStudentDirectory($teacher, $request);
        $studentList = $directory['students'];

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $students = new LengthAwarePaginator(
            $studentList->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $studentList->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $studentFeatureState = $this->resolveStudentFeatureState($teacher);
        $pageTitle = __('students::teacher/messages.title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.students', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'students',
            'directory',
            'studentFeatureState'
        ));
    }

    public function activityLogs(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_view_activity_logs')) {
            return $featureRedirect;
        }

        $type = trim((string) $request->query('type', 'all'));
        $search = trim((string) $request->query('q', ''));
        $typeMap = [
            'students' => 'teacher_student_management',
            'courses' => 'teacher_course_management',
            'coupons' => 'teacher_coupon_management',
        ];
        $selectedType = array_key_exists($type, $typeMap) ? $type : 'all';

        $logsQuery = ActiveLog::query()
            ->where('properties->teacher_id', $teacher->id)
            ->whereIn('log_name', array_values($typeMap))
            ->when($selectedType !== 'all', function ($query) use ($selectedType, $typeMap) {
                $query->where('log_name', $typeMap[$selectedType]);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('action', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhere('properties->student_name', 'like', '%' . $search . '%')
                        ->orWhere('properties->course_name', 'like', '%' . $search . '%')
                        ->orWhere('properties->coupon_code', 'like', '%' . $search . '%')
                        ->orWhere('properties->certificate_code', 'like', '%' . $search . '%')
                        ->orWhere('properties->teacher_name', 'like', '%' . $search . '%');
                });
            });

        $summary = [
            'total' => (clone $logsQuery)->count(),
            'students' => (clone $logsQuery)->where('log_name', $typeMap['students'])->count(),
            'courses' => (clone $logsQuery)->where('log_name', $typeMap['courses'])->count(),
            'coupons' => (clone $logsQuery)->where('log_name', $typeMap['coupons'])->count(),
        ];

        $logs = $logsQuery
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $pageTitle = __('students::teacher/messages.activity_logs.title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.activity_logs', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'logs',
            'summary',
            'search',
            'selectedType'
        ));
    }

    public function exportStudents(Request $request, string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export')) {
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
            fputcsv($handle, [
                __('students::teacher/messages.export.name'),
                __('students::teacher/messages.export.email'),
                __('students::teacher/messages.export.phone'),
                __('students::teacher/messages.export.courses'),
                __('students::teacher/messages.export.spent'),
                __('students::teacher/messages.export.last_purchase'),
                __('students::teacher/messages.export.progress'),
                __('students::teacher/messages.export.tag'),
            ]);

            foreach ($students as $student) {
                fputcsv($handle, [
                    $student->name,
                    $student->email,
                    $student->phone ?: '',
                    $student->teacher_course_count,
                    $student->teacher_total_spent,
                    optional($student->teacher_last_purchase_at)->format('Y-m-d H:i:s') ?: '',
                    $student->teacher_progress_percent . '%',
                    $this->normalizeStudentTagLabel($student->teacher_tag),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function showStudent(int $studentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        [$student, $details, $grants] = $this->resolveOwnedStudentContext($teacher, $studentId);

        $note = TeacherStudentNote::query()->firstWhere([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $pageTitle = __('students::teacher/messages.show.breadcrumb');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.student_show', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'student',
            'details',
            'grants',
            'note'
        ));
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

        $studentId = (int) $request->query('student_id', 0);
        $student = $studentId > 0 ? Student::query()->withTrashed()->find($studentId) : null;

        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $recentGrants = $this->teacherCourseGrantsQuery($teacher, ['pending', 'accepted'])
            ->with(['course', 'student'])
            ->latest('id')
            ->take(10)
            ->get();

        $pageTitle = __('students::teacher/messages.grant.title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.student_grant', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'student',
            'courses',
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
            'student_id' => ['required', 'integer'],
            'course_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $student = Student::query()->withTrashed()->findOrFail($data['student_id']);
        $course = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->findOrFail($data['course_id']);

        $existingGrant = TeacherCourseGrant::query()
            ->where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->whereNull('revoked_at')
            ->first();

        if ($existingGrant) {
            return back()->with('msg_danger', __('students::teacher/messages.grant.flash.already_granted'));
        }

        $grant = TeacherCourseGrant::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
            ],
            [
                'granted_by_id' => auth('students')->id(),
                'status' => 'accepted',
                'revoked_at' => null,
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
            ]
        );

        $this->notificationCenter->broadcast(
            $student,
            'grant_created',
            [
                'teacher_id' => $teacher->id,
                'course_id' => $course->id,
                'grant_id' => $grant->id,
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
            ],
            $grant->fresh(['teacher', 'course'])
        );

        return redirect()
            ->route('teacher.dashboard.students.show', $student->id)
            ->with('msg_success', __('students::teacher/messages.grant.flash.success'));
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

        return redirect()
            ->route('teacher.dashboard.students.show', $student->id)
            ->with('msg_success', __('students::teacher/messages.grant.flash.revoked'));
    }

    public function saveStudentNote(Request $request, int $studentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            if ($request->ajax()) {
                return response()->json(['error' => 'unauthorized'], 401);
            }

            return $this->redirectToStatus();
        }

        $student = Student::query()->withTrashed()->findOrFail($studentId);
        $existingNote = TeacherStudentNote::query()->firstWhere([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $data = $request->validate([
            'tag' => ['nullable', 'string', 'in:potential,support_needed,vip'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $savedNote = TeacherStudentNote::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
            ],
            [
                'tag' => $data['tag'] ?: null,
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
            ]
        );

        ActiveLog::log(
            'note_saved',
            $teacher,
            [
                'student_id' => $student->id,
                'tag' => $savedNote->tag,
                'tag_label' => $this->normalizeStudentTagLabel($savedNote->tag),
                'note_preview' => Str::limit((string) ($savedNote->note ?? ''), 160),
                'previous_tag' => $existingNote?->tag,
                'previous_note_preview' => Str::limit((string) ($existingNote?->note ?? ''), 160),
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'tag' => $savedNote->tag,
                'tag_label' => $this->normalizeStudentTagLabel($savedNote->tag),
                'note' => $savedNote->note,
            ]);
        }

        return redirect()
            ->route('teacher.dashboard.students.show', $student->id)
            ->with('msg_success', __('students::teacher/messages.show.flash.note_saved'));
    }
}
