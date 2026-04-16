<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;

class CourseQuizSubmission extends Model
{
    protected $table = 'course_quiz_submissions';

    protected $fillable = [
        'quiz_id',
        'student_id',
        'assignment_id',
        'attempt_no',
        'score',
        'passed',
        'submitted_at',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'attempt_no'   => 'integer',
        'score'        => 'integer',
        'passed'       => 'boolean',
        'submitted_at' => 'datetime',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
    ];

    public function quiz()
    {
        return $this->belongsTo(CourseQuiz::class, 'quiz_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function assignment()
    {
        return $this->belongsTo(CourseQuizAssignment::class, 'assignment_id');
    }

    public function answers()
    {
        return $this->hasMany(CourseQuizSubmissionAnswer::class, 'submission_id');
    }
}
