<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherTelegramSubscription extends Model
{
    protected $table = 'teacher_telegram_subscriptions';

    protected $fillable = [
        'teacher_id',
        'telegram_package_id',
        'started_at',
        'expires_at',
        'amount',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount' => 'float',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function package()
    {
        return $this->belongsTo(TelegramPackage::class, 'telegram_package_id');
    }
}
