<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $packageId = $this->route('id') ?? $this->route('package');
        $packageId = is_object($packageId) ? $packageId->id : $packageId;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('teacher_packages', 'code')->ignore($packageId)],
            'name' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:100'],
            'name_ko' => ['nullable', 'string', 'max:100'],
            'name_ja' => ['nullable', 'string', 'max:100'],
            'name_zh' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'description_ko' => ['nullable', 'string'],
            'description_ja' => ['nullable', 'string'],
            'description_zh' => ['nullable', 'string'],
            'tagline' => ['nullable', 'string', 'max:190'],
            'tagline_en' => ['nullable', 'string', 'max:190'],
            'tagline_ko' => ['nullable', 'string', 'max:190'],
            'tagline_ja' => ['nullable', 'string', 'max:190'],
            'tagline_zh' => ['nullable', 'string', 'max:190'],
            'badge_text' => ['nullable', 'string', 'max:100'],
            'badge_text_en' => ['nullable', 'string', 'max:100'],
            'badge_text_ko' => ['nullable', 'string', 'max:100'],
            'badge_text_ja' => ['nullable', 'string', 'max:100'],
            'badge_text_zh' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'string', 'max:20'],
            'course_limit' => ['nullable', 'integer', 'min:1'],
            'payout_account_limit' => ['nullable', 'integer', 'min:1', 'max:3'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'priority_review' => ['nullable', 'boolean'],
            'can_duplicate_courses' => ['nullable', 'boolean'],
            'can_manage_comments' => ['nullable', 'boolean'],
            'can_manage_coupons' => ['nullable', 'boolean'],
            'can_manage_students' => ['nullable', 'boolean'],
            'can_view_student_progress' => ['nullable', 'boolean'],
            'can_view_activity_logs' => ['nullable', 'boolean'],
            'coupon_limit' => ['nullable', 'integer', 'min:1'],
            'can_grant_courses' => ['nullable', 'boolean'],
            'can_export_orders' => ['nullable', 'boolean'],
            'can_export_students' => ['nullable', 'boolean'],
            'can_sell_bundles' => ['nullable', 'boolean'],
            'can_schedule_content' => ['nullable', 'boolean'],
            'can_send_promotions' => ['nullable', 'boolean'],
            'can_issue_certificates' => ['nullable', 'boolean'],
            'can_customize_teacher_landing' => ['nullable', 'boolean'],
            'can_use_affiliate_links' => ['nullable', 'boolean'],
            'support_level' => ['nullable', 'string', 'max:50'],
            'support_level_en' => ['nullable', 'string', 'max:50'],
            'support_level_ko' => ['nullable', 'string', 'max:50'],
            'support_level_ja' => ['nullable', 'string', 'max:50'],
            'support_level_zh' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'boolean'],
            'hidden_mode' => ['nullable', 'in:available,unavailable'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
