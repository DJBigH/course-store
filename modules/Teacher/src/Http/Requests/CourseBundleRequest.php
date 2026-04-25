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
            'status' => ['nullable', 'boolean'],
            'course_ids' => ['required', 'array', 'min:2'],
            'course_ids.*' => ['integer', 'distinct', 'min:1'],
        ];
    }
}
