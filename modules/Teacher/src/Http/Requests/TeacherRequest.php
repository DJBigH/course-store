<?php

namespace Modules\Teacher\Src\Http\Requests;

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
            'slug' => 'required|max:225',
            'description' => 'required',
            'exp' => 'required|integer',
            'image' => 'required|max:225',
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
