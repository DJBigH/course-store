<?php

namespace Modules\Finances\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Teacher\src\Models\Teacher;

class PayoutAccount extends Model
{
    protected $table = 'teacher_payout_accounts';

    protected $fillable = [
        'teacher_id',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }
}
