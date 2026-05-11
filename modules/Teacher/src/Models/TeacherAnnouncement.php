<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Packages\src\Models\Package;

class TeacherAnnouncement extends Model
{
    protected $table = 'teacher_announcements';

    protected $fillable = [
        'title',
        'title_en',
        'title_ko',
        'title_ja',
        'title_zh',
        'message',
        'message_en',
        'message_ko',
        'message_ja',
        'message_zh',
        'action_url',
        'action_label',
        'action_label_en',
        'action_label_ko',
        'action_label_ja',
        'action_label_zh',
        'icon',
        'status',
        'is_pinned',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_pinned' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function packages()
    {
        return $this->belongsToMany(
            Package::class,
            'teacher_announcement_package',
            'announcement_id',
            'package_id'
        )->withTimestamps();
    }

    public function reads()
    {
        return $this->hasMany(TeacherAnnouncementRead::class, 'announcement_id', 'id');
    }

    public function scopeActive($query)
    {
        $now = now();

        return $query
            ->where('status', true)
            ->where(function ($inner) use ($now) {
                $inner->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($inner) use ($now) {
                $inner->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }

    public function getTitleLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('title');
    }

    public function getMessageLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('message');
    }

    public function getActionLabelLocaleAttribute(): string
    {
        return $this->resolveLocalizedAttribute('action_label');
    }

    public function getTitleLocaleForTeacher(Teacher $teacher): string
    {
        return $this->resolveLocalizedAttributeForTeacher('title', $teacher);
    }

    public function getMessageLocaleForTeacher(Teacher $teacher): string
    {
        return $this->resolveLocalizedAttributeForTeacher('message', $teacher);
    }

    public function getActionLabelLocaleForTeacher(Teacher $teacher): string
    {
        return $this->resolveLocalizedAttributeForTeacher('action_label', $teacher);
    }

    private function resolveLocalizedAttributeForTeacher(string $attribute, Teacher $teacher): string
    {
        $locale = $teacher->student?->locale ?: 'vi';
        $fields = match ($locale) {
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
