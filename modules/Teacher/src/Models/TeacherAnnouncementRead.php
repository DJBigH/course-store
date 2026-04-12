<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAnnouncementRead extends Model
{
    protected $table = 'teacher_announcement_reads';

    protected $fillable = [
        'announcement_id',
        'student_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
