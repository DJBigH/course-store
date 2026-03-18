<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewContactNotification extends Notification
{
    use Queueable;

    protected $contact;

    public function __construct($contact)
    {
        $this->contact = $contact;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $messageTranslations = [
            'vi' => 'Có liên hệ mới từ khách hàng',
            'en' => 'There is a new customer contact',
            'ko' => '새 고객 문의가 있습니다',
            'ja' => '新しいお問い合わせがあります',
            'zh' => '有新的客户联系',
        ];

        return [
            'type' => 'contact.new',
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('contacts.show', $this->contact->id),
            'contact_id' => $this->contact->id,
        ];
    }
}
