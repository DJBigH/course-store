<?php

namespace Modules\Students\src\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


class StudentsRequest extends FormRequest
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
    public function rules(): array
    {
        $id = Auth::guard('students')->user()->id;
        $rules = [
            'name' => 'required|max:225',
            'email' => 'required|email|unique:students,email,' . $id,
            'phone' => 'required|regex:/(0)[0-9]{9}/',
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'required' => __('students::validation.required'),
            'email' => __('students::validation.email'),
            'unique' => __('students::validation.unique'),
            'max' => __('students::validation.max'),
            'min' => __('students::validation.min'),
            'integer' => __('students::validation.integer'),
            'phone.regex' => __('students::validation.regex'),
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('students::validation.attributes.name'),
            'email' => __('students::validation.attributes.email'),
            'password' => __('students::validation.attributes.password'),
            'phone' => __('students::validation.attributes.phone'),
        ];
    }
}
