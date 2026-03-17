<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $mailLocale;

    public function __construct(?string $locale = null)
    {
        $this->mailLocale = $locale ?: app()->getLocale() ?: config('app.locale', 'vi');
        $this->locale($this->mailLocale);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('auth::clients/email.password_changed.subject'))
            ->greeting(__('auth::clients/email.password_changed.greeting'))
            ->line(__('auth::clients/email.password_changed.line_1'))
            ->line(__('auth::clients/email.password_changed.line_2'))
            ->action(
                __('auth::clients/email.password_changed.action'),
                route('clients-login', ['locale' => $this->mailLocale])
            )
            ->line(__('auth::clients/email.password_changed.line_3'));
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
