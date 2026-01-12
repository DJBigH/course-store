<?php

namespace Modules\Students\src\Models;

use App\Notifications\EmailVerifyQueued;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable implements MustVerifyEmail
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
}