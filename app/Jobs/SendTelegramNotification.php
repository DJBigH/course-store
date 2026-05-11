<?php

namespace App\Jobs;

use App\Services\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $chatId,
        protected string $message,
        protected ?string $botToken = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TelegramNotificationService $service): void
    {
        $service->sendMessage($this->chatId, $this->message, $this->botToken);
    }
}
