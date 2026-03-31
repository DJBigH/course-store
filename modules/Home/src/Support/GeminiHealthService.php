<?php

namespace Modules\Home\src\Support;

use App\Notifications\GeminiHealthNotification;
use Illuminate\Support\Carbon;
use Modules\Settings\src\Models\Setting;
use Modules\User\src\Models\User;

class GeminiHealthService
{
    private const FAILURE_THRESHOLD = 3;
    private const KEY_REASON = 'gemini_health_reason';
    private const KEY_FAILURE_COUNT = 'gemini_health_failure_count';
    private const KEY_LAST_AT = 'gemini_health_last_at';
    private const KEY_AUTO_DISABLED_AT = 'gemini_health_auto_disabled_at';
    private const KEY_NOTIFIED_STAGE = 'gemini_health_notified_stage';

    public function recordSuccess(): void
    {
        $snapshot = $this->snapshot();

        if (!$snapshot['has_issue'] && $snapshot['failure_count'] === 0) {
            return;
        }

        $this->put(self::KEY_REASON, '');
        $this->put(self::KEY_FAILURE_COUNT, '0');
        $this->put(self::KEY_LAST_AT, '');
        $this->put(self::KEY_AUTO_DISABLED_AT, '');
        $this->put(self::KEY_NOTIFIED_STAGE, '');

        activity_log(
            action: 'update_settings',
            subject: null,
            properties: [
                'changed_keys' => ['Trạng thái Gemini chatbot'],
                'old' => [
                    'Trạng thái Gemini chatbot' => $this->presentSnapshot($snapshot),
                ],
                'new' => [
                    'Trạng thái Gemini chatbot' => 'Đã hồi phục bình thường',
                ],
            ],
            logName: 'Gemini chatbot',
            description: 'Gemini chatbot đã hồi phục sau sự cố'
        );
    }

    public function recordFailure(string $reason): array
    {
        if (!$this->isTrackedReason($reason)) {
            return $this->snapshot();
        }

        $now = now();
        $snapshot = $this->snapshot();
        $count = $snapshot['failure_count'] + 1;

        $this->put(self::KEY_REASON, $reason);
        $this->put(self::KEY_FAILURE_COUNT, (string) $count);
        $this->put(self::KEY_LAST_AT, $now->toDateTimeString());

        $notifiedStage = $snapshot['notified_stage'];
        $statusLabel = $this->reasonLabel($reason);

        if ($count >= self::FAILURE_THRESHOLD) {
            $this->put('chatbot_enabled', '0');
            $this->put(self::KEY_AUTO_DISABLED_AT, $now->toDateTimeString());

            if ($notifiedStage !== 'disabled') {
                $this->notifyOperators(
                    'disabled',
                    $reason,
                    $count,
                    "Gemini đã bị tự động tắt sua {$count}/" . self::FAILURE_THRESHOLD . " lần lỗi {$statusLabel} liên tiếp."
                );

                activity_log(
                    action: 'update_settings',
                    subject: null,
                    properties: [
                        'changed_keys' => ['Bật chatbot Gemini', 'Trạng thái Gemini chatbot'],
                        'old' => [
                            'Bật chatbot Gemini' => '1',
                            'Trạng thái Gemini chatbot' => 'Đang hoạt động',
                        ],
                        'new' => [
                            'Bật chatbot Gemini' => '0',
                            'Trạng thái Gemini chatbot' => "Đã tự động tắt do {$statusLabel} {$count}/" . self::FAILURE_THRESHOLD,
                        ],
                    ],
                    logName: 'Gemini chatbot',
                    description: 'Tự động tắt Gemini chatbot do lỗi quota/auth lặp lại'
                );
            }

            $this->put(self::KEY_NOTIFIED_STAGE, 'disabled');

            return $this->snapshot();
        }

        if ($count >= self::FAILURE_THRESHOLD - 1 && $notifiedStage !== 'warning') {
            $this->notifyOperators(
                'warning',
                $reason,
                $count,
                "Gemini đang gặp {$statusLabel} {$count}/" . self::FAILURE_THRESHOLD . " lần. Nếu lặp lại thêm sẽ tự động tắt."
            );

            activity_log(
                action: 'update_settings',
                subject: null,
                properties: [
                    'changed_keys' => ['Trạng thái Gemini chatbot'],
                    'old' => [
                        'Trạng thái Gemini chatbot' => $this->presentSnapshot($snapshot),
                    ],
                    'new' => [
                        'Trạng thái Gemini chatbot' => "Cảnh báo {$statusLabel} {$count}/" . self::FAILURE_THRESHOLD,
                    ],
                ],
                logName: 'Gemini chatbot',
                description: 'Cảnh báo Gemini chatbot sắp bị tắt do lỗi quota/auth'
            );

            $this->put(self::KEY_NOTIFIED_STAGE, 'warning');
        }

        return $this->snapshot();
    }

    public function snapshot(): array
    {
        $reason = (string) setting(self::KEY_REASON, '');
        $count = max(0, (int) setting(self::KEY_FAILURE_COUNT, '0'));
        $lastAt = $this->parseDate(setting(self::KEY_LAST_AT, ''));
        $autoDisabledAt = $this->parseDate(setting(self::KEY_AUTO_DISABLED_AT, ''));
        $notifiedStage = (string) setting(self::KEY_NOTIFIED_STAGE, '');
        $enabled = (int) setting('chatbot_enabled', '1') === 1;
        $tracked = $this->isTrackedReason($reason);

        return [
            'reason' => $reason,
            'reason_label' => $this->reasonLabel($reason),
            'failure_count' => $count,
            'threshold' => self::FAILURE_THRESHOLD,
            'last_at' => $lastAt,
            'auto_disabled_at' => $autoDisabledAt,
            'notified_stage' => $notifiedStage,
            'enabled' => $enabled,
            'has_issue' => $tracked && $count > 0,
            'is_warning' => $tracked && $count > 0 && $count < self::FAILURE_THRESHOLD,
            'is_disabled' => $tracked && $count >= self::FAILURE_THRESHOLD,
        ];
    }

    private function notifyOperators(string $stage, string $reason, int $count, string $message): void
    {
        User::query()
            ->adminPanelUsers()
            ->with('group.permissions')
            ->get()
            ->filter(function (User $user) {
                return $user->hasPermission('settings.update')
                    || $user->hasPermission('settings.logs')
                    || $user->hasPermission('chatbot.logs')
                    || $user->isAdmin();
            })
            ->unique('id')
            ->each(function (User $admin) use ($stage, $reason, $count, $message) {
                $admin->notify(new GeminiHealthNotification(
                    stage: $stage,
                    reason: $reason,
                    failureCount: $count,
                    threshold: self::FAILURE_THRESHOLD,
                    message: $message,
                ));
            });
    }

    private function put(string $key, string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function isTrackedReason(string $reason): bool
    {
        return in_array($reason, ['quota_exceeded', 'auth_error'], true);
    }

    private function reasonLabel(string $reason): string
    {
        return match ($reason) {
            'quota_exceeded' => 'quota',
            'auth_error' => 'xác thực API',
            default => 'sự cố',
        };
    }

    private function presentSnapshot(array $snapshot): string
    {
        if (!$snapshot['has_issue']) {
            return 'Đang hoạt động';
        }

        return sprintf(
            '%s %d/%d',
            $snapshot['reason_label'],
            $snapshot['failure_count'],
            $snapshot['threshold']
        );
    }
}
