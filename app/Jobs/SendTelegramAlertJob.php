<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendTelegramAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $message
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
            $botToken = config('services.telegram.bot_token');
            $chatId = config('services.telegram.chat_id');

            if ($isEnabled && $botToken && $chatId) {
                $cleanMessage = $this->message;
                
                // Mask sensitive keys/values in activity logs
                $sensitivePatterns = [
                    '/(token|password|secret|key|auth|api_key|bot_token|access_token|private_key|refresh_token)/i',
                    '/([0-9]{8,10}:[a-zA-Z0-9_-]{35})/i', // Telegram Bot Token pattern
                ];

                foreach ($sensitivePatterns as $pattern) {
                    if (preg_match($pattern, $cleanMessage)) {
                        // If a line contains sensitive keywords, mask the entire detail line
                        $lines = explode("\n", $cleanMessage);
                        foreach ($lines as &$line) {
                            if (preg_match($pattern, $line)) {
                                $line = preg_replace('/:[^:]+$/', ': [SENSITIVE DATA HIDDEN]', $line);
                            }
                        }
                        $cleanMessage = implode("\n", $lines);
                    }
                }

                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $cleanMessage,
                    'parse_mode' => 'Markdown'
                ]);
            }
        } catch (\Throwable $e) {
            // Log error but don't fail the queue if it's just a connection issue
            \Illuminate\Support\Facades\Log::error('Telegram Alert Job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
