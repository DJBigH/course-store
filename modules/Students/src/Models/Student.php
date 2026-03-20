<?php

namespace Modules\Students\src\Models;

use App\Notifications\EmailVerifyQueued;
use App\Notifications\ResetPasswordQueued;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\Order;

class Student extends Authenticatable implements MustVerifyEmail, CanResetPassword, HasLocalePreference
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
        'email_verified_at',
        'two_factor_email_enabled',
        'two_factor_email_enabled_at',
        'two_factor_email_code',
        'two_factor_email_purpose',
        'two_factor_email_code_expires_at',
        'two_factor_email_code_sent_at',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'last_login_browser',
        'last_login_platform',
        'last_login_device',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_email_enabled' => 'boolean',
        'two_factor_email_enabled_at' => 'datetime',
        'two_factor_email_code_expires_at' => 'datetime',
        'two_factor_email_code_sent_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify((new EmailVerifyQueued)->locale($this->preferredLocale()));
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify((new ResetPasswordQueued($token))->locale($this->preferredLocale()));
    }

    public function preferredLocale()
    {
        return app()->getLocale() ?: config('app.locale', 'vi');
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
            'coupons_students',
            'student_id',
            'coupon_id'
        )->withPivot('created_at')->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'student_id', 'id');
    }
}
