<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;

class CourseBundleItem extends Model
{
    protected $table = 'teacher_course_bundle_items';

    protected $fillable = [
        'bundle_id',
        'course_id',
        'position'
    ];

    protected $casts = [
        'bundle_id' => 'integer',
        'course_id' => 'integer',
        'position' => 'integer',
    ];

    public $timestamps = false;

    public function bundle()
    {
        return $this->belongsTo(CourseBundle::class, 'bundle_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id');
    }
}
