<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = (int) ($this->route('course') ?? 0);

        return [
            'name' => ['required', 'string', 'max:225'],
            'name_en' => ['nullable', 'string', 'max:225'],
            'name_ko' => ['nullable', 'string', 'max:225'],
            'name_ja' => ['nullable', 'string', 'max:225'],
            'name_zh' => ['nullable', 'string', 'max:225'],
            'detail' => ['required', 'string'],
            'detail_en' => ['nullable', 'string'],
            'detail_ko' => ['nullable', 'string'],
            'detail_ja' => ['nullable', 'string'],
            'detail_zh' => ['nullable', 'string'],
            'supports' => ['required', 'string'],
            'supports_en' => ['nullable', 'string'],
            'supports_ko' => ['nullable', 'string'],
            'supports_ja' => ['nullable', 'string'],
            'supports_zh' => ['nullable', 'string'],
            'thumbnail' => ['required', 'string', 'max:225'],
            'code' => ['nullable', 'string', 'max:225', Rule::unique('courses', 'code')->ignore($courseId)],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'integer', 'in:0,1'],
            'is_document' => ['required', 'integer', 'in:0,1'],
            'is_learning_locked' => ['required', 'integer', 'in:0,1'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ];
    }
}
