<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPayoutAccount extends Model
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

    public function changeRequests()
    {
        return $this->hasMany(TeacherPayoutAccountChangeRequest::class, 'replace_payout_account_id', 'id');
    }
}
