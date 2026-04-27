<?php

namespace Modules\Teacher\src\Http\Controllers\Clients\Traits;

use App\Models\Scopes\ActiveScope;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Categories\src\Models\Category;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseViewTracking;
use Modules\Coupons\src\Models\Coupons;
use Modules\Lessons\src\Models\Lesson;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Orders\src\Models\OrderStatus;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Models\StudentLessonProgress;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Courses\src\Models\CourseBundle;
use Modules\Students\src\Models\CourseGrant as TeacherCourseGrant;
use Modules\Finances\src\Models\PayoutAccount as TeacherPayoutAccount;
use Modules\Finances\src\Models\PayoutRequest as TeacherPayoutRequest;
use Modules\Packages\src\Models\Package;
use Modules\Teacher\src\Models\TeacherPromotion;
use Modules\Students\src\Models\StudentNote as TeacherStudentNote;
use Modules\Teacher\src\Http\Requests\CourseBundleRequest;
use Modules\Finances\src\Support\FinanceCalculator as TeacherFinanceCalculator;

trait TeacherDashboardHelpers
{
    protected function resolveTeacher(): ?Teacher
    {
        $student = auth('students')->user();
        if (!$student) {
            return null;
        }

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

    protected function resolveEffectiveCommissionRate(Teacher $teacher): float
    {
        $teacher->loadMissing('application.package');

        return (float) ($teacher->application?->package?->commission_rate ?? $teacher->commission_rate ?? 0);
    }

    protected function syncTeacherCommissionRate(Teacher $teacher): Teacher
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

    protected function redirectToStatus()
    {
        return redirect()->route('teacher.account.status', ['locale' => session('locale', app()->getLocale())])
            ->with('msg_danger', __('packages::teacher.flash.inactive_teacher'));
    }

    protected function ensurePackageFeatureAllowed(
        Teacher $teacher,
        string $feature,
        string $fallbackRoute = 'teacher.dashboard.index',
        array $routeParameters = []
    ) {
        if ($teacher->packageHasFeature($feature)) {
            return null;
        }

        return redirect()
            ->route($fallbackRoute, $routeParameters)
            ->with('msg_danger', __('packages::teacher.package_features.feature_locked'));
    }
    protected function resolvePackageSummary(Teacher $teacher): ?array
    {
        $currentPackage = $teacher->application?->package;
        if (!$currentPackage) {
            return null;
        }

        $pendingUpgrade = $this->resolveOpenPackageChangeRequest($teacher);
        $pendingUpgradeStartsAt = $pendingUpgrade?->activates_at;
        $pendingUpgradeIsQueued = $pendingUpgrade?->status === 'approved'
            && $pendingUpgradeStartsAt !== null
            && $pendingUpgrade?->activated_at === null;

        $nextPackage = $this->resolveNextPackage($currentPackage);
        $availablePackageChanges = $this->resolveAvailablePackageChanges($currentPackage);

        return [
            'name' => $currentPackage->name_locale ?: $currentPackage->name,
            'badge' => $currentPackage->badge_text_locale ?: strtoupper((string) $currentPackage->code),
            'price' => (float) $currentPackage->price,
            'billing_cycle' => $currentPackage->billing_cycle,
            'course_limit' => $currentPackage->effective_course_limit,
            'commission_rate' => (float) $currentPackage->commission_rate,
            'support' => $currentPackage->support_level_locale ?: '',
            'started_at' => $teacher->package_started_at,
            'expires_at' => $teacher->package_expires_at,
            'days_left' => property_exists($this, 'packageLifecycleManager') 
                ? $this->packageLifecycleManager->daysLeft($teacher) 
                : 0,
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
        ];
    }

    protected function resolvePromotionRecipientIds(
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

    protected function resolvePromotionStudentOptions(Teacher $teacher): Collection
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
                    'name' => trim((string) ($student->name ?: __('courses::teacher/messages.students.placeholder_name'))),
                    'email' => trim((string) ($student->email ?? '')),
                    'phone' => trim((string) ($student->phone ?? '')),
                    'has_paid_order' => $orderStudentIds->contains($student->id),
                    'has_grant' => $grantStudentIds->contains($student->id),
                ];
            })
            ->values();
    }

    protected function buildPromotionNotificationPayload(Teacher $teacher, TeacherPromotion $promotion, $course = null): array
    {
        $teacherName = $teacher->name_locale ?: $teacher->name ?: __('courses::teacher/messages.common.teacher');
        $courseName = $course ? (localizedModelField($course, 'name', app()->getLocale()) ?: __('courses::teacher/messages.common.unknown_course')) : null;
        $title = $promotion->title;
        $message = $promotion->message;
        $template = trim((string) ($promotion->filters['template'] ?? 'custom'));
        $messageHtml = $promotion->filters['message_html'] ?? null;
        $ctaEnabled = !empty($promotion->filters['cta_enabled']);
        $ctaLabel = $promotion->filters['cta_label'] ?? null;
        $ctaUrl = $promotion->filters['cta_url'] ?? null;
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

    protected function resolvePromotionTemplateDefinitions(Teacher $teacher): array
    {
        $teacherName = trim((string) ($teacher->name_locale ?: $teacher->name ?: __('courses::teacher/messages.common.teacher')));

        return [
            'custom' => [
                'label' => __('courses::teacher/messages.promotions.templates.custom_label'),
                'title' => '',
                'html' => __('courses::teacher/messages.promotions.templates.custom_html'),
                'cta_enabled' => false,
                'cta_label' => '',
            ],
            'flash_sale' => [
                'label' => __('courses::teacher/messages.promotions.templates.flash_sale.label'),
                'title' => __('courses::teacher/messages.promotions.templates.flash_sale.title', ['teacher' => $teacherName]),
                'html' => __('courses::teacher/messages.promotions.templates.flash_sale.html'),
                'cta_enabled' => true,
                'cta_label' => __('courses::teacher/messages.promotions.templates.flash_sale.cta'),
            ],
            'reactivation' => [
                'label' => __('courses::teacher/messages.promotions.templates.reactivation.label'),
                'title' => __('courses::teacher/messages.promotions.templates.reactivation.title', ['teacher' => $teacherName]),
                'html' => __('courses::teacher/messages.promotions.templates.reactivation.html'),
                'cta_enabled' => true,
                'cta_label' => __('courses::teacher/messages.promotions.templates.reactivation.cta'),
            ],
            'new_course' => [
                'label' => __('courses::teacher/messages.promotions.templates.new_course.label'),
                'title' => __('courses::teacher/messages.promotions.templates.new_course.title'),
                'html' => __('courses::teacher/messages.promotions.templates.new_course.html'),
                'cta_enabled' => true,
                'cta_label' => __('courses::teacher/messages.promotions.templates.new_course.cta'),
            ],
        ];
    }

    protected function resolveTeacherPromotionCourseUrl(Teacher $teacher, $course = null, ?string $locale = null): string
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

    protected function resolveTeacherPromotionFallbackUrl(Teacher $teacher, ?string $locale = null): string
    {
        return route('teacher.public.show', [
            'locale' => $locale ?: app()->getLocale(),
            'slug' => $teacher->slug_locale ?: $teacher->slug,
        ]);
    }

    protected function resolvePromotionAudienceType(string $recipientMode, ?int $courseId, ?int $recentPurchaseDays, ?int $inactiveLearningDays): string
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

        return 'custom_audience';
    }

    protected function filterInactivePromotionRecipients(
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

    protected function resolveUniqueBundleSlug(Teacher $teacher, string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $index = 2;

        while (
            CourseBundle::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $index;
            $index++;
        }

        return $slug;
    }

    protected function resolveTopBundles(Teacher $teacher): Collection
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

    protected function resolveCommittedPayoutAmount(Teacher $teacher): float
    {
        return (float) TeacherPayoutRequest::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('status', ['requested', 'processing', 'paid'])
            ->sum('amount');
    }

    protected function sanitizeBankData(array $data): array
    {
        return [
            'bank_name' => trim((string) ($data['bank_name'] ?? '')),
            'bank_account_name' => trim((string) ($data['bank_account_name'] ?? '')),
            'bank_account_number' => preg_replace('/\s+/', '', trim((string) ($data['bank_account_number'] ?? ''))),
        ];
    }

    protected function findMatchingPayoutAccount(Collection $accounts, array $bankData): ?TeacherPayoutAccount
    {
        return $accounts->first(fn (TeacherPayoutAccount $account) => $this->bankPayloadMatches($account, $bankData));
    }

    protected function bankPayloadMatches($accountLike, array $bankData): bool
    {
        return $this->normalizeBankText($accountLike->bank_name) === $this->normalizeBankText($bankData['bank_name'])
            && $this->normalizeBankText($accountLike->bank_account_name) === $this->normalizeBankText($bankData['bank_account_name'])
            && $this->normalizeBankAccountNumber($accountLike->bank_account_number) === $this->normalizeBankAccountNumber($bankData['bank_account_number']);
    }

    protected function normalizeBankText(?string $value): string
    {
        return Str::upper(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    protected function normalizeBankAccountNumber(?string $value): string
    {
        return preg_replace('/\s+/', '', trim((string) $value));
    }

    protected function resolveVietnamBankOptions(): array
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

    protected function resolveCourseLimit(Teacher $teacher): ?int
    {
        return $teacher->application?->package?->effective_course_limit;
    }

    protected function resolvePayoutAccountLimit(Teacher $teacher): int
    {
        return $teacher->application?->package?->effective_payout_account_limit ?? 3;
    }

    protected function resolvePayoutAccountUsage(Teacher $teacher, int $used): array
    {
        $limit = $this->resolvePayoutAccountLimit($teacher);

        return array_merge(
            $this->packageUsageResolver->build($used, $limit),
            [
                'limit_label' => $limit,
            ]
        );
    }

    protected function resolveStudentFeatureState(Teacher $teacher): array
    {
        $features = [
            'manage_students' => $teacher->packageHasFeature('can_manage_students'),
            'grant_courses' => $teacher->packageHasFeature('can_grant_courses'),
            'export_students' => $teacher->packageHasFeature('can_import_export'),
            'view_progress' => $teacher->packageHasFeature('can_view_student_progress'),
        ];

        return [
            'features' => $features,
            'is_feature_locked' => in_array(false, $features, true),
            'can_grant_courses' => $features['grant_courses'],
            'can_view_progress' => $features['view_progress'],
        ];
    }

    protected function resolveNextPackage(?Package $currentPackage): ?Package
    {
        if (!$currentPackage) {
            return null;
        }

        return Package::query()
            ->selectable()
            ->where('sort_order', '>', (int) $currentPackage->sort_order)
            ->orderBy('sort_order')
            ->first();
    }

    protected function resolveAvailablePackageChanges(?Package $currentPackage)
    {
        if (!$currentPackage) {
            return collect();
        }

        $query = Package::query()
            ->selectable()
            ->orderBy('sort_order');

        if (!$this->packageLifecycleManager->isRecurring($currentPackage)) {
            $query->where('id', '!=', (int) $currentPackage->id);
        }

        return $query->get();
    }

    protected function canChangePackage(?Package $currentPackage, Package $targetPackage): bool
    {
        if (!$currentPackage) {
            return false;
        }

        return $this->resolveAvailablePackageChanges($currentPackage)
            ->contains('id', $targetPackage->id);
    }

    protected function resolveOpenPackageChangeRequest(Teacher $teacher): ?TeacherApplication
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

    protected function finalizePackageChange(Teacher $teacher, TeacherApplication $upgradeRequest): string
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

    protected function redirectAfterPackageChange(string $action)
    {
        $flashKey = match ($action) {
            'extended' => 'packages::teacher.flash.auto_extended',
            'queued' => 'packages::teacher.flash.auto_queued',
            default => 'packages::teacher.flash.auto_activated',
        };

        $route = $action === 'queued'
            ? route('teacher.dashboard.package.upgrade.status')
            : route('teacher.dashboard.index');

        return redirect($route)->with('msg_success', __($flashKey));
    }

    protected function resolvePackageOverLimitWarnings(Teacher $teacher, TeacherApplication $upgradeRequest): array
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
            $warnings[] = __('packages::teacher.over_limit.course_limit', [
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
            $warnings[] = __('packages::teacher.over_limit.coupon_limit', [
                'used' => $couponCount,
                'limit' => $targetCouponLimit,
            ]);
        }

        $payoutAccountCount = TeacherPayoutAccount::query()
            ->where('teacher_id', $teacher->id)
            ->count();
        $targetPayoutLimit = $targetPackage->effective_payout_account_limit;
        if ($payoutAccountCount > $targetPayoutLimit) {
            $warnings[] = __('packages::teacher.over_limit.payout_account_limit', [
                'used' => $payoutAccountCount,
                'limit' => $targetPayoutLimit,
            ]);
        }

        return $warnings;
    }

    protected function resolvePackageFeatureLossWarnings(Teacher $teacher, TeacherApplication $upgradeRequest): array
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
            'can_import_export',
        ];

        $warnings = [];
        foreach ($featureKeys as $featureKey) {
            if ($currentPackage->hasFeature($featureKey) && !$targetPackage->hasFeature($featureKey)) {
                $warnings[] = __('packages::teacher.package_features.labels.' . $featureKey);
            }
        }

        return $warnings;
    }

    protected function summarizeStudentCourseProgress(Collection $courses): array
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

    protected function attachCourseHistoryPreview(LengthAwarePaginator $courses, Teacher $teacher): void
    {
        $courseIds = collect($courses->items())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $historyMap = app(\Modules\ActiveLogs\src\Repositories\ActiveLogsRepositoryInterface::class)
            ->getTeacherActivityPreview($teacher, $courseIds);

        foreach ($courses->items() as $course) {
            $course->teacher_activity_preview = ($historyMap->get((int) $course->id, collect()) ?? collect())
                ->take(3)
                ->values();
        }
    }

    protected function logTeacherCourseActivity(
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

    protected function logTeacherLessonActivity(
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

    protected function logTeacherQuizActivity(
        Teacher $teacher,
        Courses $course,
        CourseQuiz $quiz,
        string $action,
        string $description,
        array $properties = []
    ): void {
        activity_log(
            $action,
            $quiz,
            array_merge($properties, [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'course_id' => $course->id,
                'course_name' => $course->name_locale ?: $course->name,
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
            ]),
            'teacher_course_management',
            $description
        );
    }

    protected function summarizeLessonSchedule(?Lesson $lesson): array
    {
        return [
            'mode' => $lesson?->release_mode ?: LessonReleaseManager::MODE_IMMEDIATE,
            'mode_label' => $lesson ? $lesson->releaseSummary() : __('courses::teacher/messages.lessons.scheduling.immediate'),
            'release_at' => $lesson?->release_at?->format('Y-m-d H:i:s'),
            'release_after_days' => $lesson?->release_after_days,
        ];
    }

    protected function resolveStudentManagementHistory(Teacher $teacher, Student $student): Collection
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

    protected function logTeacherStudentActivity(
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

    protected function logTeacherBundleActivity(
        Teacher $teacher,
        $bundle,
        string $action,
        string $description,
        array $properties = []
    ): void {
        activity_log(
            $action,
            $bundle,
            array_merge($properties, [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'bundle_id' => $bundle->id,
                'bundle_name' => $bundle->name,
            ]),
            'teacher_bundle_management',
            $description
        );
    }

    protected function logTeacherSupportActivity(
        Teacher $teacher,
        $contact,
        string $action,
        string $description,
        array $properties = []
    ): void {
        activity_log(
            $action,
            $contact,
            array_merge($properties, [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
                'contact_id' => $contact->id,
                'subject' => $contact->subject,
            ]),
            'teacher_support_management',
            $description
        );
    }

    protected function logTeacherCancellationActivity(
        Teacher $teacher,
        string $action,
        string $description,
        array $properties = []
    ): void {
        activity_log(
            $action,
            $teacher,
            array_merge($properties, [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name_locale ?: $teacher->name,
            ]),
            'teacher_cancellation_management',
            $description
        );
    }

    protected function resolveOwnedCourse(Teacher $teacher, int $courseId, bool $withTrashed = false): Courses
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

    protected function resolveOwnedLesson(Courses $course, int $lessonId, bool $withTrashed = false): Lesson
    {
        $query = Lesson::query()
            ->where('course_id', $course->id)
            ->where('id', $lessonId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->firstOrFail();
    }

    protected function duplicateTitle(?string $value, string $suffix): ?string
    {
        if (!$value) {
            return $value;
        }

        return trim($value . ' (' . $suffix . ')');
    }

    protected function duplicateSlug(?string $value, string $suffix = 'copy'): ?string
    {
        if (!$value) {
            return $value;
        }

        return trim($value . '-' . $suffix . '-' . Str::lower(Str::random(4)), '-');
    }

    protected function duplicateCode(?string $value): string
    {
        $base = $value ?: 'COURSE';

        return $base . '-COPY-' . strtoupper(Str::random(4));
    }

    protected function getCourseCategories()
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

    protected function resolvePublishedCourseUsage(Teacher $teacher): array
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
            'limit_label' => $courseLimit === null ? __('courses::teacher/messages.courses.unlimited') : $courseLimit,
            ]
        );
    }

    protected function normalizeCourseStatusForPackage(Teacher $teacher, int $requestedStatus, ?Courses $course = null): int
    {
        if ($requestedStatus !== 1) {
            return 0;
        }

        return $this->canKeepOrPublishCourse($teacher, $course, $requestedStatus) ? 1 : 0;
    }

    protected function canKeepOrPublishCourse(Teacher $teacher, ?Courses $course, int $requestedStatus): bool
    {
        if ($requestedStatus !== 1) {
            return true;
        }

        if ($course && (int) $course->status === 1 && !$course->trashed() && !$course->package_locked_at) {
            return true;
        }

        return $this->hasAvailablePublishedCourseSlot($teacher, $course?->id);
    }

    protected function hasAvailablePublishedCourseSlot(Teacher $teacher, ?int $ignoreCourseId = null): bool
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

    protected function activateCourseWithinLimit(Teacher $teacher, Courses $course): void
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

    protected function syncCourseLocks(Teacher $teacher): void
    {
        $this->packageLifecycleManager->syncCourseLocks($teacher->fresh(['application.package']));
    }

    protected function ensureCourseManageable(Courses $course)
    {
        if (!$course->package_locked_at) {
            return null;
        }

        return redirect()
            ->route('teacher.dashboard.courses')
            ->with('msg_danger', __('courses::teacher/messages.courses.flash.locked_manage_only'));
    }

    protected function syncCourseCategories(Courses $course, array $categories): void
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

    protected function categoriesPivotPayload(array $categories): array
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

    protected function nextLessonPosition(Courses $course, int $parentId = 0): int
    {
        return (int) Lesson::query()
            ->where('course_id', $course->id)
            ->where('parent_id', $parentId > 0 ? $parentId : null)
            ->max('position') + 1;
    }

    protected function normalizeLessonParentId(Courses $course, int $parentId, ?int $lessonId = null): int
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

    protected function lessonImportColumns(): array
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

    protected function buildExistingLessonModuleSelectors(Courses $course): array
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

    protected function buildLessonExportRows(Courses $course): array
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

    protected function formatLessonExportRow(Lesson $lesson, string $type, string $moduleRef, string $parentSelector): array
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

    protected function lessonImportTemplateRows(): array
    {
        return [
            [
                'type' => 'module',
                'module_ref' => 'MOD-INTRO',
                'parent_selector' => '',
                'name' => __('courses::teacher/messages.lessons.import.template.module_intro'),
                'name_en' => 'Introduction module',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => __('courses::teacher/messages.lessons.import.template.module_intro_desc'),
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
                'name' => __('courses::teacher/messages.lessons.import.template.lesson_1'),
                'name_en' => 'Lesson 1',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => __('courses::teacher/messages.lessons.import.template.lesson_1_desc'),
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
                'name' => __('courses::teacher/messages.lessons.import.template.lesson_attach'),
                'name_en' => 'Attach to an existing module',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => __('courses::teacher/messages.lessons.import.template.lesson_attach_desc'),
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

    protected function lessonImportExampleRows(): array
    {
        return [
            [
                'type' => 'module',
                'module_ref' => 'MOD-FOUNDATION',
                'parent_selector' => '',
                'name' => __('courses::teacher/messages.lessons.import.example.module_foundation'),
                'name_en' => 'Foundation module',
                'name_ko' => 'ê¸°ì´ˆ ëª¨ë“ˆ',
                'name_ja' => 'åŸºç¤Žãƒ¢ã‚¸ãƒ¥ãƒ¼ãƒ«',
                'name_zh' => 'åŸºç¡€æ¨¡å —',
                'description' => __('courses::teacher/messages.lessons.import.example.module_foundation_desc'),
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
                'name' => __('courses::teacher/messages.lessons.import.example.lesson_1'),
                'name_en' => 'Lesson 1 - How to use this course',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => __('courses::teacher/messages.lessons.import.example.lesson_1_desc'),
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
                'name' => __('courses::teacher/messages.lessons.import.example.lesson_2'),
                'name_en' => 'Lesson 2 - Setup workspace',
                'name_ko' => '',
                'name_ja' => '',
                'name_zh' => '',
                'description' => __('courses::teacher/messages.lessons.import.example.lesson_2_desc'),
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
                'description' => 'VÃ­ dá»¥ nÃ y dÃ¹ng parent_selector dáº¡ng id Ä‘á»ƒ gáº¯n vÃ o module Ä‘Ã£ cÃ³ sáºµn trong khÃ³a há» c.',
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

    protected function streamCsvDownload(string $filename, array $headers, array $rows)
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

    protected function streamXlsxDownload(string $filename, array $headers, array $rows)
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

    protected function xlsxColumnLetters(int $index): string
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

    protected function lessonImportPreviewSessionKey(int $courseId): string
    {
        return 'teacher_lesson_import_preview_' . $courseId;
    }

    protected function storeLessonImportPreview(Courses $course, array $payload): void
    {
        session()->put($this->lessonImportPreviewSessionKey($course->id), $payload);
    }

    protected function getLessonImportPreview(Courses $course): ?array
    {
        $preview = session($this->lessonImportPreviewSessionKey($course->id));

        return is_array($preview) ? $preview : null;
    }

    protected function readCsvRows(string $path): array
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

    protected function readXlsxRows(string $path): array
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

    protected function xlsxSharedStrings(\ZipArchive $zip): array
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

    protected function xlsxFirstWorksheetPath(\ZipArchive $zip): ?string
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

    protected function xlsxCellValue(\SimpleXMLElement $cellNode, array $sharedStrings): string
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

    protected function xlsxColumnIndexFromReference(string $reference): int
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

    protected function resolveImportedLessonParentId(string $parentSelector, array $createdModules, Courses $course): ?int
    {
        if (Str::startsWith($parentSelector, 'ref:')) {
            return $createdModules[substr($parentSelector, 4)] ?? null;
        }

        if (Str::startsWith($parentSelector, 'id:')) {
            return $this->normalizeLessonParentId($course, (int) substr($parentSelector, 3)) ?: null;
        }

        return $createdModules[$parentSelector] ?? null;
    }

    protected function generateCourseSlug(string $value, string $column, ?int $ignoreId = null): string
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

    protected function generateOptionalCourseSlug(?string $value, string $column, ?int $ignoreId = null): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return $this->generateCourseSlug($value, $column, $ignoreId);
    }

    protected function generateCourseCode(?string $value, ?int $ignoreId = null): string
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

    protected function generateLessonSlug(string $value, string $column, ?int $ignoreId = null): string
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

    protected function generateOptionalLessonSlug(?string $value, string $column, ?int $ignoreId = null): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return $this->generateLessonSlug($value, $column, $ignoreId);
    }

    protected function paidOrderDetailsQuery(Teacher $teacher)
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

    protected function teacherOrderDetailsQuery(Teacher $teacher)
    {
        return OrderDetail::query()
            ->with(['courses', 'order.status', 'order.students'])
            ->whereHas('courses', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->latest('id');
    }

    protected function resolvePaidOrderStatusId(): int
    {
        return (int) (OrderStatus::query()->where('is_success', true)->value('id') ?? 2);
    }

    protected function resolveTeacherDashboardRange(string $range): array
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

    protected function resolveTeacherDashboardPreviousRange(array $currentRange): array
    {
        $key = $currentRange['key'];

        return match ($key) {
            'today' => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
            '7d' => [
                'start' => now()->subDays(13)->startOfDay(),
                'end' => now()->subDays(7)->endOfDay(),
            ],
            '14d' => [
                'start' => now()->subDays(27)->startOfDay(),
                'end' => now()->subDays(14)->endOfDay(),
            ],
            'month', '30d' => [
                'start' => now()->subDays(59)->startOfDay(),
                'end' => now()->subDays(30)->endOfDay(),
            ],
            'year', '90d' => [
                'start' => now()->subDays(729)->startOfDay(),
                'end' => now()->subDays(365)->endOfDay(),
            ],
            default => [
                'start' => now()->subDays(59)->startOfDay(),
                'end' => now()->subDays(30)->endOfDay(),
            ],
        };
    }

    protected function resolveTeacherDashboardRangeOptions(): array
    {
        return array_map(
            fn (string $key) => $this->resolveTeacherDashboardRange($key),
            ['today', '7d', '14d', 'month', 'year']
        );
    }

    protected function applyTeacherDashboardRangeToPaidQuery($query, array $range)
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

    protected function serializeTeacherRevenueDailyRows(Collection $rows, int $limit = 14): array
    {
        return $rows->take($limit)->map(function ($row) {
            return [
                'period' => Carbon::parse($row->date)->format('d/m/Y'),
                'short_period' => Carbon::parse($row->date)->format('d/m'),
                'orders' => number_format((int) $row->orders),
                'gross' => moneyLocale((float) $row->gross_amount, null, true),
                'revenue' => moneyLocale((float) $row->teacher_revenue, null, true),
            ];
        })->values()->all();
    }

    protected function serializeTeacherRevenueMonthlyRows(Collection $rows): array
    {
        return $rows->map(function ($row) {
            return [
                'period' => Carbon::createFromFormat('Y-m', $row->month)->format('m/Y'),
                'orders' => number_format((int) $row->orders),
                'revenue' => moneyLocale((float) $row->teacher_revenue, null, true),
            ];
        })->values()->all();
    }

    protected function serializeTeacherRevenueCourseRows(Collection $rows): array
    {
        return $rows->map(function ($row) {
            return [
                'course_name' => $row->course_name,
                'orders' => number_format((int) $row->orders),
                'revenue' => moneyLocale((float) $row->teacher_revenue, null, true),
            ];
        })->values()->all();
    }

    protected function serializeTeacherRevenueChart(Collection $rows, int $limit, string $palette): array
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
                'value' => moneyLocale((float) $row->teacher_revenue, null, true),
                'height' => round(max((((float) $row->teacher_revenue) / $maxRevenue) * 100, 8), 2),
                'start_color' => $colors[0],
                'end_color' => $colors[1],
            ];
        })->values()->all();
    }

    protected function serializeTeacherCoursePerformanceRows(Collection $rows): array
    {
        return $rows->map(function ($row) {
            return [
                'course_name' => $row->course_name,
                'views' => number_format((int) $row->views),
                'orders' => number_format((int) $row->orders),
                'conversion_rate' => number_format((float) $row->conversion_rate, 2) . '%',
                'revenue' => moneyLocale((float) $row->teacher_revenue, null, true),
            ];
        })->values()->all();
    }

    protected function serializeTeacherCoursePerformanceCards(Collection $rows): array
    {
        $items = $rows->take(6)->values();
        $maxRevenue = max((float) ($items->max('teacher_revenue') ?? 0), 1);

        return $items->map(function ($row) use ($maxRevenue) {
            return [
                'course_name' => $row->course_name,
                'revenue' => moneyLocale((float) $row->teacher_revenue, null, true),
                'width' => round(max((((float) $row->teacher_revenue) / $maxRevenue) * 100, 4), 2),
                'meta' => number_format((int) $row->views) . ' view • ' . number_format((int) $row->orders) . ' đơn • ' . number_format((float) $row->conversion_rate, 2) . '%',
            ];
        })->values()->all();
    }

    protected function serializeTeacherEarningItems(Collection $items): array
    {
        return $items->map(function ($item) {
            return [
                'order_code' => '#' . ($item->order?->code ?: '-'),
                'course_name' => $item->courses?->name_locale ?: '-',
                'student_name' => $item->order?->students?->name ?: '-',
                'gross' => moneyLocale((float) data_get($item, 'finance_breakdown.gross_amount', 0), null, true),
                'discount' => '-' . moneyLocale((float) data_get($item, 'finance_breakdown.allocated_discount', 0), null, true),
                'net' => moneyLocale((float) data_get($item, 'finance_breakdown.net_revenue', 0), null, true),
                'split' => number_format((float) data_get($item, 'finance_breakdown.commission_rate', 0), 0) . '% / ' . (100 - (float) data_get($item, 'finance_breakdown.commission_rate', 0)) . '%',
                'revenue' => moneyLocale((float) data_get($item, 'finance_breakdown.teacher_revenue', 0), null, true),
            ];
        })->values()->all();
    }

    protected function teacherCourseGrantsQuery(Teacher $teacher, ?array $statuses = ['accepted'])
    {
        $query = TeacherCourseGrant::query()
            ->where('teacher_id', $teacher->id)
            ->whereNull('revoked_at');

        if ($statuses !== null) {
            $query->whereIn('status', $statuses);
        }

        return $query;
    }

    protected function resolveOwnedStudentContext(Teacher $teacher, int $studentId): array
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

        return [
            'student' => $student,
            'details' => TeacherFinanceCalculator::decorate(
                $details,
                fn() => $this->resolveEffectiveCommissionRate($teacher)
            ),
            'grants' => $grants,
        ];
    }

    protected function normalizeStudentTagLabel(?string $tag): string
    {
        return match ($tag) {
            'potential' => 'Tiềm năng',
            'support_needed' => 'Cần hỗ trợ',
            'vip' => 'VIP',
            default => '',
        };
    }

    protected function commentErrorResponse(Request $request, string $message, int $status = 422)
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

    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    protected function sanitizeCommentContent(string $content): string
    {
        $plainText = strip_tags(str_replace('&nbsp;', ' ', $content));
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $plainText) ?? '');
    }

    protected function updateCourseDurations(?int $courseId): void
    {
        if (!$courseId) {
            return;
        }

        $lessons = $this->lessonRepository->getAllLessions($courseId);
        $durations = $lessons->reduce(fn ($prev, $item) => $prev + (float) $item->getRawOriginal('durations'), 0);
        $this->courseRepository->updateCourse($courseId, ['durations' => $durations]);
    }

    protected function collectLessonBranchIds(int $lessonId): array
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

    protected function flattenTrashedLessons($lessons, ?int $parentId = null, string $prefix = '', array &$rows = []): array
    {
        $items = $lessons->where('parent_id', $parentId)->sortBy('position');

        foreach ($items as $lesson) {
            $rows[] = [
                'id' => $lesson->id,
                'name' => $prefix . $lesson->name_locale,
                'is_trial' => $lesson->parent_id ? ((int) $lesson->is_trial === 1 ? __('courses::teacher/messages.common.yes') : __('courses::teacher/messages.common.no')) : '',
                'has_document' => $lesson->document_id ? __('courses::teacher/messages.common.yes') : __('courses::teacher/messages.common.no'),
                'status' => (int) $lesson->status === 1 ? __('courses::teacher/messages.courses.status.published') : __('courses::teacher/messages.courses.status.draft'),
                'deleted_at' => optional($lesson->deleted_at)?->format('d/m/Y H:i'),
            ];

            $this->flattenTrashedLessons($lessons, $lesson->id, $prefix . '|-- ', $rows);
        }

        return $rows;
    }

    protected function sanitizeTeacherNotificationRedirect(?string $redirect): string
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

    protected function buildCoursePayload(array $data, Teacher $teacher, ?Courses $course = null): array
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
            'price_en' => (float) ($data['price_en'] ?? 0),
            'sale_price_en' => (float) ($data['sale_price_en'] ?? 0),
            'price_ko' => (float) ($data['price_ko'] ?? 0),
            'sale_price_ko' => (float) ($data['sale_price_ko'] ?? 0),
            'price_ja' => (float) ($data['price_ja'] ?? 0),
            'sale_price_ja' => (float) ($data['sale_price_ja'] ?? 0),
            'price_zh' => (float) ($data['price_zh'] ?? 0),
            'sale_price_zh' => (float) ($data['sale_price_zh'] ?? 0),
            'code' => $this->generateCourseCode($data['code'] ?? null, $course?->id),
            'is_document' => (int) $data['is_document'],
            'status' => $normalizedStatus,
            'is_learning_locked' => (int) $data['is_learning_locked'],
            'is_coming_soon' => (bool) ($data['is_coming_soon'] ?? false),
            'coming_soon_start_at' => $data['coming_soon_start_at'] ?? null,
        ];
    }

    protected function performCourseDuplicate(Courses $course): Courses
    {
        return DB::transaction(function () use ($course) {
            $courseData = $course->getAttributes();

            unset($courseData['id'], $courseData['created_at'], $courseData['updated_at'], $courseData['deleted_at']);
            $courseData['name'] = $this->duplicateTitle($course->name, 'Bản sao');
            $courseData['name_ko'] = $this->duplicateTitle($course->name_ko, '복제본');
            $courseData['name_ja'] = $this->duplicateTitle($course->name_ja, '複製版');
            $courseData['name_zh'] = $this->duplicateTitle($course->name_zh, '复制版');
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
                $lessonData['name'] = $this->duplicateTitle($lesson->name, 'Bản sao');
                $lessonData['name_ko'] = $this->duplicateTitle($lesson->name_ko, '복제본');
                $lessonData['name_ja'] = $this->duplicateTitle($lesson->name_ja, '複製版');
                $lessonData['name_zh'] = $this->duplicateTitle($lesson->name_zh, '复制版');
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

    protected function buildLessonPayload(array $data, Courses $course, ?Lesson $lesson = null, bool $canScheduleContent = false): array
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

    protected function validateLessonImportFile(string $path, ?string $extension, Courses $course, bool $canScheduleContent): array
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
            'rows' => empty($errors) ? $validatedRows : [],
            'errors' => $errors,
            'summary' => [
                'total' => count($validatedRows),
                'modules' => collect($validatedRows)->where('type', 'module')->count(),
                'lessons' => collect($validatedRows)->where('type', 'lesson')->count(),
            ],
        ];
    }

    protected function buildTeacherRevenueInsights(Teacher $teacher, float $effectiveCommissionRate, array $range): array
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
                    'course_name' => $first?->courses?->name_locale ?: __('teacher::teacher/dashboard.common.unknown_course'),
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

    protected function buildTeacherCoursePerformance(Teacher $teacher, float $effectiveCommissionRate, array $range): Collection
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
                    'course_name' => $course->name_locale ?: __('teacher::teacher/dashboard.common.unknown_course'),
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

    protected function buildTeacherOverviewDashboardPayload(
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
                'available_balance' => moneyLocale((float) ($stats['available_balance'] ?? 0), null, true),
                'gross_revenue' => moneyLocale((float) ($stats['gross_revenue'] ?? 0), null, true),
                'allocated_discount' => moneyLocale((float) ($stats['allocated_discount'] ?? 0), null, true),
                'estimated_revenue' => moneyLocale((float) ($stats['estimated_revenue'] ?? 0), null, true),
                'platform_revenue' => moneyLocale((float) ($stats['platform_revenue'] ?? 0), null, true),
                'trends' => $stats['trends'] ?? [],
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
            'course_performance_empty' => __('teacher::teacher/dashboard.common.empty'),
        ];
    }

    protected function buildTeacherEarningsDashboardPayload(
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
                'gross_revenue' => moneyLocale((float) ($summary['gross_amount'] ?? 0), null, true),
                'teacher_revenue' => moneyLocale((float) ($summary['teacher_revenue'] ?? 0), null, true),
            ],
            'daily_rows' => $this->serializeTeacherRevenueDailyRows($revenueInsights['daily'] ?? collect()),
            'monthly_rows' => $this->serializeTeacherRevenueMonthlyRows($revenueInsights['monthly'] ?? collect()),
            'course_rows' => $this->serializeTeacherRevenueCourseRows($revenueInsights['courses'] ?? collect()),
            'revenue_chart' => $this->serializeTeacherRevenueChart($revenueInsights['daily'] ?? collect(), 10, 'earnings'),
            'course_performance' => $this->serializeTeacherCoursePerformanceCards($coursePerformance),
            'items' => $this->serializeTeacherEarningItems($latestItems),
            'empty' => __('teacher::teacher/dashboard.common.empty'),
        ];
    }

    protected function buildTeacherOrderDirectory(Teacher $teacher, Request $request): array
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

    protected function resolvePayoutBankData(Request $request, Teacher $teacher, array $data, Collection $payoutAccounts)
    {
        if (($data['account_mode'] ?? null) === 'saved') {
            $selectedAccount = $payoutAccounts->firstWhere('id', (int) ($data['payout_account_id'] ?? 0));
            if (!$selectedAccount) {
                return back()
                    ->withInput()
                    ->with('msg_danger', __('courses::teacher/messages.payouts.flash.invalid_saved_account'));
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
                ->with('msg_danger', __('courses::teacher/messages.payouts.flash.limit_reached_use_change_request', [
                    'limit' => $accountLimit,
                ]));
        }

        TeacherPayoutAccount::query()->create(array_merge($bankData, [
            'teacher_id' => $teacher->id,
        ]));

        return $bankData;
    }

    protected function buildTeacherStudentDirectory(Teacher $teacher, Request $request): array
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

    protected function buildTeacherConversionSummary(Teacher $teacher, array $range): array
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

    protected function renderTeacherCommentThread(Request $request, int $courseId, Teacher $teacher)
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

    protected function bundleFormResponse(?int $bundleId = null)
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
            $bundle = CourseBundle::query()
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
            ? __('teacher::teacher/bundle/edit.edit_title')
            : __('teacher::teacher/bundle/add.create_title');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.bundle_form', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'bundle',
            'courseOptions'
        ));
    }

    protected function persistBundle(CourseBundleRequest $request, ?int $bundleId = null)
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
                ->withErrors(['course_ids' => __('teacher::teacher/bundle/common.flash.invalid_courses')]);
        }

        $existingBundle = $bundleId
            ? CourseBundle::query()->where('teacher_id', $teacher->id)->findOrFail($bundleId)
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
                'sale_price' => isset($data['sale_price']) && $data['sale_price'] !== '' ? (float) $data['sale_price'] : null,
                'status' => (bool) ($data['status'] ?? false),
                'is_coming_soon' => (bool) ($data['is_coming_soon'] ?? false),
                'coming_soon_start_at' => $data['coming_soon_start_at'] ?? null,
                'quantity' => isset($data['quantity']) && $data['quantity'] !== '' ? (int) $data['quantity'] : null,
                'end_at' => $data['end_at'] ?? null,
            ];

            $bundle = $existingBundle;
            if ($bundle) {
                $bundle->update($payload);
            } else {
                $payload['position'] = ((int) CourseBundle::query()->where('teacher_id', $teacher->id)->max('position')) + 1;
                $bundle = CourseBundle::query()->create($payload);
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

        $this->logTeacherBundleActivity(
            $teacher,
            $bundle,
            $bundleId ? 'bundle_updated' : 'bundle_created',
            $bundleId
                ? __('teacher::teacher/bundle/common.history.bundle_updated', ['name' => $bundle->name])
                : __('teacher::teacher/bundle/common.history.bundle_created', ['name' => $bundle->name]),
            ['course_count' => $courseIds->count()]
        );

        return redirect()
            ->route('teacher.dashboard.bundles.edit', ['bundle' => $bundle->id])
            ->with('msg_success', $bundleId
                ? __('teacher::teacher/bundle/common.flash.updated')
                : __('teacher::teacher/bundle/common.flash.created'));
    }

}
