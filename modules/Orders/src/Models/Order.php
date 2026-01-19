<?php

namespace Modules\Orders\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';

    protected $fillable = [
        'id',
        'student_id',
        'total',
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
}