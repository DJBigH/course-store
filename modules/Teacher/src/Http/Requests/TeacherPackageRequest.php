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
        $packageId = $this->route('package');
        $packageId = is_object($packageId) ? $packageId->id : $packageId;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('teacher_packages', 'code')->ignore($packageId)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'string', 'max:20'],
            'course_limit' => ['nullable', 'integer', 'min:1'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'priority_review' => ['nullable', 'boolean'],
            'support_level' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
