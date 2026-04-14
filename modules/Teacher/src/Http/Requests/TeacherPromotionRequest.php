<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherPromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('students')->check();
    }

    public function rules(): array
    {
        return [
            'recipient_mode' => ['nullable', 'string', 'in:all,filtered,manual'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'min:1'],
            'course_id' => ['nullable', 'integer', 'min:1'],
            'recent_purchase_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'inactive_learning_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'promotion_template' => ['nullable', 'string', 'in:custom,flash_sale,reactivation,new_course'],
            'title' => ['required', 'string', 'max:160'],
            'message_html' => ['required', 'string', 'max:20000'],
            'cta_enabled' => ['nullable', 'boolean'],
            'cta_label' => ['nullable', 'required_if:cta_enabled,1', 'string', 'max:80'],
            'cta_url' => ['nullable', 'required_if:cta_enabled,1', 'url', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('recipient_mode', 'all') === 'manual' && empty($this->input('student_ids', []))) {
                $validator->errors()->add('student_ids', 'Vui lòng chọn ít nhất một học viên.');
            }
        });
    }
}
