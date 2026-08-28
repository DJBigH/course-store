<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramPackage extends Model
{
    protected $table = 'telegram_packages';

    protected $fillable = [
        'name',
        'name_en',
        'name_ja',
        'name_ko',
        'name_zh',
        'description',
        'description_en',
        'description_ja',
        'description_ko',
        'description_zh',
        'price',
        'sale_price',
        'duration_value',
        'duration_unit',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'sale_price' => 'float',
        'duration_value' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const UNIT_MINUTE = 'minute';
    public const UNIT_HOUR = 'hour';
    public const UNIT_DAY = 'day';
    public const UNIT_MONTH = 'month';
    public const UNIT_YEAR = 'year';
    public const UNIT_LIFETIME = 'lifetime';

    public static function getUnits(): array
    {
        return [
            self::UNIT_MINUTE => 'Phút',
            self::UNIT_HOUR => 'Giờ',
            self::UNIT_DAY => 'Ngày',
            self::UNIT_MONTH => 'Tháng',
            self::UNIT_YEAR => 'Năm',
            self::UNIT_LIFETIME => 'Vĩnh viễn',
        ];
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price) . ' VNĐ';
    }

    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_unit === self::UNIT_LIFETIME) {
            return __('teacher::teacher/telegram.gift.units.lifetime');
        }

        $unitLabel = __('teacher::teacher/telegram.gift.units.' . $this->duration_unit);
        return $this->duration_value . ' ' . $unitLabel;
    }

    public function getDurationLabelAttribute(): string
    {
        return $this->getFormattedDurationAttribute();
    }

    public function getNameLocaleAttribute(): string
    {
        $locale = app()->getLocale();
        $field = 'name_' . $locale;
        
        if ($locale === 'vi') {
            return $this->name;
        }
        
        return $this->{$field} ?: $this->name;
    }

    public function getDescriptionLocaleAttribute(): ?string
    {
        $locale = app()->getLocale();
        $field = 'description_' . $locale;
        
        if ($locale === 'vi') {
            return $this->description;
        }
        
        return $this->{$field} ?: $this->description;
    }
}
