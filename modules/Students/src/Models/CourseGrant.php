<?php

namespace Modules\Students\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Courses\src\Models\Courses;
use Modules\Teacher\src\Models\Teacher;

class CourseGrant extends Model
{
    use HasFactory;

    protected $table = 'teacher_course_grants';

    protected $fillable = [
        'teacher_id',
        'student_id',
        'course_id',
        'reason',
        'note',
        'status',
        'token',
        'locale',
        'invited_at',
        'accepted_at',
        'revoked_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id')->withTrashed();
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id')->withoutGlobalScopes()->withTrashed();
    }
}
