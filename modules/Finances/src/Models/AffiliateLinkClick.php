<?php

namespace Modules\Finances\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Teacher\src\Models\Teacher;

class AffiliateLinkClick extends Model
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
        'affiliate_link_id' => 'integer',
        'teacher_id' => 'integer',
        'student_id' => 'integer',
        'clicked_at' => 'datetime',
    ];

    public $timestamps = false;

    public function affiliateLink()
    {
        return $this->belongsTo(AffiliateLink::class, 'affiliate_link_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }
}
