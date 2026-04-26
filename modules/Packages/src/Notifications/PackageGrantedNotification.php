<?php

namespace Modules\Packages\src\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Packages\src\Models\Package;
use Modules\Teacher\src\Models\Teacher;

class PackageGrantedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Teacher $teacher,
        public readonly Package $package,
        public readonly string  $claimUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Dữ liệu lưu vào bảng notifications (database channel).
     * Cấu trúc tương thích với notificationText() helper hiện có.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title'    => '🎁 Bạn nhận được gói đặc quyền!',
            'message'  => "Admin vừa tặng bạn gói **{$this->package->name_locale}**. Nhấn vào đây để nhận ngay.",
            'icon'     => 'fas fa-gift',
            'severity' => 'warning',
            'redirect' => $this->claimUrl,
            'action_label' => 'Nhận gói ngay',
        ];
    }
}
