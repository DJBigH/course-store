<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('students')->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'status' => ['nullable', 'boolean'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'is_coming_soon' => ['nullable', 'boolean'],
            'coming_soon_start_at' => ['nullable', 'required_if:is_coming_soon,1', 'date', 'after_or_equal:now'],
            'end_at' => ['nullable', 'date', 'after:now', 'after_or_equal:coming_soon_start_at'],
            'course_ids' => ['required', 'array', 'min:2'],
            'course_ids.*' => ['integer', 'distinct', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('teacher::teacher/bundle/common.validation.name_required'),
            'price.required' => __('teacher::teacher/bundle/common.validation.price_required'),
            'course_ids.required' => __('teacher::teacher/bundle/common.validation.course_ids_required'),
            'course_ids.min' => __('teacher::teacher/bundle/common.validation.course_ids_min'),
            'sale_price.lt' => __('teacher::teacher/bundle/common.validation.sale_price_lt'),
        ];
    }
}
