<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Teacher\src\Models\Teacher;

class CourseBundle extends Model
{
    protected $table = 'teacher_course_bundles';

    protected $fillable = [
        'id',
        'teacher_id',
        'name',
        'slug',
        'price',
        'description',
        'image',
        'status',
        'is_hot',
        'position',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'price' => 'float',
        'status' => 'boolean',
        'is_hot' => 'boolean',
        'position' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(CourseBundleItem::class, 'bundle_id', 'id')->orderBy('position')->orderBy('id');
    }

    public function courses()
    {
        return $this->hasManyThrough(
            Courses::class,
            CourseBundleItem::class,
            'bundle_id',
            'id',
            'id',
            'course_id'
        );
    }
}
