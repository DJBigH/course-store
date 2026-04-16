<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;

class CourseQuizQuestion extends Model
{
    protected $table = 'course_quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question',
        'question_type',
        'position',
        'points',
        'is_required',
        'explanation',
    ];

    protected $casts = [
        'position' => 'integer',
        'points' => 'integer',
        'is_required' => 'boolean',
    ];

    public function quiz()
    {
        return $this->belongsTo(CourseQuiz::class, 'quiz_id');
    }

    public function choices()
    {
        return $this->hasMany(CourseQuizChoice::class, 'question_id');
    }
}
