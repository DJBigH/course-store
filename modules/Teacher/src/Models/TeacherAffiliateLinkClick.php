<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;

class TeacherAffiliateLinkClick extends Model
{
    protected $table = 'teacher_affiliate_link_clicks';

    protected $fillable = [
        'affiliate_link_id',
        'teacher_id',
        'student_id',
        'target_type',
        'target_id',
        'locale',
        'ip_address',
        'user_agent',
        'referer_url',
        'target_url',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function affiliateLink()
    {
        return $this->belongsTo(TeacherAffiliateLink::class, 'affiliate_link_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
