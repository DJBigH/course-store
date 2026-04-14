<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherRequest extends FormRequest
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
        $id = $this->route()->user;

        $rules = [
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
            'description' => 'required',
            'description_en' => 'nullable',
            'description_ko' => 'nullable',
            'description_ja' => 'nullable',
            'description_zh' => 'nullable',
            'exp' => 'required|integer',
            'image' => 'required|max:225',
            'is_verified_badge' => 'nullable|boolean',
            'is_premium_badge' => 'nullable|boolean',
            'badge_key' => 'nullable|string|in:none,verified,premium,top_seller,expert,featured,custom',
            'badge_label' => 'nullable|string|max:100',
            'badge_tone' => 'nullable|string|in:blue,gold,emerald,violet,rose,slate',
        ];
        return $rules;
    }


    public function messages()
    {
        return [
            'required' => __('teacher::validation.required'),
            'max' => __('teacher::validation.max'),
            'min' => __('teacher::validation.min'),
            'integer' => __('teacher::validation.integer'),
        ];
    }

    public function attributes()
    {
        return __('teacher::validation.attributes');
    }
}
