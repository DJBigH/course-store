<?php

namespace Modules\Auth\src\Http\Middlewares;

use Illuminate\Support\Facades\DB;

class DeviceLoginService
{
    public function canLogin(int $studentId, int $maxDevices = 1): bool
    {
        $activeSessions = DB::table('sessions')
            ->where('user_id', $studentId)
            ->count();

        return $activeSessions < $maxDevices;
    }
}
