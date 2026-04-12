<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Courses\src\Models\Courses;
use Modules\Students\src\Models\Student;

class TeacherCourseCertificate extends Model
{
    protected $table = 'teacher_course_certificates';

    protected $fillable = [
        'teacher_id',
        'student_id',
        'course_id',
        'issued_by_student_id',
        'code',
        'student_name_snapshot',
        'course_name_snapshot',
        'teacher_name_snapshot',
        'completed_lessons',
        'total_lessons',
        'progress_percent',
        'note',
        'issued_at',
        'revoked_at',
        'revoked_by_student_id',
        'revoke_reason',
    ];

    protected $casts = [
        'completed_lessons' => 'integer',
        'total_lessons' => 'integer',
        'progress_percent' => 'integer',
        'issued_at' => 'datetime',
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
        return $this->belongsTo(Courses::class, 'course_id', 'id')->withoutGlobalScopes();
    }

    public function issuer()
    {
        return $this->belongsTo(Student::class, 'issued_by_student_id', 'id')->withTrashed();
    }

    public function revokedBy()
    {
        return $this->belongsTo(Student::class, 'revoked_by_student_id', 'id')->withTrashed();
    }
}
