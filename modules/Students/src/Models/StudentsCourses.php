<?php

namespace Modules\Students\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Courses\src\Models\Courses;

class StudentsCourses extends Model
{
    use HasFactory;

    protected $table = 'students_courses';

    protected $fillable = [
        'student_id',
        'course_id',
        'status',
        'created_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id')->withoutGlobalScopes();
    }
}