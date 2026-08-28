<?php

namespace Modules\ActiveLogs\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveLog extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\MassPrunable;
    protected $table = 'active_logs';

    /**
     * Get the prunable model query.
     */
    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDays(60));
    }

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

    public static function log(string $action, $subject = null, array $properties = [], string $description = null, string $logName = null)
    {
        return activity_log($action, $subject, $properties, $logName, $description);
    }
}
