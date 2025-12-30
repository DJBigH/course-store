<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model{
    protected $table = 'teacher';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'description',
        'exp',
        'image',
        'created_at',
        'updated_at',
    ];
}