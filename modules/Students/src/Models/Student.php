<?php

namespace Modules\Students\src\Models;

use App\Notifications\EmailVerifyQueued;
use App\Notifications\ResetPasswordQueued;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\Order;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Teacher\src\Models\TeacherRating;
use Modules\Courses\src\Models\CourseRating;

class Student extends Authenticatable implements MustVerifyEmail, CanResetPassword, HasLocalePreference
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected static function booted()
    {
        static::created(function ($student) {
            try {
                $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
                $botToken = config('services.telegram.bot_token');
                $chatId = config('services.telegram.chat_id');

                if ($isEnabled === '1' && $botToken && $chatId) {
                    $text = "🧑‍🎓 <b>[HỌC VIÊN MỚI ĐĂNG KÝ]</b>\n\n";
                    $text .= "👤 <b>Họ tên:</b> {$student->name}\n";
                    $text .= "✉️ <b>Email:</b> <code>{$student->email}</code>\n";
                    $text .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i:s d/m/Y');

                    \App\Jobs\SendTelegramNotification::dispatch($chatId, $text, $botToken);
                }
            } catch (\Exception $e) {
                // Fail silently
            }
        });
    }

    protected $table = 'students';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'preferred_locale',
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
        'deleted_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_email_enabled' => 'boolean',
        'two_factor_email_enabled_at' => 'datetime',
        'two_factor_email_code_expires_at' => 'datetime',
        'two_factor_email_code_sent_at' => 'datetime',
        'last_login_at' => 'datetime',
        'deleted_at' => 'datetime',
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
        $locale = (string) ($this->preferred_locale ?? '');

        if (in_array($locale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
            return $locale;
        }

        return app()->getLocale() ?: config('app.locale', 'vi');
    }

    public function courses()
    {
        return $this->belongsToMany(
            Courses::class,
            'students_courses',
            'student_id',
            'course_id'
        )->withoutGlobalScopes([\App\Models\Scopes\ActiveScope::class])
            ->whereIn('courses.status', [1, 2])
            ->withPivot('status');
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

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'student_id', 'id');
    }

    public function teacherApplications()
    {
        return $this->hasMany(TeacherApplication::class, 'student_id', 'id');
    }

    public function courseRatings()
    {
        return $this->hasMany(CourseRating::class, 'student_id', 'id');
    }

    public function teacherRatings()
    {
        return $this->hasMany(TeacherRating::class, 'student_id', 'id');
    }
}
