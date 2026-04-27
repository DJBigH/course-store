<?php

namespace Modules\Announcements\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\src\Models\User;

class AnnouncementRead extends Model
{
    protected $table = 'announcement_reads';

    protected $fillable = [
        'announcement_id',
        'user_id',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
