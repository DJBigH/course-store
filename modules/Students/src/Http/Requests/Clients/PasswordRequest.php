<?php

namespace Modules\Students\src\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordRequest extends FormRequest
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
        $rules = [
            'old_password' => ['required', function($attribute, $value, $fail){
                $status = Hash::check($value, Auth::guard('students')->user()->password);
                if(!$status){
                    $fail(__('students::clients/validation.password-invalid'));
                }
            }],
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
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
            'password' => __('students::clients/validation.attributes.password'),
            'confirm_password' => __('students::clients/validation.attributes.confirm_password'),
            'old_password' => __('students::clients/validation.attributes.old_password'),
        ];
    }
}
