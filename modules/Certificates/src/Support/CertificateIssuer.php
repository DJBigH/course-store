<?php

namespace Modules\Certificates\src\Support;

use App\Mail\StudentCertificateIssuedMail;
use App\Notifications\StudentCertificateIssuedNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Courses\src\Models\Courses;
use Modules\Lessons\src\Models\Lesson;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Models\StudentLessonProgress;
use Modules\Certificates\src\Models\Certificate;

class CertificateIssuer
{
    public function issueIfEligible(
        Student $student,
        Courses $course,
        ?array $progress = null,
        ?int $issuedByStudentId = null,
        ?string $note = null,
        bool $allowExistingReissue = false,
        string $source = 'auto'
    ): ?Certificate {
        $teacher = $course->teacher;

        if (!$teacher || $teacher->status !== 'active') {
            return null;
        }

        if (!$teacher->packageHasFeature('can_issue_certificates')) {
            return null;
        }

        $existing = Certificate::query()
            ->where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        $progress ??= $this->resolveProgress($student->id, $course->id);

        if (
            (($progress['progress_percent'] ?? 0) < 100 || ($progress['total_lessons'] ?? 0) <= 0)
            && !($allowExistingReissue && $existing)
        ) {
            return null;
        }

        $certificate = Certificate::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
            ],
            [
                'issued_by_student_id' => $issuedByStudentId,
                'code' => $existing?->code ?: $this->generateCertificateCode(),
                'student_name_snapshot' => (string) ($student->name ?: 'Student'),
                'course_name_snapshot' => (string) ($course->name_locale ?: $course->name ?: 'Course'),
                'teacher_name_snapshot' => (string) ($teacher->name_locale ?: $teacher->name ?: 'Teacher'),
                'completed_lessons' => (int) ($progress['completed_lessons'] ?? 0),
                'total_lessons' => (int) ($progress['total_lessons'] ?? 0),
                'progress_percent' => (int) ($progress['progress_percent'] ?? 0),
                'note' => $note,
                'issued_at' => now(),
                'revoked_at' => null,
                'revoked_by_student_id' => null,
                'revoke_reason' => null,
            ]
        );

        $locale = method_exists($student, 'preferredLocale') ? (string) $student->preferredLocale() : (string) app()->getLocale();

        if (!$existing) {
            $student->notify(new StudentCertificateIssuedNotification($certificate, $locale));
        }

        if (!empty($student->email)) {
            Mail::to($student->email)
                ->locale($locale)
                ->queue(new StudentCertificateIssuedMail($certificate, $locale));
        }

        activity_log(
            $existing ? 'certificate_reissued' : 'certificate_issued',
            $student,
            [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'course_id' => $course->id,
                'course_name' => $course->name_locale ?: $course->name,
                'certificate_id' => $certificate->id,
                'certificate_code' => $certificate->code,
                'progress_percent' => (int) ($progress['progress_percent'] ?? 0),
                'completed_lessons' => (int) ($progress['completed_lessons'] ?? 0),
                'total_lessons' => (int) ($progress['total_lessons'] ?? 0),
                'issue_source' => $source,
                'issued_by_student_id' => $issuedByStudentId,
                'note' => $note,
                'is_revoked' => false,
            ],
            'teacher_student_management',
            $existing ? 'Đã cấp lại chứng chỉ cho học viên.' : 'Đã cấp chứng chỉ cho học viên.'
        );

        return $certificate;
    }

    public function resolveProgress(int $studentId, int $courseId): array
    {
        $course = Courses::query()->find($courseId);
        $condition = $course?->completion_condition ?? 'all_lessons';

        if ($condition === 'none') {
            return [
                'total_lessons' => 0,
                'completed_lessons' => 0,
                'progress_percent' => 0,
                'condition' => 'none',
            ];
        }

        $totalLessons = (int) Lesson::query()
            ->where('course_id', $courseId)
            ->whereNotNull('parent_id')
            ->where('status', 1)
            ->count();

        $completedLessons = (int) StudentLessonProgress::query()
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->distinct('lesson_id')
            ->count('lesson_id');

        $completedLessons = min($completedLessons, $totalLessons);
        $lessonProgress = $totalLessons > 0 ? min((int) round(($completedLessons * 100) / $totalLessons), 100) : 0;

        if ($condition === 'all_lessons') {
            return [
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'progress_percent' => $lessonProgress,
                'condition' => 'all_lessons',
            ];
        }

        $totalQuizzes = (int) \Modules\Courses\src\Models\CourseQuiz::query()
            ->where('course_id', $courseId)
            ->where('status', 1)
            ->count();

        $passedQuizzes = (int) \Modules\Courses\src\Models\CourseQuizSubmission::query()
            ->whereHas('quiz', fn($q) => $q->where('course_id', $courseId)->where('status', 1))
            ->where('student_id', $studentId)
            ->where('passed', true)
            ->distinct('quiz_id')
            ->count('quiz_id');

        $passedQuizzes = min($passedQuizzes, $totalQuizzes);
        $quizProgress = $totalQuizzes > 0 ? min((int) round(($passedQuizzes * 100) / $totalQuizzes), 100) : 0;

        if ($condition === 'all_quizzes') {
            return [
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'total_quizzes' => $totalQuizzes,
                'passed_quizzes' => $passedQuizzes,
                'progress_percent' => $totalQuizzes > 0 ? $quizProgress : 0,
                'condition' => 'all_quizzes',
            ];
        }

        // condition === 'all'
        $finalProgress = 0;
        if ($totalLessons > 0 && $totalQuizzes > 0) {
            $finalProgress = ($lessonProgress == 100 && $quizProgress == 100) ? 100 : min((int) round(($lessonProgress + $quizProgress) / 2), 99);
        } elseif ($totalLessons > 0) {
            $finalProgress = $lessonProgress;
        } elseif ($totalQuizzes > 0) {
            $finalProgress = $quizProgress;
        }

        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'total_quizzes' => $totalQuizzes,
            'passed_quizzes' => $passedQuizzes,
            'progress_percent' => $finalProgress,
            'condition' => 'all',
        ];
    }

    private function generateCertificateCode(): string
    {
        do {
            $code = 'CERT-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (Certificate::query()->where('code', $code)->exists());

        return $code;
    }
}
