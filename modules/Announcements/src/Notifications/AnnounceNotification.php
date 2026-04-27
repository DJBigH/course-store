<?php

namespace Modules\Announcements\src\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Announcements\src\Models\Announcement;

class AnnounceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via($notifiable): array
    {
        $channels = ['database'];
        
        if ($this->announcement->send_email) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $locale = $notifiable->preferredLocale();
        
        $mail = (new MailMessage)
            ->locale($locale)
            ->subject($this->announcement->title_locale)
            ->greeting(__('teacher::mail.common.greeting', ['name' => $notifiable->name], $locale))
            ->line($this->announcement->message_locale);
            
        // If there's a link, add a button
        if ($this->announcement->action_url) {
            $mail->action($this->announcement->action_label_locale ?: __('teacher::mail.common.view_detail', [], $locale), $this->announcement->action_url);
        } else {
            // Default link to the internal detail page
            $mail->action(__('teacher::mail.common.read_on_system', [], $locale), route('clients.inbox.show', ['locale' => $locale, 'announcement' => $this->announcement->id]));
        }

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title_locale,
            'message' => $this->announcement->message_locale,
            'icon' => 'fas fa-bullhorn',
            'type' => 'announcement',
            'url' => route('clients.inbox.show', ['locale' => app()->getLocale(), 'announcement' => $this->announcement->id]),
        ];
    }
}
