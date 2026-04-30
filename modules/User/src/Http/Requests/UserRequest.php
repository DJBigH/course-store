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
            'is_locked' => 'required|in:0,1',
        ];

        if ($id) {
            if (!$this->filled('password')) {
                unset($rules['password']);
            }
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => 'Tên',
            'email' => 'Email',
            'password' => 'Mật khẩu',
            'group_id' => 'Nhóm người dùng',
            'is_locked' => 'Trạng thái tài khoản',
        ];
    }
}
