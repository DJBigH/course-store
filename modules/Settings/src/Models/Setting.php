<?php

namespace Modules\Settings\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $table = 'settings';
    protected $fillable = ['key', 'value'];

    public static function getValue($key)
    {
        return self::where('key', $key)->value('value');
    }
}
