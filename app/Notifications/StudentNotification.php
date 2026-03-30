<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentNotification extends Notification
{
    use Queueable;

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
            'type' => $this->data['type'] ?? 'student.notice',
            'title' => $this->data['title'] ?? null,
            'title_translations' => $this->data['title_translations'] ?? null,
            'message' => $this->data['message'] ?? null,
            'message_translations' => $this->data['message_translations'] ?? null,
            'url' => $this->data['url'] ?? null,
            'severity' => $this->data['severity'] ?? 'info',
            'icon' => $this->data['icon'] ?? 'fas fa-bell',
            'entity_type' => $this->data['entity_type'] ?? null,
            'entity_id' => $this->data['entity_id'] ?? null,
            'meta' => $this->data['meta'] ?? [],
        ];
    }
}
