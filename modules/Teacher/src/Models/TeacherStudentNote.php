<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;

class TeacherStudentNote extends Model
{
    protected $table = 'teacher_student_notes';

    protected $fillable = [
        'teacher_id',
        'student_id',
        'tag',
        'note',
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
