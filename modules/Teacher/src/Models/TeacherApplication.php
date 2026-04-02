<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;
use Modules\User\src\Models\User;

class TeacherApplication extends Model
{
    protected $table = 'teacher_applications';

    protected $fillable = [
        'student_id',
        'applicant_type',
        'teacher_id',
        'package_id',
        'payment_method',
        'coupon_code',
        'discount_amount',
        'status',
        'full_name',
        'display_name',
        'headline',
        'bio',
        'experience_years',
        'specialties',
        'phone',
        'email',
        'locale',
        'portfolio_url',
        'facebook_url',
        'youtube_url',
        'linkedin_url',
        'intro_video_url',
        'cv_file',
        'identity_file',
        'submitted_at',
        'reviewed_at',
        'account_created_at',
        'account_credentials_sent_at',
        'reviewed_by',
        'admin_note',
    ];

    protected $casts = [
        'specialties' => 'array',
        'discount_amount' => 'float',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'account_created_at' => 'datetime',
        'account_credentials_sent_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function package()
    {
        return $this->belongsTo(TeacherPackage::class, 'package_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function getDisplayStatusAttribute(): string
    {
        return match ($this->status) {
            'draft' => __('teacher::portal.status_labels.draft'),
            'pending_payment' => __('teacher::portal.status_labels.pending_payment'),
            'pending_review' => __('teacher::portal.status_labels.pending_review'),
            'approved' => __('teacher::portal.status_labels.approved'),
            'rejected' => __('teacher::portal.status_labels.rejected'),
            'cancelled' => __('teacher::portal.status_labels.cancelled'),
            default => ucfirst((string) $this->status),
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'vnpay' => __('teacher::portal.payment_methods.vnpay'),
            'momo' => __('teacher::portal.payment_methods.momo'),
            default => __('teacher::portal.payment_methods.bank_transfer'),
        };
    }

    public function getBasePriceAttribute(): float
    {
        return (float) ($this->package?->price ?? 0);
    }

    public function getPayableAmountAttribute(): float
    {
        return max($this->base_price - (float) ($this->discount_amount ?? 0), 0);
    }
}
