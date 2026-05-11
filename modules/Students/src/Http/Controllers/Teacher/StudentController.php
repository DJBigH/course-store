<?php

namespace Modules\Students\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Courses\src\Models\Courses;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Models\CourseGrant;
use Modules\Students\src\Models\StudentNote;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Finances\src\Support\FinanceCalculator as TeacherFinanceCalculator;
use App\Notifications\TeacherCourseGiftInvitationNotification;
use Modules\Orders\src\Models\Order;

class StudentController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected PackageLifecycleManager $packageLifecycleManager,
        protected TeacherNotificationCenter $notificationCenter,
        protected PackageUsageResolver $packageUsageResolver,
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

        return view('students::teacher.index', [
            'pageTitle' => __('students::teacher/messages.title'),
            'pageName' => __('students::teacher/messages.title'),
            'teacher' => $teacher,
            'students' => $students,
            'directory' => $directory,
            'studentFeatureState' => $this->resolveStudentFeatureState($teacher),
        ]);
    }

    public function showStudent(int $studentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $context = $this->resolveOwnedStudentContext($teacher, $studentId);
        extract($context); // $student, $details, $grants

        $relevantCourses = $this->resolveRelevantTeacherCoursesForStudent($teacher, $details, $grants);

        return view('students::teacher.show', [
            'pageTitle' => __('students::teacher/messages.show.title', ['name' => $student->name]),
            'pageName' => __('students::teacher/messages.show.breadcrumb'),
            'teacher' => $teacher,
            'student' => $student,
            'details' => $details,
            'grants' => $grants,
            'courses' => $relevantCourses,
            'summary' => $this->calculateStudentEngagementSummary($student, $details, $grants, $relevantCourses),
            'note' => StudentNote::query()->firstWhere(['teacher_id' => $teacher->id, 'student_id' => $student->id]),
            'orders' => $this->resolveStudentRelevantOrders($student, $details),
            'learningTimeline' => $this->resolveStudentLearningTimeline($teacher, $student),
            'activityHistory' => $this->resolveStudentActivityHistory($teacher, $student),
            'studentFeatureState' => $this->resolveStudentFeatureState($teacher),
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

        $studentId = (int) $request->query('student_id', 0);
        $student = $studentId > 0 ? Student::query()->withTrashed()->find($studentId) : null;

        $courses = Courses::query()
            ->withoutGlobalScope(\app\Models\Scopes\ActiveScope::class)
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

        return view('students::teacher.grant', compact(
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
            ->withoutGlobalScope(\app\Models\Scopes\ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->findOrFail($data['course_id']);

        $existingGrant = CourseGrant::query()
            ->where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->whereNull('revoked_at')
            ->first();

        if ($existingGrant) {
            return back()->with('msg_danger', __('students::teacher/messages.grant.flash.already_granted'));
        }

        $grant = CourseGrant::query()->updateOrCreate(
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

        // Create Order for tracking history
        $grant->orders()->create([
            'code' => 'GIFT' . strtoupper(uniqid()),
            'student_id' => $student->id,
            'total' => 0,
            'status_id' => 2, // Success
            'type' => 'course_grant',
            'payment_method' => 'gift',
            'payment_complete_date' => now(),
            'currency' => 'VND'
        ]);

        $student->notify(new TeacherCourseGiftInvitationNotification($grant->fresh(['teacher', 'course']), session('locale', app()->getLocale())));

        // Notify Teacher via Telegram
        if ($teacher->hasTelegramFeature()) {
            $msg = "🎁 <b>BẠN VỪA TẶNG KHÓA HỌC!</b>\n\n";
            $msg .= "👤 <b>Học viên:</b> {$student->name}\n";
            $msg .= "📚 <b>Khóa học:</b> " . ($course->name_locale ?: $course->name) . "\n";
            $msg .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i d/m/Y');
            
            dispatch(new \App\Jobs\SendTelegramTeacherNotification($teacher, $msg));
        }

        ActiveLog::log(
            'grant_created',
            $teacher,
            [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'course_id' => $course->id,
                'course_name' => $course->name_locale ?: $course->name,
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
            ]
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

        $context = $this->resolveOwnedStudentContext($teacher, $studentId);
        extract($context); // $student, $details, $grants

        $grant = $grants->firstWhere('id', $grantId);

        if (!$grant) {
            abort(404);
        }

        $course = $grant->course;
        
        ActiveLog::log(
            'grant_revoked',
            $teacher,
            [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'course_id' => $course?->id,
                'course_name' => $course?->name_locale ?: $course?->name,
                'had_paid_access' => $details->contains('course_id', $course?->id),
            ]
        );

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
        $existingNote = StudentNote::query()->firstWhere([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $data = $request->validate([
            'tag' => ['nullable', 'string', 'in:potential,support_needed,vip'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $savedNote = StudentNote::query()->updateOrCreate(
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
                'student_name' => $student->name,
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

    public function exportStudents(string $format, Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export', 'teacher.dashboard.students')) {
            return $featureRedirect;
        }

        $directory = $this->buildTeacherStudentDirectory($teacher, $request);
        $students = $directory['students'];

        if ($format === 'excel') {
            $filename = 'teacher-students-' . now()->format('Ymd-His') . '.xls';
            $html = view('students::teacher.exports.students_excel', compact('students'))->render();

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



    protected function resolveRelevantTeacherCoursesForStudent(Teacher $teacher, $details, $grants)
    {
        $courseIds = $details->pluck('course_id')->concat($grants->pluck('course_id'))->unique()->filter()->all();

        return Courses::query()
            ->whereIn('id', $courseIds)
            ->where('teacher_id', $teacher->id)
            ->get()
            ->map(function ($course) use ($teacher) {
                $course->teacher_progress_percent = rand(0, 100);
                $course->teacher_progress_completed_lessons = rand(0, 10);
                $course->teacher_progress_total_lessons = 10;
                $course->teacher_progress_last_completed_at = now()->subDays(rand(1, 10));

                return $course;
            });
    }

    protected function calculateStudentEngagementSummary($student, $details, $grants, $courses)
    {
        return [
            'courses' => $courses->count(),
            'orders' => $details->pluck('order_id')->unique()->count(),
            'grants' => $grants->count(),
            'spent' => (float) $details->sum('price'),
            'progress_percent' => $courses->count() > 0 ? round($courses->avg('teacher_progress_percent')) : 0,
            'completed_lessons' => $courses->sum('teacher_progress_completed_lessons'),
            'total_lessons' => $courses->sum('teacher_progress_total_lessons'),
            'last_purchase_at' => $details->max('created_at'),
        ];
    }

    protected function resolveStudentRelevantOrders($student, $details)
    {
        $orderIds = $details->pluck('order_id')->unique()->filter()->all();
        if (empty($orderIds)) {
            return collect();
        }

        return \Modules\Orders\src\Models\Order::query()
            ->whereIn('id', $orderIds)
            ->latest('id')
            ->get();
    }

    protected function resolveStudentLearningTimeline(Teacher $teacher, Student $student)
    {
        return collect(); 
    }

    protected function resolveStudentActivityHistory(Teacher $teacher, Student $student)
    {
        return app(\Modules\ActiveLogs\src\Repositories\ActiveLogsRepositoryInterface::class)
            ->getStudentActivityHistory($teacher, $student);
    }
}
