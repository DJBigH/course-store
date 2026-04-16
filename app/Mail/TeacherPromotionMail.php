<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeacherPromotionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Truyền plain data thay vì Eloquent models để tránh ModelNotFoundException
     * khi queue worker cố restore model đã bị xóa giữa lúc dispatch và execute.
     */
    public function __construct(
        // Teacher data
        protected string $teacherName,
        protected string $teacherSlug,
        // Promotion data (plain, không phụ thuộc DB)
        protected string $promotionTitle,
        protected string $promotionMessage,
        protected array  $promotionFilters,
        // Student data
        protected string $studentName,
        protected string $studentEmail,
        // Locale
        protected string $mailLocale,
        // Course data (optional)
        protected ?string $courseSlug = null,
        protected ?string $courseName = null,
    ) {
    }

    /**
     * Factory method tiện lợi — gọi từ nơi dispatch job.
     * Trích xuất dữ liệu cần thiết trước khi đẩy vào queue.
     */
    public static function fromModels(
        \Modules\Teacher\src\Models\Teacher $teacher,
        \Modules\Teacher\src\Models\TeacherPromotion $promotion,
        \Modules\Students\src\Models\Student $student,
        string $mailLocale,
        ?\Modules\Courses\src\Models\Courses $course = null
    ): self {
        return new self(
            teacherName:       trim((string) ($teacher->name_locale ?: $teacher->name ?: 'Teacher')),
            teacherSlug:       $teacher->slug_locale ?: $teacher->slug ?: '',
            promotionTitle:    $promotion->title ?? '',
            promotionMessage:  $promotion->message ?? '',
            promotionFilters:  (array) ($promotion->filters ?? []),
            studentName:       $student->name ?? '',
            studentEmail:      $student->email ?? '',
            mailLocale:        $mailLocale,
            courseSlug:        $course ? ($course->slug_locale ?: $course->slug ?: null) : null,
            courseName:        $course ? ($course->name_locale ?: $course->name ?: null) : null,
        );
    }

    public function build()
    {
        app()->setLocale($this->mailLocale);

        $ctaEnabled = !empty($this->promotionFilters['cta_enabled']);
        $ctaUrl     = $ctaEnabled ? trim((string) ($this->promotionFilters['cta_url'] ?? '')) : '';
        $fallbackUrl = $this->resolveFallbackUrl();
        $actionUrl   = $ctaUrl !== '' ? $ctaUrl : $fallbackUrl;

        return $this->subject($this->promotionTitle)
            ->view('emails.teacher-promotion', [
                // Truyền plain data dưới dạng object giả để view cũ vẫn hoạt động
                'teacher'     => (object) [
                    'name'        => $this->teacherName,
                    'name_locale' => $this->teacherName,
                    'slug'        => $this->teacherSlug,
                    'slug_locale' => $this->teacherSlug,
                ],
                'promotion'   => (object) [
                    'title'   => $this->promotionTitle,
                    'message' => $this->promotionMessage,
                    'filters' => $this->promotionFilters,
                ],
                'student'     => (object) [
                    'name'  => $this->studentName,
                    'email' => $this->studentEmail,
                ],
                'course'      => $this->courseSlug ? (object) [
                    'slug'        => $this->courseSlug,
                    'slug_locale' => $this->courseSlug,
                    'name'        => $this->courseName,
                    'name_locale' => $this->courseName,
                ] : null,
                'mailTitle'   => $this->promotionTitle,
                'mailEyebrow' => 'Teacher promotion',
                'mailHeading' => $this->promotionTitle,
                'mailSubtitle' => $this->teacherName,
                'preheader'   => $this->promotionMessage ?: $this->promotionTitle,
                'actionUrl'   => $actionUrl,
                'fallbackUrl' => $fallbackUrl,
            ]);
    }

    protected function resolveFallbackUrl(): string
    {
        if ($this->courseSlug) {
            return route('courses.detail', [
                'locale' => $this->mailLocale,
                'slug'   => $this->courseSlug,
            ]);
        }

        return route('teacher.public.show', [
            'locale' => $this->mailLocale,
            'slug'   => $this->teacherSlug,
        ]);
    }
}
