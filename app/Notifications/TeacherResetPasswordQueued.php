<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class TeacherResetPasswordQueued extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = URL::route('teacher.password.reset', [
            'locale' => $this->locale ?? app()->getLocale(),
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

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
