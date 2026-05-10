<?php

namespace Modules\Lessons\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;

class LessonNote extends Model
{
    protected $table = 'lesson_notes';

    protected $fillable = [
        'student_id',
        'lesson_id',
        'time_at',
        'content',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id', 'id');
    }

    public function getTimeFormattedAttribute()
    {
        $seconds = $this->time_at;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
