<?php

namespace Modules\Categories\Src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Courses\Src\Models\Courses;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'parent_id',
        'created_at',
        'updated_at'
    ];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function subCategories()
    {
        return $this->children()->with('subCategories');
    }

    public function courses()
    {
        return $this->belongsToMany(Courses::class, 'categories_courses');
    }
}
