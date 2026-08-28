<?php

namespace App\Jobs;

use App\Services\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Teacher\src\Models\Teacher;

class SendTelegramTeacherNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Teacher $teacher,
        protected string $message
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TelegramNotificationService $service): void
    {
        $service->sendToTeacher($this->teacher, $this->message);
    }
}
