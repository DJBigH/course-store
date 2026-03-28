<?php

namespace App\Support;

use App\Mail\AdminTwoFactorCodeMail;
use App\Models\AdminSessionTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\User\src\Models\User;

class AdminSecurityService
{
    public function maxDevices(): int
    {
        return 2;
    }

    public function challengeLifetime(): int
    {
        return 600;
    }

    public function resendCooldown(): int
    {
        return 60;
    }

    public function issueChallenge(User $user, string $locale, bool $force = false): bool
    {
        if (!$force && $this->canReuseCurrentChallenge($user)) {
            return false;
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'two_factor_email_code' => Hash::make($code),
            'two_factor_email_code_expires_at' => now()->addSeconds($this->challengeLifetime()),
            'two_factor_email_code_sent_at' => now(),
        ])->save();

        Mail::to($user->email)
            ->locale($locale)
            ->queue(new AdminTwoFactorCodeMail($user, $code, $locale));

        $snapshot = $this->buildLoginSnapshot(request());
        $this->createAdminLog(
            $user,
            'admin_security',
            'two_factor_code_sent',
            'Da gui ma xac thuc dang nhap quan tri qua email.',
            [
                'channel' => 'email',
                'expires_in_seconds' => $this->challengeLifetime(),
                'ip' => $snapshot['ip'],
                'browser' => $snapshot['browser'],
                'platform' => $snapshot['platform'],
                'device' => $snapshot['device'],
            ]
        );

        return true;
    }

    public function verifyChallenge(User $user, string $code): string
    {
        if (!$user->two_factor_email_code || !$user->two_factor_email_code_expires_at) {
            return 'missing';
        }

        if ($user->two_factor_email_code_expires_at->isPast()) {
            return 'expired';
        }

        if (!Hash::check($code, $user->two_factor_email_code)) {
            return 'invalid';
        }

        return 'verified';
    }

    public function clearChallenge(User $user): void
    {
        $user->forceFill([
            'two_factor_email_code' => null,
            'two_factor_email_code_expires_at' => null,
            'two_factor_email_code_sent_at' => null,
        ])->save();
    }

    public function canResend(User $user): bool
    {
        if (!$user->two_factor_email_code_sent_at) {
            return true;
        }

        return $user->two_factor_email_code_sent_at->diffInSeconds(now()) >= $this->resendCooldown();
    }

    public function secondsUntilResend(User $user): int
    {
        if (!$user->two_factor_email_code_sent_at) {
            return 0;
        }

        $remaining = $this->resendCooldown() - $user->two_factor_email_code_sent_at->diffInSeconds(now());

        return max($remaining, 0);
    }

    public function finalizeSuccessfulLogin(User $user, Request $request): ?string
    {
        $snapshot = $this->buildLoginSnapshot($request);
        $this->pruneAdminTrackers($user);

        if ($this->supportsTrackedSessions()) {
            $activeSessions = $this->activeTrackers($user->id, $request->session()->getId());

            if ($activeSessions >= $this->maxDevices()) {
                return 'Tai khoan quan tri nay da dang nhap toi da 2 thiet bi cung luc.';
            }

            AdminSessionTracker::updateOrCreate(
                ['session_id' => $request->session()->getId()],
                [
                    'user_id' => $user->id,
                    'ip_address' => $snapshot['ip'],
                    'user_agent' => $snapshot['user_agent'],
                    'browser' => $snapshot['browser'],
                    'platform' => $snapshot['platform'],
                    'device' => $snapshot['device'],
                    'last_activity' => now()->timestamp,
                ]
            );
        }

        $user->forceFill([
            'last_login_at' => $snapshot['logged_at'],
            'last_login_ip' => $snapshot['ip'],
            'last_login_user_agent' => $snapshot['user_agent'],
            'last_login_browser' => $snapshot['browser'],
            'last_login_platform' => $snapshot['platform'],
            'last_login_device' => $snapshot['device'],
        ])->save();

        $this->createAdminLog(
            $user,
            'auth_login',
            'login',
            'Dang nhap quan tri thanh cong.',
            [
                'ip' => $snapshot['ip'],
                'browser' => $snapshot['browser'],
                'platform' => $snapshot['platform'],
                'device' => $snapshot['device'],
            ]
        );

        return null;
    }

    public function touchCurrentSession(User $user, Request $request): void
    {
        if (!$this->supportsTrackedSessions()) {
            return;
        }

        AdminSessionTracker::query()
            ->where('user_id', $user->id)
            ->where('session_id', $request->session()->getId())
            ->update(['last_activity' => now()->timestamp]);
    }

    public function forgetCurrentSession(User $user, Request $request): void
    {
        if (!$this->supportsTrackedSessions()) {
            return;
        }

        AdminSessionTracker::query()
            ->where('user_id', $user->id)
            ->where('session_id', $request->session()->getId())
            ->delete();
    }

    public function logoutAllSessions(User $user): void
    {
        if (!$this->supportsTrackedSessions()) {
            return;
        }

        $sessionIds = AdminSessionTracker::query()
            ->where('user_id', $user->id)
            ->pluck('session_id')
            ->filter()
            ->all();

        if (!empty($sessionIds) && Schema::hasTable(config('session.table', 'sessions'))) {
            DB::table(config('session.table', 'sessions'))
                ->whereIn('id', $sessionIds)
                ->delete();
        }

        AdminSessionTracker::query()
            ->where('user_id', $user->id)
            ->delete();
    }

    public function activeSessionCount(User $user): int
    {
        if (!$this->supportsTrackedSessions()) {
            return 0;
        }

        $this->pruneAdminTrackers($user);

        return $this->activeTrackers($user->id);
    }

    protected function activeTrackers(int $userId, ?string $exceptSessionId = null): int
    {
        $query = AdminSessionTracker::query()->where('user_id', $userId);

        if ($exceptSessionId) {
            $query->where('session_id', '!=', $exceptSessionId);
        }

        return $query->count();
    }

    protected function pruneAdminTrackers(User $user): void
    {
        if (!$this->supportsTrackedSessions()) {
            return;
        }

        $validSessionIds = DB::table(config('session.table', 'sessions'))
            ->pluck('id')
            ->all();

        AdminSessionTracker::query()
            ->where('user_id', $user->id)
            ->when(!empty($validSessionIds), function ($query) use ($validSessionIds) {
                $query->whereNotIn('session_id', $validSessionIds);
            }, function ($query) {
                $query->whereNotNull('session_id');
            })
            ->delete();
    }

    protected function canReuseCurrentChallenge(User $user): bool
    {
        return !empty($user->two_factor_email_code)
            && !empty($user->two_factor_email_code_expires_at)
            && $user->two_factor_email_code_expires_at->isFuture()
            && !$this->canResend($user);
    }

    protected function supportsTrackedSessions(): bool
    {
        return config('session.driver') === 'database'
            && Schema::hasTable(config('session.table', 'sessions'))
            && Schema::hasTable('admin_session_trackers');
    }

    protected function buildLoginSnapshot(Request $request): array
    {
        $userAgent = substr((string) $request->userAgent(), 0, 1000);

        return [
            'logged_at' => now(),
            'ip' => $this->resolveIpAddress($request),
            'user_agent' => $userAgent,
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'device' => $this->detectDevice($userAgent),
        ];
    }

    protected function resolveIpAddress(Request $request): string
    {
        $forwardedFor = (string) $request->header('X-Forwarded-For', '');

        if ($forwardedFor !== '') {
            $parts = array_filter(array_map('trim', explode(',', $forwardedFor)));

            if (!empty($parts)) {
                return (string) reset($parts);
            }
        }

        return (string) $request->ip();
    }

    protected function detectBrowser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Microsoft Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome/') && !str_contains($userAgent, 'Edg/') => 'Google Chrome',
            str_contains($userAgent, 'Firefox/') => 'Mozilla Firefox',
            str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome/') => 'Safari',
            str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident/') => 'Internet Explorer',
            default => 'Unknown browser',
        };
    }

    protected function detectPlatform(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS') => 'macOS',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown platform',
        };
    }

    protected function detectDevice(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet') => 'Tablet',
            str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone') => 'Mobile',
            default => 'Desktop',
        };
    }

    protected function createAdminLog(User $user, string $logName, string $action, string $description, array $properties = []): void
    {
        ActiveLog::create([
            'log_name' => $logName,
            'action' => $action,
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => $user->name . ' (Admin)',
            'causer_id' => $user->id,
            'properties' => $properties,
            'description' => $description,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
