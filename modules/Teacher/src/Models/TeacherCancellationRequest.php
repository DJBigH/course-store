<?php

namespace Modules\Teacher\src\Models;

use Modules\User\src\Models\User;
use Illuminate\Database\Eloquent\Model;

class TeacherCancellationRequest extends Model
{
    protected $table = 'teacher_cancellation_requests';

    protected $fillable = [
        'teacher_id',
        'reason',
        'status',
        'admin_note',
        'processed_by',
        'processed_at',
        'otp_code',
        'otp_expires_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isValidOtp(string $code): bool
    {
        return $this->otp_code === $code && $this->otp_expires_at && $this->otp_expires_at->isFuture();
    }
}
