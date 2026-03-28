<?php

namespace Modules\Coupons\src\Repositories;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\Student;

class CouponsRepository extends BaseRepository implements CouponsRepositoryInterface
{
    public function getModel()
    {
        return Coupons::class;
    }

    public function verifyCoupon($code, $order)
    {
        $orderId = $order->id;
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $coupon = $this->model->whereCode($code)->first();
        if (!$coupon) {
            return false;
        }
        $students = $coupon->students;

        if ($coupon->count && $coupon->usages->count() >= $coupon->count) {
            return false;
        }
        if ($coupon->total_condition && $order->total < $coupon->total_condition) {
            return false;
        }
        $studentId = Auth::guard('students')->user()->id;

        if ($students->count() && !$students->find(Auth::guard('students')->user()->id)) {
            return false;
        }

        // Coupon có giới hạn lượt dùng
        if ($coupon->count) {

            // CASE 1: Coupon có ràng buộc học viên
            if ($coupon->students()->exists()) {

                $usedCount = DB::table('coupons_usage')
                    ->where('coupon_id', $coupon->id)
                    ->count();
            }
            // CASE 2: Coupon KHÔNG ràng buộc học viên
            else {

                $usedCount = DB::table('coupons_usage')
                    ->where('coupon_id', $coupon->id)
                    ->count();
            }

            if ($usedCount >= $coupon->count) {
                return false;
            }
        }


        $course = $coupon->courses();
        if ($course->count()) {
            $count = $course->whereHas('orderDetail', function ($query) use ($orderId) {
                $query->where('order_id', $orderId);
            })->count();
            if (!$count) {
                return false;
            }
        }
        $startStatus = true;
        $endStatus = true;
        if ($coupon->start_date && $now < $coupon->start_date) {
            $startStatus = false;
        }
        if ($coupon->end_date && $now > $coupon->end_date) {
            $endStatus = false;
        }
        return $startStatus && $endStatus ? $coupon : false;
    }

    public function isCourseCoupon($coupon)
    {
        return $coupon->courses()->count() > 0;
    }

    public function getCourses($coupon, $orderId)
    {
        $course = $coupon->courses()->whereHas('orderDetail', function ($query) use ($orderId) {
            $query->where('order_id', $orderId);
        })->get();
        return $course;
    }

    public function getAllCoupons()
    {
        return $this->model->with(['usages', 'students', 'courses'])->select('id', 'code', 'discount_type', 'discount_value', 'total_condition', 'count', 'start_date', 'end_date')->latest();
    }
}
