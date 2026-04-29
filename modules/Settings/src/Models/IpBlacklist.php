<?php

namespace Modules\Settings\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpBlacklist extends Model
{
    use HasFactory;
    protected $table = 'ip_blacklists';
    protected $fillable = ['ip_address', 'reason'];
}
