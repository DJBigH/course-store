<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Students\src\Models\Student;

class AccountDeactivatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Student $student;

    public function __construct(Student $student, string $locale)
    {
        $this->student = $student;
        $this->locale = $locale;
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->subject(__('students::clients/email.account_deactivated.subject'))
            ->view('emails.account-deactivated', [
                'student' => $this->student,
                'loginUrl' => route('clients-login', ['locale' => $this->locale]),
            ]);
    }
}
