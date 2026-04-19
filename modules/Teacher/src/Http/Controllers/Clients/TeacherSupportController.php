<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Contacts\src\Models\Contacts;
use App\Models\User;
use Modules\Teacher\src\Models\Teacher;
use Modules\Contacts\src\Notifications\NewContactNotification;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\Teacher\src\Support\TeacherPackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;

class TeacherSupportController extends Controller
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

    public function support()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = __('courses::teacher/messages.pages.support');
        $pageName = $pageTitle;
        $items = Contacts::query()
            ->where('teacher_id', $teacher->id)
            ->where('source', 'teacher_portal')
            ->whereIn('submission_type', [Contacts::TYPE_FEEDBACK, Contacts::TYPE_REPORT])
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('teacher::clients.dashboard.support', compact('pageTitle', 'pageName', 'teacher', 'items'));
    }

    public function storeSupport(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $data = $request->validate([
            'submission_type' => ['required', 'in:' . Contacts::TYPE_FEEDBACK . ',' . Contacts::TYPE_REPORT],
            'category' => ['required', 'in:' . implode(',', array_filter(Contacts::categories(), fn ($item) => $item !== 'general_contact'))],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ]);

        $student = auth('students')->user();

        $contact = Contacts::query()->create([
            'name' => $student?->name ?: ($teacher->name_locale ?: $teacher->name),
            'phone' => $student?->phone,
            'email' => $student?->email,
            'subject' => $data['subject'],
            'submission_type' => $data['submission_type'],
            'category' => $data['category'],
            'message' => $data['message'],
            'status' => 0,
            'workflow_status' => Contacts::STATUS_NEW,
            'source' => 'teacher_portal',
            'page_url' => $data['page_url'] ?: route('teacher.dashboard.support'),
            'student_id' => $student?->id,
            'teacher_id' => $teacher->id,
        ]);

        $admins = User::query()->inGroup('super_admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewContactNotification($contact));
        }

        return redirect()
            ->route('teacher.dashboard.support')
            ->with('msg_success', __('courses::teacher/messages.support.flash.sent'));
    }
}
