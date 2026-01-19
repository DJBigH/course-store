<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CouponsController extends Controller
{


    public function __construct() {}

    public function verify(Request $request) {
        $coupon = $request->coupon;
        return [$coupon];
    }
}
