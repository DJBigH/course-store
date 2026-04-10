<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherCourseBundleItem extends Model
{
    protected $table = 'teacher_course_bundle_items';

    protected $fillable = [
        'bundle_id',
        'course_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function bundle()
    {
        return $this->belongsTo(TeacherCourseBundle::class, 'bundle_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(\Modules\Courses\src\Models\Courses::class, 'course_id', 'id')->withoutGlobalScopes();
    }
}
