<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GeminiHealthNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $stage,
        private string $reason,
        private int $failureCount,
        private int $threshold,
        private string $message,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $title = $this->stage === 'disabled'
            ? 'Gemini đã tự động tắt'
            : 'Gemini sắp bị tắt';

        $titleTranslations = [
            'vi' => $this->stage === 'disabled' ? 'Gemini đã tự động tắt' : 'Gemini sắp bị tắt tự động',
            'en' => $this->stage === 'disabled' ? 'Gemini has been automatically disabled' : 'Gemini will be automatically disabled soon',
            'ko' => $this->stage === 'disabled' ? 'Gemini가 자동으로 비활성화되었습니다' : 'Gemini가 곧 자동으로 비활성화됩니다',
            'ja' => $this->stage === 'disabled' ? 'Geminiは自動的に無効化されました' : 'Geminiはまもなく自動的に無効化されます',
            'zh' => $this->stage === 'disabled' ? 'Gemini已被自动禁用' : 'Gemini即将被自动禁用',
        ];

        $messageTranslations = [
            'vi' => $this->message,
            'en' => $this->stage === 'disabled'
                ? "Gemini was auto-disabled after {$this->failureCount}/{$this->threshold} consecutive {$this->reasonLabelEn()} errors."
                : "Gemini reached {$this->failureCount}/{$this->threshold} consecutive {$this->reasonLabelEn()} errors and is close to auto-disable.",
            'ko' => $this->message,
            'ja' => $this->message,
            'zh' => $this->message,
        ];

        return [
            'type' => 'chatbot.gemini_health',
            'stage' => $this->stage,
            'reason' => $this->reason,
            'failure_count' => $this->failureCount,
            'threshold' => $this->threshold,
            'title' => $title,
            'title_translations' => $titleTranslations,
            'message' => $this->message,
            'message_translations' => $messageTranslations,
            'url' => route('settings.setting'),
            'severity' => $this->stage === 'disabled' ? 'danger' : 'warning',
            'icon' => 'fas fa-robot',
            'entity_type' => 'settings',
            'entity_id' => null,
            'meta' => [
                'stage' => $this->stage,
                'reason' => $this->reason,
                'failure_count' => $this->failureCount,
                'threshold' => $this->threshold,
            ],
        ];
    }

    private function reasonLabelEn(): string
    {
        return $this->reason === 'auth_error' ? 'authentication' : 'quota';
    }
}
