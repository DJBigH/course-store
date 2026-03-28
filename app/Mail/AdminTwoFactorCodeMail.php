<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\User\src\Models\User;

class AdminTwoFactorCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        string $locale
    ) {
        $this->locale = $locale;
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->subject('Mã xác thực đăng nhập quản trị')
            ->view('emails.admin-two-factor-code', [
                'user' => $this->user,
                'code' => $this->code,
                'expiresInMinutes' => 10,
            ]);
    }
}
