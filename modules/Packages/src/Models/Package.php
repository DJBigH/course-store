<?php

namespace Modules\Packages\src\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = 'teacher_packages';

    protected $fillable = [
        'code',
        'name',
        'name_en',
        'name_ko',
        'name_ja',
        'name_zh',
        'description',
        'description_en',
        'description_ko',
        'description_ja',
        'description_zh',
        'tagline',
        'tagline_en',
        'tagline_ko',
        'tagline_ja',
        'tagline_zh',
        'badge_text',
        'badge_text_en',
        'badge_text_ko',
        'badge_text_ja',
        'badge_text_zh',
        'price',
        'billing_cycle',
        'course_limit',
        'payout_account_limit',
        'commission_rate',
        'priority_review',
        'can_duplicate_courses',
        'can_manage_comments',
        'can_manage_coupons',
        'can_manage_students',
        'can_view_student_progress',
        'can_view_activity_logs',
        'can_manage_quizzes',
        'can_use_ai_quiz',
        'can_import_export',
        'ai_quiz_limit',
        'coupon_limit',
        'can_grant_courses',
        'can_export_orders',
        'can_export_students',
        'can_import_export_lessons',
        'can_sell_bundles',
        'can_schedule_content',
        'can_send_promotions',
        'can_issue_certificates',
        'can_verify_certificates',
        'can_customize_teacher_landing',
        'can_use_affiliate_links',
        'support_level',
        'support_level_en',
        'support_level_ko',
        'support_level_ja',
        'support_level_zh',
        'status',
        'hidden_mode',
        'is_featured',
        'is_exclusive',
        'sort_order',
        'badge_tone',
    ];

    protected $casts = [
        'price' => 'float',
        'payout_account_limit' => 'integer',
        'coupon_limit' => 'integer',
        'ai_quiz_limit' => 'integer',
        'commission_rate' => 'float',
        'priority_review' => 'boolean',
        'can_duplicate_courses' => 'boolean',
        'can_manage_comments' => 'boolean',
        'can_manage_coupons' => 'boolean',
        'can_manage_students' => 'boolean',
        'can_view_student_progress' => 'boolean',
        'can_view_activity_logs' => 'boolean',
        'can_manage_quizzes' => 'boolean',
        'can_use_ai_quiz' => 'boolean',
        'can_import_export' => 'boolean',
        'can_grant_courses' => 'boolean',
        'can_export_orders' => 'boolean',
        'can_export_students' => 'boolean',
        'can_import_export_lessons' => 'boolean',
        'can_sell_bundles' => 'boolean',
        'can_schedule_content' => 'boolean',
        'can_send_promotions' => 'boolean',
        'can_issue_certificates' => 'boolean',
        'can_verify_certificates' => 'boolean',
        'can_customize_teacher_landing' => 'boolean',
        'can_use_affiliate_links' => 'boolean',
        'status' => 'boolean',
        'is_featured' => 'boolean',
        'is_exclusive' => 'boolean',
    ];

    protected static $systemFeatureStatus = null;

    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->{$feature} ?? false);
    }

    public function isFeatureInMaintenance(string $feature): bool
    {
        if (self::$systemFeatureStatus === null) {
            self::$systemFeatureStatus = \Modules\Packages\src\Models\PackageFeature::query()
                ->pluck('is_enabled', 'key')
                ->toArray();
        }

        $status = self::$systemFeatureStatus[$feature] ?? \Modules\Packages\src\Models\PackageFeature::STATUS_ACTIVE;
        
        return in_array($status, [
            \Modules\Packages\src\Models\PackageFeature::STATUS_MAINTENANCE_VISIBLE,
            \Modules\Packages\src\Models\PackageFeature::STATUS_MAINTENANCE_HIDDEN
        ]);
    }

    public function scopeVisibleForListing($query)
    {
        return $query->where('status', true)->where('is_exclusive', false)->orderBy('sort_order')->limit(6);
    }

    /**
     * Lấy tất cả gói (kể cả gói ẩn và gói exclusive) để Admin dùng khi chọn gói tặng giáo viên.
     */
    public function scopeGrantable($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeSelectable($query)
    {
        return $query->where(function ($innerQuery) {
            $innerQuery->where('status', true)
                ->orWhere('hidden_mode', 'available');
        });
    }

    public function applications()
    {
        return $this->hasMany(\Modules\Teacher\src\Models\TeacherApplication::class, 'package_id', 'id');
    }

    public function getEffectiveCourseLimitAttribute(): ?int
    {
        if ($this->code === 'free') {
            return max((int) ($this->course_limit ?? 0), 2);
        }

        return $this->course_limit;
    }

    public function getEffectivePayoutAccountLimitAttribute(): int
    {
        return max(1, min((int) ($this->payout_account_limit ?? 3), 3));
    }

    public function getEffectiveCouponLimitAttribute(): ?int
    {
        $limit = $this->coupon_limit;

        if ($limit === null) {
            return null;
        }

        return max((int) $limit, 1);
    }

    public function getEffectiveAiQuizLimitAttribute(): ?int
    {
        $limit = $this->ai_quiz_limit;

        if ($limit === null && $this->code === 'free') {
            return 3;
        }

        if ($limit === null) {
            return null;
        }

        return max((int) $limit, 1);
    }

    public function getNameLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('name');
    }

    public function getDescriptionLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('description');
    }

    public function getTaglineLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('tagline');
    }

    public function getBadgeTextLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('badge_text');
    }

    public function getSupportLevelLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('support_level');
    }

    public function getVisibilityStateAttribute(): string
    {
        if ($this->status) {
            return 'public';
        }

        return $this->hidden_mode === 'available'
            ? 'hidden_available'
            : 'hidden_unavailable';
    }

    private function resolveLocalizedAttribute(string $attribute): string
    {
        $fields = match (app()->getLocale()) {
            'zh' => ["{$attribute}_zh", $attribute, "{$attribute}_en", "{$attribute}_ko", "{$attribute}_ja"],
            'ja' => ["{$attribute}_ja", $attribute, "{$attribute}_en", "{$attribute}_ko", "{$attribute}_zh"],
            'ko' => ["{$attribute}_ko", $attribute, "{$attribute}_en", "{$attribute}_ja", "{$attribute}_zh"],
            'en' => ["{$attribute}_en", $attribute, "{$attribute}_ko", "{$attribute}_ja", "{$attribute}_zh"],
            default => [$attribute, "{$attribute}_en", "{$attribute}_ko", "{$attribute}_ja", "{$attribute}_zh"],
        };

        foreach ($fields as $field) {
            $value = trim((string) ($this->{$field} ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
