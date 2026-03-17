<?php

namespace Modules\ActiveLogs\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveLog extends Model
{
    use HasFactory;
    protected $table = 'active_logs';

     protected $fillable = [
        'log_name',
        'action',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'description',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];
    
}
