<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;

class CourseQuizChoice extends Model
{
    protected $table = 'course_quiz_choices';

    protected $fillable = [
        'question_id',
        'choice_text',
        'is_correct',
        'is_exclusive',
        'position',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'is_exclusive' => 'boolean',
        'position' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(CourseQuizQuestion::class, 'question_id');
    }
}
