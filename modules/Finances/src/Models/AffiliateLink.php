<?php

namespace Modules\Finances\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\Order;
use Modules\Teacher\src\Models\Teacher;
use Modules\Courses\src\Models\CourseBundle;

class AffiliateLink extends Model
{
    protected $table = 'teacher_affiliate_links';

    protected $fillable = [
        'teacher_id',
        'name',
        'code',
        'target_type',
        'target_id',
        'clicks_count',
        'last_clicked_at',
        'status',
    ];

    protected $casts = [
        'clicks_count' => 'integer',
        'last_clicked_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function clicks()
    {
        return $this->hasMany(AffiliateLinkClick::class, 'affiliate_link_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'target_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo(CourseBundle::class, 'target_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'affiliate_link_id', 'id');
    }
}
