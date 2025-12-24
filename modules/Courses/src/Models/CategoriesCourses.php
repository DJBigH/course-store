<?php

namespace Modules\Courses\Src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Categories\Src\Models\Category;

class CategoriesCourses extends Model{
    protected $table = 'categories_courses';

    protected $fillable = [
        'id',
        'category_id',
        'course_id',
        'created_at',
        'updated_at'
    ];

    public function categories(){
        $this->belongsToMany(Category::class);
    }

    public function courses(){
        $this->belongsToMany(Courses::class);
    }
}