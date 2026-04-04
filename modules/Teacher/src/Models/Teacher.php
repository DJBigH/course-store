<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Students\src\Models\Student;

class Teacher extends Model
{
    use SoftDeletes;

    protected $table = 'teacher';

    protected $fillable = [
        'id',
        'name',
        'name_en',
        'name_ko',
        'name_ja',
        'name_zh',
        'slug',
        'slug_en',
        'slug_ko',
        'slug_ja',
        'slug_zh',
        'description',
        'description_en',
        'description_ko',
        'description_ja',
        'description_zh',
        'exp',
        'image',
        'student_id',
        'application_id',
        'status',
        'commission_rate',
        'approved_at',
        'approved_by',
        'package_started_at',
        'package_expires_at',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'commission_rate' => 'float',
        'package_started_at' => 'datetime',
        'package_expires_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function application()
    {
        return $this->belongsTo(TeacherApplication::class, 'application_id', 'id');
    }

    public function payoutRequests()
    {
        return $this->hasMany(TeacherPayoutRequest::class, 'teacher_id', 'id');
    }

    public function studentNotes()
    {
        return $this->hasMany(TeacherStudentNote::class, 'teacher_id', 'id');
    }

    public function getDescriptionLocaleAttribute(): string
    {
        if (app()->getLocale() === 'zh') {
            return $this->description_zh ?: $this->description ?: $this->description_en ?: $this->description_ko ?: $this->description_ja ?: '';
        }

        if (app()->getLocale() === 'ja') {
            return $this->description_ja ?: $this->description ?: $this->description_en ?: $this->description_ko ?: $this->description_zh ?: '';
        }

        if (app()->getLocale() === 'ko') {
            return $this->description_ko ?: $this->description ?: $this->description_en ?: $this->description_ja ?: $this->description_zh ?: '';
        }

        if (app()->getLocale() === 'en') {
            return $this->description_en ?: $this->description ?: $this->description_ko ?: $this->description_ja ?: $this->description_zh ?: '';
        }

        return $this->description ?: $this->description_en ?: $this->description_ko ?: $this->description_ja ?: $this->description_zh ?: '';
    }

    public function getNameLocaleAttribute(): string
    {
        if (app()->getLocale() === 'zh') {
            return $this->name_zh ?: $this->name ?: $this->name_en ?: $this->name_ko ?: $this->name_ja ?: '';
        }

        if (app()->getLocale() === 'ja') {
            return $this->name_ja ?: $this->name ?: $this->name_en ?: $this->name_ko ?: $this->name_zh ?: '';
        }

        if (app()->getLocale() === 'ko') {
            return $this->name_ko ?: $this->name ?: $this->name_en ?: $this->name_ja ?: $this->name_zh ?: '';
        }

        if (app()->getLocale() === 'en') {
            return $this->name_en ?: $this->name ?: $this->name_ko ?: $this->name_ja ?: $this->name_zh ?: '';
        }

        return $this->name ?: $this->name_en ?: $this->name_ko ?: $this->name_ja ?: $this->name_zh ?: '';
    }

    public function getSlugLocaleAttribute(): string
    {
        if (app()->getLocale() === 'zh') {
            return $this->slug_zh ?: $this->slug ?: $this->slug_en ?: $this->slug_ko ?: $this->slug_ja ?: '';
        }

        if (app()->getLocale() === 'ja') {
            return $this->slug_ja ?: $this->slug ?: $this->slug_en ?: $this->slug_ko ?: $this->slug_zh ?: '';
        }

        if (app()->getLocale() === 'ko') {
            return $this->slug_ko ?: $this->slug ?: $this->slug_en ?: $this->slug_ja ?: $this->slug_zh ?: '';
        }

        if (app()->getLocale() === 'en') {
            return $this->slug_en ?: $this->slug ?: $this->slug_ko ?: $this->slug_ja ?: $this->slug_zh ?: '';
        }

        return $this->slug ?: $this->slug_en ?: $this->slug_ko ?: $this->slug_ja ?: $this->slug_zh ?: '';
    }
}
