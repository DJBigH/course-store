<?php

namespace Modules\Lessons\src\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Categories\src\Models\Category;
use Modules\Courses\src\Models\Courses;
use Modules\Document\src\Models\Document;
use Modules\Video\src\Models\Video;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lessons';
    protected $fillable = [
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
        'description_ko',
        'description_ja',
        'description_zh',
        'status',
    ];

    protected $with = ['video', 'document'];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

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
        if (app()->getLocale() === 'zh') {
            return $this->name_zh ?: $this->name ?: $this->name_en ?: $this->name_ko ?: $this->name_ja;
        }

        if (app()->getLocale() === 'ja') {
            return $this->name_ja ?: $this->name ?: $this->name_en ?: $this->name_ko ?: $this->name_zh;
        }

        if (app()->getLocale() === 'ko') {
            return $this->name_ko ?: $this->name ?: $this->name_en ?: $this->name_ja ?: $this->name_zh;
        }

        if (app()->getLocale() === 'en') {
            return $this->name_en ?: $this->name ?: $this->name_ko ?: $this->name_ja ?: $this->name_zh;
        }

        return $this->name ?: $this->name_en ?: $this->name_ko ?: $this->name_ja ?: $this->name_zh;
    }

    public function getSlugLocaleAttribute()
    {
        if (app()->getLocale() === 'zh') {
            return $this->slug_zh ?: $this->slug ?: $this->slug_en ?: $this->slug_ko ?: $this->slug_ja;
        }

        if (app()->getLocale() === 'ja') {
            return $this->slug_ja ?: $this->slug ?: $this->slug_en ?: $this->slug_ko ?: $this->slug_zh;
        }

        if (app()->getLocale() === 'ko') {
            return $this->slug_ko ?: $this->slug ?: $this->slug_en ?: $this->slug_ja ?: $this->slug_zh;
        }

        if (app()->getLocale() === 'en') {
            return $this->slug_en ?: $this->slug ?: $this->slug_ko ?: $this->slug_ja ?: $this->slug_zh;
        }

        return $this->slug ?: $this->slug_en ?: $this->slug_ko ?: $this->slug_ja ?: $this->slug_zh;
    }

    public function getDescriptionLocaleAttribute()
    {
        if (app()->getLocale() === 'zh') {
            return $this->description_zh ?: $this->description ?: $this->description_en ?: $this->description_ko ?: $this->description_ja;
        }

        if (app()->getLocale() === 'ja') {
            return $this->description_ja ?: $this->description ?: $this->description_en ?: $this->description_ko ?: $this->description_zh;
        }

        if (app()->getLocale() === 'ko') {
            return $this->description_ko ?: $this->description ?: $this->description_en ?: $this->description_ja ?: $this->description_zh;
        }

        if (app()->getLocale() === 'en') {
            return $this->description_en ?: $this->description ?: $this->description_ko ?: $this->description_ja ?: $this->description_zh;
        }

        return $this->description ?: $this->description_en ?: $this->description_ko ?: $this->description_ja ?: $this->description_zh;
    }

    public function getDurationsAttribute($value)
    {
        $duration = (float) $value;

        if ($duration > 0) {
            return $duration;
        }

        $videoUrl = $this->relationLoaded('video')
            ? $this->video?->url
            : $this->video()->value('url');

        if (!$videoUrl) {
            return $duration;
        }

        $externalDuration = externalVideoDuration($videoUrl);

        return $externalDuration > 0 ? $externalDuration : $duration;
    }
}
