<?php

namespace Modules\Announcements\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\src\Models\User;

class AnnouncementUser extends Model
{
    protected $table = 'announcement_user';

    protected $fillable = [
        'announcement_id',
        'user_id'
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
