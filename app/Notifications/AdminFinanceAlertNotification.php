<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminFinanceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected object $subject,
        protected string $alertType = 'payout_request' // 'payout_request' or 'account_change'
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $teacherName = $this->subject->teacher->name_locale ?? $this->subject->teacher->name ?? 'Giảng viên';
        
        if ($this->alertType === 'payout_request') {
            $amount = money((float) ($this->subject->amount ?? 0));
            $titleTranslations = [
                'vi' => 'Yêu cầu rút tiền mới',
                'en' => 'New payout request',
                'ko' => '새로운 출금 요청',
                'ja' => '新しい出金リクエスト',
                'zh' => '新提现请求',
            ];
            $messageTranslations = [
                'vi' => "Giảng viên {$teacherName} vừa gửi yêu cầu rút tiền số tiền {$amount}.",
                'en' => "Teacher {$teacherName} has submitted a payout request for {$amount}.",
                'ko' => "{$teacherName} 강사가 {$amount} 출금 요청을 제출했습니다.",
                'ja' => "講師 {$teacherName} さんが {$amount} の出金リクエストを送信しました。",
                'zh' => "讲师 {$teacherName} 提交了 {$amount} 的提现请求。",
            ];
            $type = 'admin.payout.request';
        } else {
            $titleTranslations = [
                'vi' => 'Yêu cầu đổi tài khoản ngân hàng',
                'en' => 'New bank account change request',
                'ko' => '새로운 은행 계좌 변경 요청',
                'ja' => '新しい銀行口座変更リクエスト',
                'zh' => '新银行账户变更请求',
            ];
            $messageTranslations = [
                'vi' => "Giảng viên {$teacherName} yêu cầu thay đổi thông tin tài khoản rút tiền.",
                'en' => "Teacher {$teacherName} requested to change their payout account details.",
                'ko' => "{$teacherName} 강사가 출금 계좌 정보 변경을 요청했습니다.",
                'ja' => "講師 {$teacherName} さんが出金口座情報の変更をリクエストしました。",
                'zh' => "讲师 {$teacherName} 请求变更提现账户信息。",
            ];
            $type = 'admin.payout_account.change';
        }

        return [
            'type' => $type,
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('finances.payouts'),
            'severity' => 'warning',
            'icon' => 'fas fa-hand-holding-dollar',
            'entity_type' => $this->alertType === 'payout_request' ? 'payout_request' : 'payout_account_change',
            'entity_id' => $this->subject->id,
            'meta' => [
                'teacher_name' => $teacherName,
                'payout_id' => $this->subject->id,
            ],
        ];
    }
}
