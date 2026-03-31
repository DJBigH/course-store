<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Teacher\src\Models\TeacherApplication;

class TeacherApplicationRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $localeCode;

    public function __construct(
        public TeacherApplication $application,
        string $locale
    ) {
        $this->localeCode = $locale;
    }

    public function build()
    {
        app()->setLocale($this->localeCode);

        return $this->subject('BigK Udemy - Cập nhật hồ sơ đăng ký giảng viên')
            ->view('emails.teacher-application-rejected');
    }
}
