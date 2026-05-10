<?php

namespace Modules\Packages\src\Models;

use Illuminate\Database\Eloquent\Model;

class PackageCategory extends Model
{
    protected $table = 'teacher_package_categories';

    protected $fillable = [
        'name',
        'name_en',
        'name_ko',
        'name_ja',
        'name_zh',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'boolean',
    ];

    public function packages()
    {
        return $this->hasMany(Package::class, 'category_id', 'id');
    }

    public function getNameLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('name');
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
