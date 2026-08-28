<?php

namespace Modules\Teacher\src\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Teacher\src\Models\TelegramPackage;

class TelegramGiftNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $package;
    protected $sub;

    public function __construct(TelegramPackage $package, $sub = null)
    {
        $this->package = $package;
        $this->sub = $sub;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $locale = app()->getLocale() ?: 'vi';
        $url = $this->sub && $this->sub->claim_token 
            ? route('telegram.claim.show', ['locale' => $locale, 'token' => $this->sub->claim_token])
            : route('teacher.dashboard.telegram.index', ['locale' => $locale]);

        return [
            'type' => 'telegram_gift',
            'title' => 'Bạn vừa nhận được quà tặng!',
            'message' => 'Quản trị viên đã tặng cho bạn gói tính năng Telegram: ' . ($this->package->name_locale ?? $this->package->name) . '. Hãy nhấn vào đây để nhận quà ngay!',
            'package_id' => $this->package->id,
            'icon' => 'fas fa-gift',
            'severity' => 'success',
            'url' => $url,
        ];
    }
}
