<?php

namespace App\Mail;

use App\Support\StudentTwoFactorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Students\src\Models\Student;

class StudentTwoFactorCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Student $student,
        public string $code,
        public string $purpose,
        string $locale
    ) {
        $this->locale = $locale;
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->subject(__('students::clients/email.two_factor_code.subject'))
            ->view('emails.student-two-factor-code', [
                'student' => $this->student,
                'code' => $this->code,
                'purpose' => $this->purpose,
                'expiresInMinutes' => (int) ceil(app(StudentTwoFactorService::class)->challengeLifetime() / 60),
            ]);
    }
}
