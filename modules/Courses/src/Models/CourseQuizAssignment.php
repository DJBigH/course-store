<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;

class CourseQuizAssignment extends Model
{
    protected $table = 'course_quiz_assignments';

    protected $fillable = [
        'quiz_id',
        'student_id',
        'assigned_by',
        'deadline_at',
        'status',
        'note',
    ];

    protected $casts = [
        'quiz_id'     => 'integer',
        'student_id'  => 'integer',
        'assigned_by' => 'integer',
        'deadline_at' => 'datetime',
    ];

    public function quiz()
    {
        return $this->belongsTo(CourseQuiz::class, 'quiz_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
