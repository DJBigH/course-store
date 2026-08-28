<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;

class TeacherPayoutStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected object $payout
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $status = (string) ($this->payout->status ?? 'requested');
        $amount = money((float) ($this->payout->amount ?? 0));

        $titleTranslations = [
            'vi' => 'Cập nhật yêu cầu rút tiền',
            'en' => 'Payout request updated',
            'ko' => '출금 요청 업데이트',
            'ja' => '出金リクエストの更新',
            'zh' => '提现请求更新',
        ];

        $messageTranslations = [
            'vi' => 'Yêu cầu rút tiền #' . $this->payout->id . ' đã chuyển sang trạng thái ' . $status . ' với số tiền ' . $amount . '.',
            'en' => 'Your payout request #' . $this->payout->id . ' is now ' . $status . ' for ' . $amount . '.',
            'ko' => '출금 요청 #' . $this->payout->id . ' 상태가 ' . $status . '(으)로 변경되었습니다 (금액: ' . $amount . ')',
            'ja' => '出金リクエスト #' . $this->payout->id . ' のステータスが ' . $status . ' に更新されました (金額: ' . $amount . ')',
            'zh' => '您的提现请求 #' . $this->payout->id . ' 状态已更新为 ' . $status . ' (金额: ' . $amount . ')',
        ];

        return [
            'type' => 'teacher.payout.status',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teacher.dashboard.payouts.index', ['locale' => app()->getLocale()]),
            'severity' => in_array($status, ['paid'], true) ? 'success' : (in_array($status, ['rejected'], true) ? 'warning' : 'info'),
            'icon' => 'fas fa-money-check-dollar',
            'entity_type' => 'teacher_payout',
            'entity_id' => $this->payout->id,
            'meta' => [
                'payout_id' => $this->payout->id,
                'status' => $status,
                'amount' => (float) ($this->payout->amount ?? 0),
                'admin_note' => $this->payout->admin_note ?? null,
            ],
        ];
    }
}
