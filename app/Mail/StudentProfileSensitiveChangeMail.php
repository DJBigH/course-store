<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Students\src\Models\Student;

class StudentProfileSensitiveChangeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Student $student,
        string $locale,
        public array $changes = []
    ) {
        $this->locale = $locale;
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->subject(__('students::clients/email.profile_sensitive_changed.subject'))
            ->view('emails.student-profile-sensitive-changed', [
                'student' => $this->student,
                'changes' => $this->changes,
                'loginUrl' => route('clients-login', ['locale' => $this->locale]),
            ]);
    }
}
