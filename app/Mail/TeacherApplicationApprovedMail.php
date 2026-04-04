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
        public ?string $passwordSetupUrl,
        public bool $useExistingAccount,
        public string $packageAction,
        string $locale
    ) {
        $this->localeCode = $locale;
    }

    public function build()
    {
        app()->setLocale($this->localeCode);

        return $this->subject(__('teacher::mail.approved.subjects.' . $this->resolveSubjectKey()))
            ->view('emails.teacher-application-approved');
    }

    private function resolveSubjectKey(): string
    {
        return match ($this->packageAction) {
            'extended' => 'extended',
            'queued' => 'queued',
            default => 'activated',
        };
    }
}
