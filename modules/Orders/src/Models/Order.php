<?php

namespace Modules\Orders\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\TeacherAffiliateLink;
use Modules\Teacher\src\Models\TeacherCourseBundle;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'id',
        'code',
        'student_id',
        'bundle_id',
        'affiliate_link_id',
        'customer_name_snapshot',
        'customer_email_snapshot',
        'customer_phone_snapshot',
        'customer_address_snapshot',
        'total',
        'discount',
        'coupon',
        'status_id',
        'payment_date',
        'payment_complete_date',
        'payment_method',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'total' => 'float',
        'discount' => 'float',
        'payment_date' => 'datetime',
        'payment_complete_date' => 'datetime',
    ];

    public function status(){
        return $this->belongsTo(OrderStatus::class,'status_id','id');
    }

    public function detail(){
        return $this->hasMany(OrderDetail::class, 'order_id','id');
    }
    
    public function coupon(){
        return $this->belongsTo(Coupons::class,'coupon','id');
    }

    public function students(){
        return $this->belongsTo(Student::class,'student_id','id');
    }

    public function bundle()
    {
        return $this->belongsTo(TeacherCourseBundle::class, 'bundle_id', 'id');
    }

    public function affiliateLink()
    {
        return $this->belongsTo(TeacherAffiliateLink::class, 'affiliate_link_id', 'id');
    }

    public function getCustomerNameDisplayAttribute(): string
    {
        $name = (string) ($this->students?->name ?: $this->customer_name_snapshot ?: '-');

        if ($name === '-') {
            return $name;
        }

        return $name . $this->accountStatusSuffix();
    }

    public function getCustomerEmailDisplayAttribute(): string
    {
        $email = (string) ($this->students?->email ?: $this->customer_email_snapshot ?: '-');

        if ($email === '-') {
            return $email;
        }

        return $email . $this->accountStatusSuffix();
    }

    public function getCustomerPhoneDisplayAttribute(): string
    {
        return (string) ($this->students?->phone ?: $this->customer_phone_snapshot ?: '-');
    }

    public function getCustomerAddressDisplayAttribute(): string
    {
        return (string) ($this->students?->address ?: $this->customer_address_snapshot ?: '-');
    }

    protected function accountStatusSuffix(): string
    {
        if (!$this->students && ($this->customer_name_snapshot || $this->customer_email_snapshot)) {
            return ' (' . __('students::clients/account.order_detail.account_deleted') . ')';
        }

        if ($this->students && !$this->students->email_verified_at) {
            return ' (' . __('students::clients/account.order_detail.account_deactivated') . ')';
        }

        return '';
    }

    public function getPaymentMethodCodeAttribute(): string
    {
        if (!empty($this->payment_method)) {
            return (string) $this->payment_method;
        }

        if ((float) max(($this->total ?? 0) - ($this->discount ?? 0), 0) <= 0) {
            return 'free';
        }

        return 'unknown';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method_code) {
            'bank', 'bank_transfer' => 'Chuyển khoản ngân hàng',
            'vnpay' => 'VNPay',
            'momo' => 'MoMo',
            'free' => 'Miễn phí',
            default => 'Chưa xác định',
        };
    }
    public function getPaymentMethodBadgeStyleAttribute(): string
    {
        return match ($this->payment_method_code) {
            'bank', 'bank_transfer' => 'background:#16a34a;color:#ffffff;',
            'vnpay' => 'background:#0f6cbd;color:#ffffff;',
            'momo' => 'background:#a21caf;color:#ffffff;',
            'free' => 'background:#0ea5e9;color:#ffffff;',
            default => 'background:#64748b;color:#ffffff;',
        };
    }
}
