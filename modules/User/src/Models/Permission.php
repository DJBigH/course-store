<?php

namespace Modules\User\src\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use SoftDeletes;

    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_permission', 'permission_id', 'group_id');
    }
}
