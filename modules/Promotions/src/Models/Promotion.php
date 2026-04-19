<?php

namespace Modules\Promotions\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Courses\src\Models\Courses;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;

class Promotion extends Model
{
    protected $table = 'teacher_promotions';

    protected $fillable = [
        'teacher_id',
        'course_id',
        'created_by_student_id',
        'title',
        'message',
        'audience_type',
        'recipient_count',
        'filters',
    ];

    protected $casts = [
        'recipient_count' => 'integer',
        'filters' => 'array',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(Student::class, 'created_by_student_id', 'id');
    }
}
