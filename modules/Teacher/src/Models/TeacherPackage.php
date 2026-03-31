<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPackage extends Model
{
    protected $table = 'teacher_packages';

    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'billing_cycle',
        'course_limit',
        'commission_rate',
        'priority_review',
        'support_level',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'commission_rate' => 'float',
        'priority_review' => 'boolean',
        'status' => 'boolean',
    ];

    public function applications()
    {
        return $this->hasMany(TeacherApplication::class, 'package_id', 'id');
    }

    public function getEffectiveCourseLimitAttribute(): ?int
    {
        if ($this->code === 'free') {
            return max((int) ($this->course_limit ?? 0), 2);
        }

        return $this->course_limit;
    }
}
