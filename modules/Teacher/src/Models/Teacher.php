<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Students\src\Models\Student;

class Teacher extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_CEASED = 'ceased';

    public const BADGE_PRESETS = [
        'verified' => ['label' => 'Verified Teacher', 'icon' => 'check', 'tone' => 'blue'],
        'premium' => ['label' => 'Premium Teacher', 'icon' => 'star', 'tone' => 'gold'],
        'top_seller' => ['label' => 'Top Seller', 'icon' => 'chart', 'tone' => 'emerald'],
        'expert' => ['label' => 'Expert Mentor', 'icon' => 'spark', 'tone' => 'violet'],
        'featured' => ['label' => 'Featured Teacher', 'icon' => 'bolt', 'tone' => 'rose'],
        'custom' => ['label' => 'Custom Badge', 'icon' => 'bookmark', 'tone' => 'slate'],
    ];

    protected $table = 'teacher';

    protected $fillable = [
        'id',
        'name',
        'name_en',
        'name_ko',
        'name_ja',
        'name_zh',
        'slug',
        'slug_en',
        'slug_ko',
        'slug_ja',
        'slug_zh',
        'description',
        'description_en',
        'description_ko',
        'description_ja',
        'description_zh',
        'exp',
        'image',
        'student_id',
        'application_id',
        'status',
        'is_verified_badge',
        'is_premium_badge',
        'badge_key',
        'badge_label',
        'badge_tone',
        'commission_rate',
        'approved_at',
        'approved_by',
        'package_started_at',
        'package_expires_at',
        'last_active_at',
        'inactive_teacher_notified_at',
        'inactive_admin_notified_at',
        'is_locked',
        'lock_reason',
        'locked_at',
        'locked_by',
        'telegram_chat_id',
        'is_telegram_notifications_enabled',
        'telegram_feature_expires_at',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_locked'                  => 'boolean',
        'is_verified_badge'          => 'boolean',
        'is_premium_badge'           => 'boolean',
        'locked_at'                  => 'datetime',
        'approved_at'                => 'datetime',
        'package_started_at'         => 'datetime',
        'package_expires_at'         => 'datetime',
        'last_active_at'             => 'datetime',
        'inactive_teacher_notified_at' => 'datetime',
        'inactive_admin_notified_at' => 'datetime',
        'is_telegram_notifications_enabled' => 'boolean',
        'telegram_feature_expires_at' => 'datetime',
    ];

    public function hasTelegramFeature(): bool
    {
        if (!$this->telegram_feature_expires_at) {
            return false;
        }
        return $this->telegram_feature_expires_at->isFuture();
    }

    public function getTelegramPackageStatus(): array
    {
        if (!$this->telegram_feature_expires_at) {
            return ['status' => 'inactive', 'expires_at' => null];
        }
        
        $isFuture = $this->telegram_feature_expires_at->isFuture();
        return [
            'status' => $isFuture ? 'active' : 'expired',
            'expires_at' => $this->telegram_feature_expires_at
        ];
    }

    public function lockedByAdmin()
    {
        return $this->belongsTo(\Modules\User\src\Models\User::class, 'locked_by');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function application()
    {
        return $this->belongsTo(TeacherApplication::class, 'application_id', 'id');
    }

    public function currentPackage(): ?\Modules\Packages\src\Models\Package
    {
        $this->loadMissing('application.package');

        return $this->application?->package;
    }

    public function packageHasFeature(string $feature): bool
    {
        return (bool) $this->currentPackage()?->hasFeature($feature);
    }

    public function getFeatureState(string $feature): array
    {
        $package = $this->currentPackage();

        $isMaintenance = $package ? $package->isFeatureInMaintenance($feature) : false;
        $hasFeature = $package ? $package->hasFeature($feature) : false;

        return [
            'is_maintenance' => $isMaintenance,
            'is_locked' => ! $hasFeature && ! $isMaintenance,
            'can_use' => $hasFeature && ! $isMaintenance,
        ];
    }

    public function payoutRequests()
    {
        return $this->hasMany(TeacherPayoutRequest::class, 'teacher_id', 'id');
    }

    public function payoutAccounts()
    {
        return $this->hasMany(TeacherPayoutAccount::class, 'teacher_id', 'id');
    }

    public function payoutAccountChangeRequests()
    {
        return $this->hasMany(TeacherPayoutAccountChangeRequest::class, 'teacher_id', 'id');
    }

    public function cancellationRequests()
    {
        return $this->hasMany(TeacherCancellationRequest::class, 'teacher_id', 'id');
    }

    public function latestCancellationRequest()
    {
        return $this->hasOne(TeacherCancellationRequest::class, 'teacher_id', 'id')->latest();
    }

    public function studentNotes()
    {
        return $this->hasMany(\Modules\Students\src\Models\StudentNote::class, 'teacher_id', 'id');
    }

    public function courses()
    {
        return $this->hasMany(\Modules\Courses\src\Models\Courses::class, 'teacher_id', 'id');
    }

    public function bundles()
    {
        return $this->hasMany(\Modules\Courses\src\Models\CourseBundle::class, 'teacher_id', 'id');
    }

    public function badges()
    {
        return $this->belongsToMany(TeacherBadge::class, 'teacher_has_badges', 'teacher_id', 'badge_id');
    }

    public function affiliateLinks()
    {
        return $this->hasMany(\Modules\Finances\src\Models\AffiliateLink::class, 'teacher_id', 'id');
    }

    public function ratings()
    {
        return $this->hasMany(\Modules\Students\src\Models\TeacherRating::class, 'teacher_id', 'id');
    }

    public function getDescriptionLocaleAttribute(): string
    {
        if (app()->getLocale() === 'zh') {
            return $this->description_zh ?: $this->description ?: $this->description_en ?: $this->description_ko ?: $this->description_ja ?: '';
        }

        if (app()->getLocale() === 'ja') {
            return $this->description_ja ?: $this->description ?: $this->description_en ?: $this->description_ko ?: $this->description_zh ?: '';
        }

        if (app()->getLocale() === 'ko') {
            return $this->description_ko ?: $this->description ?: $this->description_en ?: $this->description_ja ?: $this->description_zh ?: '';
        }

        if (app()->getLocale() === 'en') {
            return $this->description_en ?: $this->description ?: $this->description_ko ?: $this->description_ja ?: $this->description_zh ?: '';
        }

        return $this->description ?: $this->description_en ?: $this->description_ko ?: $this->description_ja ?: $this->description_zh ?: '';
    }

    public function getNameLocaleAttribute(): string
    {
        if (app()->getLocale() === 'zh') {
            return $this->name_zh ?: $this->name ?: $this->name_en ?: $this->name_ko ?: $this->name_ja ?: '';
        }

        if (app()->getLocale() === 'ja') {
            return $this->name_ja ?: $this->name ?: $this->name_en ?: $this->name_ko ?: $this->name_zh ?: '';
        }

        if (app()->getLocale() === 'ko') {
            return $this->name_ko ?: $this->name ?: $this->name_en ?: $this->name_ja ?: $this->name_zh ?: '';
        }

        if (app()->getLocale() === 'en') {
            return $this->name_en ?: $this->name ?: $this->name_ko ?: $this->name_ja ?: $this->name_zh ?: '';
        }

        return $this->name ?: $this->name_en ?: $this->name_ko ?: $this->name_ja ?: $this->name_zh ?: '';
    }

    public function getSlugLocaleAttribute(): string
    {
        if (app()->getLocale() === 'zh') {
            return $this->slug_zh ?: $this->slug ?: $this->slug_en ?: $this->slug_ko ?: $this->slug_ja ?: '';
        }

        if (app()->getLocale() === 'ja') {
            return $this->slug_ja ?: $this->slug ?: $this->slug_en ?: $this->slug_ko ?: $this->slug_zh ?: '';
        }

        if (app()->getLocale() === 'ko') {
            return $this->slug_ko ?: $this->slug ?: $this->slug_en ?: $this->slug_ja ?: $this->slug_zh ?: '';
        }

        if (app()->getLocale() === 'en') {
            return $this->slug_en ?: $this->slug ?: $this->slug_ko ?: $this->slug_ja ?: $this->slug_zh ?: '';
        }

        return $this->slug ?: $this->slug_en ?: $this->slug_ko ?: $this->slug_ja ?: $this->slug_zh ?: '';
    }

    public function getBadgeLabelsAttribute(): array
    {
        $dbBadges = $this->badges->filter->is_active->map(function ($badge) {
            return [
                'key' => $badge->code,
                'label' => $badge->name_locale,
                'icon' => $badge->icon,
                'tone' => 'custom', // We will use inline styles for these
                'color_bg' => $badge->color_bg,
                'color_text' => $badge->color_text,
            ];
        })->toArray();

        $primary = $this->primary_badge;
        
        if ($primary) {
            return array_merge([$primary], $dbBadges);
        }

        return $dbBadges;
    }

    public static function badgeOptions(): array
    {
        return self::BADGE_PRESETS;
    }

    public function getPrimaryBadgeAttribute(): ?array
    {
        $badgeKey = trim((string) ($this->badge_key ?? ''));

        if ($badgeKey !== '') {
            if ($badgeKey === 'custom' && trim((string) $this->badge_label) !== '') {
                return [
                    'key' => 'custom',
                    'label' => trim((string) $this->badge_label),
                    'icon' => 'bookmark',
                    'tone' => trim((string) ($this->badge_tone ?: 'slate')),
                ];
            }

            if (isset(self::BADGE_PRESETS[$badgeKey])) {
                return [
                    'key' => $badgeKey,
                    'label' => self::BADGE_PRESETS[$badgeKey]['label'],
                    'icon' => self::BADGE_PRESETS[$badgeKey]['icon'],
                    'tone' => self::BADGE_PRESETS[$badgeKey]['tone'],
                ];
            }
        }

        if ($this->is_verified_badge) {
            return [
                'key' => 'verified',
                'label' => self::BADGE_PRESETS['verified']['label'],
                'icon' => self::BADGE_PRESETS['verified']['icon'],
                'tone' => self::BADGE_PRESETS['verified']['tone'],
            ];
        }

        if ($this->is_premium_badge) {
            return [
                'key' => 'premium',
                'label' => self::BADGE_PRESETS['premium']['label'],
                'icon' => self::BADGE_PRESETS['premium']['icon'],
                'tone' => self::BADGE_PRESETS['premium']['tone'],
            ];
        }

        return null;
    }

    public function addTelegramDuration(int $value, string $unit): Carbon
    {
        $currentExpires = $this->telegram_feature_expires_at;
        $baseDate = ($currentExpires && $currentExpires->isFuture()) ? $currentExpires : now();

        $newExpires = match ($unit) {
            'minute', 'minutes' => $baseDate->addMinutes($value),
            'hour', 'hours' => $baseDate->addHours($value),
            'day', 'days' => $baseDate->addDays($value),
            'month', 'months' => $baseDate->addMonths($value),
            'year', 'years' => $baseDate->addYears($value),
            'lifetime' => now()->addYears(73), // ~2099
            default => $baseDate,
        };

        $this->update([
            'telegram_feature_expires_at' => $newExpires
        ]);

        return $newExpires;
    }
}
