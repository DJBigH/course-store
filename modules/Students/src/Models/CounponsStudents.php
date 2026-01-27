<?php

namespace Modules\Students\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\Order;

class CounponsStudents extends Model
{
    use HasFactory;
    protected $table = 'coupons_students';

    protected $fillable = [
        'coupon_id',
        'student_id',
    ];

    public function students(){
        return $this->hasMany(Student::class,'student_id','id');
    }

    public function coupons(){
        return $this->hasMany(Coupons::class,'coupon_id','id');
    }
}
