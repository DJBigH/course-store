<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;

class CourseQuizSubmissionAnswer extends Model
{
    protected $table = 'course_quiz_submission_answers';

    protected $fillable = [
        'submission_id',
        'question_id',
        'choice_id',
        'answer_text',
        'is_correct',
        'points_earned',
    ];

    protected $casts = [
        'submission_id' => 'integer',
        'question_id'   => 'integer',
        'choice_id'     => 'integer',
        'is_correct'    => 'boolean',
        'points_earned' => 'integer',
    ];

    public function submission()
    {
        return $this->belongsTo(CourseQuizSubmission::class, 'submission_id');
    }

    public function question()
    {
        return $this->belongsTo(CourseQuizQuestion::class, 'question_id');
    }

    public function choice()
    {
        return $this->belongsTo(CourseQuizChoice::class, 'choice_id');
    }
}
