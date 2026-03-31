<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\src\Models\User;

class AdminSessionTracker extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'browser',
        'platform',
        'device',
        'last_activity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
