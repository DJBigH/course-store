<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;

class CourseRating extends Model
{
    protected $table = 'course_ratings';

    protected $fillable = [
        'course_id',
        'student_id',
        'rating',
        'status',
    ];

    protected $casts = [
        'rating' => 'float',
    ];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
