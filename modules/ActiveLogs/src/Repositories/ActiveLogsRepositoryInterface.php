<?php

namespace Modules\ActiveLogs\src\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface ActiveLogsRepositoryInterface extends RepositoryInterface
{
    public function getAllLogs(Request $request);
}
