<?php

namespace Modules\Courses\src\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';
    protected $fillable = ['code', 'rate'];
}
