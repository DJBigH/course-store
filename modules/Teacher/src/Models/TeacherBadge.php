<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherBadge extends Model
{
    use SoftDeletes;

    protected $table = 'teacher_badges';

    protected $fillable = [
        'name',
        'code',
        'icon',
        'color_bg',
        'color_text',
        'description',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
    ];

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_has_badges', 'badge_id', 'teacher_id');
    }

    public function getNameLocaleAttribute(): string
    {
        $name = $this->name;
        $locale = app()->getLocale();
        return $name[$locale] ?? $name['vi'] ?? $name['en'] ?? '';
    }

    public function getDescriptionLocaleAttribute(): string
    {
        $description = $this->description;
        $locale = app()->getLocale();
        return $description[$locale] ?? $description['vi'] ?? $description['en'] ?? '';
    }
}
