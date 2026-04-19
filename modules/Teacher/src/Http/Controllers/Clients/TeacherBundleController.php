<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherCourseBundle;
use Modules\Courses\src\Models\Courses;
use App\Models\Scopes\ActiveScope;
use Modules\Teacher\src\Http\Requests\TeacherCourseBundleRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\Teacher\src\Support\TeacherPackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;

class TeacherBundleController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected TeacherPackageLifecycleManager $packageLifecycleManager,
        protected TeacherNotificationCenter $notificationCenter,
        protected TeacherPackageUsageResolver $packageUsageResolver,
    ) {}

    public function bundles(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_sell_bundles')) {
            return $featureRedirect;
        }

        $bundles = TeacherCourseBundle::query()
            ->withCount('items')
            ->where('teacher_id', $teacher->id)
            ->orderBy('position')
            ->paginate(5);

        $pageTitle = __('teacher::teacher/bundle/list.title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.bundles', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'bundles'
        ));
    }

    public function createBundle()
    {
        return $this->bundleFormResponse();
    }

    public function storeBundle(TeacherCourseBundleRequest $request)
    {
        return $this->persistBundle($request);
    }

    public function editBundle(int $bundleId)
    {
        return $this->bundleFormResponse($bundleId);
    }

    public function updateBundle(TeacherCourseBundleRequest $request, int $bundleId)
    {
        return $this->persistBundle($request, $bundleId);
    }

    public function deleteBundle(int $bundleId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_sell_bundles', 'teacher.dashboard.bundles')) {
            return $featureRedirect;
        }

        $bundle = TeacherCourseBundle::query()
            ->where('teacher_id', $teacher->id)
            ->findOrFail($bundleId);

        $bundle->delete();

        return redirect()
            ->route('teacher.dashboard.bundles')
            ->with('msg_success', __('teacher::teacher/bundle/common.flash.deleted'));
    }
}
