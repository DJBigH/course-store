<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Packages\src\Models\Package;
use Modules\Teacher\src\Models\Teacher;

class TeacherPackageGrantedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $localeCode;

    public function __construct(
        public Teacher $teacher,
        public Package $package,
        public string  $claimUrl,
        string $locale
    ) {
        $this->localeCode = $locale;
    }

    public function build()
    {
        app()->setLocale($this->localeCode);

        return $this->subject("🎁 Admin vừa tặng bạn gói {$this->package->name_locale}!")
            ->view('emails.teacher-package-granted');
    }
}
