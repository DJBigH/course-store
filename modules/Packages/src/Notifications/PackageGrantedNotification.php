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
        $packageName = $this->package->name_locale ?: $this->package->name;
        
        $titleTranslations = [
            'vi' => '🎁 Bạn nhận được gói đặc quyền!',
            'en' => '🎁 You have received a special package!',
            'ko' => '🎁 특별 혜택 패키지 지급!',
            'ja' => '🎁 特典パッケージの付与！',
            'zh' => '🎁 获得特权礼包！',
        ];

        $messageTranslations = [
            'vi' => "Admin vừa tặng bạn gói **{$packageName}**. Nhấn vào đây để nhận ngay.",
            'en' => "Admin has gifted you the **{$packageName}** package. Click here to claim it now.",
            'ko' => "관리자가 **{$packageName}** 패키지를 선물했습니다. 지금 바로 확인해보세요.",
            'ja' => "管理者から **{$packageName}** パッケージが届きました。今すぐ受け取ってください。",
            'zh' => "管理员向您赠送了 **{$packageName}** 礼包。点击立即领取。",
        ];

        return [
            'type' => 'teacher.package_grant',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => $this->claimUrl,
            'icon' => 'fas fa-gift',
            'severity' => 'warning',
            'entity_type' => 'teacher_package',
            'entity_id' => $this->package->id,
            'meta' => [
                'package_id' => $this->package->id,
                'package_name' => $packageName,
                'action_label' => 'Nhận gói ngay',
            ],
        ];
    }
}
