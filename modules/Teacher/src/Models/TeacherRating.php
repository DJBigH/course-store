<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;

class TeacherRating extends Model
{
    protected $table = 'teacher_ratings';

    protected $fillable = [
        'teacher_id',
        'student_id',
        'rating',
    ];

    protected $casts = [
        'rating' => 'float',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
