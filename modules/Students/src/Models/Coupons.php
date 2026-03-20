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

    public function scopeActive($query, $now = null)
    {
        $now = $now ?: now();

        return $query
            ->where(function ($sub) use ($now) {
                $sub->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($sub) use ($now) {
                $sub->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->whereRaw(
                '(coupons.count is null or coupons.count = 0 or (select count(*) from coupons_usage where coupons_usage.coupon_id = coupons.id) < coupons.count)'
            );
    }
}
