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
            'vi' => $this->stage === 'disabled'
                ? 'Gemini đã tự động tắt'
                : 'Gemini sắp bị tắt tự động',

            'en' => $this->stage === 'disabled'
                ? 'Gemini has been auto-disabled'
                : 'Gemini is about to be auto-disabled',

            'ko' => $this->stage === 'disabled'
                ? 'Gemini가 자동으로 비활성화되었습니다'
                : 'Gemini가 곧 자동으로 비활성화됩니다',

            'ja' => $this->stage === 'disabled'
                ? 'Geminiは自動的に無効化されました'
                : 'Geminiはまもなく自動的に無効化されます',

            'zh' => $this->stage === 'disabled'
                ? 'Gemini 已自动停用'
                : 'Gemini 即将被自动停用',
        ];

        $messageTranslations = [
            'vi' => $this->message,
            'en' => $this->stage === 'disabled'
                ? "Gemini was auto-disabled after {$this->failureCount}/{$this->threshold} consecutive {$this->reasonLabelEn()} errors."
                : "Gemini reached {$this->failureCount}/{$this->threshold} consecutive {$this->reasonLabelEn()} errors and is close to auto-disable.",
            'ko' => $this->stage === 'disabled'
                ? "Gemini? {$this->failureCount}/{$this->threshold}? ?? {$this->reasonLabelKo()} ??? ?? ?????????."
                : "Gemini? {$this->failureCount}/{$this->threshold}? ?? {$this->reasonLabelKo()} ??? ??? ?? ????? ??????.",
            'ja' => $this->stage === 'disabled'
                ? "Gemini?{$this->failureCount}/{$this->threshold}????{$this->reasonLabelJa()}??????????????"
                : "Gemini?{$this->failureCount}/{$this->threshold}????{$this->reasonLabelJa()}?????????????????",
            'zh' => $this->stage === 'disabled'
                ? "Gemini ??? {$this->failureCount}/{$this->threshold} ?{$this->reasonLabelZh()}?????????"
                : "Gemini ??? {$this->failureCount}/{$this->threshold} ???{$this->reasonLabelZh()}????????????",
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
        ];
    }

    private function reasonLabelEn(): string
    {
        return $this->reason === 'auth_error' ? 'authentication' : 'quota';
    }

    private function reasonLabelKo(): string
    {
        return $this->reason === 'auth_error' ? '??' : '??';
    }

    private function reasonLabelJa(): string
    {
        return $this->reason === 'auth_error' ? '??' : '????';
    }

    private function reasonLabelZh(): string
    {
        return $this->reason === 'auth_error' ? '??' : '??';
    }
}
