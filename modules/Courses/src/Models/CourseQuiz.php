<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Lessons\src\Models\Lesson;
use Modules\Teacher\src\Models\Teacher;

class CourseQuiz extends Model
{
    protected $table = 'course_quizzes';

    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'slug',
        'description',
        'passing_score',
        'max_attempts',
        'time_limit_minutes',
        'show_answers_after',
        'position',
        'status',
        'published_at',
        'deadline_at',
        'created_by',
    ];

    protected $casts = [
        'lesson_id' => 'integer',
        'passing_score' => 'integer',
        'max_attempts' => 'integer',
        'time_limit_minutes' => 'integer',
        'show_answers_after' => 'boolean',
        'position' => 'integer',
        'status' => 'integer',
        'published_at' => 'datetime',
        'deadline_at' => 'datetime',
        'created_by' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function questions()
    {
        return $this->hasMany(CourseQuizQuestion::class, 'quiz_id');
    }

    public function submissions()
    {
        return $this->hasMany(CourseQuizSubmission::class, 'quiz_id');
    }

    public function assignments()
    {
        return $this->hasMany(CourseQuizAssignment::class, 'quiz_id');
    }

    public function creator()
    {
        return $this->belongsTo(Teacher::class, 'created_by', 'student_id');
    }
}
