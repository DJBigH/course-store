<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminSecurityAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $type,
        private string $title,
        private string $message,
        private string $severity = 'warning',
        private string $icon = 'fas fa-shield-alt',
        private ?string $url = null,
        private array $meta = [],
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'severity' => $this->severity,
            'icon' => $this->icon,
            'entity_type' => 'admin_security',
            'entity_id' => $notifiable->id ?? null,
            'meta' => $this->meta,
        ];
    }
}
