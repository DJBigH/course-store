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
        $titleTranslations = [
            'vi' => 'Liên hệ mới',
            'en' => 'New contact',
            'ko' => '새 문의',
            'ja' => '新しいお問い合わせ',
            'zh' => '新的联系',
        ];

        $messageTranslations = [
            'vi' => 'Có liên hệ mới từ khách hàng',
            'en' => 'There is a new customer contact',
            'ko' => '고객으로부터 새로운 문의가 있습니다',
            'ja' => 'お客様から新しいお問い合わせがあります',
            'zh' => '有来自客户的新联系',
        ];

        return [
            'type' => 'contact.new',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('contacts.show', $this->contact->id),
            'severity' => 'warning',
            'icon' => 'fas fa-envelope-open-text',
            'entity_type' => 'contact',
            'entity_id' => $this->contact->id,
            'contact_id' => $this->contact->id,
            'meta' => [
                'name' => $this->contact->name ?? null,
                'email' => $this->contact->email ?? null,
            ],
        ];
    }
}
