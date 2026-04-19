<?php

namespace Modules\Finances\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Teacher\src\Models\Teacher;

class PayoutRequest extends Model
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

    public function getBankLabelAttribute(): string
    {
        return trim($this->bank_name . ' - ' . $this->bank_account_name . ' - ' . $this->bank_account_number, ' -');
    }
}
