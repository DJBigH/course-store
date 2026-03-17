<?php

namespace Modules\Categories\src\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:225',
            'name_en' => 'nullable|max:225',
            'name_ko' => 'nullable|max:225',
            'name_ja' => 'nullable|max:225',
            'name_zh' => 'nullable|max:225',
            'slug' => 'required|max:225',
            'slug_en' => 'nullable|max:225',
            'slug_ko' => 'nullable|max:225',
            'slug_ja' => 'nullable|max:225',
            'slug_zh' => 'nullable|max:225',
            'parent_id' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'required' => __('categories::validation.required'),
            'max' => __('categories::validation.max'),
            'integer' => __('categories::validation.integer'),
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('categories::validation.attributes.name'),
            'name_en' => __('categories::validation.attributes.name_en'),
            'name_ko' => __('categories::validation.attributes.name_ko'),
            'name_ja' => __('categories::validation.attributes.name_ja'),
            'name_zh' => __('categories::validation.attributes.name_zh'),
            'slug' => __('categories::validation.attributes.slug'),
            'slug_en' => __('categories::validation.attributes.slug_en'),
            'slug_ko' => __('categories::validation.attributes.slug_ko'),
            'slug_ja' => __('categories::validation.attributes.slug_ja'),
            'slug_zh' => __('categories::validation.attributes.slug_zh'),
            'parent_id' => __('categories::validation.attributes.parent_id'),
        ];
    }
}
