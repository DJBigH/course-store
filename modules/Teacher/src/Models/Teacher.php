<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'teacher';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'description',
        'description_en',
        'exp',
        'image',
        'created_at',
        'updated_at',
    ];

    public function getDescriptionLocaleAttribute(): string
    {
        return app()->getLocale() === 'en'
            ? ($this->description_en ?: $this->description ?: '')
            : ($this->description ?: $this->description_en ?: '');
    }
}
