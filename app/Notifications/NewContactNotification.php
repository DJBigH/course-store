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
        $type = $this->contact->submission_type ?? 'contact';
        $isFeedback = $type === 'feedback';
        $isReport = $type === 'report';

        $titleTranslations = [
            'vi' => $isReport ? 'Báo cáo mới' : ($isFeedback ? 'Góp ý mới' : 'Liên hệ mới'),
            'en' => $isReport ? 'New report' : ($isFeedback ? 'New feedback' : 'New contact'),
            'ko' => $isReport ? '새 신고' : ($isFeedback ? '새 제안' : '새 문의'),
            'ja' => $isReport ? '新しい報告' : ($isFeedback ? '新しいフィードバック' : '新しいお問い合わせ'),
            'zh' => $isReport ? '新的举报' : ($isFeedback ? '新的反馈' : '新的联系'),
        ];

        $messageTranslations = [
            'vi' => $isReport ? 'Có một báo cáo mới cần admin kiểm tra' : ($isFeedback ? 'Có một góp ý mới từ người dùng' : 'Có liên hệ mới từ khách hàng'),
            'en' => $isReport ? 'There is a new report for the admin team' : ($isFeedback ? 'There is new user feedback' : 'There is a new customer contact'),
            'ko' => $isReport ? '관리팀이 확인할 새 신고가 있습니다' : ($isFeedback ? '새 사용자 피드백이 있습니다' : '고객으로부터 새 문의가 있습니다'),
            'ja' => $isReport ? '管理者向けの新しい報告があります' : ($isFeedback ? '新しいユーザーフィードバックがあります' : 'お客様から新しいお問い合わせがあります'),
            'zh' => $isReport ? '有新的报告需要管理员处理' : ($isFeedback ? '有新的用户反馈' : '有来自客户的新联系'),
        ];

        return [
            'type' => 'contact.new',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('contacts.show', $this->contact->id),
            'severity' => $isReport ? 'danger' : 'warning',
            'icon' => $isReport ? 'fas fa-triangle-exclamation' : ($isFeedback ? 'fas fa-lightbulb' : 'fas fa-envelope-open-text'),
            'entity_type' => 'contact',
            'entity_id' => $this->contact->id,
            'contact_id' => $this->contact->id,
            'meta' => [
                'name' => $this->contact->name ?? null,
                'email' => $this->contact->email ?? null,
                'submission_type' => $type,
                'subject' => $this->contact->subject ?? null,
            ],
        ];
    }
}
