<?php

namespace Modules\Students\src\Models;

use App\Notifications\EmailVerifyQueued;
use App\Notifications\ResetPasswordQueued;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\Order;

class Student extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
    use HasFactory;
    use Notifiable;

    protected $table = 'students';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'address',
        'phone',
        'remember_token',
        'email_verified_at'
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerifyQueued);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordQueued($token));
    }

    public function courses()
    {
        return $this->belongsToMany(
            Courses::class,
            'students_courses',
            'student_id',
            'course_id'
        )->withPivot('status');
    }

    public function coupons()
    {
        return $this->belongsToMany(
            Coupons::class,
            'coupons_students', // bảng pivot
            'student_id',
            'coupon_id'
        )->withPivot('created_at')->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'student_id', 'id');
    }
}
