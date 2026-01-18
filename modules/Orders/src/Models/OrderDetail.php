<?php

namespace Modules\Orders\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Courses\src\Models\Courses;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'orders_detail';

    protected $fillable = [
        'id',
        'order_id',
        'course_id',
        'price',
        'created_at',
        'updated_at',
    ];

    public function courses(){
        return $this->belongsTo(Courses::class,'course_id','id')->withoutGlobalScopes();
    }
}
