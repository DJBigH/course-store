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
            'promotion_template' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:160'],
            'message_html' => ['required', 'string', 'max:100000'],
            'cta_enabled' => ['nullable', 'boolean'],
            'cta_label' => ['nullable', 'required_with:cta_enabled', 'string', 'max:80'],
            'cta_url' => ['nullable', 'required_with:cta_enabled', 'url', 'max:1000'],
            'send_via_web' => ['nullable', 'boolean'],
            'send_via_email' => ['nullable', 'boolean'],
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
