<?php

namespace Modules\Video\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Courses\src\Models\Courses;
use Modules\Lessons\src\Models\Lesson;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'size'
    ];

    protected $attributes = [
        'size' => 0
    ];

    public function courses()
    {
        return $this->hasMany(Lesson::class, 'video_id','id');
    }
}
