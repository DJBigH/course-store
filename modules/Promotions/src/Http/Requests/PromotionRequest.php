<?php

namespace Modules\Promotions\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:160',
            'message_html' => 'required|string',
            'recipient_mode' => 'required|in:all,filtered,manual',
            'course_id' => 'nullable|integer',
            'recent_purchase_days' => 'nullable|integer|min:1|max:3650',
            'inactive_learning_days' => 'nullable|integer|min:1|max:3650',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer',
            'promotion_template' => 'nullable|string',
            'cta_enabled' => 'nullable|boolean',
            'cta_label' => 'nullable|string|max:80',
            'cta_url' => 'nullable|url|max:1000',
            'send_via_web' => 'nullable|boolean',
            'send_via_email' => 'nullable|boolean',
        ];
    }
}
