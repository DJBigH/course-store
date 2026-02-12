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
            'required' => __('students::clients/validation.required'),
            'email' => __('students::clients/validation.email'),
            'unique' => __('students::clients/validation.unique'),
            'max' => __('students::clients/validation.max'),
            'min' => __('students::clients/validation.min'),
            'integer' => __('students::clients/validation.integer'),
            'phone.regex' => __('students::clients/validation.regex'),
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('students::clients/validation.attributes.name'),
            'email' => __('students::clients/validation.attributes.email'),
            'password' => __('students::clients/validation.attributes.password'),
            'phone' => __('students::clients/validation.attributes.phone'),
        ];
    }
}
