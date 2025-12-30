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
            'slug' => 'required|max:225',
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
            'slug' => __('categories::validation.attributes.slug'),
            'parent_id' => __('categories::validation.attributes.parent_id'),
        ];
    }
}
