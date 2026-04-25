<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeacherPayoutStatusNotification extends Notification
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

        return [
            'type' => 'teacher.payout.status',
            'title' => 'Cap nhat yeu cau rut tien',
            'title_translations' => [
                'vi' => 'Cap nhat yeu cau rut tien',
                'en' => 'Payout request updated',
            ],
            'message' => 'Yeu cau rut tien #' . $this->payout->id . ' da chuyen sang trang thai ' . $status . ' voi so tien ' . $amount . '.',
            'message_translations' => [
                'vi' => 'Yeu cau rut tien #' . $this->payout->id . ' da chuyen sang trang thai ' . $status . ' voi so tien ' . $amount . '.',
                'en' => 'Your payout request #' . $this->payout->id . ' is now ' . $status . ' for ' . $amount . '.',
            ],
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
