<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherNotificationRead extends Model
{
    protected $table = 'teacher_notification_reads';

    protected $fillable = [
        'student_id',
        'notification_key',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
