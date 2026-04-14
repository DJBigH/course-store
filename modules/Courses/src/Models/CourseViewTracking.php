<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;

class CourseViewTracking extends Model
{
    protected $table = 'course_view_trackings';

    protected $fillable = [
        'course_id',
        'student_id',
        'visitor_hash',
        'view_date',
        'viewed_at',
    ];

    protected $casts = [
        'view_date' => 'date',
        'viewed_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id');
    }
}
