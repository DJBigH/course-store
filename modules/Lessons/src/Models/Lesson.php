<?php

namespace Modules\Lessons\src\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Categories\src\Models\Category;
use Modules\Courses\src\Models\Courses;
use Modules\Document\src\Models\Document;
use Modules\Video\src\Models\Video;

class Lesson extends Model
{
    use HasFactory;

    protected $table = 'lessons';
    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'slug_en',
        'video_id',
        'course_id',
        'document_id',
        'parent_id',
        'is_trial',
        'view',
        'position',
        'durations',
        'description',
        'description_en',
        'status',
    ];

    protected $with = ['video', 'document'];

    public function children()
    {
        return $this->hasMany(Lesson::class, 'parent_id');
    }

    public function subLessons()
    {
        return $this->children()->orderBy('position', 'asc')->with('subLessons');
    }

    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id');
    }

    public function scopeActive(Builder $query)
    {
        queryActive($query);
    }

    public function scopePosition(Builder $query)
    {
        queryPosition($query);
    }

    public function getNameLocaleAttribute()
    {
        return app()->getLocale() === 'en'
            ? ($this->name_en ?: $this->name)
            : $this->name;
    }

    public function getSlugLocaleAttribute()
    {
        return app()->getLocale() === 'en'
            ? ($this->slug_en ?: $this->slug)
            : $this->slug;
    }

    public function getDescriptionLocaleAttribute()
    {
        return app()->getLocale() === 'en'
            ? ($this->description_en ?: $this->description)
            : $this->description;
    }
}
