<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Teacher\src\Models\Teacher;

class TeacherAccountStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Teacher $teacher,
        protected string $changeType // 'locked', 'unlocked', 'ceased', 'restored'
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        switch ($this->changeType) {
            case 'locked':
                $reason = $this->teacher->lock_reason ?: 'Vi phạm chính sách hệ thống.';
                $titleTranslations = [
                    'vi' => 'Tài khoản giảng viên đã bị khóa',
                    'en' => 'Teacher account locked',
                    'ko' => '강사 계정이 잠겼습니다',
                    'ja' => '講師アカウントがロックされました',
                    'zh' => '讲师账户已锁定',
                ];
                $messageTranslations = [
                    'vi' => "Tài khoản giảng viên của bạn đã bị khóa. Lý do: {$reason}",
                    'en' => "Your teacher account has been locked. Reason: {$reason}",
                    'ko' => "강사 계정이 잠겼습니다. 사유: {$reason}",
                    'ja' => "講師アカウントがロックされました。理由: {$reason}",
                    'zh' => "您的讲师账户已被锁定。原因：{$reason}",
                ];
                $severity = 'danger';
                $icon = 'fas fa-user-lock';
                break;
            case 'unlocked':
                $titleTranslations = [
                    'vi' => 'Tài khoản giảng viên đã được mở khóa',
                    'en' => 'Teacher account unlocked',
                    'ko' => '강사 계정 잠금이 해제되었습니다',
                    'ja' => '講師アカウントのロックが解除されました',
                    'zh' => '讲师账户已解锁',
                ];
                $messageTranslations = [
                    'vi' => 'Chúc mừng! Tài khoản giảng viên của bạn đã được mở khóa thành công.',
                    'en' => 'Congratulations! Your teacher account has been successfully unlocked.',
                    'ko' => '축하합니다! 강사 계정 잠금이 성공적으로 해제되었습니다.',
                    'ja' => 'おめでとうございます！講師アカウントのロックが正常に解除されました。',
                    'zh' => '恭喜！您的讲师账户已成功解锁。',
                ];
                $severity = 'success';
                $icon = 'fas fa-user-check';
                break;
            case 'ceased':
                $titleTranslations = [
                    'vi' => 'Thông báo ngừng hợp tác',
                    'en' => 'Partnership ceased notification',
                    'ko' => '파트너십 중단 안내',
                    'ja' => '提携終了のお知らせ',
                    'zh' => '停止合作通知',
                ];
                $messageTranslations = [
                    'vi' => 'Hệ thống đã tạm ngừng hợp tác với tài khoản giảng viên của bạn.',
                    'en' => 'The system has ceased partnership with your teacher account.',
                    'ko' => '시스템이 귀하의 강사 계정과의 파트너십을 중단했습니다.',
                    'ja' => 'システムはあなたのアカウントとの提携を終了しました。',
                    'zh' => '系统已停止与您的讲师账户的合作。',
                ];
                $severity = 'warning';
                $icon = 'fas fa-user-slash';
                break;
            case 'restored':
                $titleTranslations = [
                    'vi' => 'Đã khôi phục hợp tác giảng viên',
                    'en' => 'Teacher partnership restored',
                    'ko' => '강사 파트너십이 복구되었습니다',
                    'ja' => '講師との提携が再開されました',
                    'zh' => '已恢复讲师合作',
                ];
                $messageTranslations = [
                    'vi' => 'Chào mừng bạn quay trở lại! Hợp tác giảng viên của bạn đã được khôi phục.',
                    'en' => 'Welcome back! Your teacher partnership has been successfully restored.',
                    'ko' => '다시 오신 것을 환영합니다! 강사 파트너십이 성공적으로 복구되었습니다.',
                    'ja' => 'おかえりなさい！講師との提携が正常に再開されました。',
                    'zh' => '欢迎回来！您的讲师合作已成功恢复。',
                ];
                $severity = 'success';
                $icon = 'fas fa-handshake';
                break;
            default:
                return [];
        }

        return [
            'type' => 'teacher.account.' . $this->changeType,
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teacher.dashboard.index'),
            'severity' => $severity,
            'icon' => $icon,
            'entity_type' => 'teacher',
            'entity_id' => $this->teacher->id,
            'meta' => [
                'change_type' => $this->changeType,
            ],
        ];
    }
}
