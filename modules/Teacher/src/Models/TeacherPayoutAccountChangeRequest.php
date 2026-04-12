<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPayoutAccountChangeRequest extends Model
{
    protected $table = 'teacher_payout_account_change_requests';

    protected $fillable = [
        'teacher_id',
        'replace_payout_account_id',
        'replace_bank_name',
        'replace_bank_account_name',
        'replace_bank_account_number',
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
        'processed_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function replaceAccount()
    {
        return $this->belongsTo(TeacherPayoutAccount::class, 'replace_payout_account_id', 'id');
    }
}
