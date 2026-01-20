<?php

namespace Modules\Students\src\Repositories;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\Student;

class CouponsRepository extends BaseRepository implements CouponsRepositoryInterface
{
    public function getModel()
    {
        return Coupons::class;
    }

    public function verifyCoupon($code,$orderId)
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $coupon = $this->model->whereCode($code)->first();
        if (!$coupon) {
            return false;
        }
        $students = $coupon->students;
        if ($students->count() && !$students->find(Auth::guard('students')->user()->id)) {
            return false;
        }
        $course = $coupon->courses();
        if ($course->count()) {
            $count = $course->whereHas('orderDetail', function ($query) use ($orderId){
                $query->where('order_id',$orderId);
            })->count();
            if(!$count){
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
}
