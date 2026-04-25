<?php

namespace Modules\Students\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Teacher\src\Models\Teacher;

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
