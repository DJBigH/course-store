<?php

namespace Modules\Students\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Orders\src\Models\Order;

class CouponUsage extends Model
{
    use HasFactory;
    protected $table = 'coupons_usage';

    protected $fillable = [
        'coupon_id',
        'order_id',
        'student_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}