<?php

namespace Modules\ActiveLogs\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\ActiveLogs\src\Repositories\ActiveLogsRepositoryInterface;

class ActiveLogController extends Controller
{
    protected $activeLogRepository;
    public function __construct(ActiveLogsRepositoryInterface $activeLogsRepository)
    {
        $this->activeLogRepository = $activeLogsRepository;
    }

    public function index(Request $request)
    {
        $pageTitle = 'Log tổng hệ thống';

        $data = $this->activeLogRepository->getAllLogs($request);

        return view('activelogs::index', array_merge(
            ['pageTitle' => $pageTitle],
            $data
        ));
    }
}
