<?php

namespace Modules\Promotions\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Promotions\src\Models\Promotion;
use Modules\Students\src\Models\Student;
use Modules\Courses\src\Models\Courses;
use Modules\Promotions\src\Http\Requests\PromotionRequest;
use App\Mail\TeacherPromotionMail;
use App\Notifications\StudentNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\Scopes\ActiveScope;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Illuminate\Support\Carbon;

class PromotionController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
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

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_send_promotions')) {
            return $featureRedirect;
        }

        $courseOptions = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh', 'slug', 'slug_en', 'slug_ko', 'slug_ja', 'slug_zh']);

        $promotions = Promotion::query()
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $availablePromotionStudents = $this->resolvePromotionStudentOptions($teacher);
        $recipientPreviewCount = count($this->resolvePromotionRecipientIds(
            $teacher,
            (string) $request->query('recipient_mode', 'all'),
            (int) $request->query('course_id', 0) ?: null,
            (int) $request->query('recent_purchase_days', 0) ?: null,
            (int) $request->query('inactive_learning_days', 0) ?: null,
            (array) $request->query('student_ids', [])
        ));

        $promotionTemplates = $this->resolvePromotionTemplateDefinitions($teacher);
        $teacherPublicUrl = $this->resolveTeacherPromotionFallbackUrl($teacher, app()->getLocale());

        $recipientMode = (string) $request->query('recipient_mode', 'all');
        $recentPurchaseDays = (int) $request->query('recent_purchase_days', 0);
        $inactiveLearningDays = (int) $request->query('inactive_learning_days', 0);
        $selectedStudentIds = (array) $request->query('student_ids', []);
        $selectedCourseId = (int) $request->query('course_id', 0);

        $pageTitle = __('promotions::teacher/promotions.title');
        $pageName = $pageTitle;

        return view('promotions::teacher.promotions.index', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'promotions',
            'courseOptions',
            'recipientPreviewCount',
            'promotionTemplates',
            'teacherPublicUrl',
            'availablePromotionStudents',
            'selectedCourseId',
            'recipientMode',
            'recentPurchaseDays',
            'inactiveLearningDays',
            'selectedStudentIds'
        ));
    }

    public function store(PromotionRequest $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_send_promotions', 'teacher.dashboard.promotions')) {
            return $featureRedirect;
        }

        $data = $request->validated();
        $recipientMode = trim((string) ($data['recipient_mode'] ?? 'all'));
        $selectedStudentIds = collect($data['student_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $courseId = !empty($data['course_id']) ? (int) $data['course_id'] : null;
        $recentPurchaseDays = !empty($data['recent_purchase_days']) ? (int) $data['recent_purchase_days'] : null;
        $inactiveLearningDays = !empty($data['inactive_learning_days']) ? (int) $data['inactive_learning_days'] : null;
        $promotionTemplate = trim((string) ($data['promotion_template'] ?? 'custom'));
        $messageHtml = trim((string) ($data['message_html'] ?? ''));
        $messagePlain = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($messageHtml, ENT_QUOTES, 'UTF-8'))));
        $ctaEnabled = $request->boolean('cta_enabled');
        $ctaLabel = $ctaEnabled ? trim((string) ($data['cta_label'] ?? '')) : '';
        $ctaUrl = $ctaEnabled ? trim((string) ($data['cta_url'] ?? '')) : '';
        $sendViaWeb = $request->boolean('send_via_web', true);
        $sendViaEmail = $request->boolean('send_via_email', true);
        $selectedCourse = null;

        if ($courseId) {
            $selectedCourse = Courses::query()
                ->withoutGlobalScope(ActiveScope::class)
                ->where('teacher_id', $teacher->id)
                ->findOrFail($courseId);
        }

        $recipientIds = $this->resolvePromotionRecipientIds($teacher, $recipientMode, $courseId, $recentPurchaseDays, $inactiveLearningDays, $selectedStudentIds);
        if (empty($recipientIds)) {
            return back()
                ->withInput()
                ->with('msg_danger', __('promotions::teacher/promotions.flash.no_recipients'));
        }

        $promotion = DB::transaction(function () use ($teacher, $data, $recipientMode, $selectedStudentIds, $courseId, $recentPurchaseDays, $inactiveLearningDays, $recipientIds, $promotionTemplate, $messageHtml, $messagePlain, $ctaEnabled, $ctaLabel, $ctaUrl, $sendViaWeb, $sendViaEmail) {
            return Promotion::query()->create([
                'teacher_id' => $teacher->id,
                'course_id' => $courseId,
                'created_by_student_id' => auth('students')->id(),
                'title' => trim((string) $data['title']),
                'message' => $messagePlain,
                'audience_type' => $this->resolvePromotionAudienceType($recipientMode, $courseId, $recentPurchaseDays, $inactiveLearningDays),
                'recipient_count' => count($recipientIds),
                'filters' => array_filter([
                    'recipient_mode' => $recipientMode,
                    'student_ids' => $recipientMode === 'manual' ? $selectedStudentIds : null,
                    'recent_purchase_days' => $recentPurchaseDays,
                    'inactive_learning_days' => $inactiveLearningDays,
                    'template' => $promotionTemplate !== '' ? $promotionTemplate : 'custom',
                    'message_html' => $messageHtml !== '' ? $messageHtml : null,
                    'cta_enabled' => $ctaEnabled ? 1 : null,
                    'cta_label' => $ctaLabel !== '' ? $ctaLabel : null,
                    'cta_url' => $ctaUrl !== '' ? $ctaUrl : null,
                    'send_via_web' => $sendViaWeb,
                    'send_via_email' => $sendViaEmail,
                ], fn ($value) => $value !== null && $value !== ''),
            ]);
        });

        activity_log(
            action: 'send_promotion',
            subject: $promotion,
            properties: [
                'data' => [
                    'title'           => $promotion->title,
                    'recipient_count' => $promotion->recipient_count,
                    'audience_type'   => $promotion->audience_type,
                    'course_id'       => $promotion->course_id,
                    'send_via_web'    => $sendViaWeb,
                    'send_via_email'  => $sendViaEmail,
                ],
            ],
            logName: 'teacher_promotion_management',
        );

        $notificationPayload = $this->buildPromotionNotificationPayload($teacher, $promotion, $selectedCourse);

        Student::query()
            ->whereIn('id', $recipientIds)
            ->chunkById(100, function ($students) use ($notificationPayload, $teacher, $promotion, $selectedCourse, $sendViaWeb, $sendViaEmail) {
                foreach ($students as $student) {
                    if ($sendViaWeb) {
                        $student->notify(new StudentNotification($notificationPayload));
                    }

                    if ($sendViaEmail && !empty($student->email)) {
                        $locale = method_exists($student, 'preferredLocale')
                            ? (string) $student->preferredLocale()
                            : (string) app()->getLocale();

                        Mail::to($student->email)
                            ->locale($locale)
                            ->queue(TeacherPromotionMail::fromModels($teacher, $promotion, $student, $locale, $selectedCourse));
                    }
                }
            });

        return redirect()
            ->route('teacher.dashboard.promotions') // Route name remains same for now, will update in routes/web.php
            ->with('msg_success', __('promotions::teacher/promotions.flash.sent', [
                'count' => $promotion->recipient_count,
            ]));
    }

    protected function buildPromotionNotificationPayload(\Modules\Teacher\src\Models\Teacher $teacher, Promotion $promotion, $course = null): array
    {
        $teacherName = $teacher->name_locale ?: $teacher->name ?: __('promotions::teacher/promotions.form.instructor_fallback');
        $courseName = $course ? (localizedModelField($course, 'name', app()->getLocale()) ?: __('promotions::teacher/promotions.form.unknown_course')) : null;
        $title = $promotion->title;
        $message = $promotion->message;
        $template = trim((string) ($promotion->filters['template'] ?? 'custom'));
        $messageHtml = $promotion->filters['message_html'] ?? null;
        $ctaEnabled = !empty($promotion->filters['cta_enabled']);
        $ctaLabel = $promotion->filters['cta_label'] ?? null;
        $ctaUrl = $promotion->filters['cta_url'] ?? null;
        $url = route('students.account.promotions.show', ['promotion' => $promotion->id, 'locale' => app()->getLocale()]);

        return [
            'type' => 'teacher.promotion',
            'title' => $title,
            'title_translations' => [
                app()->getLocale() => $title,
            ],
            'message' => $message,
            'message_translations' => [
                app()->getLocale() => $message,
            ],
            'url' => $url,
            'severity' => 'primary',
            'icon' => 'fas fa-bullhorn',
            'entity_type' => Promotion::class,
            'entity_id' => $promotion->id,
            'meta' => [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacherName,
                'course_id' => $course?->id,
                'course_name' => $courseName,
                'promotion_id' => $promotion->id,
                'template' => $template,
                'message_html' => $messageHtml,
                'cta_enabled' => $ctaEnabled,
                'cta_label' => $ctaLabel,
                'cta_url' => $ctaUrl,
            ],
        ];
    }

    public function test(PromotionRequest $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_send_promotions', 'teacher.dashboard.promotions')) {
            return $featureRedirect;
        }

        $student = auth('students')->user();
        if (!$student || empty($student->email)) {
            return back()
                ->withInput()
                ->with('msg_danger', __('promotions::teacher/promotions.flash.test_email_missing'));
        }

        $data = $request->validated();
        $recipientMode = trim((string) ($data['recipient_mode'] ?? 'all'));
        $selectedStudentIds = collect($data['student_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $courseId = !empty($data['course_id']) ? (int) $data['course_id'] : null;
        $messageHtml = trim((string) ($data['message_html'] ?? ''));
        $messagePlain = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($messageHtml, ENT_QUOTES, 'UTF-8'))));
        $ctaEnabled = $request->boolean('cta_enabled');
        $ctaLabel = $ctaEnabled ? trim((string) ($data['cta_label'] ?? '')) : '';
        $ctaUrl = $ctaEnabled ? trim((string) ($data['cta_url'] ?? '')) : '';
        $selectedCourse = null;

        if ($courseId) {
            $selectedCourse = Courses::query()
                ->withoutGlobalScope(ActiveScope::class)
                ->where('teacher_id', $teacher->id)
                ->findOrFail($courseId);
        }

        $promotion = new Promotion([
            'teacher_id' => $teacher->id,
            'course_id' => $courseId,
            'created_by_student_id' => $student->id,
            'title' => trim((string) ($data['title'] ?? '')),
            'message' => $messagePlain,
            'audience_type' => 'test_send',
            'recipient_count' => 1,
            'filters' => array_filter([
                'recipient_mode' => $recipientMode,
                'student_ids' => $recipientMode === 'manual' ? $selectedStudentIds : null,
                'template' => trim((string) ($data['promotion_template'] ?? 'custom')) ?: 'custom',
                'message_html' => $messageHtml !== '' ? $messageHtml : null,
                'cta_enabled' => $ctaEnabled ? 1 : null,
                'cta_label' => $ctaLabel !== '' ? $ctaLabel : null,
                'cta_url' => $ctaUrl !== '' ? $ctaUrl : null,
            ], fn ($value) => $value !== null && $value !== ''),
        ]);

        $locale = method_exists($student, 'preferredLocale')
            ? (string) $student->preferredLocale()
            : (string) app()->getLocale();


        Mail::to($student->email)
            ->locale($locale)
            ->queue(TeacherPromotionMail::fromModels($teacher, $promotion, $student, $locale, $selectedCourse));

        return back()
            ->withInput()
            ->with('msg_success', __('promotions::teacher/promotions.flash.test_sent', ['email' => $student->email]));
    }

    protected function resolvePromotionTemplateDefinitions(\Modules\Teacher\src\Models\Teacher $teacher): array
    {
        $teacherName = trim((string) ($teacher->name_locale ?: $teacher->name ?: __('promotions::teacher/promotions.form.instructor_fallback')));

        return [
            'custom' => [
                'label' => __('promotions::teacher/promotions.form.custom_label'),
                'title' => '',
                'html' => __('promotions::teacher/promotions.form.custom_html'),
                'cta_enabled' => false,
                'cta_label' => '',
            ],
            'flash_sale' => [
                'label' => __('promotions::teacher/promotions.form.flash_sale.label'),
                'title' => __('promotions::teacher/promotions.form.flash_sale.title', ['teacher' => $teacherName]),
                'html' => __('promotions::teacher/promotions.form.flash_sale.html'),
                'cta_enabled' => true,
                'cta_label' => __('promotions::teacher/promotions.form.flash_sale.cta'),
            ],
            'reactivation' => [
                'label' => __('promotions::teacher/promotions.form.reactivation.label'),
                'title' => __('promotions::teacher/promotions.form.reactivation.title', ['teacher' => $teacherName]),
                'html' => __('promotions::teacher/promotions.form.reactivation.html'),
                'cta_enabled' => true,
                'cta_label' => __('promotions::teacher/promotions.form.reactivation.cta'),
            ],
            'new_course' => [
                'label' => __('promotions::teacher/promotions.form.new_course.label'),
                'title' => __('promotions::teacher/promotions.form.new_course.title'),
                'html' => __('promotions::teacher/promotions.form.new_course.html'),
                'cta_enabled' => true,
                'cta_label' => __('promotions::teacher/promotions.form.new_course.cta'),
            ],
        ];
    }
}
