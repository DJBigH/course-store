<?php

namespace Modules\Packages\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:100'],
            'name_ko' => ['nullable', 'string', 'max:100'],
            'name_ja' => ['nullable', 'string', 'max:100'],
            'name_zh' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
