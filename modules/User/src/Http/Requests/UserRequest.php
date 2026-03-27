<?php

namespace Modules\User\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user');

        $rules = [
            'name' => 'required|max:225',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'required|min:6',
            'group_id' => 'required|integer|exists:groups,id',
        ];

        if ($id) {
            if (!$this->filled('password')) {
                unset($rules['password']);
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'required' => __('user::validation.required'),
            'email' => __('user::validation.email'),
            'unique' => __('user::validation.unique'),
            'max' => __('user::validation.max'),
            'min' => __('user::validation.min'),
            'integer' => __('user::validation.integer'),
            'exists' => ':attribute không tồn tại trong hệ thống.',
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('user::validation.attributes.name'),
            'email' => __('user::validation.attributes.email'),
            'password' => __('user::validation.attributes.password'),
            'group_id' => __('user::validation.attributes.group_id'),
        ];
    }
}
