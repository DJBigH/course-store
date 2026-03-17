<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;
use Modules\User\src\Models\User;

class CourseComment extends Model
{
    use HasFactory;

    protected $table = 'course_comments';

    protected $fillable = [
        'course_id',
        'parent_id',
        'student_id',
        'user_id',
        'content',
        'is_visible',
        'is_flagged',
        'flagged_terms',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_flagged' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->oldest();
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function getAuthorNameAttribute(): string
    {
        if ($this->user_id && $this->admin) {
            return (string) $this->admin->name;
        }

        return (string) ($this->student->name ?? 'Hoc vien');
    }

    public function getAuthorRoleAttribute(): string
    {
        return $this->user_id ? 'admin' : 'student';
    }
}
