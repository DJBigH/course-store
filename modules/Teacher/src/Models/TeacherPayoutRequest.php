<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPayoutRequest extends Model
{
    protected $table = 'teacher_payout_requests';

    protected $fillable = [
        'teacher_id',
        'amount',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'note',
        'admin_note',
        'status',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'processed_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }
}
