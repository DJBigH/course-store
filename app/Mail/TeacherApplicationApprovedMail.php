<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Teacher\src\Models\TeacherApplication;

class TeacherApplicationApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $localeCode;

    public function __construct(
        public TeacherApplication $application,
        public ?string $plainPassword,
        string $locale
    ) {
        $this->localeCode = $locale;
    }

    public function build()
    {
        app()->setLocale($this->localeCode);

        return $this->subject('BigK Udemy - Hồ sơ giảng viên của bạn đã được duyệt')
            ->view('emails.teacher-application-approved');
    }
}
