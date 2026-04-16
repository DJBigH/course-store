<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Mail\TeacherPromotionMail;
use App\Http\Controllers\Controller;
use App\Notifications\NewContactNotification;
use App\Notifications\StudentNotification;
use App\Notifications\TeacherCourseGiftInvitationNotification;
use App\Models\Scopes\ActiveScope;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Contacts\src\Models\Contacts;
use Modules\Categories\src\Models\Category;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseComment;
use Modules\Courses\src\Models\CourseViewTracking;
use Modules\Students\src\Models\Coupons;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Modules\Lessons\src\Models\Lesson;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Orders\src\Models\OrderStatus;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Models\StudentLessonProgress;
use Modules\Students\src\Models\StudentsCourses;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Http\Requests\TeacherCourseRequest;
use Modules\Teacher\src\Http\Requests\TeacherCourseBundleRequest;
use Modules\Teacher\src\Http\Requests\TeacherLessonRequest;
use Modules\Teacher\src\Http\Requests\TeacherPromotionRequest;
use Modules\Teacher\src\Models\TeacherAnnouncement;
use Modules\Teacher\src\Models\TeacherAnnouncementRead;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Teacher\src\Models\TeacherCourseBundle;
use Modules\Teacher\src\Models\TeacherCourseGrant;
use Modules\Teacher\src\Models\TeacherNotificationRead;
use Modules\Teacher\src\Models\TeacherPayoutAccount;
use Modules\Teacher\src\Models\TeacherPayoutAccountChangeRequest;
use Modules\Teacher\src\Models\TeacherPayoutRequest;
use Modules\Teacher\src\Models\TeacherPackage;
use Modules\Teacher\src\Models\TeacherPromotion;
use Modules\Teacher\src\Models\TeacherStudentNote;
use Modules\Teacher\src\Support\TeacherFinanceCalculator;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;
use Modules\Teacher\src\Support\TeacherPackageUsageResolver;
use Modules\User\src\Models\User;
use Modules\Video\src\Repositories\VideoRepositoryInterface;

class TeacherDashboardController extends Controller
{
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

    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }
        $effectiveCommissionRate = $this->resolveEffectiveCommissionRate($teacher);
        $dashboardRange = $this->resolveTeacherDashboardRange((string) $request->query('range', 'today'));
        $dashboardRangeOptions = $this->resolveTeacherDashboardRangeOptions();

        $coursesQuery = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id);

        $orderDetails = $this->applyTeacherDashboardRangeToPaidQuery(
            $this->paidOrderDetailsQuery($teacher),
            $dashboardRange
        )->get();
        $summary = TeacherFinanceCalculator::summarize(
            $orderDetails,
            fn () => $effectiveCommissionRate
        );
        $payoutRequested = $this->resolveCommittedPayoutAmount($teacher);

        $pageTitle = __('teacher::dashboard.pages.overview');
        $pageName = __('teacher::dashboard.pages.overview');
        $stats = [
            'courses' => (clone $coursesQuery)->count(),
            'active_courses' => (clone $coursesQuery)->where('status', 1)->count(),
            'students' => $orderDetails->pluck('order.student_id')->filter()->unique()->count(),
            'gross_revenue' => $summary['gross_amount'],
            'allocated_discount' => $summary['allocated_discount'],
            'estimated_revenue' => $summary['teacher_revenue'],
            'platform_revenue' => $summary['platform_revenue'],
            'available_balance' => max($summary['teacher_revenue'] - $payoutRequested, 0),
        ];
        $conversionSummary = $this->buildTeacherConversionSummary($teacher, $dashboardRange);
        $revenueInsights = $this->buildTeacherRevenueInsights($teacher, $effectiveCommissionRate, $dashboardRange);
        $coursePerformance = $this->buildTeacherCoursePerformance($teacher, $effectiveCommissionRate, $dashboardRange);
        $recentCourses = $coursesQuery->latest('id')->take(4)->get();
        $recentSales = TeacherFinanceCalculator::decorate($orderDetails->sortByDesc('created_at')->take(6)->values(), fn () => $effectiveCommissionRate);
        $topBundles = $this->resolveTopBundles($teacher);
        $currentPackage = $teacher->application?->package;
        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        $pendingUpgradeStartsAt = $pendingUpgrade?->activates_at;
        $pendingUpgradeIsQueued = $pendingUpgrade?->status === 'approved'
            && $pendingUpgradeStartsAt !== null
            && $pendingUpgrade?->activated_at === null;
        $nextPackage = $this->resolveNextPackage($currentPackage);
        $availablePackageChanges = $this->resolveAvailablePackageChanges($currentPackage);
        $packageSummary = $currentPackage ? [
            'name' => $currentPackage->name_locale ?: $currentPackage->name,
            'badge' => $currentPackage->badge_text_locale ?: strtoupper((string) $currentPackage->code),
            'price' => (float) $currentPackage->price,
            'billing_cycle' => $currentPackage->billing_cycle,
            'course_limit' => $currentPackage->effective_course_limit,
            'commission_rate' => (float) $currentPackage->commission_rate,
            'support' => $currentPackage->support_level_locale ?: '',
            'started_at' => $teacher->package_started_at,
            'expires_at' => $teacher->package_expires_at,
            'days_left' => $this->packageLifecycleManager->daysLeft($teacher),
            'can_upgrade' => $availablePackageChanges->isNotEmpty() && $pendingUpgrade === null,
            'has_higher_package' => $nextPackage !== null,
            'upgrade_name' => $nextPackage?->name_locale ?: $nextPackage?->name,
            'upgrade_url' => route('teacher.dashboard.package.upgrade'),
            'pending_upgrade' => $pendingUpgrade !== null,
            'pending_upgrade_status' => $pendingUpgrade?->display_status,
            'pending_upgrade_url' => $pendingUpgrade ? route('teacher.dashboard.package.upgrade.status') : null,
            'pending_upgrade_name' => $pendingUpgrade?->package?->name_locale ?: $pendingUpgrade?->package?->name,
            'pending_upgrade_starts_at' => $pendingUpgradeStartsAt,
            'pending_upgrade_days_until_activation' => $pendingUpgradeStartsAt
                ? max(now()->startOfDay()->diffInDays($pendingUpgradeStartsAt->copy()->startOfDay(), false), 0)
                : null,
            'pending_upgrade_is_queued' => $pendingUpgradeIsQueued,
        ] : null;

        $overviewPayload = $this->buildTeacherOverviewDashboardPayload(
            $teacher,
            $effectiveCommissionRate,
            $dashboardRange,
            $stats,
            $conversionSummary,
            $revenueInsights,
            $coursePerformance
        );

        if ($request->boolean('ajax')) {
            return response()->json($overviewPayload);
        }

        return view('teacher::clients.dashboard.index', compact('pageTitle', 'pageName', 'teacher', 'stats', 'dashboardRange', 'dashboardRangeOptions', 'conversionSummary', 'revenueInsights', 'coursePerformance', 'recentCourses', 'recentSales', 'topBundles', 'packageSummary', 'effectiveCommissionRate', 'overviewPayload'));
    }

    public function upgradePackage()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        if ($pendingUpgrade) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $currentPackage = $teacher->application?->package;
        $upgradePackages = $this->resolveAvailablePackageChanges($currentPackage);
        if ($upgradePackages->isEmpty()) {
            return redirect()->route('teacher.dashboard.index')
                ->with('msg_danger', __('teacher::dashboard.package.flash.no_upgrade_available'));
        }

        $pageTitle = __('teacher::dashboard.package.upgrade_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.package_upgrade', compact('pageTitle', 'pageName', 'teacher', 'currentPackage', 'upgradePackages'));
    }

    public function notifications()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $student = auth('students')->user();
        $notificationSummary = $this->notificationCenter->summary($student, 40);
        $notifications = $notificationSummary['items'];

        $pageTitle = __('teacher::dashboard.pages.notifications');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.notifications', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'notifications',
            'notificationSummary'
        ));
    }

    public function promotions(Request $request)
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
            ->orderByDesc('id')
            ->get(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh', 'slug', 'slug_en', 'slug_ko', 'slug_ja', 'slug_zh']);

        $oldInput = session()->getOldInput();
        $selectedCourseId = (int) ($oldInput['course_id'] ?? $request->query('course_id', 0));
        $recentPurchaseDays = (int) ($oldInput['recent_purchase_days'] ?? $request->query('recent_purchase_days', 0));
        $inactiveLearningDays = (int) ($oldInput['inactive_learning_days'] ?? $request->query('inactive_learning_days', 0));
        $recipientMode = trim((string) ($oldInput['recipient_mode'] ?? $request->query('recipient_mode', 'all')));
        $selectedStudentIds = collect($oldInput['student_ids'] ?? $request->query('student_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $promotions = TeacherPromotion::query()
            ->with(['course'])
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
        $availablePromotionStudents = $this->resolvePromotionStudentOptions($teacher);
        $recipientPreviewCount = count($this->resolvePromotionRecipientIds(
            $teacher,
            $recipientMode,
            $selectedCourseId > 0 ? $selectedCourseId : null,
            $recentPurchaseDays > 0 ? $recentPurchaseDays : null,
            $inactiveLearningDays > 0 ? $inactiveLearningDays : null,
            $selectedStudentIds
        ));
        $promotionTemplates = $this->resolvePromotionTemplateDefinitions($teacher);
        $teacherPublicUrl = $this->resolveTeacherPromotionFallbackUrl($teacher, app()->getLocale());

        $pageTitle = __('teacher::dashboard.pages.promotions');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.promotions', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'courseOptions',
            'promotions',
            'selectedCourseId',
            'recentPurchaseDays',
            'inactiveLearningDays',
            'recipientMode',
            'selectedStudentIds',
            'recipientPreviewCount',
            'promotionTemplates',
            'teacherPublicUrl',
            'availablePromotionStudents'
        ));
    }

    public function storePromotion(TeacherPromotionRequest $request)
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
                ->with('msg_danger', __('teacher::dashboard.promotions.flash.no_recipients'));
        }

        $promotion = DB::transaction(function () use ($teacher, $data, $recipientMode, $selectedStudentIds, $courseId, $recentPurchaseDays, $inactiveLearningDays, $recipientIds, $promotionTemplate, $messageHtml, $messagePlain, $ctaEnabled, $ctaLabel, $ctaUrl) {
            return TeacherPromotion::query()->create([
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
            ->route('teacher.dashboard.promotions')
            ->with('msg_success', __('teacher::dashboard.promotions.flash.sent', [
                'count' => $promotion->recipient_count,
            ]));
    }

    public function testPromotion(TeacherPromotionRequest $request)
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
                ->with('msg_danger', 'Tài khoản giảng viên hiện chưa có email để nhận thư test.');
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

        $promotion = new TeacherPromotion([
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
            ->with('msg_success', 'Đã đưa email test vào email của bạn.');
    }

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
            ->with(['items.course'])
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $pageTitle = __('teacher::dashboard.pages.bundles');
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
            ->with('msg_success', __('teacher::dashboard.bundles.flash.deleted'));
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

    public function storeUpgradePackage(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($this->resolveOpenPackageChangeRequest($teacher)) {
            return redirect()->route('teacher.dashboard.package.upgrade.status');
        }

        $currentPackage = $teacher->application?->package;
        $upgradePackages = $this->resolveAvailablePackageChanges($currentPackage);
        $allowedPackageIds = $upgradePackages->pluck('id')->all();

        $data = $request->validate([
            'package_id' => ['required', 'integer'],
            'payment_method' => ['nullable', 'string', 'in:bank_transfer,vnpay,momo'],
        ]);

        if (!in_array((int) $data['package_id'], $allowedPackageIds, true)) {
            return back()->withErrors([
                'package_id' => __('teacher::dashboard.package.flash.invalid_target'),
            ])->withInput();
        }

        $targetPackage = $upgradePackages->firstWhere('id', (int) $data['package_id']);
        $paymentMethod = (float) $targetPackage->price > 0
            ? ($data['payment_method'] ?: 'bank_transfer')
            : null;

        $sourceApplication = $teacher->application;
        $student = auth('students')->user();

        $upgradeRequest = TeacherApplication::query()->create([
            'student_id' => $student?->id,
            'teacher_id' => $teacher->id,
            'applicant_type' => $student ? 'student' : 'guest',
            'package_id' => $targetPackage->id,
            'payment_method' => $paymentMethod,
            'coupon_code' => null,
            'discount_amount' => 0,
            'status' => (float) $targetPackage->price > 0 ? 'pending_payment' : 'approved',
            'full_name' => $sourceApplication?->full_name ?: $teacher->name,
            'display_name' => $sourceApplication?->display_name ?: $teacher->name,
            'headline' => $sourceApplication?->headline,
            'bio' => $sourceApplication?->bio ?: $teacher->description,
            'experience_years' => $sourceApplication?->experience_years ?: $teacher->exp,
            'specialties' => $sourceApplication?->specialties ?? [],
            'phone' => $sourceApplication?->phone ?: $student?->phone,
            'email' => $sourceApplication?->email ?: $student?->email,
            'locale' => $sourceApplication?->locale ?: session('locale', app()->getLocale()),
            'portfolio_url' => $sourceApplication?->portfolio_url,
            'facebook_url' => $sourceApplication?->facebook_url,
            'youtube_url' => $sourceApplication?->youtube_url,
            'linkedin_url' => $sourceApplication?->linkedin_url,
            'custom_links' => $sourceApplication?->custom_links ?? [],
            'intro_video_url' => $sourceApplication?->intro_video_url,
            'cv_file' => $sourceApplication?->cv_file,
            'identity_file' => $sourceApplication?->identity_file,
            'submitted_at' => now(),
            'admin_note' => 'package_upgrade',
        ]);

        if ((float) $targetPackage->price <= 0) {
            $action = $this->finalizePackageChange($teacher, $upgradeRequest);

            return $this->redirectAfterPackageChange($action);
        }

        return redirect()->route('teacher.dashboard.package.upgrade.status')
            ->with('msg_success', __('teacher::dashboard.package.flash.created'));
    }

    public function upgradePackageStatus()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.package.upgrade');
        }

        $pageTitle = __('teacher::dashboard.package.status_title');
        $pageName = $pageTitle;
        $currentPackage = $teacher->application?->package;
        $overLimitWarnings = $this->resolvePackageOverLimitWarnings($teacher, $upgradeRequest);
        $featureLossWarnings = $this->resolvePackageFeatureLossWarnings($teacher, $upgradeRequest);

        return view('teacher::clients.dashboard.package_upgrade_status', compact('pageTitle', 'pageName', 'teacher', 'currentPackage', 'upgradeRequest', 'overLimitWarnings', 'featureLossWarnings'));
    }

    public function markUpgradePaid()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.package.upgrade');
        }

        if ($upgradeRequest->status !== 'pending_payment') {
            return back()->with('msg_danger', __('teacher::dashboard.package.flash.invalid_payment_status'));
        }

        if ($upgradeRequest->payment_method === 'bank_transfer') {
            $upgradeRequest->update([
                'status' => 'pending_review',
                'submitted_at' => now(),
            ]);

            return back()->with('msg_success', __('teacher::dashboard.package.flash.bank_transfer_waiting_confirmation'));
        }

        $action = $this->finalizePackageChange($teacher, $upgradeRequest);

        return $this->redirectAfterPackageChange($action);
    }

    public function cancelUpgradePackage()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $upgradeRequest = $this->resolveOpenPackageChangeRequest($teacher);
        if (!$upgradeRequest) {
            return redirect()->route('teacher.dashboard.package.upgrade');
        }

        if (!in_array($upgradeRequest->status, ['pending_payment', 'pending_review'], true)) {
            return back()->with('msg_danger', 'YÃªu cáº§u Ä‘á»•i gÃ³i nÃ y khÃ´ng cÃ²n cÃ³ thá»ƒ há»§y.');
        }

        $upgradeRequest->update([
            'status' => 'cancelled',
            'activates_at' => null,
            'package_started_at' => null,
            'package_expires_at' => null,
            'activated_at' => null,
        ]);

        return redirect()
            ->route('teacher.dashboard.package.upgrade')
            ->with('msg_success', 'ÄÃ£ há»§y yÃªu cáº§u Ä‘á»•i gÃ³i.');
    }

    public function courses()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $this->syncCourseLocks($teacher);
        $teacher->refresh();

        $pageTitle = __('teacher::dashboard.pages.courses');
        $pageName = __('teacher::dashboard.pages.courses');
        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->withCount(['lessons', 'students', 'ratings'])
            ->withAvg('ratings', 'rating')
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(12)
            ->withQueryString();
        $this->attachCourseHistoryPreview($courses, $teacher);

        $usage = $this->resolvePublishedCourseUsage($teacher);

        return view('teacher::clients.dashboard.courses', compact('pageTitle', 'pageName', 'teacher', 'courses', 'usage'));
    }

    public function coursesTrash()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = __('teacher::dashboard.courses.trash_title');
        $pageName = __('teacher::dashboard.courses.trash_title');
        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->onlyTrashed()
            ->withCount(['lessons', 'students'])
            ->where('teacher_id', $teacher->id)
            ->latest('deleted_at')
            ->paginate(12)
            ->withQueryString();

        return view('teacher::clients.dashboard.courses_trash', compact('pageTitle', 'pageName', 'teacher', 'courses'));
    }

    public function createCourse()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $this->syncCourseLocks($teacher);
        $teacher->refresh();

        $pageTitle = __('teacher::dashboard.courses.create_title');
        $pageName = __('teacher::dashboard.courses.create_title');
        $categories = $this->getCourseCategories();
        $usage = $this->resolvePublishedCourseUsage($teacher);

        if (!($usage['can_create_draft'] ?? true)) {
            return redirect()
                ->route('teacher.dashboard.courses')
                ->with('msg_danger', __('teacher::dashboard.package_features.feature_locked'));
        }

        return view('teacher::clients.dashboard.create_course', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'categories' => $categories,
            'usage' => $usage,
            'course' => null,
            'selectedCategories' => [],
            'formAction' => route('teacher.dashboard.courses.store'),
            'submitLabel' => __('teacher::dashboard.courses.actions.create'),
        ]);
    }

    public function storeCourse(TeacherCourseRequest $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $usage = $this->resolvePublishedCourseUsage($teacher);
        if (!($usage['can_create_draft'] ?? true)) {
            return redirect()
                ->route('teacher.dashboard.courses')
                ->with('msg_danger', __('teacher::dashboard.package_features.feature_locked'));
        }

        $data = $request->validated();
        
        // Force draft if trying to publish but over limit
        if ((int) $data['status'] === 1 && !($usage['can_create'] ?? true)) {
            $data['status'] = 0;
            $limitMessage = __('teacher::dashboard.courses.flash.created_as_draft_due_limit', ['limit' => $usage['limit'] ?? 0]);
        }

        $course = Courses::query()->create($this->buildCoursePayload($data, $teacher));
        $this->syncCourseCategories($course, $data['categories'] ?? []);
        $this->logTeacherCourseActivity(
            $teacher,
            $course->fresh(),
            'course_created',
            'Da tao khoa hoc moi',
            [
                'status' => (int) $course->status,
                'price' => (float) $course->price,
                'sale_price' => (float) $course->sale_price,
                'category_count' => count($data['categories'] ?? []),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', $limitMessage ?? __('teacher::dashboard.courses.flash.created'));
    }

    public function editCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $pageTitle = __('teacher::dashboard.courses.edit_title');
        $pageName = __('teacher::dashboard.courses.edit_title');

        return view('teacher::clients.dashboard.create_course', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'categories' => $this->getCourseCategories(),
            'usage' => $this->resolvePublishedCourseUsage($teacher),
            'course' => $course,
            'selectedCategories' => $course->categories()->pluck('categories.id')->all(),
            'formAction' => route('teacher.dashboard.courses.update', $course->id),
            'submitLabel' => __('teacher::dashboard.courses.actions.update'),
        ]);
    }

    public function updateCourse(TeacherCourseRequest $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $data = $request->validated();
        $before = $course->only(['name', 'price', 'sale_price', 'status', 'is_learning_locked']);

        $course->update($this->buildCoursePayload($data, $teacher, $course));
        $this->syncCourseCategories($course, $data['categories'] ?? []);
        $course->refresh();
        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_updated',
            'Da cap nhat khoa hoc',
            [
                'before' => $before,
                'after' => $course->only(['name', 'price', 'sale_price', 'status', 'is_learning_locked']),
                'category_count' => count($data['categories'] ?? []),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __(
                ((int) $data['status'] === 1 && (int) $course->status !== 1)
                    ? 'teacher::dashboard.courses.flash.updated_as_draft_due_limit'
                    : 'teacher::dashboard.courses.flash.updated'
            ));
    }

    public function duplicateCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $usage = $this->resolvePublishedCourseUsage($teacher);
        if (!($usage['can_create_draft'] ?? true)) {
            return back()->with('msg_danger', __('teacher::dashboard.package_features.feature_locked'));
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $newCourse = $this->performCourseDuplicate($course, $teacher);
        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_duplicated',
            'Da nhan ban khoa hoc',
            [
                'duplicate_course_id' => $newCourse->id,
                'duplicate_course_name' => $newCourse->name_locale ?: $newCourse->name,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::dashboard.courses.flash.duplicated'));
    }

    public function updateCourseVisibility(Request $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $data = $request->validate([
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $targetStatus = (int) $data['status'];
        $previousStatus = (int) $course->status;
        if ($targetStatus === 1) {
            $this->activateCourseWithinLimit($teacher, $course);
        } else {
            $course->update([
                'status' => 0,
                'package_locked_at' => null,
                'package_lock_reason' => null,
            ]);
            $this->syncCourseLocks($teacher);
        }
        $course->refresh();
        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            $targetStatus === 1 ? 'course_published' : 'course_moved_to_draft',
            $targetStatus === 1 ? 'Da dua khoa hoc len publish' : 'Da chuyen khoa hoc ve ban nhap',
            [
                'before_status' => $previousStatus,
                'after_status' => (int) $course->status,
                'package_lock_reason' => $course->package_lock_reason,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __(
                $targetStatus === 1
                    ? 'teacher::dashboard.courses.flash.published'
                    : 'teacher::dashboard.courses.flash.drafted'
            ));
    }

    public function toggleCoursePriority(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($this->resolveCourseLimit($teacher) === null) {
            if ((bool) $course->is_package_priority) {
                $course->update([
                    'is_package_priority' => false,
                ]);
            }

            return redirect()
                ->route('teacher.dashboard.courses')
                ->with('msg_success', __('teacher::dashboard.courses.flash.priority_disabled'));
        }

        $wasPriority = (bool) $course->is_package_priority;
        $course->update([
            'is_package_priority' => !$course->is_package_priority,
        ]);

        $this->syncCourseLocks($teacher);
        $course->refresh();
        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            $course->is_package_priority ? 'course_priority_enabled' : 'course_priority_disabled',
            $course->is_package_priority ? 'Da bat uu tien goi cho khoa hoc' : 'Da tat uu tien goi cho khoa hoc',
            [
                'before_priority' => $wasPriority,
                'after_priority' => (bool) $course->is_package_priority,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __(
                $course->fresh()->is_package_priority
                    ? 'teacher::dashboard.courses.flash.priority_enabled'
                    : 'teacher::dashboard.courses.flash.priority_disabled'
            ));
    }

    public function deleteCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $courseSnapshot = $course->only(['id', 'name', 'status']);
        $course->delete();
        Lesson::query()->where('course_id', $course->id)->delete();
        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_deleted',
            'Da dua khoa hoc vao thung rac',
            $courseSnapshot
        );

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_success', __('teacher::dashboard.courses.flash.deleted'));
    }

    public function restoreCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        if (!$course->trashed()) {
            abort(404);
        }

        $wasTrashed = $course->trashed();
        $originalStatus = (int) $course->status;
        $course->restore();
        $restoredAsDraft = false;
        if (
            $wasTrashed &&
            (int) $course->status === 1 &&
            !$this->hasAvailablePublishedCourseSlot($teacher, $course->id)
        ) {
            $course->forceFill(['status' => 0])->save();
            $restoredAsDraft = true;
        }
        Lesson::query()->onlyTrashed()->where('course_id', $course->id)->restore();
        $course->refresh();
        $this->logTeacherCourseActivity(
            $teacher,
            $course,
            'course_restored',
            $restoredAsDraft ? 'Da khoi phuc khoa hoc ve ban nhap' : 'Da khoi phuc khoa hoc',
            [
                'was_trashed' => $wasTrashed,
                'before_status' => $originalStatus,
                'after_status' => (int) $course->status,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.courses.trash')
            ->with('msg_success', __(
                $restoredAsDraft
                    ? 'teacher::dashboard.courses.flash.restored_as_draft_due_limit'
                    : 'teacher::dashboard.courses.flash.restored'
            ));
    }

    public function forceDeleteCourse(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        if (!$course->trashed()) {
            abort(404);
        }
        $courseName = $course->name_locale ?: $course->name;
        $courseKey = $course->id;

        Lesson::query()->withTrashed()->where('course_id', $course->id)->forceDelete();
        $course->categories()->detach();
        $course->forceDelete();
        activity_log(
            'course_force_deleted',
            null,
            [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'course_id' => $courseKey,
                'course_name' => $courseName,
            ],
            'teacher_course_management',
            'Da xoa vinh vien khoa hoc'
        );

        return redirect()
            ->route('teacher.dashboard.courses.trash')
            ->with('msg_success', __('teacher::dashboard.courses.flash.force_deleted'));
    }

    public function lessons(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $pageTitle = __('teacher::dashboard.lessons.title', ['course' => $course->name_locale]);
        $pageName = __('teacher::dashboard.lessons.title_short');
        $modules = Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->with(['subLessons' => fn ($query) => $query->orderBy('position')])
            ->orderBy('position')
            ->get();

        return view('teacher::clients.dashboard.lessons', compact('pageTitle', 'pageName', 'teacher', 'course', 'modules') + [
            'canImportExportLessons' => $teacher->packageHasFeature('can_import_export_lessons'),
            'existingModuleSelectors' => $this->buildExistingLessonModuleSelectors($course),
            'lessonImportColumns' => $this->lessonImportColumns(),
            'lessonImportPreview' => $this->getLessonImportPreview($course),
        ]);
    }

    public function lessonsTrash(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $pageTitle = __('teacher::dashboard.lessons.trash_title', ['course' => $course->name_locale]);
        $pageName = __('teacher::dashboard.lessons.trash_short');
        $lessons = Lesson::query()
            ->onlyTrashed()
            ->where('course_id', $course->id)
            ->orderByRaw('COALESCE(parent_id, 0)')
            ->orderBy('position')
            ->get();
        $trashedLessonRows = $this->flattenTrashedLessons($lessons);

        return view('teacher::clients.dashboard.lessons_trash', compact('pageTitle', 'pageName', 'teacher', 'course', 'trashedLessonRows'));
    }

    public function exportLessons(Request $request, int $courseId, string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export_lessons', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        if ($format !== 'csv') {
            abort(404);
        }

        $rows = $this->buildLessonExportRows($course);
        $filename = 'teacher-lessons-' . $course->id . '-' . now()->format('Ymd-His') . '.csv';

        return $this->streamCsvDownload($filename, $this->lessonImportColumns(), $rows);
    }

    public function downloadLessonImportTemplate(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export_lessons', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        $filename = 'lesson-import-template-' . $course->id . '.csv';
        $rows = $this->lessonImportTemplateRows();

        return $this->streamCsvDownload($filename, $this->lessonImportColumns(), $rows);
    }

    public function downloadLessonImportExample(int $courseId, ?string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export_lessons', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        $format = Str::lower((string) $format);
        $rows = $this->lessonImportExampleRows();

        if ($format === 'xlsx') {
            $filename = 'lesson-import-example-' . $course->id . '.xlsx';

            return $this->streamXlsxDownload($filename, $this->lessonImportColumns(), $rows);
        }

        $filename = 'lesson-import-example-' . $course->id . '.csv';

        return $this->streamCsvDownload($filename, $this->lessonImportColumns(), $rows);
    }

    public function importLessons(Request $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export_lessons', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        $payload = $request->validate([
            'lesson_import_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:4096'],
        ]);

        $result = $this->validateLessonImportFile(
            $payload['lesson_import_file']->getRealPath(),
            $payload['lesson_import_file']->getClientOriginalExtension(),
            $course,
            $teacher->packageHasFeature('can_schedule_content')
        );

        if (!empty($result['errors'])) {
            session()->forget($this->lessonImportPreviewSessionKey($course->id));

            return redirect()
                ->route('teacher.dashboard.lessons.index', $course->id)
                ->with('msg_danger', __('teacher::dashboard.lessons.import.flash.validation_failed'))
                ->with('lesson_import_errors', $result['errors'])
                ->with('lesson_import_summary', $result['summary'] ?? null);
        }

        $createdRows = DB::transaction(function () use ($result, $teacher, $course) {
            $createdModules = [];
            $createdRows = [];

            foreach ($result['rows'] as $row) {
                $parentId = null;
                if ($row['type'] === 'lesson') {
                    $parentId = $this->resolveImportedLessonParentId($row['parent_selector'], $createdModules, $course);
                }

                $lesson = Lesson::query()->create($this->buildLessonPayload(
                    [
                        'name' => $row['name'],
                        'name_en' => $row['name_en'],
                        'name_ko' => $row['name_ko'],
                        'name_ja' => $row['name_ja'],
                        'name_zh' => $row['name_zh'],
                        'parent_id' => $parentId,
                        'is_trial' => $row['type'] === 'module' ? 0 : $row['is_trial'],
                        'position' => $row['position'],
                        'video' => $row['video'],
                        'document' => $row['document'],
                        'description' => $row['description'],
                        'description_en' => $row['description_en'],
                        'description_ko' => $row['description_ko'],
                        'description_ja' => $row['description_ja'],
                        'description_zh' => $row['description_zh'],
                        'status' => $row['status'],
                        'release_mode' => $row['release_mode'],
                        'release_at' => $row['release_at'],
                        'release_after_days' => $row['release_after_days'],
                    ],
                    $course,
                    null,
                    $teacher->packageHasFeature('can_schedule_content')
                ));

                if ($row['type'] === 'module' && $row['module_ref'] !== '') {
                    $createdModules[$row['module_ref']] = (int) $lesson->id;
                }

                $createdRows[] = [
                    'lesson' => $lesson,
                    'row' => $row,
                ];
            }

            return $createdRows;
        });

        foreach ($createdRows as $createdRow) {
            $lesson = $createdRow['lesson'];
            $row = $createdRow['row'];

            $this->logTeacherLessonActivity(
                $teacher,
                $course,
                $lesson,
                'import_lesson',
                'Import bÃ i há» c tá»« CSV',
                [
                    'import_type' => $row['type'],
                    'import_line' => $row['line'],
                    'module_ref' => $row['module_ref'],
                    'parent_selector' => $row['parent_selector'],
                    'schedule' => $this->summarizeLessonSchedule($lesson),
                ]
            );
        }

        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.import.flash.success', [
                'count' => count($createdRows),
            ]))
            ->with('lesson_import_summary', [
                'total_rows' => count($createdRows),
                'imported_modules' => collect($createdRows)->where('row.type', 'module')->count(),
                'imported_lessons' => collect($createdRows)->where('row.type', 'lesson')->count(),
            ]);
    }

    public function previewLessonImport(Request $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export_lessons', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        $payload = $request->validate([
            'lesson_import_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:4096'],
        ]);

        $result = $this->validateLessonImportFile(
            $payload['lesson_import_file']->getRealPath(),
            $payload['lesson_import_file']->getClientOriginalExtension(),
            $course,
            $teacher->packageHasFeature('can_schedule_content')
        );

        if (!empty($result['errors'])) {
            return redirect()
                ->route('teacher.dashboard.lessons.index', $course->id)
                ->with('msg_danger', __('teacher::dashboard.lessons.import.flash.validation_failed'))
                ->with('lesson_import_errors', $result['errors'])
                ->with('lesson_import_summary', $result['summary'] ?? null);
        }

        $this->storeLessonImportPreview($course, [
            'rows' => $result['rows'],
            'summary' => $result['summary'],
            'file_name' => $payload['lesson_import_file']->getClientOriginalName(),
            'detected_format' => Str::lower((string) $payload['lesson_import_file']->getClientOriginalExtension()),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.import.flash.preview_ready', [
                'count' => $result['summary']['total_rows'] ?? 0,
            ]));
    }

    public function confirmLessonImport(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_import_export_lessons', 'teacher.dashboard.lessons.index', [$course->id])) {
            return $featureRedirect;
        }

        $preview = $this->getLessonImportPreview($course);
        if (empty($preview['rows']) || !is_array($preview['rows'])) {
            return redirect()
                ->route('teacher.dashboard.lessons.index', $course->id)
                ->with('msg_danger', __('teacher::dashboard.lessons.import.flash.preview_missing'));
        }

        $createdRows = $this->persistImportedLessons($teacher, $course, $preview['rows']);
        session()->forget($this->lessonImportPreviewSessionKey($course->id));

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.import.flash.success', [
                'count' => count($createdRows),
            ]))
            ->with('lesson_import_summary', [
                'total_rows' => count($createdRows),
                'imported_modules' => collect($createdRows)->where('row.type', 'module')->count(),
                'imported_lessons' => collect($createdRows)->where('row.type', 'lesson')->count(),
            ]);
    }

    public function clearLessonImportPreview(int $courseId)
    {
        session()->forget($this->lessonImportPreviewSessionKey($courseId));

        return redirect()
            ->route('teacher.dashboard.lessons.index', $courseId)
            ->with('msg_success', __('teacher::dashboard.lessons.import.flash.preview_cleared'));
    }

    public function createLesson(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $pageTitle = __('teacher::dashboard.lessons.create_title', ['course' => $course->name_locale]);
        $pageName = __('teacher::dashboard.lessons.create_short');
        $defaultParentId = (int) request()->query('module', 0);
        $modules = Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();

        return view('teacher::clients.dashboard.lesson_form', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'course' => $course,
            'lesson' => null,
            'modules' => $modules,
            'formAction' => route('teacher.dashboard.lessons.store', $course->id),
            'submitLabel' => __('teacher::dashboard.lessons.actions.create'),
            'position' => $this->nextLessonPosition($course, $defaultParentId),
            'defaultParentId' => $defaultParentId,
            'canScheduleContent' => $teacher->packageHasFeature('can_schedule_content'),
        ]);
    }

    public function storeLesson(TeacherLessonRequest $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $data = $request->validated();

        $lesson = Lesson::query()->create($this->buildLessonPayload(
            $data,
            $course,
            null,
            $teacher->packageHasFeature('can_schedule_content')
        ));
        $this->updateCourseDurations($course->id);
        $this->logTeacherLessonActivity($teacher, $course, $lesson, 'create_lesson', 'Táº¡o bÃ i há»c', [
            'schedule' => $this->summarizeLessonSchedule($lesson),
        ]);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.created'));
    }

    public function editLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $pageTitle = __('teacher::dashboard.lessons.edit_title', ['lesson' => $lesson->name_locale]);
        $pageName = __('teacher::dashboard.lessons.edit_short');
        $modules = Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->where('id', '!=', $lesson->id)
            ->orderBy('position')
            ->get();

        return view('teacher::clients.dashboard.lesson_form', [
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'teacher' => $teacher,
            'course' => $course,
            'lesson' => $lesson,
            'modules' => $modules,
            'formAction' => route('teacher.dashboard.lessons.update', [$course->id, $lesson->id]),
            'submitLabel' => __('teacher::dashboard.lessons.actions.update'),
            'position' => $lesson->position,
            'defaultParentId' => $lesson->parent_id ?? 0,
            'canScheduleContent' => $teacher->packageHasFeature('can_schedule_content'),
        ]);
    }

    public function updateLesson(TeacherLessonRequest $request, int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $data = $request->validated();

        $oldSchedule = $this->summarizeLessonSchedule($lesson);
        $lesson->update($this->buildLessonPayload(
            $data,
            $course,
            $lesson,
            $teacher->packageHasFeature('can_schedule_content')
        ));
        $lesson->refresh();
        $this->updateCourseDurations($course->id);
        $this->logTeacherLessonActivity($teacher, $course, $lesson, 'update_lesson', 'Cáº­p nháº­t bÃ i há»c', [
            'old_schedule' => $oldSchedule,
            'new_schedule' => $this->summarizeLessonSchedule($lesson),
        ]);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.updated'));
    }

    public function deleteLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        $branchIds = $this->collectLessonBranchIds($lesson->id);
        Lesson::query()->whereIn('id', $branchIds)->delete();
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.index', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.deleted'));
    }

    public function getLessonPreviewData(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId);
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Course not found'], 404);
        }

        $lesson = $this->resolveOwnedLesson($course, $lessonId);
        if (!$lesson) {
            return response()->json(['success' => false, 'message' => 'Lesson not found'], 404);
        }

        if (!$lesson->video) {
            return response()->json([
                'success' => false,
                'message' => 'Bài học này không có video để xem trước.'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $lesson->id,
                'name' => $lesson->name_locale,
                'video' => videoPlaybackMeta($lesson->video->url, app()->getLocale()),
            ],
        ]);
    }

    public function restoreLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $lesson = $this->resolveOwnedLesson($course, $lessonId, true);
        if (!$lesson->trashed()) {
            abort(404);
        }

        $branchIds = $this->collectLessonBranchIds($lesson->id);
        Lesson::query()->onlyTrashed()->whereIn('id', $branchIds)->restore();
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.trash', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.restored'));
    }

    public function forceDeleteLesson(int $courseId, int $lessonId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $course = $this->resolveOwnedCourse($teacher, $courseId, true);
        if ($lockedRedirect = $this->ensureCourseManageable($course)) {
            return $lockedRedirect;
        }
        $lesson = $this->resolveOwnedLesson($course, $lessonId, true);
        if (!$lesson->trashed()) {
            abort(404);
        }

        $branchIds = $this->collectLessonBranchIds($lesson->id);
        Lesson::query()->onlyTrashed()->whereIn('id', $branchIds)->forceDelete();
        $this->updateCourseDurations($course->id);

        return redirect()
            ->route('teacher.dashboard.lessons.trash', $course->id)
            ->with('msg_success', __('teacher::dashboard.lessons.flash.force_deleted'));
    }

    public function earnings(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }
        $effectiveCommissionRate = $this->resolveEffectiveCommissionRate($teacher);
        $dashboardRange = $this->resolveTeacherDashboardRange((string) $request->query('range', 'today'));
        $dashboardRangeOptions = $this->resolveTeacherDashboardRangeOptions();

        $pageTitle = __('teacher::dashboard.pages.earnings');
        $pageName = __('teacher::dashboard.pages.earnings');
        $items = $this->applyTeacherDashboardRangeToPaidQuery(
            $this->paidOrderDetailsQuery($teacher),
            $dashboardRange
        )->paginate(12)->withQueryString();
        $summary = TeacherFinanceCalculator::summarize(
            $this->applyTeacherDashboardRangeToPaidQuery(
                $this->paidOrderDetailsQuery($teacher),
                $dashboardRange
            )->get(),
            fn () => $effectiveCommissionRate
        );
        $revenueInsights = $this->buildTeacherRevenueInsights($teacher, $effectiveCommissionRate, $dashboardRange);
        $coursePerformance = $this->buildTeacherCoursePerformance($teacher, $effectiveCommissionRate, $dashboardRange);
        $items->setCollection(TeacherFinanceCalculator::decorate(
            $items->getCollection(),
            fn () => $effectiveCommissionRate
        ));

        $earningsPayload = $this->buildTeacherEarningsDashboardPayload(
            $teacher,
            $effectiveCommissionRate,
            $dashboardRange,
            $summary,
            $revenueInsights,
            $coursePerformance
        );

        if ($request->boolean('ajax')) {
            return response()->json($earningsPayload);
        }

        return view('teacher::clients.dashboard.earnings', compact('pageTitle', 'pageName', 'teacher', 'items', 'summary', 'dashboardRange', 'dashboardRangeOptions', 'revenueInsights', 'coursePerformance', 'effectiveCommissionRate', 'earningsPayload'));
    }

    public function payouts()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }
        $effectiveCommissionRate = $this->resolveEffectiveCommissionRate($teacher);

        $pageTitle = __('teacher::dashboard.pages.payouts');
        $pageName = __('teacher::dashboard.pages.payouts');
        $summary = TeacherFinanceCalculator::summarize(
            $this->paidOrderDetailsQuery($teacher)->get(),
            fn () => $effectiveCommissionRate
        );
        $requestedAmount = $this->resolveCommittedPayoutAmount($teacher);
        $availableBalance = max($summary['teacher_revenue'] - $requestedAmount, 0);
        $payoutAccounts = $teacher->payoutAccounts()->orderBy('id')->get();
        $payoutAccountLimit = $this->resolvePayoutAccountLimit($teacher);
        $pendingAccountChangeRequests = $teacher->payoutAccountChangeRequests()
            ->with('replaceAccount')
            ->latest('id')
            ->take(10)
            ->get();
        $payoutAccountUsage = $this->resolvePayoutAccountUsage($teacher, $payoutAccounts->count());
        $bankOptions = $this->resolveVietnamBankOptions();
        $payouts = TeacherPayoutRequest::query()
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('teacher::clients.dashboard.payouts', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'payouts',
            'summary',
            'requestedAmount',
            'availableBalance',
            'payoutAccounts',
            'payoutAccountLimit',
            'pendingAccountChangeRequests',
            'payoutAccountUsage',
            'bankOptions'
        ));
    }

    public function support()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = 'Góp ý / Báo cáo';
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

    public function orders(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $directory = $this->buildTeacherOrderDirectory($teacher, $request);
        $groupedOrders = $directory['orders'];

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $orders = new LengthAwarePaginator(
            $groupedOrders->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $groupedOrders->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $pageTitle = __('teacher::dashboard.pages.orders');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.orders', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'orders',
            'directory'
        ));
    }

    public function exportOrders(Request $request, string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_export_orders')) {
            return $featureRedirect;
        }

        $directory = $this->buildTeacherOrderDirectory($teacher, $request);
        $orders = $directory['orders'];

        if ($format === 'excel') {
            $filename = 'teacher-orders-' . now()->format('Ymd-His') . '.xls';
            $html = view('teacher::clients.dashboard.exports.orders_excel', compact('orders'))->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $filename = 'teacher-orders-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                __('teacher::dashboard.orders.export.order'),
                __('teacher::dashboard.orders.export.student'),
                __('teacher::dashboard.orders.export.email'),
                __('teacher::dashboard.orders.export.phone'),
                __('teacher::dashboard.orders.export.payment_method'),
                __('teacher::dashboard.orders.export.paid_at'),
                __('teacher::dashboard.orders.export.course_count'),
                __('teacher::dashboard.orders.export.courses'),
                __('teacher::dashboard.orders.export.gross'),
                __('teacher::dashboard.orders.export.discount'),
                __('teacher::dashboard.orders.export.net'),
                __('teacher::dashboard.orders.export.revenue'),
            ]);

            foreach ($orders as $item) {
                fputcsv($handle, [
                    $item->order?->code ?: $item->order?->id,
                    $item->order?->customer_name_display ?: '',
                    $item->order?->customer_email_display ?: '',
                    $item->order?->customer_phone_display ?: '',
                    $item->order?->payment_method_label ?: '',
                    optional($item->payment_at)->format('Y-m-d H:i:s'),
                    $item->item_count,
                    $item->details->pluck(fn ($detail) => $detail->courses?->name_locale ?: $detail->courses?->name ?: '')->implode(' | '),
                    $item->gross_amount,
                    $item->allocated_discount,
                    $item->net_revenue,
                    $item->teacher_revenue,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function showOrder(int $orderId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $details = $this->paidOrderDetailsQuery($teacher)
            ->where('order_id', $orderId)
            ->get();

        if ($details->isEmpty()) {
            abort(404);
        }

        $details = TeacherFinanceCalculator::decorate(
            $details,
            fn() => $this->resolveEffectiveCommissionRate($teacher)
        );

        $order = $details->first()?->order;
        if (!$order) {
            abort(404);
        }

        $summary = [
            'gross_amount' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
            'allocated_discount' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.allocated_discount', 0)),
            'net_revenue' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.net_revenue', 0)),
            'teacher_revenue' => (float) $details->sum(fn($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
            'item_count' => $details->count(),
        ];

        $pageTitle = __('teacher::dashboard.orders.show.title', ['code' => $order->code ?: $order->id]);
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.order_show', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'order',
            'details',
            'summary'
        ));
    }

    public function students(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_students')) {
            return $featureRedirect;
        }

        $directory = $this->buildTeacherStudentDirectory($teacher, $request);
        $search = $directory['search'];
        $summary = $directory['summary'];
        $courseOptions = $directory['courseOptions'];
        $selectedCourse = $directory['selectedCourse'];
        $tag = $directory['tag'];
        $sort = $directory['sort'];

        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $students = new LengthAwarePaginator(
            $directory['students']->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $directory['students']->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $pageTitle = 'Học viên của tôi';
        $pageName = $pageTitle;
        return view('teacher::clients.dashboard.students', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'students',
            'directory'
        ) + [
            'studentFeatureState' => $this->resolveStudentFeatureState($teacher),
        ]);
    }

    public function activityLogs(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_view_activity_logs')) {
            return $featureRedirect;
        }

        $type = trim((string) $request->query('type', 'all'));
        $search = trim((string) $request->query('q', ''));
        $typeMap = [
            'students' => 'teacher_student_management',
            'courses' => 'teacher_course_management',
            'coupons' => 'teacher_coupon_management',
        ];
        $selectedType = array_key_exists($type, $typeMap) ? $type : 'all';

        $logsQuery = ActiveLog::query()
            ->where('properties->teacher_id', $teacher->id)
            ->whereIn('log_name', array_values($typeMap))
            ->when($selectedType !== 'all', function ($query) use ($selectedType, $typeMap) {
                $query->where('log_name', $typeMap[$selectedType]);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('action', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhere('properties->student_name', 'like', '%' . $search . '%')
                        ->orWhere('properties->course_name', 'like', '%' . $search . '%')
                        ->orWhere('properties->coupon_code', 'like', '%' . $search . '%')
                        ->orWhere('properties->certificate_code', 'like', '%' . $search . '%')
                        ->orWhere('properties->teacher_name', 'like', '%' . $search . '%');
                });
            });

        $summary = [
            'total' => (clone $logsQuery)->count(),
            'students' => (clone $logsQuery)->where('log_name', $typeMap['students'])->count(),
            'courses' => (clone $logsQuery)->where('log_name', $typeMap['courses'])->count(),
            'coupons' => (clone $logsQuery)->where('log_name', $typeMap['coupons'])->count(),
        ];

        $logs = $logsQuery
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $pageTitle = __('teacher::dashboard.pages.activity_logs');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.activity_logs', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'logs',
            'summary',
            'search',
            'selectedType'
        ));
    }

    public function comments(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        $selectedCourseId = (int) $request->query('course_id', 0);
        $selectedCourse = $selectedCourseId > 0
            ? $courses->firstWhere('id', $selectedCourseId)
            : $courses->first();

        $threads = $selectedCourse
            ? courseCommentThreads($selectedCourse->id, true)
            : collect();

        $pageTitle = __('teacher::comments.page_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.comments', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'courses',
            'selectedCourse',
            'threads'
        ));
    }

    public function replyComment(Request $request, int $commentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $comment = CourseComment::query()
            ->whereNull('parent_id')
            ->whereHas('course', function ($query) use ($teacher) {
                $query->withoutGlobalScope(ActiveScope::class)
                    ->where('teacher_id', $teacher->id);
            })
            ->findOrFail($commentId);

        $payload = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $content = $this->sanitizeCommentContent($payload['content']);

        if (mb_strlen($content) < 2) {
            return $this->commentErrorResponse($request, __('teacher::comments.flash.reply_too_short'), 422);
        }

        $moderation = courseCommentModeration($content);

        CourseComment::create([
            'course_id' => $comment->course_id,
            'parent_id' => $comment->id,
            'student_id' => $teacher->student_id ?? auth('students')->id(),
            'content' => $content,
            'is_visible' => true,
            'is_flagged' => $moderation['is_flagged'],
            'flagged_terms' => $moderation['is_flagged'] ? implode(', ', $moderation['matched_terms']) : null,
        ]);

        return $this->renderTeacherCommentThread($request, $comment->course_id, $teacher);
    }

    public function toggleCommentVisibility(Request $request, int $commentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_comments')) {
            return $featureRedirect;
        }

        $comment = CourseComment::query()
            ->whereHas('course', function ($query) use ($teacher) {
                $query->withoutGlobalScope(ActiveScope::class)
                    ->where('teacher_id', $teacher->id);
            })
            ->findOrFail($commentId);

        $comment->update([
            'is_visible' => !$comment->is_visible,
        ]);

        return $this->renderTeacherCommentThread($request, $comment->course_id, $teacher);
    }

    public function exportStudents(Request $request, string $format = 'csv')
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_students')) {
            return $featureRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_export_students')) {
            return $featureRedirect;
        }

        $directory = $this->buildTeacherStudentDirectory($teacher, $request);
        $students = $directory['students'];

        if ($format === 'excel') {
            $filename = 'teacher-students-' . now()->format('Ymd-His') . '.xls';
            $html = view('teacher::clients.dashboard.exports.students_excel', compact('students'))->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $filename = 'teacher-students-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student', 'Email', 'Phone', 'Address', 'Tag', 'Courses Bought', 'Orders Paid', 'Spent', 'Last Purchase', 'Recent Learning']);

            foreach ($students as $student) {
                fputcsv($handle, [
                    $student->name,
                    $student->email,
                    $student->phone,
                    $student->address,
                    $this->normalizeStudentTagLabel($student->teacher_tag),
                    $student->teacher_courses_all->pluck(fn ($course) => $course->name_locale ?: $course->name)->implode(' | '),
                    $student->teacher_order_count,
                    (float) $student->teacher_total_spent,
                    optional($student->teacher_last_purchase_at)->format('Y-m-d H:i:s'),
                    optional($student->teacher_last_learning_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function createStudentGrant(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_students')) {
            return $featureRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_grant_courses', 'teacher.dashboard.students')) {
            return $featureRedirect;
        }

        $selectedStudent = Student::query()
            ->where('id', (int) $request->query('student_id', 0))
            ->first();
        $selectedEmail = old('student_email', $selectedStudent?->email ?? '');
        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();
        $recentGrants = $this->teacherCourseGrantsQuery($teacher, ['pending', 'accepted'])
            ->with(['student', 'course'])
            ->latest('id')
            ->take(8)
            ->get();

        $pageTitle = __('teacher::gifts.teacher.page_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.student_grant', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'courses',
            'selectedEmail',
            'recentGrants'
        ));
    }

    public function storeStudentGrant(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_students')) {
            return $featureRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_grant_courses', 'teacher.dashboard.students')) {
            return $featureRedirect;
        }

        $data = $request->validate([
            'student_email' => ['required', 'email:rfc'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'reason' => ['required', 'in:gift,support,special_trial,compensation'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $email = Str::lower(trim((string) $data['student_email']));
        $student = Student::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$student || $student->deleted_at) {
            return back()
                ->withErrors(['student_email' => __('teacher::gifts.flash.student_not_found')])
                ->withInput();
        }

        $course = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->findOrFail((int) $data['course_id']);

        $alreadyHasAccess = StudentsCourses::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyHasAccess) {
            return back()->with('msg_danger', __('teacher::gifts.flash.already_has_access'));
        }

        $locale = $student->preferredLocale();
        $grant = TeacherCourseGrant::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
            ],
            [
                'reason' => $data['reason'],
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
                'status' => 'pending',
                'token' => (string) Str::uuid(),
                'locale' => $locale,
                'invited_at' => now(),
                'accepted_at' => null,
                'revoked_at' => null,
            ]
        );

        $student->notify(new TeacherCourseGiftInvitationNotification(
            $grant->fresh(['teacher', 'course']),
            $locale
        ));

        $this->logTeacherStudentActivity(
            $teacher,
            $student,
            'grant_created',
            'ÄÃ£ cáº¥p quyá»n há»c thá»§ cÃ´ng',
            [
                'course_id' => $course->id,
                'course_name' => $course->name_locale ?: $course->name,
                'reason' => $data['reason'],
                'reason_label' => match ($data['reason']) {
                    'gift' => 'QuÃ  táº·ng',
                    'support' => 'Há»— trá»£',
                    'special_trial' => 'Há»c thá»­ Ä‘áº·c biá»‡t',
                    'compensation' => 'BÃ¹ quyá»n truy cáº­p',
                    default => $data['reason'],
                },
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.students.grants.create', ['student_id' => $student->id])
            ->with('msg_success', __('teacher::gifts.flash.invitation_sent'));
    }

    public function revokeStudentGrant(int $studentId, int $grantId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_students')) {
            return $featureRedirect;
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_grant_courses', 'teacher.dashboard.students')) {
            return $featureRedirect;
        }

        [$student, $details, $grants] = $this->resolveOwnedStudentContext($teacher, $studentId);
        $grant = $grants->firstWhere('id', $grantId);

        if (!$grant) {
            abort(404);
        }

        $grant->delete();

        $hasPaidAccess = $details->contains(function ($detail) use ($grant) {
            return (int) $detail->course_id === (int) $grant->course_id;
        });

        if (!$hasPaidAccess) {
            StudentsCourses::query()
                ->where('student_id', $student->id)
                ->where('course_id', $grant->course_id)
                ->delete();
        }

        $this->logTeacherStudentActivity(
            $teacher,
            $student,
            'grant_revoked',
            'ÄÃ£ thu há»“i quyá»n há»c thá»§ cÃ´ng',
            [
                'course_id' => $grant->course_id,
                'course_name' => $grant->course?->name_locale ?: $grant->course?->name,
                'had_paid_access' => $hasPaidAccess,
            ]
        );

        return redirect()
            ->route('teacher.dashboard.students.show', $student->id)
            ->with('msg_success', 'Da thu hoi suat cap quyen hoc.');
    }

    public function showStudent(int $studentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_students')) {
            return $featureRedirect;
        }

        [$student, $details, $grants] = $this->resolveOwnedStudentContext($teacher, $studentId);
        $orders = $details->pluck('order')->filter()->unique('id')->sortByDesc('id')->values();
        $paidCourses = $details->pluck('courses')->filter()->unique('id')->values();
        $grantedCourses = $grants->pluck('course')->filter()->unique('id')->values();
        $courses = $paidCourses->concat($grantedCourses)->unique('id')->values();
        $note = TeacherStudentNote::query()->firstWhere([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);
        $courseIds = $courses->pluck('id')->map(fn ($id) => (int) $id)->all();
        $courseLessonTotals = collect();
        if (!empty($courseIds)) {
            $courseLessonTotals = \Modules\Lessons\src\Models\Lesson::query()
                ->whereIn('course_id', $courseIds)
                ->whereNotNull('parent_id')
                ->where('status', 1)
                ->selectRaw('course_id, COUNT(*) as total_lessons')
                ->groupBy('course_id')
                ->pluck('total_lessons', 'course_id');
        }
        $courseProgressMap = StudentLessonProgress::query()
            ->where('student_id', $student->id)
            ->whereIn('course_id', $courseIds ?: [0])
            ->selectRaw('course_id, COUNT(DISTINCT lesson_id) as completed_lessons, MAX(completed_at) as last_completed_at')
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');
        $learningTimeline = StudentLessonProgress::query()
            ->with(['course', 'lesson'])
            ->where('student_id', $student->id)
            ->whereIn('course_id', $courseIds ?: [0])
            ->latest('completed_at')
            ->take(20)
            ->get();

        $courses = $courses->map(function ($course) use ($courseLessonTotals, $courseProgressMap) {
            $totalLessons = (int) ($courseLessonTotals[$course->id] ?? 0);
            $completedLessons = (int) ($courseProgressMap->get($course->id)?->completed_lessons ?? 0);
            $progressPercent = $totalLessons > 0
                ? min((int) round(($completedLessons * 100) / $totalLessons), 100)
                : 0;

            $course->teacher_progress_total_lessons = $totalLessons;
            $course->teacher_progress_completed_lessons = min($completedLessons, $totalLessons);
            $course->teacher_progress_percent = $progressPercent;
            $course->teacher_progress_last_completed_at = $courseProgressMap->get($course->id)?->last_completed_at;

            return $course;
        });
        $progressSummary = $this->summarizeStudentCourseProgress($courses);
        $activityHistory = $this->resolveStudentManagementHistory($teacher, $student);

        $pageTitle = 'Chi tiáº¿t há»c viÃªn';
        $pageName = $pageTitle;
        $summary = [
            'orders' => $orders->count(),
            'courses' => $courses->count(),
            'grants' => $grants->count(),
            'spent' => (float) $details->sum(fn ($detail) => (float) ($detail->price ?? 0)),
            'progress_percent' => $progressSummary['progress_percent'],
            'completed_lessons' => $progressSummary['completed_lessons'],
            'total_lessons' => $progressSummary['total_lessons'],
            'last_purchase_at' => optional(
                $details->sortByDesc(function ($detail) {
                    return optional($detail->order?->payment_complete_date ?: $detail->order?->payment_date ?: $detail->created_at)->timestamp ?? 0;
                })->first()
            )->order?->payment_complete_date
                ?: optional(
                    $details->sortByDesc(function ($detail) {
                        return optional($detail->order?->payment_complete_date ?: $detail->order?->payment_date ?: $detail->created_at)->timestamp ?? 0;
                    })->first()
                )->order?->payment_date
                ?: optional($details->sortByDesc('created_at')->first())->created_at,
        ];

        return view('teacher::clients.dashboard.student_show', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'student',
            'orders',
            'courses',
            'details',
            'grants',
            'note',
            'summary',
            'learningTimeline',
            'activityHistory'
        ) + [
            'studentFeatureState' => $this->resolveStudentFeatureState($teacher),
        ]);
    }

    public function saveStudentNote(Request $request, int $studentId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_manage_students')) {
            return $featureRedirect;
        }

        [$student] = $this->resolveOwnedStudentContext($teacher, $studentId);
        $existingNote = TeacherStudentNote::query()->firstWhere([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $data = $request->validate([
            'tag' => ['nullable', 'in:potential,support_needed,vip'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $savedNote = TeacherStudentNote::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
            ],
            [
                'tag' => $data['tag'] ?? null,
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
            ]
        );

        $this->logTeacherStudentActivity(
            $teacher,
            $student,
            'note_saved',
            'ÄÃ£ cáº­p nháº­t ghi chÃº ná»™i bá»™',
            [
                'tag' => $savedNote->tag,
                'tag_label' => $this->normalizeStudentTagLabel($savedNote->tag),
                'note_preview' => Str::limit((string) ($savedNote->note ?? ''), 160),
                'previous_tag' => $existingNote?->tag,
                'previous_note_preview' => Str::limit((string) ($existingNote?->note ?? ''), 160),
            ]
        );

        return redirect()
            ->route('teacher.dashboard.students.show', $student->id)
            ->with('msg_success', 'ÄÃ£ lÆ°u ghi chÃº ná»™i bá»™ cho há»c viÃªn.');
    }

    public function storePayout(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:5000'],
            'account_mode' => ['required', 'in:saved,new'],
            'payout_account_id' => ['nullable', 'integer'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:120'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
        ]);

        $summary = TeacherFinanceCalculator::summarize(
            $this->paidOrderDetailsQuery($teacher)->get(),
            fn () => $this->resolveEffectiveCommissionRate($teacher)
        );
        $requestedAmount = $this->resolveCommittedPayoutAmount($teacher);
        $availableBalance = max($summary['teacher_revenue'] - $requestedAmount, 0);

        if ((float) $data['amount'] > $availableBalance) {
            return back()->with('msg_danger', __('teacher::dashboard.payouts.flash.amount_exceeds_balance'));
        }

        $payoutAccounts = $teacher->payoutAccounts()->orderBy('id')->get();
        $bankData = $this->resolvePayoutBankData($request, $teacher, $data, $payoutAccounts);
        if ($bankData instanceof \Illuminate\Http\RedirectResponse) {
            return $bankData;
        }

        TeacherPayoutRequest::query()->create([
            'teacher_id' => $teacher->id,
            'amount' => $data['amount'],
            'bank_name' => $bankData['bank_name'],
            'bank_account_name' => $bankData['bank_account_name'],
            'bank_account_number' => $bankData['bank_account_number'],
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
            'status' => 'requested',
        ]);

        return redirect()->route('teacher.dashboard.payouts')
            ->with('msg_success', __('teacher::dashboard.payouts.flash.request_sent'));
    }

    public function storePayoutAccount(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:120'],
            'bank_account_number' => ['required', 'string', 'max:50'],
        ]);

        $bankData = $this->sanitizeBankData($data);
        $accounts = $teacher->payoutAccounts()->orderBy('id')->get();

        if ($this->findMatchingPayoutAccount($accounts, $bankData)) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.account_already_saved'));
        }

        $accountLimit = $this->resolvePayoutAccountLimit($teacher);
        if ($accounts->count() >= $accountLimit) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.limit_reached_use_change_request', [
                    'limit' => $accountLimit,
                ]));
        }

        TeacherPayoutAccount::query()->create(array_merge($bankData, [
            'teacher_id' => $teacher->id,
        ]));

        return redirect()->route('teacher.dashboard.payouts')
            ->with('msg_success', __('teacher::dashboard.payouts.flash.account_saved_success'));
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
            ->with('msg_success', 'Bạn đã gửi góp ý / báo cáo thành công cho admin.');
    }

    public function storePayoutAccountChangeRequest(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $accounts = $teacher->payoutAccounts()->orderBy('id')->get();
        $accountLimit = $this->resolvePayoutAccountLimit($teacher);
        if ($accounts->count() < $accountLimit) {
            return back()->with('msg_danger', __('teacher::dashboard.payouts.flash.change_request_not_required', [
                'limit' => $accountLimit,
            ]));
        }

        $data = $request->validate([
            'replace_payout_account_id' => ['required', 'integer'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:120'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
        ]);

        $replaceAccount = $accounts->firstWhere('id', (int) $data['replace_payout_account_id']);
        if (!$replaceAccount) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.invalid_saved_account'));
        }

        $bankData = $this->sanitizeBankData($data);
        if ($this->findMatchingPayoutAccount($accounts, $bankData)) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.account_already_saved'));
        }

        $duplicatePendingRequest = $teacher->payoutAccountChangeRequests()
            ->where('status', 'pending')
            ->get()
            ->first(fn (TeacherPayoutAccountChangeRequest $item) => $item->replace_payout_account_id === (int) $replaceAccount->id
                && $this->bankPayloadMatches($item, $bankData));

        if ($duplicatePendingRequest) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.change_request_exists'));
        }

        TeacherPayoutAccountChangeRequest::query()->create([
            'teacher_id' => $teacher->id,
            'replace_payout_account_id' => $replaceAccount->id,
            'replace_bank_name' => $replaceAccount->bank_name,
            'replace_bank_account_name' => $replaceAccount->bank_account_name,
            'replace_bank_account_number' => $replaceAccount->bank_account_number,
            'bank_name' => $bankData['bank_name'],
            'bank_account_name' => $bankData['bank_account_name'],
            'bank_account_number' => $bankData['bank_account_number'],
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.dashboard.payouts')
            ->with('msg_success', __('teacher::dashboard.payouts.flash.change_request_sent'));
    }

    private function resolveTeacher(): ?Teacher
    {
        $student = auth('students')->user();
        $teacher = Teacher::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$teacher) {
            return null;
        }

        $teacher = $this->packageLifecycleManager->sync($teacher);

        return $this->syncTeacherCommissionRate($teacher);
    }

    private function resolveEffectiveCommissionRate(Teacher $teacher): float
    {
        $teacher->loadMissing('application.package');

        return (float) ($teacher->application?->package?->commission_rate ?? $teacher->commission_rate ?? 0);
    }

    private function syncTeacherCommissionRate(Teacher $teacher): Teacher
    {
        $effectiveCommissionRate = $this->resolveEffectiveCommissionRate($teacher);

        if (abs((float) $teacher->commission_rate - $effectiveCommissionRate) < 0.0001) {
            return $teacher;
        }

        $teacher->forceFill([
            'commission_rate' => $effectiveCommissionRate,
        ])->save();

        return $teacher->fresh(['application.package']);
    }

    private function redirectToStatus()
    {
        return redirect()->route('teacher.account.status', ['locale' => session('locale', app()->getLocale())])
            ->with('msg_danger', __('teacher::dashboard.payouts.flash.inactive_teacher'));
    }

    private function ensurePackageFeatureAllowed(
        Teacher $teacher,
        string $feature,
        string $fallbackRoute = 'teacher.dashboard.index',
        array $routeParameters = []
    )
    {
        if ($teacher->packageHasFeature($feature)) {
            return null;
        }

        return redirect()
            ->route($fallbackRoute, $routeParameters)
            ->with('msg_danger', __('teacher::dashboard.package_features.feature_locked'));
    }

    private function resolvePromotionRecipientIds(
        Teacher $teacher,
        string $recipientMode = 'all',
        ?int $courseId = null,
        ?int $recentPurchaseDays = null,
        ?int $inactiveLearningDays = null,
        array $manualStudentIds = []
    ): array
    {
        if ($recipientMode === 'manual') {
            $allowedIds = $this->resolvePromotionStudentOptions($teacher)->pluck('id')->all();

            return collect($manualStudentIds)
                ->filter(fn ($id) => in_array((int) $id, $allowedIds, true))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        if ($recipientMode === 'all') {
            return $this->resolvePromotionStudentOptions($teacher)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        $orderDetailsQuery = $this->paidOrderDetailsQuery($teacher)
            ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
            ->when($recentPurchaseDays, function ($query) use ($recentPurchaseDays) {
                $threshold = Carbon::now()->subDays($recentPurchaseDays)->startOfDay();

                $query->whereHas('order', function ($orderQuery) use ($threshold) {
                    $orderQuery->where(function ($dateQuery) use ($threshold) {
                        $dateQuery->where('payment_complete_date', '>=', $threshold)
                            ->orWhere(function ($fallbackQuery) use ($threshold) {
                                $fallbackQuery->whereNull('payment_complete_date')
                                    ->where('payment_date', '>=', $threshold);
                            });
                    });
                });
            });

        $orderStudentIds = $orderDetailsQuery->get()->pluck('order.student_id');

        $grantStudentIds = collect();
        if (!$recentPurchaseDays) {
            $grantStudentIds = $this->teacherCourseGrantsQuery($teacher)
                ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
                ->pluck('student_id');
        }

        $recipientIds = $orderStudentIds
            ->concat($grantStudentIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($inactiveLearningDays) {
            $recipientIds = $this->filterInactivePromotionRecipients(
                $teacher,
                $recipientIds->all(),
                $courseId,
                $inactiveLearningDays
            );
        }

        return collect($recipientIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function resolvePromotionStudentOptions(Teacher $teacher): Collection
    {
        $orderStudentIds = $this->paidOrderDetailsQuery($teacher)
            ->get()
            ->pluck('order.student_id');

        $grantStudentIds = $this->teacherCourseGrantsQuery($teacher)
            ->pluck('student_id');

        $studentIds = $orderStudentIds
            ->concat($grantStudentIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($studentIds->isEmpty()) {
            return collect();
        }

        return Student::query()
            ->whereIn('id', $studentIds->all())
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone'])
            ->map(function ($student) use ($orderStudentIds, $grantStudentIds) {
                return (object) [
                    'id' => (int) $student->id,
                    'name' => trim((string) ($student->name ?: 'Học viên')),
                    'email' => trim((string) ($student->email ?? '')),
                    'phone' => trim((string) ($student->phone ?? '')),
                    'has_paid_order' => $orderStudentIds->contains($student->id),
                    'has_grant' => $grantStudentIds->contains($student->id),
                ];
            })
            ->values();
    }

    private function buildPromotionNotificationPayload(Teacher $teacher, TeacherPromotion $promotion, $course = null): array
    {
        $teacherName = $teacher->name_locale ?: $teacher->name ?: 'Giang vien';
        $courseName = $course ? (localizedModelField($course, 'name', app()->getLocale()) ?: __('teacher::dashboard.common.unknown_course')) : null;
        $title = $promotion->title;
        $message = $promotion->message;
        $template = trim((string) ($promotion->filters['template'] ?? 'custom'));
        $messageHtml = $promotion->filters['message_html'] ?? null;
        $ctaEnabled = !empty($promotion->filters['cta_enabled']);
        $ctaLabel = $promotion->filters['cta_label'] ?? null;
        $ctaUrl = $promotion->filters['cta_url'] ?? null;
        // Inbox Routing instead of direct route
        $url = route('students.promotions.show', ['promotion' => $promotion->id, 'locale' => app()->getLocale()]);

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
            'entity_type' => TeacherPromotion::class,
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

    private function resolvePromotionTemplateDefinitions(Teacher $teacher): array
    {
        $teacherName = trim((string) ($teacher->name_locale ?: $teacher->name ?: 'Giang vien'));

        return [
            'custom' => [
                'label' => 'Tự viết',
                'title' => '',
                'html' => '<p>Xin chào học viên,</p><p>Mình gửi tới bạn một cập nhật mới từ khóa học của mình.</p><p>Cảm ơn bạn đã luôn đồng hành.</p>',
                'cta_enabled' => false,
                'cta_label' => '',
            ],
            'flash_sale' => [
                'label' => 'Flash sale',
                'title' => 'Flash sale hôm nay từ ' . $teacherName,
                'html' => '<p>Xin chào học viên,</p><p>Mình đang mở ưu đãi trong thời gian ngắn cho khóa học <strong>__COURSE_NAME__</strong>. Nếu bạn đang muốn học tiếp hoặc mua thêm khóa mới, đây là thời điểm rất tốt để bắt đầu.</p><p>Ưu đãi có thể kết thúc sớm, bạn xem ngay để không bỏ lỡ.</p>',
                'cta_enabled' => true,
                'cta_label' => 'Xem ưu đãi ngay',
            ],
            'reactivation' => [
                'label' => 'Tái kích hoạt học viên',
                'title' => 'Bạn quay lại học cùng ' . $teacherName . ' nhé',
                'html' => '<p>Xin chào học viên,</p><p>Mình thấy bạn đã tạm dừng một thời gian ở khóa <strong>__COURSE_NAME__</strong>. Mình vừa cập nhật thêm nội dung và muốn mời bạn quay lại để học tiếp cho đúng lộ trình.</p><p>Nếu bạn cần một cú hích nhỏ để bắt đầu lại, mình đã để sẵn nút truy cập nhanh bên dưới.</p>',
                'cta_enabled' => true,
                'cta_label' => 'Học tiếp ngay',
            ],
            'new_course' => [
                'label' => 'Ra khóa mới',
                'title' => 'Khóa học mới đã lên sóng',
                'html' => '<p>Xin chào học viên,</p><p>Mình vừa ra mắt một khóa học mới với nội dung thực chiến hơn, cập nhật hơn và rất phù hợp để bạn học tiếp sau lộ trình hiện tại.</p><p>Bạn có thể xem chi tiết ngay để biết khóa học này có phù hợp với mình đến đâu, hoặc ghé trang của <strong>__TEACHER_NAME__</strong> để xem thêm các khóa học khác.</p>',
                'cta_enabled' => true,
                'cta_label' => 'Xem khóa học mới',
            ],
        ];
    }

    private function resolveTeacherPromotionCourseUrl(Teacher $teacher, $course = null, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        if ($course && ($course->slug_locale ?: $course->slug)) {
            return route('courses.detail', [
                'locale' => $locale,
                'slug' => $course->slug_locale ?: $course->slug,
            ]);
        }

        return $this->resolveTeacherPromotionFallbackUrl($teacher, $locale);
    }

    private function resolveTeacherPromotionFallbackUrl(Teacher $teacher, ?string $locale = null): string
    {
        return route('teacher.public.show', [
            'locale' => $locale ?: app()->getLocale(),
            'slug' => $teacher->slug_locale ?: $teacher->slug,
        ]);
    }

    private function resolvePromotionAudienceType(string $recipientMode, ?int $courseId, ?int $recentPurchaseDays, ?int $inactiveLearningDays): string
    {
        if ($recipientMode === 'manual') {
            return 'manual_students';
        }

        if ($recipientMode === 'all') {
            return 'all_students';
        }

        if ($courseId && $inactiveLearningDays) {
            return 'course_inactive_learners';
        }

        if ($courseId) {
            return 'course_students';
        }

        if ($recentPurchaseDays) {
            return 'recent_buyers';
        }

        if ($inactiveLearningDays) {
            return 'inactive_learners';
        }

        return 'all_students';
    }

    private function filterInactivePromotionRecipients(
        Teacher $teacher,
        array $recipientIds,
        ?int $courseId,
        int $inactiveLearningDays
    ): array {
        if (empty($recipientIds)) {
            return [];
        }

        $threshold = Carbon::now()->subDays($inactiveLearningDays)->endOfDay();
        $activeStudentIds = StudentLessonProgress::query()
            ->select('student_id')
            ->whereIn('student_id', $recipientIds)
            ->when($courseId, fn ($query) => $query->where('course_id', $courseId))
            ->when(!$courseId, function ($query) use ($teacher) {
                $query->whereHas('course', function ($courseQuery) use ($teacher) {
                    $courseQuery->withoutGlobalScopes()->where('teacher_id', $teacher->id);
                });
            })
            ->where('updated_at', '>', $threshold)
            ->groupBy('student_id')
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($recipientIds)
            ->diff($activeStudentIds)
            ->values()
            ->all();
    }

    private function bundleFormResponse(?int $bundleId = null)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_sell_bundles', 'teacher.dashboard.bundles')) {
            return $featureRedirect;
        }

        $bundle = null;
        if ($bundleId) {
            $bundle = TeacherCourseBundle::query()
                ->with('items')
                ->where('teacher_id', $teacher->id)
                ->findOrFail($bundleId);
        }

        $courseOptions = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->where('status', 1)
            ->where('is_learning_locked', '!=', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh', 'price', 'sale_price', 'thumbnail']);

        $pageTitle = $bundle
            ? __('teacher::dashboard.bundles.edit_title')
            : __('teacher::dashboard.bundles.create_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.bundle_form', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'bundle',
            'courseOptions'
        ));
    }

    private function persistBundle(TeacherCourseBundleRequest $request, ?int $bundleId = null)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensurePackageFeatureAllowed($teacher, 'can_sell_bundles', 'teacher.dashboard.bundles')) {
            return $featureRedirect;
        }

        $data = $request->validated();
        $courseIds = collect($data['course_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->where('status', 1)
            ->where('is_learning_locked', '!=', 1)
            ->whereIn('id', $courseIds)
            ->get(['id']);

        if ($courses->count() < 2 || $courses->count() !== $courseIds->count()) {
            return back()
                ->withInput()
                ->withErrors(['course_ids' => __('teacher::dashboard.bundles.flash.invalid_courses')]);
        }

        $existingBundle = $bundleId
            ? TeacherCourseBundle::query()->where('teacher_id', $teacher->id)->findOrFail($bundleId)
            : null;

        $baseSlug = Str::slug((string) $data['name']);
        $slug = $this->resolveUniqueBundleSlug($teacher, $baseSlug !== '' ? $baseSlug : 'combo-khoa-hoc', $existingBundle?->id);

        $bundle = DB::transaction(function () use ($teacher, $data, $courseIds, $existingBundle, $slug) {
            $payload = [
                'teacher_id' => $teacher->id,
                'name' => trim((string) $data['name']),
                'slug' => $slug,
                'description' => trim((string) ($data['description'] ?? '')),
                'thumbnail' => trim((string) ($data['thumbnail'] ?? '')),
                'price' => (float) $data['price'],
                'status' => (bool) ($data['status'] ?? false),
            ];

            $bundle = $existingBundle;
            if ($bundle) {
                $bundle->update($payload);
            } else {
                $payload['position'] = ((int) TeacherCourseBundle::query()->where('teacher_id', $teacher->id)->max('position')) + 1;
                $bundle = TeacherCourseBundle::query()->create($payload);
            }

            $bundle->items()->delete();

            foreach ($courseIds->values() as $index => $courseId) {
                $bundle->items()->create([
                    'course_id' => $courseId,
                    'position' => $index + 1,
                ]);
            }

            return $bundle;
        });

        return redirect()
            ->route('teacher.dashboard.bundles.edit', ['bundle' => $bundle->id])
            ->with('msg_success', $bundleId
                ? __('teacher::dashboard.bundles.flash.updated')
                : __('teacher::dashboard.bundles.flash.created'));
    }

    private function resolveUniqueBundleSlug(Teacher $teacher, string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $index = 2;

        while (
            TeacherCourseBundle::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $index;
            $index++;
        }

        return $slug;
    }

    private function resolveTopBundles(Teacher $teacher): Collection
    {
        $orders = \Modules\Orders\src\Models\Order::query()
            ->with(['bundle'])
            ->whereNotNull('bundle_id')
            ->where('status_id', 2)
            ->whereHas('bundle', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->get();

        return $orders
            ->groupBy('bundle_id')
            ->map(function ($bundleOrders) {
                $firstOrder = $bundleOrders->first();
                $bundle = $firstOrder?->bundle;

                return (object) [
                    'bundle' => $bundle,
                    'sales_count' => $bundleOrders->count(),
                    'gross_revenue' => (float) $bundleOrders->sum('total'),
                    'discount_amount' => (float) $bundleOrders->sum('discount'),
                    'net_revenue' => (float) $bundleOrders->sum(fn ($order) => max((float) $order->total - (float) ($order->discount ?? 0), 0)),
                ];
            })
            ->filter(fn ($item) => $item->bundle !== null)
            ->sortByDesc('net_revenue')
            ->take(5)
            ->values();
    }

    private function resolveCommittedPayoutAmount(Teacher $teacher): float
    {
        return (float) TeacherPayoutRequest::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('status', ['requested', 'processing', 'paid'])
            ->sum('amount');
    }

    private function resolvePayoutBankData(Request $request, Teacher $teacher, array $data, Collection $payoutAccounts)
    {
        if (($data['account_mode'] ?? null) === 'saved') {
            $selectedAccount = $payoutAccounts->firstWhere('id', (int) ($data['payout_account_id'] ?? 0));
            if (!$selectedAccount) {
                return back()
                    ->withInput()
                    ->with('msg_danger', __('teacher::dashboard.payouts.flash.invalid_saved_account'));
            }

            return [
                'bank_name' => $selectedAccount->bank_name,
                'bank_account_name' => $selectedAccount->bank_account_name,
                'bank_account_number' => $selectedAccount->bank_account_number,
            ];
        }

        $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:120'],
            'bank_account_number' => ['required', 'string', 'max:50'],
        ]);

        $bankData = $this->sanitizeBankData($data);
        $matchedAccount = $this->findMatchingPayoutAccount($payoutAccounts, $bankData);
        if ($matchedAccount) {
            return [
                'bank_name' => $matchedAccount->bank_name,
                'bank_account_name' => $matchedAccount->bank_account_name,
                'bank_account_number' => $matchedAccount->bank_account_number,
            ];
        }

        $accountLimit = $this->resolvePayoutAccountLimit($teacher);
        if ($payoutAccounts->count() >= $accountLimit) {
            return back()
                ->withInput()
                ->with('msg_danger', __('teacher::dashboard.payouts.flash.limit_reached_use_change_request', [
                    'limit' => $accountLimit,
                ]));
        }

        TeacherPayoutAccount::query()->create(array_merge($bankData, [
            'teacher_id' => $teacher->id,
        ]));

        return $bankData;
    }

    private function sanitizeBankData(array $data): array
    {
        return [
            'bank_name' => trim((string) ($data['bank_name'] ?? '')),
            'bank_account_name' => trim((string) ($data['bank_account_name'] ?? '')),
            'bank_account_number' => preg_replace('/\s+/', '', trim((string) ($data['bank_account_number'] ?? ''))),
        ];
    }

    private function findMatchingPayoutAccount(Collection $accounts, array $bankData): ?TeacherPayoutAccount
    {
        return $accounts->first(fn (TeacherPayoutAccount $account) => $this->bankPayloadMatches($account, $bankData));
    }

    private function bankPayloadMatches($accountLike, array $bankData): bool
    {
        return $this->normalizeBankText($accountLike->bank_name) === $this->normalizeBankText($bankData['bank_name'])
            && $this->normalizeBankText($accountLike->bank_account_name) === $this->normalizeBankText($bankData['bank_account_name'])
            && $this->normalizeBankAccountNumber($accountLike->bank_account_number) === $this->normalizeBankAccountNumber($bankData['bank_account_number']);
    }

    private function normalizeBankText(?string $value): string
    {
        return Str::upper(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    private function normalizeBankAccountNumber(?string $value): string
    {
        return preg_replace('/\s+/', '', trim((string) $value));
    }

    private function resolveVietnamBankOptions(): array
    {
        return [
            'Vietcombank' => 'Vietcombank',
            'VietinBank' => 'VietinBank',
            'BIDV' => 'BIDV',
            'Agribank' => 'Agribank',
            'Techcombank' => 'Techcombank',
            'MB Bank' => 'MB Bank',
            'ACB' => 'ACB',
            'VPBank' => 'VPBank',
            'TPBank' => 'TPBank',
            'Sacombank' => 'Sacombank',
            'HDBank' => 'HDBank',
            'SHB' => 'SHB',
            'VIB' => 'VIB',
            'SeABank' => 'SeABank',
            'OCB' => 'OCB',
            'Eximbank' => 'Eximbank',
            'MSB' => 'MSB',
            'Nam A Bank' => 'Nam A Bank',
            'SCB' => 'SCB',
            'ABBank' => 'ABBANK',
            'PVcomBank' => 'PVcomBank',
            'Bac A Bank' => 'Bac A Bank',
            'LienVietPostBank' => 'LPBank',
            'KienlongBank' => 'KienlongBank',
            'VietBank' => 'VietBank',
            'BaoViet Bank' => 'BaoViet Bank',
            'NCB' => 'NCB',
            'Saigonbank' => 'Saigonbank',
            'DongA Bank' => 'DongA Bank',
            'OceanBank' => 'OceanBank',
            'CBBank' => 'CBBank',
            'GPBank' => 'GPBank',
            'UOB Vietnam' => 'UOB Vietnam',
            'Standard Chartered Vietnam' => 'Standard Chartered Vietnam',
            'HSBC Vietnam' => 'HSBC Vietnam',
            'Shinhan Bank Vietnam' => 'Shinhan Bank Vietnam',
            'Woori Bank Vietnam' => 'Woori Bank Vietnam',
            'Public Bank Vietnam' => 'Public Bank Vietnam',
            'Hong Leong Bank Vietnam' => 'Hong Leong Bank Vietnam',
            'CIMB Bank Vietnam' => 'CIMB Bank Vietnam',
        ];
    }

    private function resolveCourseLimit(Teacher $teacher): ?int
    {
        return $teacher->application?->package?->effective_course_limit;
    }

    private function resolvePayoutAccountLimit(Teacher $teacher): int
    {
        return $teacher->application?->package?->effective_payout_account_limit ?? 3;
    }

    private function resolvePayoutAccountUsage(Teacher $teacher, int $used): array
    {
        $limit = $this->resolvePayoutAccountLimit($teacher);

        return array_merge(
            $this->packageUsageResolver->build($used, $limit),
            [
                'limit_label' => $limit,
            ]
        );
    }

    private function resolveStudentFeatureState(Teacher $teacher): array
    {
        $features = [
            'manage_students' => $teacher->packageHasFeature('can_manage_students'),
            'grant_courses' => $teacher->packageHasFeature('can_grant_courses'),
            'export_students' => $teacher->packageHasFeature('can_export_students'),
            'view_progress' => $teacher->packageHasFeature('can_view_student_progress'),
        ];

        return [
            'features' => $features,
            'is_feature_locked' => in_array(false, $features, true),
            'can_grant_courses' => $features['grant_courses'],
            'can_export_students' => $features['export_students'],
            'can_view_progress' => $features['view_progress'],
        ];
    }

    private function resolveNextPackage(?TeacherPackage $currentPackage): ?TeacherPackage
    {
        if (!$currentPackage) {
            return null;
        }

        return TeacherPackage::query()
            ->selectable()
            ->where('sort_order', '>', (int) $currentPackage->sort_order)
            ->orderBy('sort_order')
            ->first();
    }

    private function resolveAvailablePackageChanges(?TeacherPackage $currentPackage)
    {
        if (!$currentPackage) {
            return collect();
        }

        $query = TeacherPackage::query()
            ->selectable()
            ->orderBy('sort_order');

        if (!$this->packageLifecycleManager->isRecurring($currentPackage)) {
            $query->where('id', '!=', (int) $currentPackage->id);
        }

        return $query->get();
    }

    private function resolveOpenPackageChangeRequest(Teacher $teacher): ?TeacherApplication
    {
        return TeacherApplication::query()
            ->with(['package'])
            ->where('teacher_id', $teacher->id)
            ->where('id', '!=', (int) $teacher->application_id)
            ->where(function ($query) {
                $query->whereIn('status', ['pending_payment', 'pending_review'])
                    ->orWhere(function ($approvedQuery) {
                        $approvedQuery->where('status', 'approved')
                            ->whereNotNull('activates_at')
                            ->whereNull('activated_at');
                    });
            })
            ->latest('id')
            ->first();
    }

    private function finalizePackageChange(Teacher $teacher, TeacherApplication $upgradeRequest): string
    {
        $upgradeRequest->forceFill([
            'status' => 'approved',
            'submitted_at' => $upgradeRequest->submitted_at ?: now(),
            'reviewed_at' => now(),
        ])->save();

        $action = $this->packageLifecycleManager->applyApprovedChange($teacher, $upgradeRequest->fresh(['package']));

        $teacher->refresh();

        return $action;
    }

    private function redirectAfterPackageChange(string $action)
    {
        $flashKey = match ($action) {
            'extended' => 'teacher::dashboard.package.flash.auto_extended',
            'queued' => 'teacher::dashboard.package.flash.auto_queued',
            default => 'teacher::dashboard.package.flash.auto_activated',
        };

        $route = $action === 'queued'
            ? route('teacher.dashboard.package.upgrade.status')
            : route('teacher.dashboard.index');

        return redirect($route)->with('msg_success', __($flashKey));
    }

    private function resolvePackageOverLimitWarnings(Teacher $teacher, TeacherApplication $upgradeRequest): array
    {
        $currentPackage = $teacher->application?->package;
        $targetPackage = $upgradeRequest->package;

        if (
            !$currentPackage ||
            !$targetPackage ||
            (int) $targetPackage->sort_order >= (int) $currentPackage->sort_order
        ) {
            return [];
        }

        $warnings = [];

        $courseCount = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->where('status', 1)
            ->count();
        $targetCourseLimit = $targetPackage->effective_course_limit;
        if ($targetCourseLimit !== null && $courseCount > $targetCourseLimit) {
            $warnings[] = __('teacher::dashboard.package.over_limit.course_limit', [
                'used' => $courseCount,
                'limit' => $targetCourseLimit,
            ]);
        }

        $couponCount = Coupons::query()
            ->where('teacher_id', $teacher->id)
            ->count();
        $targetCouponLimit = $targetPackage->hasFeature('can_manage_coupons')
            ? $targetPackage->effective_coupon_limit
            : 0;
        if ($targetCouponLimit !== null && $couponCount > $targetCouponLimit) {
            $warnings[] = __('teacher::dashboard.package.over_limit.coupon_limit', [
                'used' => $couponCount,
                'limit' => $targetCouponLimit,
            ]);
        }

        $payoutAccountCount = TeacherPayoutAccount::query()
            ->where('teacher_id', $teacher->id)
            ->count();
        $targetPayoutLimit = $targetPackage->effective_payout_account_limit;
        if ($payoutAccountCount > $targetPayoutLimit) {
            $warnings[] = __('teacher::dashboard.package.over_limit.payout_account_limit', [
                'used' => $payoutAccountCount,
                'limit' => $targetPayoutLimit,
            ]);
        }

        return $warnings;
    }

    private function resolvePackageFeatureLossWarnings(Teacher $teacher, TeacherApplication $upgradeRequest): array
    {
        $currentPackage = $teacher->application?->package;
        $targetPackage = $upgradeRequest->package;

        if (
            !$currentPackage ||
            !$targetPackage ||
            (int) $targetPackage->sort_order >= (int) $currentPackage->sort_order
        ) {
            return [];
        }

        $featureKeys = [
            'can_duplicate_courses',
            'can_manage_comments',
            'can_manage_coupons',
            'can_manage_students',
            'can_view_student_progress',
            'can_view_activity_logs',
            'can_grant_courses',
            'can_export_orders',
            'can_export_students',
        ];

        $warnings = [];
        foreach ($featureKeys as $featureKey) {
            if ($currentPackage->hasFeature($featureKey) && !$targetPackage->hasFeature($featureKey)) {
                $warnings[] = __('teacher::dashboard.package_features.labels.' . $featureKey);
            }
        }

        return $warnings;
    }

    private function summarizeStudentCourseProgress(Collection $courses): array
    {
        $totalLessons = (int) $courses->sum('teacher_progress_total_lessons');
        $completedLessons = (int) $courses->sum('teacher_progress_completed_lessons');
        $progressPercent = $totalLessons > 0
            ? min((int) round(($completedLessons * 100) / $totalLessons), 100)
            : 0;

        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percent' => $progressPercent,
        ];
    }

    private function attachCourseHistoryPreview(LengthAwarePaginator $courses, Teacher $teacher): void
    {
        $courseIds = collect($courses->items())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $historyMap = ActiveLog::query()
            ->where('log_name', 'teacher_course_management')
            ->where(function ($query) use ($courseIds) {
                $query->where(function ($subjectQuery) use ($courseIds) {
                    $subjectQuery->where('subject_type', Courses::class)
                        ->whereIn('subject_id', $courseIds ?: [0]);
                })->orWhere(function ($propertyQuery) use ($courseIds) {
                    $propertyQuery->whereNull('subject_id')
                        ->whereIn('properties->course_id', $courseIds ?: [0]);
                });
            })
            ->where('properties->teacher_id', $teacher->id)
            ->latest('id')
            ->get()
            ->groupBy(function ($log) {
                return (int) ($log->subject_id ?: data_get($log->properties, 'course_id', 0));
            });

        foreach ($courses->items() as $course) {
            $course->teacher_activity_preview = ($historyMap->get((int) $course->id, collect()) ?? collect())
                ->take(3)
                ->values();
        }
    }

    private function logTeacherCourseActivity(
        Teacher $teacher,
        Courses $course,
        string $action,
        string $description,
        array $properties = []
    ): void {
        activity_log(
            $action,
            $course,
            array_merge($properties, [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'course_id' => $course->id,
                'course_name' => $course->name_locale ?: $course->name,
            ]),
            'teacher_course_management',
            $description
        );
    }

    private function logTeacherLessonActivity(
        Teacher $teacher,
        Courses $course,
        Lesson $lesson,
        string $action,
        string $description,
        array $properties = []
    ): void {
        activity_log(
            $action,
            $lesson,
            array_merge($properties, [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'course_id' => $course->id,
                'course_name' => $course->name_locale ?: $course->name,
                'lesson_id' => $lesson->id,
                'lesson_name' => $lesson->name_locale ?: $lesson->name,
            ]),
            'teacher_course_management',
            $description
        );
    }

    private function summarizeLessonSchedule(?Lesson $lesson): array
    {
        return [
            'mode' => $lesson?->release_mode ?: LessonReleaseManager::MODE_IMMEDIATE,
            'mode_label' => $lesson ? $lesson->releaseSummary() : 'Má»Ÿ ngay',
            'release_at' => $lesson?->release_at?->format('Y-m-d H:i:s'),
            'release_after_days' => $lesson?->release_after_days,
        ];
    }

    private function resolveStudentManagementHistory(Teacher $teacher, Student $student): Collection
    {
        return ActiveLog::query()
            ->where('log_name', 'teacher_student_management')
            ->where('subject_type', Student::class)
            ->where('subject_id', $student->id)
            ->where('properties->teacher_id', $teacher->id)
            ->latest('id')
            ->take(12)
            ->get();
    }

    private function logTeacherStudentActivity(
        Teacher $teacher,
        Student $student,
        string $action,
        string $description,
        array $properties = []
    ): void {
        activity_log(
            $action,
            $student,
            array_merge($properties, [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'student_id' => $student->id,
                'student_name' => $student->name,
            ]),
            'teacher_student_management',
            $description
        );
    }

    private function resolveOwnedCourse(Teacher $teacher, int $courseId, bool $withTrashed = false): Courses
    {
        $this->syncCourseLocks($teacher);

        $query = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->where('id', $courseId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->firstOrFail();
    }

    private function resolveOwnedLesson(Courses $course, int $lessonId, bool $withTrashed = false): Lesson
    {
        $query = Lesson::query()
            ->where('course_id', $course->id)
            ->where('id', $lessonId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->firstOrFail();
    }

    private function duplicateTitle(?string $value, string $suffix): ?string
    {
        if (!$value) {
            return $value;
        }

        return trim($value . ' (' . $suffix . ')');
    }

    private function duplicateSlug(?string $value, string $suffix = 'copy'): ?string
    {
        if (!$value) {
            return $value;
        }

        return trim($value . '-' . $suffix . '-' . Str::lower(Str::random(4)), '-');
    }

    private function duplicateCode(?string $value): string
    {
        $base = $value ?: 'COURSE';

        return $base . '-COPY-' . strtoupper(Str::random(4));
    }

    private function performCourseDuplicate(Courses $course): Courses
    {
        return DB::transaction(function () use ($course) {
            $courseData = $course->getAttributes();

            unset($courseData['id'], $courseData['created_at'], $courseData['updated_at'], $courseData['deleted_at']);
            $courseData['name'] = $this->duplicateTitle($course->name, 'Báº£n sao');
            $courseData['name_ko'] = $this->duplicateTitle($course->name_ko, 'ë³µì œë³¸');
            $courseData['name_ja'] = $this->duplicateTitle($course->name_ja, 'è¤‡è£½ç‰ˆ');
            $courseData['name_zh'] = $this->duplicateTitle($course->name_zh, 'å¤åˆ¶ç‰ˆ');
            $courseData['slug'] = $this->duplicateSlug($course->slug, 'copy');
            $courseData['slug_en'] = $this->duplicateSlug($course->slug_en, 'copy');
            $courseData['slug_ko'] = $this->duplicateSlug($course->slug_ko, 'copy');
            $courseData['slug_ja'] = $this->duplicateSlug($course->slug_ja, 'copy');
            $courseData['slug_zh'] = $this->duplicateSlug($course->slug_zh, 'copy');
            $courseData['code'] = $this->duplicateCode($course->code);
            $courseData['status'] = 0;
            $courseData['is_learning_locked'] = 0;
            $courseData['view'] = 0;

            $newCourse = $this->courseRepository->create($courseData);

            $categoryIds = $this->courseRepository->getRelatedCategories($course);
            if (!empty($categoryIds)) {
                $newCourse->categories()->attach($this->categoriesPivotPayload($categoryIds));
            }

            $lessonMap = [];
            $lessons = Lesson::query()
                ->where('course_id', $course->id)
                ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
                ->orderBy('parent_id')
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            foreach ($lessons as $lesson) {
                $lessonData = $lesson->getAttributes();

                unset($lessonData['id'], $lessonData['created_at'], $lessonData['updated_at']);

                $oldParentId = $lessonData['parent_id'] ?? null;
                $lessonData['course_id'] = $newCourse->id;
                $lessonData['name'] = $this->duplicateTitle($lesson->name, 'Báº£n sao');
                $lessonData['name_ko'] = $this->duplicateTitle($lesson->name_ko, 'ë³µì œë³¸');
                $lessonData['name_ja'] = $this->duplicateTitle($lesson->name_ja, 'è¤‡è£½ç‰ˆ');
                $lessonData['name_zh'] = $this->duplicateTitle($lesson->name_zh, 'å¤åˆ¶ç‰ˆ');
                $lessonData['slug'] = $this->duplicateSlug($lesson->slug, 'copy');
                $lessonData['slug_en'] = $this->duplicateSlug($lesson->slug_en, 'copy');
                $lessonData['slug_ko'] = $this->duplicateSlug($lesson->slug_ko, 'copy');
                $lessonData['slug_ja'] = $this->duplicateSlug($lesson->slug_ja, 'copy');
                $lessonData['slug_zh'] = $this->duplicateSlug($lesson->slug_zh, 'copy');
                $lessonData['status'] = 0;
                $lessonData['view'] = 0;
                $lessonData['parent_id'] = null;

                $newLesson = Lesson::query()->create($lessonData);
                $lessonMap[$lesson->id] = [
                    'id' => $newLesson->id,
                    'parent_id' => $oldParentId,
                ];
            }

            foreach ($lessonMap as $map) {
                if (!empty($map['parent_id']) && isset($lessonMap[$map['parent_id']])) {
                    Lesson::query()
                        ->whereKey($map['id'])
                        ->update(['parent_id' => $lessonMap[$map['parent_id']]['id']]);
                }
            }

            return $newCourse;
        });
    }

    private function getCourseCategories()
    {
        return Category::query()
            ->where(function ($query) {
                $query->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->with([
                'children' => fn ($query) => $query
                    ->orderBy('id')
                    ->with([
                        'children' => fn ($childQuery) => $childQuery
                            ->orderBy('id')
                            ->with([
                                'children' => fn ($grandChildQuery) => $grandChildQuery->orderBy('id'),
                            ]),
                    ]),
            ])
            ->orderBy('id')
            ->get();
    }

    private function buildCoursePayload(array $data, Teacher $teacher, ?Courses $course = null): array
    {
        $normalizedStatus = $this->normalizeCourseStatusForPackage($teacher, (int) $data['status'], $course);

        return [
            'teacher_id' => $teacher->id,
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'name_ko' => $data['name_ko'] ?? null,
            'name_ja' => $data['name_ja'] ?? null,
            'name_zh' => $data['name_zh'] ?? null,
            'slug' => $this->generateCourseSlug($data['name'], 'slug', $course?->id),
            'slug_en' => $this->generateOptionalCourseSlug($data['name_en'] ?? null, 'slug_en', $course?->id),
            'slug_ko' => $this->generateOptionalCourseSlug($data['name_ko'] ?? null, 'slug_ko', $course?->id),
            'slug_ja' => $this->generateOptionalCourseSlug($data['name_ja'] ?? null, 'slug_ja', $course?->id),
            'slug_zh' => $this->generateOptionalCourseSlug($data['name_zh'] ?? null, 'slug_zh', $course?->id),
            'detail' => $data['detail'],
            'detail_en' => $data['detail_en'] ?? null,
            'detail_ko' => $data['detail_ko'] ?? null,
            'detail_ja' => $data['detail_ja'] ?? null,
            'detail_zh' => $data['detail_zh'] ?? null,
            'supports' => $data['supports'],
            'supports_en' => $data['supports_en'] ?? null,
            'supports_ko' => $data['supports_ko'] ?? null,
            'supports_ja' => $data['supports_ja'] ?? null,
            'supports_zh' => $data['supports_zh'] ?? null,
            'thumbnail' => $data['thumbnail'],
            'price' => (float) ($data['price'] ?? 0),
            'sale_price' => (float) ($data['sale_price'] ?? 0),
            'code' => $this->generateCourseCode($data['code'] ?? null, $course?->id),
            'is_document' => (int) $data['is_document'],
            'status' => $normalizedStatus,
            'is_learning_locked' => (int) $data['is_learning_locked'],
        ];
    }

    private function resolvePublishedCourseUsage(Teacher $teacher): array
    {
        $courseLimit = $this->resolveCourseLimit($teacher);
        $totalCourses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->count();
        $publishedCourses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->where('status', 1)
            ->count();

        return array_merge(
            $this->packageUsageResolver->build($publishedCourses, $courseLimit),
            [
            'published' => $publishedCourses,
            'total' => $totalCourses,
            'limit_label' => $courseLimit === null ? __('teacher::dashboard.courses.unlimited') : $courseLimit,
            ]
        );
    }

    private function normalizeCourseStatusForPackage(Teacher $teacher, int $requestedStatus, ?Courses $course = null): int
    {
        if ($requestedStatus !== 1) {
            return 0;
        }

        return $this->canKeepOrPublishCourse($teacher, $course, $requestedStatus) ? 1 : 0;
    }

    private function canKeepOrPublishCourse(Teacher $teacher, ?Courses $course, int $requestedStatus): bool
    {
        if ($requestedStatus !== 1) {
            return true;
        }

        if ($course && (int) $course->status === 1 && !$course->trashed() && !$course->package_locked_at) {
            return true;
        }

        return $this->hasAvailablePublishedCourseSlot($teacher, $course?->id);
    }

    private function hasAvailablePublishedCourseSlot(Teacher $teacher, ?int $ignoreCourseId = null): bool
    {
        $limit = $this->resolveCourseLimit($teacher);
        if ($limit === null) {
            return true;
        }

        $publishedCount = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->where('status', 1)
            ->when($ignoreCourseId, fn ($query) => $query->where('id', '!=', $ignoreCourseId))
            ->count();

        return $publishedCount < $limit;
    }

    private function activateCourseWithinLimit(Teacher $teacher, Courses $course): void
    {
        $limit = $this->resolveCourseLimit($teacher);

        if ($limit !== null) {
            $publishedQuery = Courses::query()
                ->withoutGlobalScope(ActiveScope::class)
                ->where('teacher_id', $teacher->id)
                ->where('status', 1)
                ->where('id', '!=', $course->id);

            if ($publishedQuery->count() >= $limit) {
                $demoteCourse = (clone $publishedQuery)
                    ->orderBy('is_package_priority')
                    ->orderBy('updated_at')
                    ->orderBy('id')
                    ->first();

                if ($demoteCourse) {
                    $demoteCourse->forceFill([
                        'status' => 0,
                        'package_locked_at' => now(),
                        'package_lock_reason' => 'package_limit_locked',
                    ])->save();
                }
            }
        }

        $course->forceFill([
            'status' => 1,
            'package_locked_at' => null,
            'package_lock_reason' => null,
        ])->save();

        $this->syncCourseLocks($teacher);
    }

    private function syncCourseLocks(Teacher $teacher): void
    {
        $this->packageLifecycleManager->syncCourseLocks($teacher->fresh(['application.package']));
    }

    private function ensureCourseManageable(Courses $course)
    {
        if (!$course->package_locked_at) {
            return null;
        }

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_danger', __('teacher::dashboard.courses.flash.locked_manage_only'));
    }

    private function syncCourseCategories(Courses $course, array $categories): void
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $normalized = collect($categories)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $course->categories()->sync(
            $normalized->mapWithKeys(fn ($categoryId) => [
                $categoryId => ['created_at' => $timestamp, 'updated_at' => $timestamp],
            ])->all()
        );
    }

    private function categoriesPivotPayload(array $categories): array
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');

        return collect($categories)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($categoryId) => [
                $categoryId => ['created_at' => $timestamp, 'updated_at' => $timestamp],
            ])->all();
    }

    private function buildLessonPayload(array $data, Courses $course, ?Lesson $lesson = null, bool $canScheduleContent = false): array
    {
        $parentId = $this->normalizeLessonParentId($course, (int) ($data['parent_id'] ?? 0), $lesson?->id);
        $videoUrl = trim((string) ($data['video'] ?? ''));
        $documentUrl = trim((string) ($data['document'] ?? ''));
        $videoId = $lesson?->video_id;
        $documentId = $lesson?->document_id;
        $durations = $lesson?->getRawOriginal('durations') ?? 0;

        if ($documentUrl !== '') {
            $documentInfo = getFileInfo($documentUrl);
            $document = $this->documentRepository->createDocument([
                'name' => $documentInfo['name'] ?? $data['name'],
                'url' => $documentUrl,
                'size' => $documentInfo['size'] ?? 0,
            ], $documentUrl);
            $documentId = $document?->id;
        } elseif (($data['remove_document'] ?? 0) == 1) {
            $documentId = null;
        }

        if ($videoUrl !== '') {
            $host = strtolower((string) parse_url($videoUrl, PHP_URL_HOST));
            $isExternal = $host && (
                str_contains($host, 'youtube.com') ||
                str_contains($host, 'youtu.be') ||
                str_contains($host, 'vimeo.com')
            );

            if ($isExternal) {
                $video = $this->videoRepository->createVideo([
                    'url' => $videoUrl,
                    'name' => $data['name'],
                    'size' => 0,
                ], $videoUrl);
                $videoId = $video?->id;
                $durations = externalVideoDuration($videoUrl);
            } else {
                $videoInfo = getVideoInfo($videoUrl);
                $video = $this->videoRepository->createVideo([
                    'url' => $videoUrl,
                    'name' => $videoInfo['filename'] ?? $data['name'],
                    'size' => $videoInfo['playtime_seconds'] ?? 0,
                ], $videoUrl);
                $videoId = $video?->id;
                $durations = $videoInfo['playtime_seconds'] ?? 0;
            }
        } elseif (($data['remove_video'] ?? 0) == 1) {
            $videoId = null;
            $durations = 0;
        }

        return array_merge([
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'name_ko' => $data['name_ko'] ?? null,
            'name_ja' => $data['name_ja'] ?? null,
            'name_zh' => $data['name_zh'] ?? null,
            'slug' => $this->generateLessonSlug($data['name'], 'slug', $lesson?->id),
            'slug_en' => $this->generateOptionalLessonSlug($data['name_en'] ?? null, 'slug_en', $lesson?->id),
            'slug_ko' => $this->generateOptionalLessonSlug($data['name_ko'] ?? null, 'slug_ko', $lesson?->id),
            'slug_ja' => $this->generateOptionalLessonSlug($data['name_ja'] ?? null, 'slug_ja', $lesson?->id),
            'slug_zh' => $this->generateOptionalLessonSlug($data['name_zh'] ?? null, 'slug_zh', $lesson?->id),
            'video_id' => $videoId,
            'course_id' => $course->id,
            'document_id' => $documentId,
            'parent_id' => $parentId > 0 ? $parentId : null,
            'is_trial' => (int) ($data['is_trial'] ?? 0),
            'position' => (int) ($data['position'] ?? $this->nextLessonPosition($course, $parentId)),
            'durations' => $durations,
            'description' => $data['description'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'description_ko' => $data['description_ko'] ?? null,
            'description_ja' => $data['description_ja'] ?? null,
            'description_zh' => $data['description_zh'] ?? null,
            'status' => (int) ($data['status'] ?? 0),
        ], $this->lessonReleaseManager->normalizePayload($data, $lesson, $canScheduleContent));
    }

    private function nextLessonPosition(Courses $course, int $parentId = 0): int
    {
        return (int) Lesson::query()
            ->where('course_id', $course->id)
            ->where('parent_id', $parentId > 0 ? $parentId : null)
            ->max('position') + 1;
    }

    private function normalizeLessonParentId(Courses $course, int $parentId, ?int $lessonId = null): int
    {
        if ($parentId <= 0) {
            return 0;
        }

        $query = Lesson::query()
            ->where('course_id', $course->id)
            ->where('id', $parentId)
            ->whereNull('parent_id');

        if ($lessonId) {
            $query->where('id', '!=', $lessonId);
        }

        return $query->exists() ? $parentId : 0;
    }

    private function lessonImportColumns(): array
    {
        return [
            'type',
            'module_ref',
            'parent_selector',
            'name',
            'name_en',
            'name_ko',
            'name_ja',
            'name_zh',
            'description',
            'description_en',
            'description_ko',
            'description_ja',
            'description_zh',
            'position',
            'is_trial',
            'status',
            'video',
            'document',
            'release_mode',
            'release_at',
            'release_after_days',
        ];
    }

    private function buildExistingLessonModuleSelectors(Courses $course): array
    {
        return Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn (Lesson $lesson) => [
                'value' => 'id:' . $lesson->id,
                'label' => 'id:' . $lesson->id . ' - ' . ($lesson->name_locale ?: $lesson->name),
            ])
            ->all();
    }

    private function buildLessonExportRows(Courses $course): array
    {
        $rows = [];
        $modules = Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->with(['subLessons' => fn ($query) => $query->orderBy('position')->orderBy('id')])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        foreach ($modules as $module) {
            $moduleRef = 'module-' . $module->id;
            $rows[] = $this->formatLessonExportRow($module, 'module', $moduleRef, '');

            foreach ($module->subLessons as $lesson) {
                $rows[] = $this->formatLessonExportRow($lesson, 'lesson', '', 'ref:' . $moduleRef);
            }
        }

        return $rows;
    }

    private function formatLessonExportRow(Lesson $lesson, string $type, string $moduleRef, string $parentSelector): array
    {
        return [
            'type' => $type,
            'module_ref' => $moduleRef,
            'parent_selector' => $parentSelector,
            'name' => $lesson->name,
            'name_en' => $lesson->name_en,
            'name_ko' => $lesson->name_ko,
            'name_ja' => $lesson->name_ja,
            'name_zh' => $lesson->name_zh,
            'description' => $lesson->description,
            'description_en' => $lesson->description_en,
            'description_ko' => $lesson->description_ko,
            'description_ja' => $lesson->description_ja,
            'description_zh' => $lesson->description_zh,
            'position' => $lesson->position,
            'is_trial' => $type === 'lesson' ? (int) $lesson->is_trial : 0,
            'status' => (int) $lesson->status,
            'video' => $lesson->video?->url,
            'document' => $lesson->document?->url,
            'release_mode' => $lesson->release_mode ?: LessonReleaseManager::MODE_IMMEDIATE,
            'release_at' => $lesson->release_at?->format('Y-m-d H:i:s'),
            'release_after_days' => $lesson->release_after_days,
        ];
    }

    private function lessonImportTemplateRows(): array
    {
        return [
            [
                'type' => 'module',
                'module_ref' => 'MOD-INTRO',
                'parent_selector' => '',
                'name' => 'Module má»Ÿ Ä‘áº§u',
                'name_en' => 'Introduction module',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => 'NhÃ³m bÃ i há»c giá»›i thiá»‡u',
                'description_en' => '',
                'description_ko' => '',
                'description_ja' => '',
                'description_zh' => '',
                'position' => '1',
                'is_trial' => '0',
                'status' => '1',
                'video' => '',
                'document' => '',
                'release_mode' => 'immediate',
                'release_at' => '',
                'release_after_days' => '',
            ],
            [
                'type' => 'lesson',
                'module_ref' => '',
                'parent_selector' => 'ref:MOD-INTRO',
                'name' => 'BÃ i 1',
                'name_en' => 'Lesson 1',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => 'Ná»™i dung bÃ i há»c Ä‘áº§u tiÃªn',
                'description_en' => '',
                'description_ko' => '',
                'description_ja' => '',
                'description_zh' => '',
                'position' => '1',
                'is_trial' => '1',
                'status' => '1',
                'video' => 'https://example.com/video-1',
                'document' => 'https://example.com/document-1.pdf',
                'release_mode' => 'immediate',
                'release_at' => '',
                'release_after_days' => '',
            ],
            [
                'type' => 'lesson',
                'module_ref' => '',
                'parent_selector' => 'id:12',
                'name' => 'BÃ i gáº¯n vÃ o module sáºµn cÃ³',
                'name_en' => 'Attach to an existing module',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => 'VÃ­ dá»¥ dÃ¹ng id cá»§a module Ä‘Ã£ cÃ³ trong khÃ³a há»c',
                'description_en' => '',
                'description_ko' => '',
                'description_ja' => '',
                'description_zh' => '',
                'position' => '',
                'is_trial' => '0',
                'status' => '0',
                'video' => '',
                'document' => '',
                'release_mode' => 'immediate',
                'release_at' => '',
                'release_after_days' => '',
            ],
        ];
    }

    private function lessonImportExampleRows(): array
    {
        return [
            [
                'type' => 'module',
                'module_ref' => 'MOD-FOUNDATION',
                'parent_selector' => '',
                'name' => 'Module ná»n táº£ng',
                'name_en' => 'Foundation module',
                'name_ko' => 'ê¸°ì´ˆ ëª¨ë“ˆ',
                'name_ja' => 'åŸºç¤Žãƒ¢ã‚¸ãƒ¥ãƒ¼ãƒ«',
                'name_zh' => 'åŸºç¡€æ¨¡å—',
                'description' => 'NhÃ³m bÃ i giÃºp há»c viÃªn lÃ m quen vá»›i khÃ³a há»c vÃ  chuáº©n bá»‹ tÃ i nguyÃªn cáº§n thiáº¿t.',
                'description_en' => 'A starter module that helps students prepare before diving into the core lessons.',
                'description_ko' => '',
                'description_ja' => '',
                'description_zh' => '',
                'position' => '1',
                'is_trial' => '0',
                'status' => '1',
                'video' => '',
                'document' => '',
                'release_mode' => 'immediate',
                'release_at' => '',
                'release_after_days' => '',
            ],
            [
                'type' => 'lesson',
                'module_ref' => '',
                'parent_selector' => 'ref:MOD-FOUNDATION',
                'name' => 'BÃ i 1 - CÃ¡ch dÃ¹ng khÃ³a há»c',
                'name_en' => 'Lesson 1 - How to use this course',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => 'Video onboarding, lá»™ trÃ¬nh há»c vÃ  checklist tÃ i nguyÃªn cáº§n chuáº©n bá»‹.',
                'description_en' => 'Onboarding video, study roadmap, and a quick resource checklist.',
                'description_ko' => '',
                'description_ja' => '',
                'description_zh' => '',
                'position' => '1',
                'is_trial' => '1',
                'status' => '1',
                'video' => 'https://example.com/videos/course-onboarding',
                'document' => 'https://example.com/docs/course-checklist.pdf',
                'release_mode' => 'immediate',
                'release_at' => '',
                'release_after_days' => '',
            ],
            [
                'type' => 'lesson',
                'module_ref' => '',
                'parent_selector' => 'ref:MOD-FOUNDATION',
                'name' => 'BÃ i 2 - Thiáº¿t láº­p mÃ´i trÆ°á»ng',
                'name_en' => 'Lesson 2 - Setup workspace',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => 'HÆ°á»›ng dáº«n cÃ i Ä‘áº·t cÃ´ng cá»¥ vÃ  táº£i file thá»±c hÃ nh trÆ°á»›c khi há»c.',
                'description_en' => 'Setup guide for tools and practice files before starting the real project.',
                'description_ko' => '',
                'description_ja' => '',
                'description_zh' => '',
                'position' => '2',
                'is_trial' => '0',
                'status' => '1',
                'video' => 'https://example.com/videos/setup-workspace',
                'document' => '',
                'release_mode' => 'days_after_enrollment',
                'release_at' => '',
                'release_after_days' => '2',
            ],
            [
                'type' => 'lesson',
                'module_ref' => '',
                'parent_selector' => 'id:12',
                'name' => 'BÃ i thÃªm vÃ o module sáºµn cÃ³',
                'name_en' => 'Lesson added to an existing module',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => 'VÃ­ dá»¥ nÃ y dÃ¹ng parent_selector dáº¡ng id Ä‘á»ƒ gáº¯n vÃ o module Ä‘Ã£ cÃ³ sáºµn trong khÃ³a há»c.',
                'description_en' => 'This sample shows how to attach a lesson to an existing module by id.',
                'description_ko' => '',
                'description_ja' => '',
                'description_zh' => '',
                'position' => '3',
                'is_trial' => '0',
                'status' => '0',
                'video' => '',
                'document' => 'https://example.com/docs/existing-module-note.pdf',
                'release_mode' => 'immediate',
                'release_at' => '',
                'release_after_days' => '',
            ],
        ];
    }

    private function streamCsvDownload(string $filename, array $headers, array $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, collect($headers)->map(fn ($header) => $row[$header] ?? '')->all());
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function streamXlsxDownload(string $filename, array $headers, array $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $tempFile = tempnam(sys_get_temp_dir(), 'lesson-xlsx-');
            if ($tempFile === false) {
                throw new \RuntimeException('Unable to create temporary XLSX file.');
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                @unlink($tempFile);
                throw new \RuntimeException('Unable to open temporary XLSX archive.');
            }

            $allRows = array_merge([$headers], array_map(
                fn ($row) => collect($headers)->map(fn ($header) => (string) ($row[$header] ?? ''))->all(),
                $rows
            ));

            $sharedStringIndex = [];
            $sharedStrings = [];
            $sheetRowsXml = '';

            foreach ($allRows as $rowIndex => $rowValues) {
                $cellsXml = '';

                foreach (array_values($rowValues) as $columnIndex => $value) {
                    $value = (string) $value;
                    if ($value === '') {
                        continue;
                    }

                    if (!array_key_exists($value, $sharedStringIndex)) {
                        $sharedStringIndex[$value] = count($sharedStrings);
                        $sharedStrings[] = $value;
                    }

                    $cellRef = $this->xlsxColumnLetters($columnIndex) . ($rowIndex + 1);
                    $cellsXml .= '<c r="' . $cellRef . '" t="s"><v>' . $sharedStringIndex[$value] . '</v></c>';
                }

                $sheetRowsXml .= '<row r="' . ($rowIndex + 1) . '">' . $cellsXml . '</row>';
            }

            $sharedStringsXml = '';
            foreach ($sharedStrings as $value) {
                $sharedStringsXml .= '<si><t xml:space="preserve">' . htmlspecialchars($value, ENT_XML1) . '</t></si>';
            }

            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
                . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
                . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
                . '</Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
                . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
                . '</Relationships>');
            $zip->addFromString('docProps/app.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
                . '<Application>BigK Udemy</Application>'
                . '</Properties>');
            $zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                . '<dc:title>Lesson Import Example</dc:title>'
                . '<dc:creator>BigK Udemy</dc:creator>'
                . '<cp:lastModifiedBy>BigK Udemy</cp:lastModifiedBy>'
                . '<dcterms:created xsi:type="dcterms:W3CDTF">' . now()->toAtomString() . '</dcterms:created>'
                . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . now()->toAtomString() . '</dcterms:modified>'
                . '</cp:coreProperties>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="Lessons" sheetId="1" r:id="rId1"/></sheets>'
                . '</workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
                . '</Relationships>');
            $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">'
                . $sharedStringsXml
                . '</sst>');
            $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<sheetData>' . $sheetRowsXml . '</sheetData>'
                . '</worksheet>');

            $zip->close();

            readfile($tempFile);
            @unlink($tempFile);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function xlsxColumnLetters(int $index): string
    {
        $letters = '';
        $index++;

        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letters = chr(65 + $remainder) . $letters;
            $index = (int) floor(($index - 1) / 26);
        }

        return $letters;
    }

    private function lessonImportPreviewSessionKey(int $courseId): string
    {
        return 'teacher_lesson_import_preview_' . $courseId;
    }

    private function storeLessonImportPreview(Courses $course, array $payload): void
    {
        session()->put($this->lessonImportPreviewSessionKey($course->id), $payload);
    }

    private function getLessonImportPreview(Courses $course): ?array
    {
        $preview = session($this->lessonImportPreviewSessionKey($course->id));

        return is_array($preview) ? $preview : null;
    }

    private function validateLessonImportFile(string $path, ?string $extension, Courses $course, bool $canScheduleContent): array
    {
        $expectedHeaders = $this->lessonImportColumns();
        $normalizedExtension = Str::lower((string) $extension);

        $fileData = $normalizedExtension === 'xlsx'
            ? $this->readXlsxRows($path)
            : $this->readCsvRows($path);

        if (!empty($fileData['errors'])) {
            return [
                'rows' => [],
                'errors' => $fileData['errors'],
            ];
        }

        $headers = collect($fileData['headers'] ?? [])
            ->map(fn ($value) => Str::lower(trim((string) $value)))
            ->values()
            ->all();

        if ($headers !== $expectedHeaders) {
            return [
                'rows' => [],
                'errors' => [
                    'Invalid import header. Please use the latest export/template file.',
                ],
            ];
        }

        $rawRows = [];
        foreach ($fileData['rows'] ?? [] as $rowData) {
            $normalized = [];

            foreach ($expectedHeaders as $index => $header) {
                $normalized[$header] = trim((string) ($rowData['values'][$index] ?? ''));
            }

            if (collect($normalized)->every(fn ($value) => $value === '')) {
                continue;
            }

            $normalized['line'] = (int) ($rowData['line'] ?? 0);
            $rawRows[] = $normalized;
        }

        $errors = [];
        if (count($rawRows) === 0) {
            $errors[] = 'The import file does not contain any data rows.';
        }

        $lessonRowCount = collect($rawRows)
            ->filter(fn ($row) => Str::lower((string) ($row['type'] ?? '')) === 'lesson')
            ->count();

        if ($lessonRowCount > 20) {
            $errors[] = 'Each import allows up to 20 lesson rows.';
        }

        $moduleRefs = [];
        $existingModuleIds = Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $validatedRows = [];
        foreach ($rawRows as $row) {
            $lineErrors = [];
            $type = Str::lower($row['type']);
            if (!in_array($type, ['module', 'lesson'], true)) {
                $lineErrors[] = 'type must be either module or lesson.';
            }

            if ($row['name'] === '') {
                $lineErrors[] = 'name is required.';
            }

            $moduleRef = $row['module_ref'];
            if ($type === 'module') {
                if ($moduleRef === '') {
                    $lineErrors[] = 'Module rows must include module_ref.';
                } elseif (isset($moduleRefs[$moduleRef])) {
                    $lineErrors[] = 'module_ref duplicates line ' . $moduleRefs[$moduleRef] . '.';
                } else {
                    $moduleRefs[$moduleRef] = $row['line'];
                }

                if ($row['parent_selector'] !== '') {
                    $lineErrors[] = 'Module rows cannot include parent_selector.';
                }
            }

            if ($type === 'lesson' && $row['parent_selector'] === '') {
                $lineErrors[] = 'Lesson rows must include parent_selector.';
            }

            if ($row['position'] !== '' && (!ctype_digit($row['position']) || (int) $row['position'] < 1)) {
                $lineErrors[] = 'position must be an integer >= 1.';
            }

            if ($row['is_trial'] !== '' && !in_array($row['is_trial'], ['0', '1'], true)) {
                $lineErrors[] = 'is_trial must be 0 or 1.';
            }

            if ($row['status'] !== '' && !in_array($row['status'], ['0', '1'], true)) {
                $lineErrors[] = 'status must be 0 or 1.';
            }

            $releaseMode = $row['release_mode'] !== '' ? $row['release_mode'] : LessonReleaseManager::MODE_IMMEDIATE;
            if (!in_array($releaseMode, [
                LessonReleaseManager::MODE_IMMEDIATE,
                LessonReleaseManager::MODE_DATETIME,
                LessonReleaseManager::MODE_DAYS_AFTER_ENROLLMENT,
                LessonReleaseManager::MODE_AFTER_PREVIOUS_COMPLETED,
            ], true)) {
                $lineErrors[] = 'release_mode is invalid.';
            }

            if (!$canScheduleContent && $releaseMode !== LessonReleaseManager::MODE_IMMEDIATE) {
                $lineErrors[] = 'Your current package only supports release_mode=immediate.';
            }

            if ($releaseMode === LessonReleaseManager::MODE_DATETIME
                && ($row['release_at'] === '' || strtotime($row['release_at']) === false)
            ) {
                $lineErrors[] = 'release_at must be a valid datetime when release_mode=datetime.';
            }

            if ($releaseMode === LessonReleaseManager::MODE_DAYS_AFTER_ENROLLMENT
                && ($row['release_after_days'] === '' || !ctype_digit($row['release_after_days']) || (int) $row['release_after_days'] < 1)
            ) {
                $lineErrors[] = 'release_after_days must be an integer >= 1 when release_mode=days_after_enrollment.';
            }

            if (!empty($lineErrors)) {
                foreach ($lineErrors as $lineError) {
                    $errors[] = 'Line ' . $row['line'] . ': ' . $lineError;
                }
            }

            $validatedRows[] = [
                'line' => $row['line'],
                'type' => $type,
                'module_ref' => $moduleRef,
                'parent_selector' => $row['parent_selector'],
                'name' => $row['name'],
                'name_en' => $row['name_en'] ?: null,
                'name_ko' => $row['name_ko'] ?: null,
                'name_ja' => $row['name_ja'] ?: null,
                'name_zh' => $row['name_zh'] ?: null,
                'description' => $row['description'] ?: null,
                'description_en' => $row['description_en'] ?: null,
                'description_ko' => $row['description_ko'] ?: null,
                'description_ja' => $row['description_ja'] ?: null,
                'description_zh' => $row['description_zh'] ?: null,
                'position' => $row['position'] !== '' ? (int) $row['position'] : null,
                'is_trial' => $row['is_trial'] !== '' ? (int) $row['is_trial'] : 0,
                'status' => $row['status'] !== '' ? (int) $row['status'] : 1,
                'video' => $row['video'] ?: null,
                'document' => $row['document'] ?: null,
                'release_mode' => $releaseMode,
                'release_at' => $row['release_at'] !== '' ? $row['release_at'] : null,
                'release_after_days' => $row['release_after_days'] !== '' ? (int) $row['release_after_days'] : null,
            ];
        }

        foreach ($validatedRows as $row) {
            if ($row['type'] !== 'lesson' || $row['parent_selector'] === '') {
                continue;
            }

            $selector = $row['parent_selector'];
            if (Str::startsWith($selector, 'ref:')) {
                $ref = substr($selector, 4);
                if ($ref === '' || !isset($moduleRefs[$ref])) {
                    $errors[] = 'Line ' . $row['line'] . ': parent_selector references a module_ref that does not exist in this file.';
                }

                continue;
            }

            if (Str::startsWith($selector, 'id:')) {
                $id = (int) substr($selector, 3);
                if ($id <= 0 || !in_array($id, $existingModuleIds, true)) {
                    $errors[] = 'Line ' . $row['line'] . ': parent_selector uses a module id that does not exist in this course.';
                }

                continue;
            }

            $errors[] = 'Line ' . $row['line'] . ': parent_selector must use ref:MODULE_REF or id:MODULE_ID.';
        }

        return [
            'rows' => $validatedRows,
            'errors' => array_values(array_unique($errors)),
            'summary' => [
                'total_rows' => count($validatedRows),
                'imported_modules' => collect($validatedRows)->where('type', 'module')->count(),
                'imported_lessons' => collect($validatedRows)->where('type', 'lesson')->count(),
            ],
        ];
    }

    private function persistImportedLessons(Teacher $teacher, Courses $course, array $rows): array
    {
        $createdRows = DB::transaction(function () use ($teacher, $course, $rows) {
            $createdModules = [];
            $createdRows = [];

            foreach ($rows as $row) {
                $parentId = null;
                if (($row['type'] ?? null) === 'lesson') {
                    $parentId = $this->resolveImportedLessonParentId((string) ($row['parent_selector'] ?? ''), $createdModules, $course);
                }

                $lesson = Lesson::query()->create($this->buildLessonPayload(
                    [
                        'name' => $row['name'] ?? null,
                        'name_en' => $row['name_en'] ?? null,
                        'name_ko' => $row['name_ko'] ?? null,
                        'name_ja' => $row['name_ja'] ?? null,
                        'name_zh' => $row['name_zh'] ?? null,
                        'parent_id' => $parentId,
                        'is_trial' => ($row['type'] ?? null) === 'module' ? 0 : ($row['is_trial'] ?? 0),
                        'position' => $row['position'] ?? null,
                        'video' => $row['video'] ?? null,
                        'document' => $row['document'] ?? null,
                        'description' => $row['description'] ?? null,
                        'description_en' => $row['description_en'] ?? null,
                        'description_ko' => $row['description_ko'] ?? null,
                        'description_ja' => $row['description_ja'] ?? null,
                        'description_zh' => $row['description_zh'] ?? null,
                        'status' => $row['status'] ?? 1,
                        'release_mode' => $row['release_mode'] ?? LessonReleaseManager::MODE_IMMEDIATE,
                        'release_at' => $row['release_at'] ?? null,
                        'release_after_days' => $row['release_after_days'] ?? null,
                    ],
                    $course,
                    null,
                    $teacher->packageHasFeature('can_schedule_content')
                ));

                if (($row['type'] ?? null) === 'module' && !empty($row['module_ref'])) {
                    $createdModules[$row['module_ref']] = (int) $lesson->id;
                }

                $createdRows[] = [
                    'lesson' => $lesson,
                    'row' => $row,
                ];
            }

            return $createdRows;
        });

        foreach ($createdRows as $createdRow) {
            $lesson = $createdRow['lesson'];
            $row = $createdRow['row'];

            $this->logTeacherLessonActivity(
                $teacher,
                $course,
                $lesson,
                'import_lesson',
                'Import lesson from file',
                [
                    'import_type' => $row['type'] ?? null,
                    'import_line' => $row['line'] ?? null,
                    'module_ref' => $row['module_ref'] ?? null,
                    'parent_selector' => $row['parent_selector'] ?? null,
                    'schedule' => $this->summarizeLessonSchedule($lesson),
                ]
            );
        }

        $this->updateCourseDurations($course->id);

        return $createdRows;
    }

    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['Cannot read the import file.'],
            ];
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);

            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['The import file is empty or is not a valid CSV.'],
            ];
        }

        $headers = collect($headerRow)
            ->map(function ($value, $index) {
                $value = trim((string) $value);

                if ($index === 0) {
                    $value = ltrim($value, chr(239) . chr(187) . chr(191));
                }

                return $value;
            })
            ->values()
            ->all();

        $rows = [];
        $line = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            $rows[] = [
                'line' => $line,
                'values' => $row,
            ];
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'rows' => $rows,
            'errors' => [],
        ];
    }

    private function readXlsxRows(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['The server cannot read XLSX files because ZipArchive is unavailable.'],
            ];
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['Cannot open the XLSX file for import.'],
            ];
        }

        $sharedStrings = $this->xlsxSharedStrings($zip);
        $worksheetPath = $this->xlsxFirstWorksheetPath($zip);
        if ($worksheetPath === null) {
            $zip->close();

            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['No worksheet data was found in the XLSX file.'],
            ];
        }

        $worksheetXml = $zip->getFromName($worksheetPath);
        $zip->close();

        if ($worksheetXml === false) {
            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['Cannot read the first worksheet in the XLSX file.'],
            ];
        }

        $worksheet = @simplexml_load_string($worksheetXml);
        if ($worksheet === false || !isset($worksheet->sheetData)) {
            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['The XLSX worksheet content is invalid.'],
            ];
        }

        $sheetRows = [];
        foreach ($worksheet->sheetData->row as $rowNode) {
            $line = (int) ($rowNode['r'] ?? 0);
            $cells = [];

            foreach ($rowNode->c as $cellNode) {
                $reference = (string) ($cellNode['r'] ?? '');
                $columnIndex = $this->xlsxColumnIndexFromReference($reference);
                $cells[$columnIndex] = $this->xlsxCellValue($cellNode, $sharedStrings);
            }

            if (!empty($cells)) {
                ksort($cells);
            }

            $sheetRows[] = [
                'line' => $line,
                'values' => array_values($cells),
            ];
        }

        if (empty($sheetRows)) {
            return [
                'headers' => [],
                'rows' => [],
                'errors' => ['The XLSX file is empty.'],
            ];
        }

        $headerRow = array_shift($sheetRows);

        return [
            'headers' => $headerRow['values'] ?? [],
            'rows' => $sheetRows,
            'errors' => [],
        ];
    }

    private function xlsxSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $sharedStrings = @simplexml_load_string($xml);
        if ($sharedStrings === false) {
            return [];
        }

        $values = [];
        foreach ($sharedStrings->si as $item) {
            if (isset($item->t)) {
                $values[] = (string) $item->t;
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) ($run->t ?? '');
            }
            $values[] = $text;
        }

        return $values;
    }

    private function xlsxFirstWorksheetPath(\ZipArchive $zip): ?string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        if ($workbookXml === false) {
            return null;
        }

        $workbook = @simplexml_load_string($workbookXml);
        if ($workbook === false || !isset($workbook->sheets->sheet[0])) {
            return null;
        }

        $namespaces = $workbook->getNamespaces(true);
        $attributes = $workbook->sheets->sheet[0]->attributes($namespaces['r'] ?? null);
        $relationshipId = (string) ($attributes['id'] ?? '');
        if ($relationshipId === '') {
            return null;
        }

        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($relsXml === false) {
            return null;
        }

        $relationships = @simplexml_load_string($relsXml);
        if ($relationships === false) {
            return null;
        }

        foreach ($relationships->Relationship as $relationship) {
            if ((string) ($relationship['Id'] ?? '') !== $relationshipId) {
                continue;
            }

            $target = (string) ($relationship['Target'] ?? '');
            if ($target === '') {
                return null;
            }

            return Str::startsWith($target, 'xl/') ? $target : 'xl/' . ltrim($target, '/');
        }

        return null;
    }

    private function xlsxCellValue(\SimpleXMLElement $cellNode, array $sharedStrings): string
    {
        $type = (string) ($cellNode['t'] ?? '');
        $value = isset($cellNode->v) ? (string) $cellNode->v : '';

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }

        if ($type === 'inlineStr') {
            return isset($cellNode->is->t) ? (string) $cellNode->is->t : '';
        }

        if ($type === 'b') {
            return $value === '1' ? '1' : '0';
        }

        return trim($value);
    }

    private function xlsxColumnIndexFromReference(string $reference): int
    {
        if (!preg_match('/^[A-Z]+/i', $reference, $matches)) {
            return 0;
        }

        $letters = strtoupper($matches[0]);
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max($index - 1, 0);
    }

    private function resolveImportedLessonParentId(string $parentSelector, array $createdModules, Courses $course): ?int
    {
        if (Str::startsWith($parentSelector, 'ref:')) {
            return $createdModules[substr($parentSelector, 4)] ?? null;
        }

        if (Str::startsWith($parentSelector, 'id:')) {
            return $this->normalizeLessonParentId($course, (int) substr($parentSelector, 3)) ?: null;
        }

        return $createdModules[$parentSelector] ?? null;
    }

    private function generateCourseSlug(string $value, string $column, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        if ($baseSlug === '') {
            $baseSlug = 'course-' . Str::lower(Str::random(6));
        }

        $slug = $baseSlug;
        $counter = 2;
        while (Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where($column, $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateOptionalCourseSlug(?string $value, string $column, ?int $ignoreId = null): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return $this->generateCourseSlug($value, $column, $ignoreId);
    }

    private function generateCourseCode(?string $value, ?int $ignoreId = null): string
    {
        $base = trim((string) $value);
        if (
            $base !== '' &&
            !Courses::query()
                ->withoutGlobalScope(ActiveScope::class)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('code', $base)
                ->exists()
        ) {
            return $base;
        }

        do {
            $candidate = 'KH' . random_int(100000, 999999);
        } while (Courses::query()->withoutGlobalScope(ActiveScope::class)->where('code', $candidate)->exists());

        return $candidate;
    }

    private function generateLessonSlug(string $value, string $column, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        if ($baseSlug === '') {
            $baseSlug = 'lesson-' . Str::lower(Str::random(6));
        }

        $slug = $baseSlug;
        $counter = 2;
        while (Lesson::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where($column, $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateOptionalLessonSlug(?string $value, string $column, ?int $ignoreId = null): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return $this->generateLessonSlug($value, $column, $ignoreId);
    }

    private function buildTeacherStudentDirectory(Teacher $teacher, Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $courseId = (int) $request->query('course_id', 0);
        $tag = trim((string) $request->query('tag', ''));
        $accessType = trim((string) $request->query('access_type', 'all'));
        $sort = trim((string) $request->query('sort', 'recent_purchase'));
        $allowedAccessTypes = ['all', 'paid', 'granted', 'both'];
        $allowedSorts = ['recent_purchase', 'highest_spent', 'recent_learning'];
        if (!in_array($accessType, $allowedAccessTypes, true)) {
            $accessType = 'all';
        }
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'recent_purchase';
        }

        $baseOrderDetails = $this->paidOrderDetailsQuery($teacher)->get();
        $baseGrants = $this->teacherCourseGrantsQuery($teacher)->with(['course', 'student'])->get();
        $courseOptions = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        $validCourseIds = $courseOptions->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selectedCourse = in_array($courseId, $validCourseIds, true) ? $courseId : 0;
        $orderDetails = $selectedCourse > 0
            ? $baseOrderDetails->where('course_id', $selectedCourse)->values()
            : $baseOrderDetails;
        $grants = $selectedCourse > 0
            ? $baseGrants->where('course_id', $selectedCourse)->values()
            : $baseGrants;

        $studentIds = $orderDetails->pluck('order.student_id')
            ->concat($grants->pluck('student_id'))
            ->filter()
            ->unique()
            ->values();
        $notesMap = TeacherStudentNote::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        $students = Student::query()
            ->withTrashed()
            ->whereIn('id', $studentIds)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->get()
            ->values();

        if (in_array($tag, ['potential', 'support_needed', 'vip'], true)) {
            $students = $students
                ->filter(fn (Student $student) => $notesMap->get((int) $student->id)?->tag === $tag)
                ->values();
        }

        $filteredStudentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();
        $detailsByStudent = $orderDetails->groupBy(fn ($detail) => (int) ($detail->order->student_id ?? 0));
        $grantsByStudent = $grants->groupBy(fn ($grant) => (int) $grant->student_id);
        $teacherCourseIds = $orderDetails->pluck('course_id')
            ->concat($grants->pluck('course_id'))
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->all();
        $courseLessonTotals = Lesson::query()
            ->when(!empty($teacherCourseIds), fn ($query) => $query->whereIn('course_id', $teacherCourseIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->whereNotNull('parent_id')
            ->where('status', 1)
            ->selectRaw('course_id, COUNT(*) as total_lessons')
            ->groupBy('course_id')
            ->pluck('total_lessons', 'course_id');
        $recentLearningMap = StudentLessonProgress::query()
            ->when(!empty($filteredStudentIds), fn ($query) => $query->whereIn('student_id', $filteredStudentIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->when(!empty($teacherCourseIds), fn ($query) => $query->whereIn('course_id', $teacherCourseIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->selectRaw('student_id, MAX(completed_at) as last_learning_at')
            ->groupBy('student_id')
            ->pluck('last_learning_at', 'student_id');
        $progressRows = StudentLessonProgress::query()
            ->when(!empty($filteredStudentIds), fn ($query) => $query->whereIn('student_id', $filteredStudentIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->when(!empty($teacherCourseIds), fn ($query) => $query->whereIn('course_id', $teacherCourseIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->selectRaw('student_id, course_id, COUNT(DISTINCT lesson_id) as completed_lessons')
            ->groupBy('student_id', 'course_id')
            ->get()
            ->groupBy('student_id');

        $students = $students->map(function (Student $student) use ($detailsByStudent, $grantsByStudent, $notesMap, $recentLearningMap, $progressRows, $courseLessonTotals) {
            $details = $detailsByStudent->get((int) $student->id, collect());
            $grantItems = $grantsByStudent->get((int) $student->id, collect());
            $courses = $details->pluck('courses')
                ->concat($grantItems->pluck('course'))
                ->filter()
                ->unique('id')
                ->values();
            $lastPurchase = $details->sortByDesc(function ($detail) {
                return optional($detail->order?->payment_complete_date ?: $detail->order?->payment_date ?: $detail->created_at)->timestamp ?? 0;
            })->first();
            $noteItem = $notesMap->get((int) $student->id);
            $lastLearningAt = $recentLearningMap->get((int) $student->id);
            $studentProgressRows = $progressRows->get((int) $student->id, collect());
            $totalLessons = $courses->sum(fn ($course) => (int) ($courseLessonTotals[$course->id] ?? 0));
            $completedLessons = $courses->sum(function ($course) use ($studentProgressRows, $courseLessonTotals) {
                $totalCourseLessons = (int) ($courseLessonTotals[$course->id] ?? 0);
                $completedCourseLessons = (int) ($studentProgressRows->firstWhere('course_id', $course->id)?->completed_lessons ?? 0);

                return min($completedCourseLessons, $totalCourseLessons);
            });
            $progressPercent = $totalLessons > 0
                ? min((int) round(($completedLessons * 100) / $totalLessons), 100)
                : 0;

            $student->teacher_course_count = $courses->count();
            $student->teacher_order_count = $details->pluck('order_id')->filter()->unique()->count();
            $student->teacher_grant_count = $grantItems->count();
            $student->teacher_total_spent = (float) $details->sum(fn ($detail) => (float) ($detail->price ?? 0));
            $student->teacher_last_purchase_at = $lastPurchase?->order?->payment_complete_date
                ?: $lastPurchase?->order?->payment_date
                ?: $lastPurchase?->created_at;
            $student->teacher_courses_all = $courses;
            $student->teacher_courses_preview = $courses->take(4);
            $student->teacher_courses_remaining = max($courses->count() - $student->teacher_courses_preview->count(), 0);
            $student->teacher_note_preview = $noteItem?->note;
            $student->teacher_tag = $noteItem?->tag;
            $student->teacher_last_learning_at = $lastLearningAt ? Carbon::parse($lastLearningAt) : null;
            $student->teacher_progress_total_lessons = $totalLessons;
            $student->teacher_progress_completed_lessons = $completedLessons;
            $student->teacher_progress_percent = $progressPercent;

            return $student;
        });

        $students = $students->filter(function (Student $student) use ($accessType) {
            $hasPaid = (int) $student->teacher_order_count > 0;
            $hasGranted = (int) $student->teacher_grant_count > 0;

            return match ($accessType) {
                'paid' => $hasPaid,
                'granted' => $hasGranted,
                'both' => $hasPaid && $hasGranted,
                default => true,
            };
        })->values();

        $filteredStudentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();

        $students = match ($sort) {
            'highest_spent' => $students->sortByDesc(fn (Student $student) => sprintf('%015.2f-%015d-%010d', $student->teacher_total_spent, optional($student->teacher_last_purchase_at)->timestamp ?? 0, (int) $student->id)),
            'recent_learning' => $students->sortByDesc(fn (Student $student) => sprintf('%015d-%015d-%010d', optional($student->teacher_last_learning_at)->timestamp ?? 0, optional($student->teacher_last_purchase_at)->timestamp ?? 0, (int) $student->id)),
            default => $students->sortByDesc(fn (Student $student) => sprintf('%015d-%015d-%010d', optional($student->teacher_last_purchase_at)->timestamp ?? 0, optional($student->teacher_last_learning_at)->timestamp ?? 0, (int) $student->id)),
        };
        $students = $students->values();

        $filteredDetails = $orderDetails->filter(fn ($detail) => in_array((int) ($detail->order->student_id ?? 0), $filteredStudentIds, true));
        $filteredGrants = $grants->filter(fn ($grant) => in_array((int) $grant->student_id, $filteredStudentIds, true));

        return [
            'students' => $students,
            'search' => $search,
            'courseOptions' => $courseOptions,
            'selectedCourse' => $selectedCourse,
            'tag' => $tag,
            'access_type' => $accessType,
            'sort' => $sort,
            'summary' => [
                'total_students' => $students->count(),
                'total_orders' => $filteredDetails->pluck('order_id')->filter()->unique()->count(),
                'total_courses' => $filteredDetails->pluck('course_id')->concat($filteredGrants->pluck('course_id'))->filter()->unique()->count(),
            ],
        ];
    }

    private function paidOrderDetailsQuery(Teacher $teacher)
    {
        return OrderDetail::query()
            ->with(['courses', 'order.status', 'order.students'])
            ->whereHas('courses', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->whereHas('order', function ($query) {
                $query->where('status_id', 2);
            })
            ->latest('id');
    }

    private function teacherOrderDetailsQuery(Teacher $teacher)
    {
        return OrderDetail::query()
            ->with(['courses', 'order.status', 'order.students'])
            ->whereHas('courses', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->latest('id');
    }

    private function resolvePaidOrderStatusId(): int
    {
        return (int) (OrderStatus::query()->where('is_success', true)->value('id') ?? 2);
    }

    private function resolveTeacherDashboardRange(string $range): array
    {
        $range = Str::lower(trim($range));
        $end = now()->endOfDay();

        return match ($range) {
            'today' => [
                'key' => 'today',
                'start' => now()->startOfDay(),
                'end' => $end,
                'label' => 'Hôm nay',
            ],
            '7d' => [
                'key' => '7d',
                'start' => now()->subDays(6)->startOfDay(),
                'end' => $end,
                'label' => '7 ngày',
            ],
            '14d' => [
                'key' => '14d',
                'start' => now()->subDays(13)->startOfDay(),
                'end' => $end,
                'label' => '14 ngày',
            ],
            '30d', 'month' => [
                'key' => 'month',
                'start' => now()->subDays(29)->startOfDay(),
                'end' => $end,
                'label' => 'Tháng này',
            ],
            '90d', 'year' => [
                'key' => 'year',
                'start' => now()->subDays(364)->startOfDay(),
                'end' => $end,
                'label' => '90 ngày',
            ],
            default => [
                'key' => 'today',
                'start' => now()->startOfDay(),
                'end' => $end,
                'label' => '30 ngày',
            ],
        };
    }

    private function resolveTeacherDashboardRangeOptions(): array
    {
        return array_map(
            fn (string $key) => $this->resolveTeacherDashboardRange($key),
            ['today', '7d', '14d', 'month', 'year']
        );
    }

    private function applyTeacherDashboardRangeToPaidQuery($query, array $range)
    {
        return $query->whereHas('order', function ($orderQuery) use ($range) {
            $orderQuery->where(function ($dateQuery) use ($range) {
                $dateQuery->whereBetween('payment_complete_date', [$range['start'], $range['end']])
                    ->orWhere(function ($fallbackQuery) use ($range) {
                        $fallbackQuery->whereNull('payment_complete_date')
                            ->whereBetween('payment_date', [$range['start'], $range['end']]);
                    })
                    ->orWhere(function ($createdFallbackQuery) use ($range) {
                        $createdFallbackQuery->whereNull('payment_complete_date')
                            ->whereNull('payment_date')
                            ->whereBetween('created_at', [$range['start'], $range['end']]);
                    });
            });
        });
    }

    private function buildTeacherConversionSummary(Teacher $teacher, array $range): array
    {
        $paidStatusId = $this->resolvePaidOrderStatusId();
        $allDetails = $this->teacherOrderDetailsQuery($teacher)->get();

        $allOrders = $allDetails
            ->pluck('order')
            ->filter()
            ->unique('id')
            ->values();

        $paidOrders = $allOrders
            ->where('status_id', $paidStatusId)
            ->values();

        $monthOrders = $allOrders
            ->filter(fn ($order) => optional($order->created_at)?->between($range['start'], $range['end']))
            ->values();
        $monthPaidOrders = $monthOrders
            ->where('status_id', $paidStatusId)
            ->values();

        $paymentStartedOrders = $allOrders
            ->filter(fn ($order) => !empty($order->payment_date) && optional($order->payment_date)?->between($range['start'], $range['end']))
            ->values();
        $paidCompletedOrders = $paidOrders
            ->filter(fn ($order) => optional($order->payment_complete_date ?: $order->payment_date ?: $order->created_at)?->between($range['start'], $range['end']))
            ->values();

        $failedOrders = $allOrders
            ->filter(function ($order) use ($paidStatusId) {
                if ((int) $order->status_id === $paidStatusId) {
                    return false;
                }

                $statusName = Str::lower((string) ($order->status?->name_locale ?: $order->status?->name ?: ''));

                return Str::contains($statusName, ['thất bại', 'that bai', 'failed', 'cancel', 'hủy', 'huy']);
            })
            ->values();

        $monthlyConversionRate = $monthOrders->count() > 0
            ? round(($monthPaidOrders->count() * 100) / $monthOrders->count(), 1)
            : 0.0;
        $paymentConversionRate = $paymentStartedOrders->count() > 0
            ? round(($paidCompletedOrders->count() * 100) / $paymentStartedOrders->count(), 1)
            : 0.0;
        $failedRate = $allOrders->count() > 0
            ? round(($failedOrders->count() * 100) / $allOrders->count(), 1)
            : 0.0;
        $aov = $paidOrders->count() > 0
            ? round((float) $paidOrders->sum('total') / $paidOrders->count())
            : 0.0;

        return [
            'orders_total' => $allOrders->count(),
            'orders_paid' => $paidOrders->count(),
            'orders_this_month' => $monthOrders->count(),
            'orders_paid_this_month' => $monthPaidOrders->count(),
            'payment_started' => $paymentStartedOrders->count(),
            'payment_completed' => $paidCompletedOrders->count(),
            'failed_orders' => $failedOrders->count(),
            'conversion_rate_created' => $monthlyConversionRate,
            'conversion_rate_payment' => $paymentConversionRate,
            'failed_rate' => $failedRate,
            'average_order_value' => $aov,
        ];
    }

    private function buildTeacherRevenueInsights(Teacher $teacher, float $effectiveCommissionRate, array $range): array
    {
        $paidDetails = TeacherFinanceCalculator::decorate(
            $this->applyTeacherDashboardRangeToPaidQuery(
                $this->paidOrderDetailsQuery($teacher),
                $range
            )->get(),
            fn () => $effectiveCommissionRate
        );

        $daily = $paidDetails
            ->groupBy(fn ($detail) => optional($detail->order?->payment_complete_date ?: $detail->order?->payment_date ?: $detail->created_at)?->format('Y-m-d'))
            ->map(function ($rows, $date) {
                return (object) [
                    'date' => $date,
                    'orders' => $rows->pluck('order_id')->filter()->unique()->count(),
                    'courses' => $rows->pluck('course_id')->filter()->unique()->count(),
                    'gross_amount' => (float) $rows->sum(fn ($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
                    'teacher_revenue' => (float) $rows->sum(fn ($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
                ];
            })
            ->sortByDesc('date')
            ->take(14)
            ->values();

        $monthly = $paidDetails
            ->groupBy(fn ($detail) => optional($detail->order?->payment_complete_date ?: $detail->order?->payment_date ?: $detail->created_at)?->format('Y-m'))
            ->map(function ($rows, $month) {
                return (object) [
                    'month' => $month,
                    'orders' => $rows->pluck('order_id')->filter()->unique()->count(),
                    'courses' => $rows->pluck('course_id')->filter()->unique()->count(),
                    'gross_amount' => (float) $rows->sum(fn ($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
                    'teacher_revenue' => (float) $rows->sum(fn ($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
                ];
            })
            ->sortByDesc('month')
            ->take(6)
            ->values();

        $byCourse = $paidDetails
            ->groupBy('course_id')
            ->map(function ($rows) {
                $first = $rows->first();

                return (object) [
                    'course_name' => $first?->courses?->name_locale ?: __('teacher::dashboard.common.unknown_course'),
                    'orders' => $rows->pluck('order_id')->filter()->unique()->count(),
                    'students' => $rows->pluck('order.student_id')->filter()->unique()->count(),
                    'gross_amount' => (float) $rows->sum(fn ($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
                    'teacher_revenue' => (float) $rows->sum(fn ($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
                ];
            })
            ->sortByDesc('teacher_revenue')
            ->take(8)
            ->values();

        return [
            'daily' => $daily,
            'monthly' => $monthly,
            'courses' => $byCourse,
        ];
    }

    private function buildTeacherCoursePerformance(Teacher $teacher, float $effectiveCommissionRate, array $range): Collection
    {
        $courses = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->get(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh', 'view']);

        $viewTrackingMap = CourseViewTracking::query()
            ->selectRaw('course_id, COUNT(*) as tracked_views')
            ->whereIn('course_id', $courses->pluck('id')->all())
            ->whereBetween('view_date', [
                $range['start']->toDateString(),
                $range['end']->toDateString(),
            ])
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $paidDetails = TeacherFinanceCalculator::decorate(
            $this->applyTeacherDashboardRangeToPaidQuery(
                $this->paidOrderDetailsQuery($teacher),
                $range
            )->get(),
            fn () => $effectiveCommissionRate
        );

        $courseRevenueMap = $paidDetails
            ->groupBy('course_id')
            ->map(function ($rows) {
                return [
                    'orders' => $rows->pluck('order_id')->filter()->unique()->count(),
                    'students' => $rows->pluck('order.student_id')->filter()->unique()->count(),
                    'teacher_revenue' => (float) $rows->sum(fn ($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
                ];
            });

        return $courses
            ->map(function ($course) use ($courseRevenueMap, $viewTrackingMap) {
                $courseStats = $courseRevenueMap->get($course->id, [
                    'orders' => 0,
                    'students' => 0,
                    'teacher_revenue' => 0.0,
                ]);
                $views = (int) ($viewTrackingMap->get($course->id)?->tracked_views ?? 0);
                $orders = (int) ($courseStats['orders'] ?? 0);

                return (object) [
                    'course_name' => $course->name_locale ?: __('teacher::dashboard.common.unknown_course'),
                    'views' => $views,
                    'orders' => $orders,
                    'students' => (int) ($courseStats['students'] ?? 0),
                    'teacher_revenue' => (float) ($courseStats['teacher_revenue'] ?? 0),
                    'conversion_rate' => $views > 0 ? round(($orders * 100) / $views, 2) : 0.0,
                ];
            })
            ->sortByDesc(fn ($row) => [$row->teacher_revenue, $row->orders, $row->views])
            ->take(8)
            ->values();
    }

    private function buildTeacherOverviewDashboardPayload(
        Teacher $teacher,
        float $effectiveCommissionRate,
        array $range,
        array $stats,
        array $conversionSummary,
        array $revenueInsights,
        Collection $coursePerformance
    ): array {
        return [
            'range' => [
                'key' => $range['key'],
                'label' => $range['label'],
                'start' => $range['start']->toDateString(),
                'end' => $range['end']->toDateString(),
            ],
            'stats' => [
                'courses' => number_format((int) ($stats['courses'] ?? 0)),
                'active_courses' => number_format((int) ($stats['active_courses'] ?? 0)),
                'students' => number_format((int) ($stats['students'] ?? 0)),
                'available_balance' => money((float) ($stats['available_balance'] ?? 0), 'đ', '0 đ'),
                'gross_revenue' => money((float) ($stats['gross_revenue'] ?? 0), 'đ', '0 đ'),
                'allocated_discount' => money((float) ($stats['allocated_discount'] ?? 0), 'đ', '0 đ'),
                'estimated_revenue' => money((float) ($stats['estimated_revenue'] ?? 0), 'đ', '0 đ'),
                'platform_revenue' => money((float) ($stats['platform_revenue'] ?? 0), 'đ', '0 đ'),
            ],
            'conversion' => [
                'created' => number_format((int) ($conversionSummary['orders_this_month'] ?? 0)),
                'paid' => number_format((int) ($conversionSummary['orders_paid_this_month'] ?? 0)),
                'rate' => number_format((float) ($conversionSummary['conversion_rate_created'] ?? 0), 1) . '%',
                'failed' => number_format((int) ($conversionSummary['failed_orders'] ?? 0)),
            ],
            'revenue_rows' => $this->serializeTeacherRevenueDailyRows($revenueInsights['daily'] ?? collect(), 5),
            'revenue_chart' => $this->serializeTeacherRevenueChart($revenueInsights['daily'] ?? collect(), 7, 'overview'),
            'course_performance' => $this->serializeTeacherCoursePerformanceRows($coursePerformance),
            'course_performance_empty' => __('teacher::dashboard.earnings.empty'),
        ];
    }

    private function buildTeacherEarningsDashboardPayload(
        Teacher $teacher,
        float $effectiveCommissionRate,
        array $range,
        array $summary,
        array $revenueInsights,
        Collection $coursePerformance
    ): array {
        $latestItems = TeacherFinanceCalculator::decorate(
            $this->applyTeacherDashboardRangeToPaidQuery(
                $this->paidOrderDetailsQuery($teacher),
                $range
            )->take(12)->get(),
            fn () => $effectiveCommissionRate
        );

        return [
            'range' => [
                'key' => $range['key'],
                'label' => $range['label'],
                'start' => $range['start']->toDateString(),
                'end' => $range['end']->toDateString(),
            ],
            'summary' => [
                'gross_revenue' => money((float) ($summary['gross_amount'] ?? 0), 'đ', '0 đ'),
                'teacher_revenue' => money((float) ($summary['teacher_revenue'] ?? 0), 'đ', '0 đ'),
            ],
            'daily_rows' => $this->serializeTeacherRevenueDailyRows($revenueInsights['daily'] ?? collect()),
            'monthly_rows' => $this->serializeTeacherRevenueMonthlyRows($revenueInsights['monthly'] ?? collect()),
            'course_rows' => $this->serializeTeacherRevenueCourseRows($revenueInsights['courses'] ?? collect()),
            'revenue_chart' => $this->serializeTeacherRevenueChart($revenueInsights['daily'] ?? collect(), 10, 'earnings'),
            'course_performance' => $this->serializeTeacherCoursePerformanceCards($coursePerformance),
            'items' => $this->serializeTeacherEarningItems($latestItems),
            'empty' => __('teacher::dashboard.earnings.empty'),
        ];
    }

    private function serializeTeacherRevenueDailyRows(Collection $rows, int $limit = 14): array
    {
        return $rows->take($limit)->map(function ($row) {
            return [
                'period' => Carbon::parse($row->date)->format('d/m/Y'),
                'short_period' => Carbon::parse($row->date)->format('d/m'),
                'orders' => number_format((int) $row->orders),
                'gross' => money((float) $row->gross_amount, 'đ', '0 đ'),
                'revenue' => money((float) $row->teacher_revenue, 'đ', '0 đ'),
            ];
        })->values()->all();
    }

    private function serializeTeacherRevenueMonthlyRows(Collection $rows): array
    {
        return $rows->map(function ($row) {
            return [
                'period' => Carbon::createFromFormat('Y-m', $row->month)->format('m/Y'),
                'orders' => number_format((int) $row->orders),
                'revenue' => money((float) $row->teacher_revenue, 'đ', '0 đ'),
            ];
        })->values()->all();
    }

    private function serializeTeacherRevenueCourseRows(Collection $rows): array
    {
        return $rows->map(function ($row) {
            return [
                'course_name' => $row->course_name,
                'orders' => number_format((int) $row->orders),
                'revenue' => money((float) $row->teacher_revenue, 'đ', '0 đ'),
            ];
        })->values()->all();
    }

    private function serializeTeacherRevenueChart(Collection $rows, int $limit, string $palette): array
    {
        $chartRows = $rows->take($limit)->reverse()->values();
        $maxRevenue = max((float) ($chartRows->max('teacher_revenue') ?? 0), 1);
        $palettes = [
            'overview' => [
                ['#60a5fa', '#2563eb'],
                ['#38bdf8', '#0f766e'],
                ['#f59e0b', '#ea580c'],
            ],
            'earnings' => [
                ['#34d399', '#059669'],
                ['#22c55e', '#15803d'],
                ['#f59e0b', '#dc2626'],
            ],
        ];
        $activePalette = $palettes[$palette] ?? $palettes['overview'];

        return $chartRows->map(function ($row, $index) use ($maxRevenue, $activePalette) {
            $colors = $activePalette[$index % count($activePalette)];

            return [
                'label' => Carbon::parse($row->date)->format('d/m'),
                'value' => money((float) $row->teacher_revenue, 'đ', '0 đ'),
                'height' => round(max((((float) $row->teacher_revenue) / $maxRevenue) * 100, 8), 2),
                'start_color' => $colors[0],
                'end_color' => $colors[1],
            ];
        })->values()->all();
    }

    private function serializeTeacherCoursePerformanceRows(Collection $rows): array
    {
        return $rows->map(function ($row) {
            return [
                'course_name' => $row->course_name,
                'views' => number_format((int) $row->views),
                'orders' => number_format((int) $row->orders),
                'conversion_rate' => number_format((float) $row->conversion_rate, 2) . '%',
                'revenue' => money((float) $row->teacher_revenue, 'đ', '0 đ'),
            ];
        })->values()->all();
    }

    private function serializeTeacherCoursePerformanceCards(Collection $rows): array
    {
        $items = $rows->take(6)->values();
        $maxRevenue = max((float) ($items->max('teacher_revenue') ?? 0), 1);

        return $items->map(function ($row) use ($maxRevenue) {
            return [
                'course_name' => $row->course_name,
                'revenue' => money((float) $row->teacher_revenue, 'đ', '0 đ'),
                'width' => round(max((((float) $row->teacher_revenue) / $maxRevenue) * 100, 4), 2),
                'meta' => number_format((int) $row->views) . ' view • ' . number_format((int) $row->orders) . ' đơn • ' . number_format((float) $row->conversion_rate, 2) . '%',
            ];
        })->values()->all();
    }

    private function serializeTeacherEarningItems(Collection $items): array
    {
        return $items->map(function ($item) {
            return [
                'order_code' => '#' . ($item->order?->code ?: '-'),
                'course_name' => $item->courses?->name_locale ?: '-',
                'student_name' => $item->order?->students?->name ?: '-',
                'gross' => money((float) data_get($item, 'finance_breakdown.gross_amount', 0), 'đ', '0 đ'),
                'discount' => '-' . money((float) data_get($item, 'finance_breakdown.allocated_discount', 0), 'đ', '0 đ'),
                'net' => money((float) data_get($item, 'finance_breakdown.net_revenue', 0), 'đ', '0 đ'),
                'revenue' => money((float) data_get($item, 'finance_breakdown.teacher_revenue', 0), 'đ', '0 đ'),
            ];
        })->values()->all();
    }

    private function buildTeacherOrderDirectory(Teacher $teacher, Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $courseId = (int) $request->query('course_id', 0);
        $paymentMethod = trim((string) $request->query('payment_method', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $courseOptions = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('id')
            ->get(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh']);

        $details = $this->paidOrderDetailsQuery($teacher)
            ->when($courseId > 0, fn($query) => $query->where('course_id', $courseId))
            ->when($paymentMethod !== '', fn($query) => $query->whereHas('order', fn($orderQuery) => $orderQuery->where('payment_method', $paymentMethod)))
            ->when($dateFrom !== '', fn($query) => $query->whereHas('order', fn($orderQuery) => $orderQuery->whereDate('payment_complete_date', '>=', $dateFrom)))
            ->when($dateTo !== '', fn($query) => $query->whereHas('order', fn($orderQuery) => $orderQuery->whereDate('payment_complete_date', '<=', $dateTo)))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('order', function ($orderQuery) use ($search) {
                    $orderQuery->where(function ($nestedQuery) use ($search) {
                        $nestedQuery->where('code', 'like', '%' . $search . '%')
                            ->orWhere('customer_name_snapshot', 'like', '%' . $search . '%')
                            ->orWhere('customer_email_snapshot', 'like', '%' . $search . '%')
                            ->orWhere('customer_phone_snapshot', 'like', '%' . $search . '%')
                            ->orWhereHas('students', function ($studentQuery) use ($search) {
                                $studentQuery->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%')
                                    ->orWhere('phone', 'like', '%' . $search . '%');
                            });
                    });
                });
            })
            ->get();

        $decoratedDetails = TeacherFinanceCalculator::decorate(
            $details,
            fn() => $this->resolveEffectiveCommissionRate($teacher)
        );

        $orders = $decoratedDetails
            ->groupBy('order_id')
            ->map(function ($orderDetails) {
                $firstDetail = $orderDetails->first();
                $order = $firstDetail?->order;

                return (object) [
                    'order' => $order,
                    'details' => $orderDetails->values(),
                    'item_count' => $orderDetails->count(),
                    'gross_amount' => (float) $orderDetails->sum(fn($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
                    'allocated_discount' => (float) $orderDetails->sum(fn($item) => data_get($item, 'finance_breakdown.allocated_discount', 0)),
                    'net_revenue' => (float) $orderDetails->sum(fn($item) => data_get($item, 'finance_breakdown.net_revenue', 0)),
                    'teacher_revenue' => (float) $orderDetails->sum(fn($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
                    'payment_at' => $order?->payment_complete_date ?: $order?->payment_date ?: $order?->created_at,
                ];
            })
            ->sortByDesc(fn($item) => optional($item->payment_at)->timestamp ?? 0)
            ->values();

        return [
            'search' => $search,
            'courseId' => $courseId,
            'paymentMethod' => $paymentMethod,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'courseOptions' => $courseOptions,
            'orders' => $orders,
            'summary' => [
                'orders' => $orders->count(),
                'students' => $orders->pluck('order.student_id')->filter()->unique()->count(),
                'courses' => $decoratedDetails->pluck('course_id')->filter()->unique()->count(),
                'gross_amount' => (float) $decoratedDetails->sum(fn($item) => data_get($item, 'finance_breakdown.gross_amount', 0)),
                'allocated_discount' => (float) $decoratedDetails->sum(fn($item) => data_get($item, 'finance_breakdown.allocated_discount', 0)),
                'teacher_revenue' => (float) $decoratedDetails->sum(fn($item) => data_get($item, 'finance_breakdown.teacher_revenue', 0)),
            ],
        ];
    }

    private function teacherCourseGrantsQuery(Teacher $teacher, ?array $statuses = ['accepted'])
    {
        $query = TeacherCourseGrant::query()
            ->where('teacher_id', $teacher->id)
            ->whereNull('revoked_at');

        if ($statuses !== null) {
            $query->whereIn('status', $statuses);
        }

        return $query;
    }

    private function resolveOwnedStudentContext(Teacher $teacher, int $studentId): array
    {
        $student = Student::query()->withTrashed()->findOrFail($studentId);
        $details = $this->paidOrderDetailsQuery($teacher)
            ->whereHas('order', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->get();
        $grants = $this->teacherCourseGrantsQuery($teacher)
            ->with(['course', 'student'])
            ->where('student_id', $studentId)
            ->get();

        if ($details->isEmpty() && $grants->isEmpty()) {
            abort(404);
        }

        return [$student, $details, $grants];
    }

    private function normalizeStudentTagLabel(?string $tag): string
    {
        return match ($tag) {
            'potential' => 'Tiá»m nÄƒng',
            'support_needed' => 'Cáº§n há»— trá»£',
            'vip' => 'VIP',
            default => '',
        };
    }

    private function renderTeacherCommentThread(Request $request, int $courseId, Teacher $teacher)
    {
        $course = Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->findOrFail($courseId);

        $threads = courseCommentThreads($course->id, true);

        $html = view('teacher::clients.dashboard.comments_thread', [
            'course' => $course,
            'threads' => $threads,
            'teacher' => $teacher,
        ])->render();

        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        return redirect()->route('teacher.dashboard.comments', ['course_id' => $course->id]);
    }

    private function commentErrorResponse(Request $request, string $message, int $status = 422)
    {
        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()
            ->withInput()
            ->with('msg_danger', $message);
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function sanitizeCommentContent(string $content): string
    {
        $plainText = strip_tags(str_replace('&nbsp;', ' ', $content));
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $plainText) ?? '');
    }

    private function updateCourseDurations(?int $courseId): void
    {
        if (!$courseId) {
            return;
        }

        $lessons = $this->lessonRepository->getAllLessions($courseId);
        $durations = $lessons->reduce(fn ($prev, $item) => $prev + (float) $item->getRawOriginal('durations'), 0);
        $this->courseRepository->updateCourse($courseId, ['durations' => $durations]);
    }

    private function collectLessonBranchIds(int $lessonId): array
    {
        $ids = [$lessonId];
        $childIds = Lesson::query()
            ->withTrashed()
            ->where('parent_id', $lessonId)
            ->pluck('id');

        foreach ($childIds as $childId) {
            $ids = array_merge($ids, $this->collectLessonBranchIds((int) $childId));
        }

        return array_values(array_unique($ids));
    }

    private function flattenTrashedLessons($lessons, ?int $parentId = null, string $prefix = '', array &$rows = []): array
    {
        $items = $lessons->where('parent_id', $parentId)->sortBy('position');

        foreach ($items as $lesson) {
            $rows[] = [
                'id' => $lesson->id,
                'name' => $prefix . $lesson->name_locale,
                'is_trial' => $lesson->parent_id ? ((int) $lesson->is_trial === 1 ? __('teacher::dashboard.common.yes') : __('teacher::dashboard.common.no')) : '',
                'has_document' => $lesson->document_id ? __('teacher::dashboard.common.yes') : __('teacher::dashboard.common.no'),
                'status' => (int) $lesson->status === 1 ? __('teacher::dashboard.courses.status.published') : __('teacher::dashboard.courses.status.draft'),
                'deleted_at' => optional($lesson->deleted_at)?->format('d/m/Y H:i'),
            ];

            $this->flattenTrashedLessons($lessons, $lesson->id, $prefix . '|-- ', $rows);
        }

        return $rows;
    }

    private function sanitizeTeacherNotificationRedirect(?string $redirect): string
    {
        $fallback = route('teacher.dashboard.notifications');
        $redirect = trim((string) $redirect);

        if ($redirect === '') {
            return $fallback;
        }

        $parts = parse_url($redirect);
        if ($parts === false) {
            return $fallback;
        }

        $host = $parts['host'] ?? null;
        $currentHost = request()->getHost();
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($host && !in_array($host, array_filter([$currentHost, $appHost]), true)) {
            return $fallback;
        }

        if ($host && !empty($parts['scheme'])) {
            return $redirect;
        }

        if (!Str::startsWith($redirect, ['/']) && !Str::startsWith($redirect, url('/'))) {
            return $fallback;
        }

        return $redirect;
    }
}



