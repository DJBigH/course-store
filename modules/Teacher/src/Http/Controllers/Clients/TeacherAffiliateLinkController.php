<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\Order;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherAffiliateLink;
use Modules\Teacher\src\Models\TeacherCourseBundle;
use Modules\Teacher\src\Support\TeacherAffiliateLinkManager;
use Modules\Teacher\src\Support\TeacherPackageLifecycleManager;

class TeacherAffiliateLinkController extends Controller
{
    public function __construct(
        protected TeacherAffiliateLinkManager $affiliateLinkManager,
        protected TeacherPackageLifecycleManager $packageLifecycleManager,
    ) {}

    public function index()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $allLinks = TeacherAffiliateLink::query()
            ->where('teacher_id', $teacher->id)
            ->get(['id', 'target_type', 'clicks_count']);

        $allLinkIds = $allLinks->pluck('id')->all();
        $paidStatsByLink = collect();

        if (!empty($allLinkIds)) {
            $paidStatsByLink = Order::query()
                ->selectRaw('affiliate_link_id, COUNT(*) as paid_orders_count, SUM(GREATEST(total - discount, 0)) as paid_revenue')
                ->whereIn('affiliate_link_id', $allLinkIds)
                ->whereHas('status', fn($query) => $query->where('is_success', true))
                ->groupBy('affiliate_link_id')
                ->get()
                ->keyBy('affiliate_link_id');
        }

        $links = TeacherAffiliateLink::query()
            ->with(['course', 'bundle', 'teacher'])
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(12)
            ->through(function (TeacherAffiliateLink $link) use ($paidStatsByLink) {
                try {
                    $link->public_url = $this->affiliateLinkManager->buildPublicUrl($link);
                } catch (\Throwable $throwable) {
                    $link->public_url = null;
                }

                $paidStat = $paidStatsByLink->get($link->id);
                $link->paid_orders_count = (int) ($paidStat->paid_orders_count ?? 0);
                $link->paid_revenue = (float) ($paidStat->paid_revenue ?? 0);
                $link->conversion_rate = $this->calculateConversionRate((int) $link->clicks_count, (int) $link->paid_orders_count);

                return $link;
            });

        $allLinksWithStats = $allLinks->map(function (TeacherAffiliateLink $link) use ($paidStatsByLink) {
            $paidStat = $paidStatsByLink->get($link->id);
            $link->paid_orders_count = (int) ($paidStat->paid_orders_count ?? 0);
            $link->paid_revenue = (float) ($paidStat->paid_revenue ?? 0);
            $link->conversion_rate = $this->calculateConversionRate((int) $link->clicks_count, (int) $link->paid_orders_count);

            return $link;
        });

        $totalClicks = (int) $allLinksWithStats->sum('clicks_count');
        $paidOrders = (int) $allLinksWithStats->sum('paid_orders_count');
        $paidRevenue = (float) $allLinksWithStats->sum('paid_revenue');

        $stats = [
            'total_links' => (int) $allLinksWithStats->count(),
            'total_clicks' => $totalClicks,
            'landing_links' => (int) $allLinksWithStats->where('target_type', 'landing')->count(),
            'paid_orders' => $paidOrders,
            'paid_revenue' => $paidRevenue,
            'conversion_rate' => $this->calculateConversionRate($totalClicks, $paidOrders),
        ];

        $topLinks = TeacherAffiliateLink::query()
            ->with(['course', 'bundle', 'teacher'])
            ->where('teacher_id', $teacher->id)
            ->get()
            ->map(function (TeacherAffiliateLink $link) use ($paidStatsByLink) {
                $paidStat = $paidStatsByLink->get($link->id);
                $link->paid_orders_count = (int) ($paidStat->paid_orders_count ?? 0);
                $link->paid_revenue = (float) ($paidStat->paid_revenue ?? 0);
                $link->conversion_rate = $this->calculateConversionRate((int) $link->clicks_count, (int) $link->paid_orders_count);

                try {
                    $link->public_url = $this->affiliateLinkManager->buildPublicUrl($link);
                } catch (\Throwable $throwable) {
                    $link->public_url = null;
                }

                return $link;
            })
            ->sort(function ($left, $right) {
                return [$right->paid_revenue, $right->paid_orders_count, $right->clicks_count, $right->id]
                    <=> [$left->paid_revenue, $left->paid_orders_count, $left->clicks_count, $left->id];
            })
            ->take(3)
            ->values();

        $breakdown = collect([
            'course' => $this->buildBreakdownRow($allLinksWithStats, 'course'),
            'bundle' => $this->buildBreakdownRow($allLinksWithStats, 'bundle'),
            'landing' => $this->buildBreakdownRow($allLinksWithStats, 'landing'),
        ]);

        $pageTitle = __('courses::teacher/messages.pages.affiliate_links');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.affiliate_links.index', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'links',
            'stats',
            'topLinks',
            'breakdown'
        ));
    }

    private function buildBreakdownRow($links, string $targetType): array
    {
        $targetLinks = $links->where('target_type', $targetType);
        $clicks = (int) $targetLinks->sum('clicks_count');
        $paidOrders = (int) $targetLinks->sum('paid_orders_count');

        return [
            'target_type' => $targetType,
            'links_count' => (int) $targetLinks->count(),
            'clicks' => $clicks,
            'paid_orders' => $paidOrders,
            'paid_revenue' => (float) $targetLinks->sum('paid_revenue'),
            'conversion_rate' => $this->calculateConversionRate($clicks, $paidOrders),
        ];
    }

    private function calculateConversionRate(int $clicks, int $paidOrders): float
    {
        if ($clicks <= 0 || $paidOrders <= 0) {
            return 0.0;
        }

        return round(($paidOrders * 100) / $clicks, 2);
    }

    public function create()
    {
        return $this->formResponse();
    }

    public function store(Request $request)
    {
        return $this->persist($request);
    }

    public function edit(int $id)
    {
        return $this->formResponse($id);
    }

    public function update(Request $request, int $id)
    {
        return $this->persist($request, $id);
    }

    public function delete(int $id)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $link = TeacherAffiliateLink::query()
            ->where('teacher_id', $teacher->id)
            ->findOrFail($id);

        $link->delete();

        return redirect()
            ->route('teacher.dashboard.affiliate-links.index')
            ->with('msg_success', __('courses::teacher/messages.affiliate_links.flash.deleted'));
    }

    private function formResponse(?int $id = null)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $link = $id
            ? TeacherAffiliateLink::query()->where('teacher_id', $teacher->id)->findOrFail($id)
            : null;

        if ($link) {
            try {
                $link->public_url = $this->affiliateLinkManager->buildPublicUrl($link);
            } catch (\Throwable $throwable) {
                $link->public_url = null;
            }
        }

        $courseOptions = Courses::query()
            ->withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->where('status', 1)
            ->orderBy('name')
            ->get();
        $bundleOptions = TeacherCourseBundle::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $pageTitle = $link
            ? __('courses::teacher/messages.affiliate_links.edit')
            : __('courses::teacher/messages.affiliate_links.create');
        $pageName = $pageTitle;

        return view('teacher::clients.dashboard.affiliate_links.form', compact(
            'pageTitle',
            'pageName',
            'teacher',
            'link',
            'courseOptions',
            'bundleOptions'
        ));
    }

    private function persist(Request $request, ?int $id = null)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        if ($featureRedirect = $this->ensureFeatureAllowed($teacher)) {
            return $featureRedirect;
        }

        $link = $id
            ? TeacherAffiliateLink::query()->where('teacher_id', $teacher->id)->findOrFail($id)
            : null;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'target_type' => ['required', 'in:course,bundle,landing'],
            'target_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ]);

        $targetId = $this->resolveValidatedTargetId($teacher, $data['target_type'], $data['target_id'] ?? null);

        $payload = [
            'teacher_id' => $teacher->id,
            'name' => trim((string) $data['name']),
            'target_type' => $data['target_type'],
            'target_id' => $targetId,
            'status' => $request->boolean('status', true),
        ];

        if ($link) {
            $link->update($payload);
        } else {
            $payload['code'] = $this->affiliateLinkManager->generateCode();
            $link = TeacherAffiliateLink::query()->create($payload);
        }

        return redirect()
            ->route('teacher.dashboard.affiliate-links.index')
            ->with('msg_success', __($id
                ? 'courses::teacher/messages.affiliate_links.flash.updated'
                : 'courses::teacher/messages.affiliate_links.flash.created'));
    }

    private function resolveValidatedTargetId(Teacher $teacher, string $targetType, mixed $targetId): ?int
    {
        if ($targetType === 'landing') {
            return null;
        }

        $targetId = (int) $targetId;
        abort_if($targetId <= 0, 422);

        if ($targetType === 'course') {
            $exists = Courses::query()
                ->withoutGlobalScopes()
                ->where('teacher_id', $teacher->id)
                ->where('status', 1)
                ->where('id', $targetId)
                ->exists();

            abort_unless($exists, 422);

            return $targetId;
        }

        $exists = TeacherCourseBundle::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', true)
            ->where('id', $targetId)
            ->exists();

        abort_unless($exists, 422);

        return $targetId;
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

        return $this->packageLifecycleManager->sync($teacher);
    }

    private function redirectToStatus()
    {
        return redirect()->route('teacher.account.status', ['locale' => session('locale', app()->getLocale())]);
    }

    private function ensureFeatureAllowed(Teacher $teacher)
    {
        if ($teacher->packageHasFeature('can_use_affiliate_links')) {
            return null;
        }

        return redirect()
            ->route('teacher.dashboard.index')
            ->with('msg_danger', __('courses::teacher/messages.package_features.feature_locked'));
    }
}
