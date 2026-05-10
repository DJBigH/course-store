<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Teacher\src\Models\Teacher;

class TelegramNotificationService
{
    /**
     * Send a notification to the teacher via Telegram Bot.
     *
     * @param Teacher $teacher
     * @param string $message (HTML formatted)
     * @return bool
     */
    public function sendToTeacher(Teacher $teacher, string $message): bool
    {
        if (!$teacher->hasTelegramFeature()) {
            return false;
        }

        if (empty($teacher->telegram_chat_id) || !$teacher->is_telegram_notifications_enabled) {
            return false;
        }

        $botToken = config('services.telegram_teacher.bot_token') ?: config('services.telegram.bot_token');
        if (empty($botToken)) {
            Log::warning('TelegramNotificationService: Teacher Bot token is not configured.');
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $teacher->telegram_chat_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('TelegramNotificationService: Failed to send message.', [
                'teacher_id' => $teacher->id,
                'chat_id' => $teacher->telegram_chat_id,
                'response' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('TelegramNotificationService: Exception while sending message.', [
                'teacher_id' => $teacher->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
