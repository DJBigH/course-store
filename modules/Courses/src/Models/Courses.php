<?php

namespace Modules\Courses\src\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;
use Modules\Categories\Src\Models\Category;
use Modules\Lessons\src\Models\Lesson;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;

class Courses extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'id',
        'name',
        'name_en',
        'slug',
        'slug_en',
        'detail',
        'detail_en',
        'teacher_id',
        'thumbnail',
        'price',
        'sale_price',
        'code',
        'durations',
        'is_document',
        'supports',
        'supports_en',
        'status',
        'view',
        'created_at',
        'updated_at',
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

    public function getNameLocaleAttribute(): string
    {
        return app()->getLocale() === 'en'
            ? ($this->name_en ?: $this->name ?: '')
            : ($this->name ?: $this->name_en ?: '');
    }

    public function getDetailLocaleAttribute(): string
    {
        return app()->getLocale() === 'en'
            ? ($this->detail_en ?: $this->detail ?: '')
            : ($this->detail ?: $this->detail_en ?: '');
    }

    public function getSupportsLocaleAttribute(): string
    {
        return app()->getLocale() === 'en'
            ? ($this->supports_en ?: $this->supports ?: '')
            : ($this->supports ?: $this->supports_en ?: '');
    }

    public function getSlugLocaleAttribute(): string
    {
        return app()->getLocale() === 'en'
            ? ($this->slug_en ?: $this->slug ?: '')
            : ($this->slug ?: $this->slug_en ?: '');
    }
}
