<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherCourseBundle extends Model
{
    protected $table = 'teacher_course_bundles';

    protected $fillable = [
        'teacher_id',
        'name',
        'slug',
        'description',
        'thumbnail',
        'price',
        'status',
        'position',
    ];

    protected $casts = [
        'price' => 'float',
        'status' => 'boolean',
        'position' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(TeacherCourseBundleItem::class, 'bundle_id', 'id')->orderBy('position')->orderBy('id');
    }

    public function courses()
    {
        return $this->belongsToMany(
            \Modules\Courses\src\Models\Courses::class,
            'teacher_course_bundle_items',
            'bundle_id',
            'course_id'
        )->withPivot(['position'])->orderBy('teacher_course_bundle_items.position');
    }
}
