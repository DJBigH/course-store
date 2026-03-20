<?php

namespace Modules\Orders\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\Student;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';

    protected $fillable = [
        'id',
        'code',
        'student_id',
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
        'created_at',
        'updated_at',
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
}
