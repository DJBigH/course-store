<?php

namespace Modules\Students\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\Order;
use Modules\Teacher\src\Models\Teacher;

class Coupons extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'coupons';

    protected $fillable = [
        'teacher_id',
        'code',
        'discount_type',
        'discount_value',
        'total_condition',
        'count',
        'per_student_once',
        'start_date',
        'end_date',
        'package_locked_at',
        'package_lock_reason',
        'is_package_priority',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'per_student_once' => 'boolean',
        'is_package_priority' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'package_locked_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'coupons_students', 'coupon_id', 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function courses()
    {
        return $this->belongsToMany(Courses::class, 'coupons_courses', 'coupon_id', 'course_id')->withoutGlobalScopes();
    }

    public function bundles()
    {
        return $this->belongsToMany(
            \Modules\Courses\src\Models\CourseBundle::class,
            'coupons_teacher_course_bundles',
            'coupon_id',
            'bundle_id'
        );
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
            ->whereNull('package_locked_at')
            ->whereRaw(
                '(coupons.count is null or coupons.count = 0 or (select count(*) from coupons_usage where coupons_usage.coupon_id = coupons.id) < coupons.count)'
            );
    }

    public function scopeVisibleForStudent($query, ?int $studentId = null)
    {
        if (!$studentId) {
            return $query;
        }

        return $query->where(function ($subQuery) use ($studentId) {
            $subQuery->where('per_student_once', false)
                ->orWhereDoesntHave('usagescoupon', function ($usageQuery) use ($studentId) {
                    $usageQuery->where('student_id', $studentId);
                });
        });
    }
}
