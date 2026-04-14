<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Courses\src\Models\Courses;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherPromotion;

class TeacherPromotionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Teacher $teacher,
        public TeacherPromotion $promotion,
        public Student $student,
        protected string $mailLocale,
        public ?Courses $course = null
    ) {
    }

    public function build()
    {
        app()->setLocale($this->mailLocale);

        $ctaEnabled = !empty($this->promotion->filters['cta_enabled']);
        $ctaUrl = $ctaEnabled ? trim((string) ($this->promotion->filters['cta_url'] ?? '')) : '';
        $fallbackUrl = $this->resolveFallbackUrl();
        $actionUrl = $ctaUrl !== '' ? $ctaUrl : $fallbackUrl;

        return $this->subject($this->promotion->title)
            ->view('emails.teacher-promotion', [
                'teacher' => $this->teacher,
                'promotion' => $this->promotion,
                'student' => $this->student,
                'course' => $this->course,
                'mailTitle' => $this->promotion->title,
                'mailEyebrow' => 'Teacher promotion',
                'mailHeading' => $this->promotion->title,
                'mailSubtitle' => trim((string) ($this->teacher->name_locale ?: $this->teacher->name ?: 'Teacher')),
                'preheader' => $this->promotion->message ?: $this->promotion->title,
                'actionUrl' => $actionUrl,
                'fallbackUrl' => $fallbackUrl,
            ]);
    }

    protected function resolveFallbackUrl(): string
    {
        if ($this->course && ($this->course->slug_locale ?: $this->course->slug)) {
            return route('courses.detail', [
                'locale' => $this->mailLocale,
                'slug' => $this->course->slug_locale ?: $this->course->slug,
            ]);
        }

        return route('teacher.public.show', [
            'locale' => $this->mailLocale,
            'slug' => $this->teacher->slug_locale ?: $this->teacher->slug,
        ]);
    }
}
