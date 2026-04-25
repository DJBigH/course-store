<?php

namespace Modules\Contacts\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Contacts\src\Models\Contacts;
use Modules\User\src\Models\User;
use App\Notifications\NewContactNotification;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
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
        protected PackageLifecycleManager $packageLifecycleManager,
        protected PackageUsageResolver $packageUsageResolver,
        protected TeacherNotificationCenter $notificationCenter,
    ) {}

    public function support(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $request->ajax() 
                ? response()->json(['error' => 'Unauthorized'], 401)
                : $this->redirectToStatus();
        }

        $items = Contacts::query()
            ->where('teacher_id', $teacher->id)
            ->where('source', 'teacher_portal')
            ->whereIn('submission_type', [Contacts::TYPE_FEEDBACK, Contacts::TYPE_REPORT])
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('contacts::teacher.partials.support_history', compact('items'))->render();
        }

        $pageTitle = __('contacts::teacher.support.page_title');
        $pageName = $pageTitle;

        return view('contacts::teacher.support', compact('pageTitle', 'pageName', 'teacher', 'items'));
    }

    public function storeSupport(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $request->ajax() 
                ? response()->json(['error' => 'Unauthorized'], 401)
                : $this->redirectToStatus();
        }

        try {
            // Clean HTML tags for empty content checking
            $cleanSubject = trim(strip_tags($request->input('subject')));
            $cleanMessage = trim(strip_tags($request->input('message')));

            if (empty($cleanSubject) || empty($cleanMessage)) {
                $errors = [];
                if (empty($cleanSubject)) $errors['subject'] = ['Tiêu đề không được để trống (Chỉ chứa thẻ HTML không hợp lệ).'];
                if (empty($cleanMessage)) $errors['message'] = ['Nội dung không được để trống (Chỉ chứa thẻ HTML không hợp lệ).'];
                
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'errors' => $errors], 422);
                }
                return back()->withErrors($errors)->withInput();
            }

            $data = $request->validate([
                'submission_type' => ['required', 'in:' . Contacts::TYPE_FEEDBACK . ',' . Contacts::TYPE_REPORT],
                'category' => ['required', 'in:' . implode(',', array_filter(Contacts::categories(), fn ($item) => $item !== 'general_contact'))],
                'subject' => ['required', 'string', 'max:500'],
                'message' => ['required', 'string', 'max:10000'],
                'page_url' => ['nullable', 'string', 'max:500'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

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

        $this->logTeacherSupportActivity(
            $teacher,
            $contact,
            'support_request_sent',
            "Gửi yêu cầu hỗ trợ: {$contact->subject}"
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('contacts::teacher.support.flash.sent')
            ]);
        }

        return redirect()
            ->route('teacher.dashboard.support')
            ->with('msg_success', __('contacts::teacher.support.flash.sent'));
    }
}
