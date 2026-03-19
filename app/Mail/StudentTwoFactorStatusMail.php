<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Students\src\Models\Student;

class StudentTwoFactorStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Student $student,
        public string $type,
        string $locale,
        public array $context = []
    ) {
        $this->locale = $locale;
    }

    public function build()
    {
        app()->setLocale($this->locale);

        $prefix = match ($this->type) {
            'enabled' => 'two_factor_enabled',
            'disabled' => 'two_factor_disabled',
            default => 'unusual_login',
        };

        return $this->subject(__('students::clients/email.' . $prefix . '.subject'))
            ->view('emails.student-two-factor-status', [
                'student' => $this->student,
                'prefix' => $prefix,
                'loginUrl' => route('clients-login', ['locale' => $this->locale]),
                'currentLogin' => $this->context['currentLogin'] ?? null,
                'previousLogin' => $this->context['previousLogin'] ?? null,
            ]);
    }
}