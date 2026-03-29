<?php

namespace App\Support;

use App\Mail\StudentTwoFactorCodeMail;
use App\Mail\StudentTwoFactorStatusMail;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Students\src\Models\Student;

class StudentTwoFactorService
{
    public const PURPOSE_LOGIN = 'login';
    public const PURPOSE_STEP_UP = 'step_up';
    public const PURPOSE_ENABLE = 'enable';
    public const PURPOSE_DISABLE = 'disable';

    public function issueChallenge(Student $student, string $purpose, string $locale, bool $force = false): bool
    {
        if (!$force && $this->canReuseCurrentChallenge($student, $purpose)) {
            return false;
        }

        $code = (string) random_int(100000, 999999);

        $student->forceFill([
            'two_factor_email_code' => Hash::make($code),
            'two_factor_email_purpose' => $purpose,
            'two_factor_email_code_expires_at' => now()->addSeconds($this->challengeLifetime()),
            'two_factor_email_code_sent_at' => now(),
        ])->save();

        Mail::to($student->email)
            ->locale($locale)
            ->queue(new StudentTwoFactorCodeMail($student, $code, $purpose, $locale));

        $snapshot = $this->buildLoginSnapshot(request());
        $this->createStudentLog(
            $student,
            'student_security',
            'two_factor_code_sent',
            __('students::clients/account.activity_log.two_factor_code_sent_desc'),
            [
                'purpose' => __($this->purposeLabelKey($purpose)),
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

    public function verifyChallenge(Student $student, string $code): string
    {
        if (!$student->two_factor_email_code || !$student->two_factor_email_code_expires_at) {
            return 'missing';
        }

        if ($student->two_factor_email_code_expires_at->isPast()) {
            return 'expired';
        }

        if (!Hash::check($code, $student->two_factor_email_code)) {
            return 'invalid';
        }

        return 'verified';
    }

    public function clearChallenge(Student $student): void
    {
        $student->forceFill([
            'two_factor_email_code' => null,
            'two_factor_email_purpose' => null,
            'two_factor_email_code_expires_at' => null,
            'two_factor_email_code_sent_at' => null,
        ])->save();
    }

    public function markRecentVerification(Request $request): void
    {
        $request->session()->put('students.two_factor.verified_at', now()->timestamp);
    }

    public function forgetRecentVerification(Request $request): void
    {
        $request->session()->forget('students.two_factor.verified_at');
    }

    public function hasFreshVerification(Request $request): bool
    {
        $verifiedAt = $request->session()->get('students.two_factor.verified_at');

        if (!$verifiedAt) {
            return false;
        }

        return now()->diffInSeconds(now()->setTimestamp((int) $verifiedAt)) <= $this->reauthWindow();
    }

    public function challengeLifetime(): int
    {
        return max(60, (int) setting('student_two_factor_code_expire', config('auth.student_two_factor_code_expire', 600)));
    }

    public function resendCooldown(): int
    {
        return max(10, (int) setting('student_two_factor_resend_cooldown', config('auth.student_two_factor_resend_cooldown', 60)));
    }

    public function reauthWindow(): int
    {
        return max(60, (int) setting('student_two_factor_timeout', config('auth.student_two_factor_timeout', 600)));
    }

    public function purposeLabelKey(string $purpose): string
    {
        return match ($purpose) {
            self::PURPOSE_ENABLE => 'students::clients/account.two_factor.purpose_enable',
            self::PURPOSE_DISABLE => 'students::clients/account.two_factor.purpose_disable',
            self::PURPOSE_STEP_UP => 'students::clients/account.two_factor.purpose_step_up',
            default => 'students::clients/account.two_factor.purpose_login',
        };
    }

    public function canResend(Student $student): bool
    {
        if (!$student->two_factor_email_code_sent_at) {
            return true;
        }

        return $student->two_factor_email_code_sent_at->diffInSeconds(now()) >= $this->resendCooldown();
    }

    public function secondsUntilResend(Student $student): int
    {
        if (!$student->two_factor_email_code_sent_at) {
            return 0;
        }

        $remaining = $this->resendCooldown() - $student->two_factor_email_code_sent_at->diffInSeconds(now());

        return max($remaining, 0);
    }

    public function handleSuccessfulLogin(Student $student, Request $request, ?string $locale = null): void
    {
        $current = $this->buildLoginSnapshot($request);
        $previous = $this->previousLoginSnapshot($student);
        $shouldNotify = $this->shouldNotifyAboutUnusualLogin($previous, $current);

        $student->forceFill([
            'last_login_at' => $current['logged_at'],
            'last_login_ip' => $current['ip'],
            'last_login_user_agent' => $current['user_agent'],
            'last_login_browser' => $current['browser'],
            'last_login_platform' => $current['platform'],
            'last_login_device' => $current['device'],
        ])->save();

        $this->createStudentLog(
            $student,
            'auth_login',
            'login',
            $shouldNotify
                ? __('students::clients/account.activity_log.unusual_login_desc')
                : __('students::clients/account.activity_log.login_desc'),
            [
                'ip' => $current['ip'],
                'browser' => $current['browser'],
                'platform' => $current['platform'],
                'device' => $current['device'],
                'previous_ip' => $previous['ip'] ?? null,
                'previous_browser' => $previous['browser'] ?? null,
                'previous_platform' => $previous['platform'] ?? null,
                'previous_device' => $previous['device'] ?? null,
                'is_unusual' => $shouldNotify,
            ]
        );

        if (!$shouldNotify) {
            return;
        }

        Mail::to($student->email)
            ->locale($locale ?: $student->preferredLocale())
            ->queue(new StudentTwoFactorStatusMail(
                $student,
                'unusual_login',
                $locale ?: $student->preferredLocale(),
                [
                    'currentLogin' => $current,
                    'previousLogin' => $previous,
                ]
            ));
    }

    protected function canReuseCurrentChallenge(Student $student, string $purpose): bool
    {
        if (
            !$student->two_factor_email_code ||
            !$student->two_factor_email_code_expires_at instanceof CarbonInterface ||
            !$student->two_factor_email_purpose
        ) {
            return false;
        }

        return $student->two_factor_email_purpose === $purpose
            && $student->two_factor_email_code_expires_at->isFuture()
            && !$this->canResend($student);
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

    protected function previousLoginSnapshot(Student $student): ?array
    {
        if (!$student->last_login_at) {
            return null;
        }

        return [
            'logged_at' => $student->last_login_at,
            'ip' => $student->last_login_ip,
            'user_agent' => $student->last_login_user_agent,
            'browser' => $student->last_login_browser ?: $this->detectBrowser((string) $student->last_login_user_agent),
            'platform' => $student->last_login_platform ?: $this->detectPlatform((string) $student->last_login_user_agent),
            'device' => $student->last_login_device ?: $this->detectDevice((string) $student->last_login_user_agent),
        ];
    }

    protected function shouldNotifyAboutUnusualLogin(?array $previous, array $current): bool
    {
        if (!$previous) {
            return false;
        }

        $ipChanged = !empty($previous['ip']) && $previous['ip'] !== $current['ip'];
        $deviceChanged = $this->loginSignature($previous) !== $this->loginSignature($current);

        return $ipChanged || $deviceChanged;
    }

    protected function loginSignature(array $snapshot): string
    {
        return implode('|', [
            strtolower((string) ($snapshot['browser'] ?? '')),
            strtolower((string) ($snapshot['platform'] ?? '')),
            strtolower((string) ($snapshot['device'] ?? '')),
        ]);
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

    protected function createStudentLog(
        Student $student,
        string $logName,
        string $action,
        string $description,
        array $properties = []
    ): void {
        ActiveLog::create([
            'log_name' => $logName,
            'action' => $action,
            'subject_type' => Student::class,
            'subject_id' => $student->id,
            'causer_type' => $student->name . ' (Học viên)',
            'causer_id' => $student->id,
            'properties' => $properties,
            'description' => $description,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
