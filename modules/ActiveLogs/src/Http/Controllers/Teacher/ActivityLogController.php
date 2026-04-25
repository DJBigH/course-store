<?php

namespace Modules\ActiveLogs\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ActiveLogs\src\Repositories\ActiveLogsRepositoryInterface;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;

use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Packages\src\Support\PackageUsageResolver;

class ActivityLogController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected ActiveLogsRepositoryInterface $activeLogsRepository,
        protected PackageLifecycleManager $packageLifecycleManager,
        protected TeacherNotificationCenter $notificationCenter,
        protected PackageUsageResolver $packageUsageResolver,
    ) {}

    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_view_activity_logs')) {
            return $featureRedirect;
        }

        $result = $this->activeLogsRepository->getTeacherLogs($teacher, $request);

        return view('activelogs::teacher.index', array_merge($result, [
            'pageTitle' => __('activelogs::teacher/messages.activity_logs.title'),
            'pageName' => __('activelogs::teacher/messages.activity_logs.title'),
            'teacher' => $teacher,
        ]));
    }
}
