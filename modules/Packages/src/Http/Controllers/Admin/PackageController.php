<?php

namespace Modules\Packages\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Packages\src\Http\Requests\PackageRequest;
use Modules\Packages\src\Models\Package;

class PackageController extends Controller
{
    public function index()
    {
        $pageTitle = __('packages::admin.titles.packages');
        $packages = Package::query()->orderBy('sort_order')->get();

        return view('packages::admin.lists', compact('pageTitle', 'packages'));
    }

    public function create()
    {
        $pageTitle = __('packages::admin.titles.create');
        $nextSortOrder = ((int) Package::query()->max('sort_order')) + 1;

        return view('packages::admin.create', compact('pageTitle', 'nextSortOrder'));
    }

    public function store(PackageRequest $request)
    {
        DB::transaction(function () use ($request) {
            $payload = $this->payload($request);
            $sortOrder = $this->resolveRequestedSortOrder($request);

            $this->shiftPackagesForInsert($sortOrder);
            $payload['sort_order'] = $sortOrder;

            Package::query()->create($payload);
        });

        return redirect()->route('teacher-packages.index')->with('msg', __('packages::admin.messages.create_success'));
    }

    public function edit($id)
    {
        $pageTitle = __('packages::admin.titles.edit');
        $package = Package::query()->findOrFail($id);

        return view('packages::admin.edit', compact('pageTitle', 'package'));
    }

    public function update(PackageRequest $request, $id)
    {
        $package = Package::query()->findOrFail($id);
        DB::transaction(function () use ($request, $package) {
            $payload = $this->payload($request);
            $requestedSortOrder = $this->resolveRequestedSortOrder($request);

            $this->reorderPackages($package, $requestedSortOrder);
            $payload['sort_order'] = $requestedSortOrder;

            $package->update($payload);
        });

        return redirect()->route('teacher-packages.edit', $package->id)->with('msg', __('packages::admin.messages.update_success'));
    }

    public function delete($id)
    {
        $package = Package::query()->findOrFail($id);
        DB::transaction(function () use ($package) {
            $deletedOrder = (int) $package->sort_order;
            $package->delete();

            Package::query()
                ->where('sort_order', '>', $deletedOrder)
                ->decrement('sort_order');
        });

        return redirect()->route('teacher-packages.index')->with('msg', __('packages::admin.messages.delete_success'));
    }

    public function reorder(Request $request)
    {
        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        $count = Package::query()->count();
        if ($ids->isEmpty() || $ids->count() !== $count) {
            return response()->json([
                'success' => false,
                'message' => __('packages::admin.messages.invalid_reorder_data'),
            ], 422);
        }

        $existingIds = Package::query()
            ->whereIn('id', $ids->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();

        if ($existingIds->count() !== $count || $existingIds->all() !== $ids->sort()->values()->all()) {
            return response()->json([
                'success' => false,
                'message' => __('packages::admin.messages.incomplete_reorder_list'),
            ], 422);
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                Package::query()
                    ->whereKey($id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => __('packages::admin.messages.reorder_success'),
        ]);
    }

    private function payload(PackageRequest $request): array
    {
        return [
            'code' => $request->string('code')->toString(),
            'name' => $request->string('name')->toString(),
            'name_en' => $request->string('name_en')->toString() ?: null,
            'name_ko' => $request->string('name_ko')->toString() ?: null,
            'name_ja' => $request->string('name_ja')->toString() ?: null,
            'name_zh' => $request->string('name_zh')->toString() ?: null,
            'description' => $request->string('description')->toString() ?: null,
            'description_en' => $request->string('description_en')->toString() ?: null,
            'description_ko' => $request->string('description_ko')->toString() ?: null,
            'description_ja' => $request->string('description_ja')->toString() ?: null,
            'description_zh' => $request->string('description_zh')->toString() ?: null,
            'tagline' => $request->string('tagline')->toString() ?: null,
            'tagline_en' => $request->string('tagline_en')->toString() ?: null,
            'tagline_ko' => $request->string('tagline_ko')->toString() ?: null,
            'tagline_ja' => $request->string('tagline_ja')->toString() ?: null,
            'tagline_zh' => $request->string('tagline_zh')->toString() ?: null,
            'badge_text' => $request->string('badge_text')->toString() ?: null,
            'badge_text_en' => $request->string('badge_text_en')->toString() ?: null,
            'badge_text_ko' => $request->string('badge_text_ko')->toString() ?: null,
            'badge_text_ja' => $request->string('badge_text_ja')->toString() ?: null,
            'badge_text_zh' => $request->string('badge_text_zh')->toString() ?: null,
            'price' => $request->input('price', 0),
            'billing_cycle' => $request->string('billing_cycle')->toString(),
            'course_limit' => $request->filled('course_limit') ? $request->integer('course_limit') : null,
            'payout_account_limit' => $request->filled('payout_account_limit') ? $request->integer('payout_account_limit') : 3,
            'commission_rate' => $request->input('commission_rate', 50),
            'priority_review' => $request->boolean('priority_review'),
            'can_duplicate_courses' => $request->boolean('can_duplicate_courses'),
            'can_manage_comments' => $request->boolean('can_manage_comments'),
            'can_manage_coupons' => $request->boolean('can_manage_coupons'),
            'can_manage_students' => $request->boolean('can_manage_students'),
            'can_view_student_progress' => $request->boolean('can_view_student_progress'),
            'can_view_activity_logs' => $request->boolean('can_view_activity_logs'),
            'can_manage_quizzes' => $request->boolean('can_manage_quizzes'),
            'can_use_ai_quiz' => $request->boolean('can_use_ai_quiz'),
            'can_import_export' => $request->boolean('can_import_export'),
            'ai_quiz_limit' => $request->filled('ai_quiz_limit') ? $request->integer('ai_quiz_limit') : null,
            'coupon_limit' => $request->filled('coupon_limit') ? $request->integer('coupon_limit') : null,
            'can_grant_courses' => $request->boolean('can_grant_courses'),
            'can_export_orders' => $request->boolean('can_import_export'),
            'can_export_students' => $request->boolean('can_import_export'),
            'can_import_export_lessons' => $request->boolean('can_import_export'),
            'can_sell_bundles' => $request->boolean('can_sell_bundles'),
            'can_schedule_content' => $request->boolean('can_schedule_content'),
            'can_send_promotions' => $request->boolean('can_send_promotions'),
            'can_issue_certificates' => $request->boolean('can_issue_certificates'),
            'can_verify_certificates' => $request->boolean('can_verify_certificates'),
            'can_customize_teacher_landing' => $request->boolean('can_customize_teacher_landing'),
            'can_use_affiliate_links' => $request->boolean('can_use_affiliate_links'),
            'support_level' => $request->string('support_level')->toString() ?: null,
            'support_level_en' => $request->string('support_level_en')->toString() ?: null,
            'support_level_ko' => $request->string('support_level_ko')->toString() ?: null,
            'support_level_ja' => $request->string('support_level_ja')->toString() ?: null,
            'support_level_zh' => $request->string('support_level_zh')->toString() ?: null,
            'status' => $request->boolean('status'),
            'hidden_mode' => $request->string('hidden_mode')->toString() ?: 'unavailable',
            'is_featured' => $request->boolean('is_featured'),
            'badge_tone' => $request->string('badge_tone')->toString() ?: null,
        ];
    }

    private function resolveRequestedSortOrder(PackageRequest $request): int
    {
        $maxSortOrder = max((int) Package::query()->max('sort_order'), 0);
        $requestedSortOrder = $request->filled('sort_order')
            ? $request->integer('sort_order')
            : ($maxSortOrder + 1);

        return max(1, min($requestedSortOrder, $maxSortOrder + 1));
    }

    private function shiftPackagesForInsert(int $sortOrder): void
    {
        Package::query()
            ->where('sort_order', '>=', $sortOrder)
            ->increment('sort_order');
    }

    private function reorderPackages(Package $package, int $newSortOrder): void
    {
        $currentSortOrder = (int) $package->sort_order;

        if ($newSortOrder === $currentSortOrder) {
            return;
        }

        if ($newSortOrder < $currentSortOrder) {
            Package::query()
                ->whereKeyNot($package->id)
                ->whereBetween('sort_order', [$newSortOrder, $currentSortOrder - 1])
                ->increment('sort_order');

            return;
        }

        Package::query()
            ->whereKeyNot($package->id)
            ->whereBetween('sort_order', [$currentSortOrder + 1, $newSortOrder])
            ->decrement('sort_order');
    }
}
