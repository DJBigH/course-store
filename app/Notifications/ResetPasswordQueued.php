<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordQueued extends ResetPassword implements ShouldQueue
{
    use Queueable;

    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('clients/forgot.subject'))
            ->greeting(__('clients/forgot.greeting'))
            ->line(__('clients/forgot.line'))
            ->action(__('clients/forgot.action'), $url)
            ->line(
                __('clients/forgot.line_2')
                    . ' '
                    . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')
                    . ' '
                    . __('clients/forgot.subject')
            )
            ->line(__('clients/forgot.outro'));
    }
}
