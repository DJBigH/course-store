<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Teacher\src\Models\TeacherCourseCertificate;

class StudentCertificateIssuedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public TeacherCourseCertificate $certificate,
        protected string $mailLocale
    ) {
    }

    public function build()
    {
        app()->setLocale($this->mailLocale);

        return $this->subject(__('teacher::dashboard.certificates.mail.subject', ['app_name' => config('app.name')]))
            ->view('emails.student-certificate-issued', [
                'certificate' => $this->certificate,
                'certificateUrl' => route('students.account.certificates.show', [
                    'locale' => $this->mailLocale,
                    'id' => $this->certificate->id,
                ]),
            ]);
    }
}
