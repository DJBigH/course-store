<?php

namespace Modules\Courses\src\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Categories\Src\Models\Category;
use Modules\Courses\src\Models\CourseComment;
use Modules\Courses\src\Models\CourseRating;
use Modules\Courses\src\Models\CourseViewTracking;
use Modules\Lessons\src\Models\Lesson;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;

class Courses extends Model
{
    use SoftDeletes;

    protected $table = 'courses';

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
        'detail',
        'detail_en',
        'detail_ko',
        'detail_ja',
        'detail_zh',
        'teacher_id',
        'thumbnail',
        'price',
        'sale_price',
        'price_en',
        'sale_price_en',
        'price_ko',
        'sale_price_ko',
        'price_ja',
        'sale_price_ja',
        'price_zh',
        'sale_price_zh',
        'code',
        'durations',
        'is_document',
        'supports',
        'supports_en',
        'supports_ko',
        'supports_ja',
        'supports_zh',
        'status',
        'package_locked_at',
        'package_lock_reason',
        'is_package_priority',
        'is_learning_locked',
        'completion_condition',
        'is_coming_soon',
        'coming_soon_start_at',
        'quantity',
        'end_at',
        'view',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'package_locked_at' => 'datetime',
        'is_package_priority' => 'boolean',
        'is_coming_soon' => 'boolean',
        'coming_soon_start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected $with = ['teacher'];

    protected static function booted()
    {
        static::addGlobalScope(new ActiveScope());
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_courses');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'course_id', 'id');
    }

    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'students_courses',
            'course_id',   // FK của Course trong pivot
            'student_id',
            'id'
        );
    }

    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'course_id', 'id')->withoutGlobalScopes();
    }

    public function comments()
    {
        return $this->hasMany(CourseComment::class, 'course_id', 'id');
    }

    public function ratings()
    {
        return $this->hasMany(CourseRating::class, 'course_id', 'id');
    }

    public function viewTrackings()
    {
        return $this->hasMany(CourseViewTracking::class, 'course_id', 'id');
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

    public function getDetailLocaleAttribute(): string
    {
        if (app()->getLocale() === 'zh') {
            return $this->detail_zh ?: $this->detail ?: $this->detail_en ?: $this->detail_ko ?: $this->detail_ja ?: '';
        }

        if (app()->getLocale() === 'ja') {
            return $this->detail_ja ?: $this->detail ?: $this->detail_en ?: $this->detail_ko ?: $this->detail_zh ?: '';
        }

        if (app()->getLocale() === 'ko') {
            return $this->detail_ko ?: $this->detail ?: $this->detail_en ?: $this->detail_ja ?: $this->detail_zh ?: '';
        }

        if (app()->getLocale() === 'en') {
            return $this->detail_en ?: $this->detail ?: $this->detail_ko ?: $this->detail_ja ?: $this->detail_zh ?: '';
        }

        return $this->detail ?: $this->detail_en ?: $this->detail_ko ?: $this->detail_ja ?: $this->detail_zh ?: '';
    }

    public function getSupportsLocaleAttribute(): string
    {
        if (app()->getLocale() === 'zh') {
            return $this->supports_zh ?: $this->supports ?: $this->supports_en ?: $this->supports_ko ?: $this->supports_ja ?: '';
        }

        if (app()->getLocale() === 'ja') {
            return $this->supports_ja ?: $this->supports ?: $this->supports_en ?: $this->supports_ko ?: $this->supports_zh ?: '';
        }

        if (app()->getLocale() === 'ko') {
            return $this->supports_ko ?: $this->supports ?: $this->supports_en ?: $this->supports_ja ?: $this->supports_zh ?: '';
        }

        if (app()->getLocale() === 'en') {
            return $this->supports_en ?: $this->supports ?: $this->supports_ko ?: $this->supports_ja ?: $this->supports_zh ?: '';
        }

        return $this->supports ?: $this->supports_en ?: $this->supports_ko ?: $this->supports_ja ?: $this->supports_zh ?: '';
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

    public function getDurationsAttribute($value)
    {
        $duration = (float) $value;

        if ($duration > 0) {
            return $duration;
        }

        if ($this->relationLoaded('lessons')) {
            return (float) $this->lessons
                ->whereNotNull('parent_id')
                ->where('status', 1)
                ->sum('durations');
        }

        return (float) $this->lessons()
            ->whereNotNull('parent_id')
            ->where('status', 1)
            ->get()
            ->sum('durations');
    }

    public function getPriceLocaleAttribute(): float
    {
        $locale = app()->getLocale();
        $field = $locale === 'vi' ? 'price' : 'price_' . $locale;
        return (float) ($this->{$field} ?? $this->price ?? 0);
    }

    public function getSalePriceLocaleAttribute(): float
    {
        $locale = app()->getLocale();
        $field = $locale === 'vi' ? 'sale_price' : 'sale_price_' . $locale;
        return (float) ($this->{$field} ?? $this->sale_price ?? 0);
    }

    public function getCurrencySymbolAttribute(): string
    {
        $locale = app()->getLocale();
        return match ($locale) {
            'en' => '$',
            'ko' => '₩',
            'ja' => '¥',
            'zh' => '元',
            default => '₫',
        };
    }

    public function getCurrencyCodeAttribute(): string
    {
        $locale = app()->getLocale();
        return match ($locale) {
            'en' => 'USD',
            'ko' => 'KRW',
            'ja' => 'JPY',
            'zh' => 'CNY',
            default => 'VND',
        };
    }

    public function getIsOnFlashSaleAttribute(): bool
    {
        return $this->sale_price_locale > 0 
            && $this->end_at 
            && $this->end_at->isFuture();
    }
}
