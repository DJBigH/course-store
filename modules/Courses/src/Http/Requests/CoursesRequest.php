<?php

namespace Modules\Courses\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoursesRequest extends FormRequest
{
    /**
     * Determine if the users is authorized to make this request.
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
        $id = $this->route()->courses;

        $uniqueRule = 'unique:courses,code';

        if($id){
            $uniqueRule.=','.$id;
        }

        $rules = [
            'name' => 'required|max:225',
            'slug' => 'required|max:225',
            'detail' => 'required',
            'teacher_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                if ($value == 0) {
                    $fail(__('courses::validation.select'));
                }
            }],
            'thumbnail' => 'required|max:225',
            'code' => 'required|max:225|'.$uniqueRule,
            'is_document' => 'required|integer',
            'supports' => 'required',
            'status' => 'required|integer',
            'categories' => 'required',
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'required' => __('courses::validation.required'),
            'email' => __('courses::validation.email'),
            'unique' => __('courses::validation.unique'),
            'max' => __('courses::validation.max'),
            'min' => __('courses::validation.min'),
            'integer' => __('courses::validation.integer'),
        ];
    }

    public function attributes()
    {
        return __('courses::validation.attributes');
    }
}
