<?php

namespace Modules\Finances\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Teacher\src\Models\Teacher;

class PayoutAccountChangeRequest extends Model
{
    protected $table = 'teacher_payout_account_change_requests';

    protected $fillable = [
        'teacher_id',
        'replace_payout_account_id',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'status',
        'admin_note',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function replaceAccount()
    {
        return $this->belongsTo(PayoutAccount::class, 'replace_payout_account_id', 'id');
    }
}
