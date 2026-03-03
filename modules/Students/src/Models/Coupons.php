<?php

namespace Modules\Students\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\Order;

class Coupons extends Model
{
    use HasFactory;
    protected $table = 'coupons';

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'total_condition',
        'count',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'coupons_students', 'coupon_id', 'student_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Courses::class, 'coupons_courses', 'coupon_id', 'course_id')->withoutGlobalScopes();
    }

    public function usages()
    {
        return $this->belongsToMany(Order::class, 'coupons_usage', 'coupon_id', 'order_id');
    }

    public function usagescoupon()
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id');
    }
}
