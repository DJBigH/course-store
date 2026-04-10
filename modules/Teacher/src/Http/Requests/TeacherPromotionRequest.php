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
            'course_id' => ['nullable', 'integer', 'min:1'],
            'recent_purchase_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'inactive_learning_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'title' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }
}
