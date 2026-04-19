<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherAnnouncement;
use Modules\Teacher\src\Models\TeacherAnnouncementRead;
use Modules\Teacher\src\Models\TeacherNotificationRead;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\Teacher\src\Support\TeacherPackageUsageResolver;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;

class TeacherNotificationController extends Controller
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

    public function notifications()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $student = auth('students')->user();
        $notificationSummary = $this->notificationCenter->summary($student, 40);
        $notifications = $notificationSummary['items'];

        $pageTitle = __('courses::teacher/messages.pages.notifications');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.notifications', compact('pageTitle', 'pageName', 'teacher', 'notifications'));
    }

    public function readAnnouncement(int $announcementId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $student = auth('students')->user();
        $currentPackageId = $teacher->currentPackage()?->id;
        $announcement = TeacherAnnouncement::query()
            ->active()
            ->where(function ($query) use ($currentPackageId) {
                $query->whereDoesntHave('packages');

                if ($currentPackageId) {
                    $query->orWhereHas('packages', function ($packageQuery) use ($currentPackageId) {
                        $packageQuery->where('teacher_packages.id', $currentPackageId);
                    });
                }
            })
            ->findOrFail($announcementId);

        TeacherAnnouncementRead::query()->updateOrCreate(
            [
                'announcement_id' => $announcement->id,
                'student_id' => $student->id,
            ],
            [
                'read_at' => now(),
            ]
        );

        TeacherNotificationRead::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'notification_key' => 'announcement:' . $announcement->id . ':' . ($announcement->updated_at?->timestamp ?? 0),
            ],
            [
                'read_at' => now(),
            ]
        );

        $targetUrl = trim((string) ($announcement->action_url ?? ''));

        return redirect()->to($targetUrl !== '' ? $targetUrl : route('teacher.dashboard.notifications'));
    }

    public function readNotification(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $student = auth('students')->user();
        $payload = $request->validate([
            'key' => ['required', 'string', 'max:191'],
            'redirect' => ['nullable', 'string', 'max:1000'],
        ]);

        TeacherNotificationRead::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'notification_key' => $payload['key'],
            ],
            [
                'read_at' => now(),
            ]
        );

        return redirect()->to($this->sanitizeTeacherNotificationRedirect($payload['redirect'] ?? null));
    }
}
