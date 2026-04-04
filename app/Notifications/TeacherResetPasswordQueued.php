<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class TeacherResetPasswordQueued extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct(string $token, protected string $locale)
    {
        parent::__construct($token);
        $this->locale($locale);
    }

    public function toMail($notifiable)
    {
        app()->setLocale($this->locale);

        return $this->buildMailMessage(
            URL::route('teacher.password.reset', [
                'locale' => $this->locale,
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ])
        );
    }

    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('teacher::auth.email.reset_password.subject'))
            ->greeting(__('teacher::auth.email.reset_password.greeting'))
            ->line(__('teacher::auth.email.reset_password.line'))
            ->action(__('teacher::auth.email.reset_password.action'), $url)
            ->line(__('teacher::auth.email.reset_password.expire_notice', [
                'count' => config('auth.passwords.students.expire'),
            ]))
            ->line(__('teacher::auth.email.reset_password.outro'));
    }
}
