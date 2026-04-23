<?php

namespace Modules\Packages\src\Models;

use Illuminate\Database\Eloquent\Model;

class PackageFeature extends Model
{
    protected $table = 'teacher_package_features';

    protected $fillable = [
        'key',
        'name_vi',
        'name_en',
        'name_ko',
        'name_ja',
        'name_zh',
        'description_vi',
        'description_en',
        'description_ko',
        'description_ja',
        'description_zh',
        'group',
        'icon',
        'sort_order',
        'is_enabled',
    ];

    public const STATUS_DISABLED = 0; // Tạm khóa (Ẩn)
    public const STATUS_ACTIVE = 1; // Hoạt động
    public const STATUS_MAINTENANCE_VISIBLE = 2; // Bảo trì (Hiện bảng so sánh)
    public const STATUS_MAINTENANCE_HIDDEN = 3; // Bảo trì (Ẩn bảng so sánh)

    protected $casts = [
        'sort_order' => 'integer',
        'is_enabled' => 'integer',
    ];

    public function getNameLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('name');
    }

    public function getDescriptionLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('description');
    }

    private function resolveLocalizedAttribute(string $attribute): string
    {
        $fields = match (app()->getLocale()) {
            'zh' => ["{$attribute}_zh", $attribute . '_vi', "{$attribute}_en", "{$attribute}_ko", "{$attribute}_ja"],
            'ja' => ["{$attribute}_ja", $attribute . '_vi', "{$attribute}_en", "{$attribute}_ko", "{$attribute}_zh"],
            'ko' => ["{$attribute}_ko", $attribute . '_vi', "{$attribute}_en", "{$attribute}_ja", "{$attribute}_zh"],
            'en' => ["{$attribute}_en", $attribute . '_vi', "{$attribute}_ko", "{$attribute}_ja", "{$attribute}_zh"],
            default => ["{$attribute}_vi", "{$attribute}_en", "{$attribute}_ko", "{$attribute}_ja", "{$attribute}_zh"],
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
