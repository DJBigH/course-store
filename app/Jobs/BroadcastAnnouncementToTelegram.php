<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherAnnouncement;

class BroadcastAnnouncementToTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected TeacherAnnouncement $announcement
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $packages = $this->announcement->packages->pluck('id');
        
        $query = Teacher::query()
            ->where('status', 'active')
            ->whereNotNull('telegram_chat_id')
            ->where('is_telegram_notifications_enabled', true);

        // Filter by package if specified
        if ($packages->isNotEmpty()) {
            $query->whereIn('package_id', $packages);
        }

        $query->chunk(100, function ($teachers) {
            foreach ($teachers as $teacher) {
                // Determine message based on teacher locale if possible, or just default to localized message
                $msg = "📢 <b>THÔNG BÁO MỚI</b>\n\n";
                $msg .= "📌 <b>" . $this->announcement->getTitleLocaleForTeacher($teacher) . "</b>\n\n";
                $msg .= strip_tags((string) $this->announcement->getMessageLocaleForTeacher($teacher)) . "\n";
                
                if ($this->announcement->action_url) {
                    $label = $this->announcement->getActionLabelLocaleForTeacher($teacher) ?: 'Xem chi tiết';
                    $msg .= "\n🔗 <a href=\"{$this->announcement->action_url}\">{$label}</a>";
                }

                dispatch(new SendTelegramTeacherNotification($teacher, $msg));
            }
        });
    }
}
