<?php

namespace Modules\Lessons\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules =  [
            'name' => 'required|max:255',
            'name_en' => 'nullable|max:255',
            'name_ko' => 'nullable|max:255',
            'name_ja' => 'nullable|max:255',
            'name_zh' => 'nullable|max:255',
            'slug' => 'required|max:255',
            'slug_en' => 'nullable|max:255',
            'slug_ko' => 'nullable|max:255',
            'slug_ja' => 'nullable|max:255',
            'slug_zh' => 'nullable|max:255',
            'parent_id' => 'required|integer',
            'is_trial' => 'required|integer',
            'position' => 'required|integer',
            'description' => 'required',
            'description_en' => 'nullable',
            'description_ko' => 'nullable',
            'description_ja' => 'nullable',
            'description_zh' => 'nullable',

        ];
        if ($this->parent_id !== 0) {
            $rules['parent_id'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'required' => __('lessons::validation.required'),
            'email' => __('lessons::validation.email'),
            'integer' => __('lessons::validation.integer'),
        ];
    }

    public function attributes()
    {
        return __('lessons::validation.attributes');
    }
}
