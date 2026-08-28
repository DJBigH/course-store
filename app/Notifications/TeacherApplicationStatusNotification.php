<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Teacher\src\Models\TeacherApplication;

class TeacherApplicationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected TeacherApplication $application
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $status = $this->application->status;
        $isApproved = $status === 'approved';

        if ($isApproved) {
            $titleTranslations = [
                'vi' => 'Chúc mừng! Hồ sơ giảng viên đã được duyệt',
                'en' => 'Congratulations! Your teacher application is approved',
                'ko' => '축하합니다! 강사 신청이 승인되었습니다',
                'ja' => 'おめでとうございます！講師への応募が承認されました',
                'zh' => '恭喜！您的讲师申请已获批准',
            ];
            $messageTranslations = [
                'vi' => 'Chào mừng bạn gia nhập đội ngũ giảng viên. Bạn có thể bắt đầu tạo khóa học ngay bây giờ.',
                'en' => 'Welcome to our teaching team. You can start creating your courses now.',
                'ko' => '강사 팀에 오신 것을 환영합니다. 지금 바로 강좌를 만드실 수 있습니다.',
                'ja' => '講師チームへようこそ。今すぐコースの作成を開始できます。',
                'zh' => '欢迎加入我们的教学团队。您现在可以开始创建课程了。',
            ];
            $url = route('teacher.dashboard.index');
            $severity = 'success';
        } else {
            $reason = $this->application->admin_note ?: 'Vui lòng kiểm tra email để biết thêm chi tiết.';
            $titleTranslations = [
                'vi' => 'Thông báo kết quả hồ sơ giảng viên',
                'en' => 'Teacher application status update',
                'ko' => '강사 신청 결과 안내',
                'ja' => '講師応募結果のお知らせ',
                'zh' => '讲师申请结果通知',
            ];
            $messageTranslations = [
                'vi' => "Rất tiếc, hồ sơ của bạn chưa được duyệt. Lý do: {$reason}",
                'en' => "We regret to inform you that your application was not approved. Reason: {$reason}",
                'ko' => "안타깝게도 신청이 승인되지 않았습니다. 사유: {$reason}",
                'ja' => "残念ながら、応募は承認されませんでした。理由: {$reason}",
                'zh' => "很遗憾，您的申请未获批准。原因：{$reason}",
            ];
            $url = route('teacher.account.status', ['locale' => app()->getLocale()]);
            $severity = 'warning';
        }

        return [
            'type' => 'student.teacher_application.' . $status,
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => $url,
            'severity' => $severity,
            'icon' => $isApproved ? 'fas fa-user-check' : 'fas fa-user-times',
            'entity_type' => 'teacher_application',
            'entity_id' => $this->application->id,
            'meta' => [
                'status' => $status,
                'note' => $this->application->admin_note,
            ],
        ];
    }
}
