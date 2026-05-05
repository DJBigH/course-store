<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Finances\src\Models\PayoutAccountChangeRequest;

class PayoutAccountStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected PayoutAccountChangeRequest $request
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $status = $this->request->status;
        $isApproved = $status === 'approved';

        if ($isApproved) {
            $titleTranslations = [
                'vi' => 'Cập nhật tài khoản thanh toán thành công',
                'en' => 'Payout account updated successfully',
                'ko' => '결제 계좌 정보 업데이트 완료',
                'ja' => '支払い口座情報の更新が完了しました',
                'zh' => '收款账户更新成功',
            ];
            $messageTranslations = [
                'vi' => 'Yêu cầu thay đổi tài khoản ngân hàng của bạn đã được phê duyệt.',
                'en' => 'Your bank account change request has been approved.',
                'ko' => '은행 계좌 변경 요청이 승인되었습니다.',
                'ja' => '銀行口座の変更リクエストが承認されました。',
                'zh' => '您的银行账户变更请求已获批准。',
            ];
            $severity = 'success';
        } else {
            $reason = $this->request->admin_note ?: 'Vui lòng liên hệ Admin để biết thêm chi tiết.';
            $titleTranslations = [
                'vi' => 'Yêu cầu đổi tài khoản bị từ chối',
                'en' => 'Payout account change rejected',
                'ko' => '계좌 변경 요청 반려',
                'ja' => '口座変更リクエストが却下されました',
                'zh' => '账户变更请求已拒绝',
            ];
            $messageTranslations = [
                'vi' => "Yêu cầu thay đổi tài khoản ngân hàng của bạn không được phê duyệt. Lý do: {$reason}",
                'en' => "Your bank account change request was not approved. Reason: {$reason}",
                'ko' => "은행 계좌 변경 요청이 반려되었습니다. 사유: {$reason}",
                'ja' => "銀行口座の変更リクエストは承認されませんでした。理由: {$reason}",
                'zh' => "您的银行账户变更请求未获批准。原因：{$reason}",
            ];
            $severity = 'warning';
        }

        return [
            'type' => 'teacher.payout_account.' . $status,
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teacher.dashboard.payouts.index'),
            'severity' => $severity,
            'icon' => $isApproved ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle',
            'entity_type' => 'payout_account_change',
            'entity_id' => $this->request->id,
            'meta' => [
                'status' => $status,
                'admin_note' => $this->request->admin_note,
            ],
        ];
    }
}
