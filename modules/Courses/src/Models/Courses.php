<?php

namespace Modules\Courses\src\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;
use Modules\Categories\Src\Models\Category;
use Modules\Lessons\src\Models\Lesson;
use Modules\Teacher\src\Models\Teacher;

class Courses extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'detail',
        'teacher_id',
        'thumbnail',
        'price',
        'sale_price',
        'code',
        'durations',
        'is_document',
        'supports',
        'status',
        'view',
        'created_at',
        'updated_at',
    ];

    protected $with = ['teacher','lessons'];

    protected static function booted()
    {
        static::addGlobalScope(new ActiveScope());
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_courses');
    }

    public function teacher(){
        return $this->belongsTo(Teacher::class, 'teacher_id','id');
    }

    public function lessons(){
        return $this->hasMany(Lesson::class,'course_id','id');
    }
}

