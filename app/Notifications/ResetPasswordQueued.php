<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordQueued extends ResetPassword implements ShouldQueue
{
    use Queueable;

    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('auth::clients/email.reset_password.subject'))
            ->greeting(__('auth::clients/email.reset_password.greeting'))
            ->line(__('auth::clients/email.reset_password.line'))
            ->action(__('auth::clients/email.reset_password.action'), $url)
            ->line(__('auth::clients/email.reset_password.expire_notice', [
                'count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
            ]))
            ->line(__('auth::clients/email.reset_password.outro'));
    }
}
