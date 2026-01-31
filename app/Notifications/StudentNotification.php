<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->data['title'],
            'message' => $this->data['message'],
            'url' => $this->data['url'] ?? null,
        ];
    }
}
