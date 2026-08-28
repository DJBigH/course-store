<?php

namespace Modules\Students\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class studentRequest extends FormRequest
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
        $id = $this->route()->student;
        $rules = [
            'name' => 'required|max:225',
            'email' => 'required|email|unique:students,email',
            'password' => 'required|min:6',
            'status' => ['required', 'integer', function ($attribute, $value, $fail) {
                if ($value != 0 && $value != 1) {
                    $fail(__('student::validation.select'));
                }
            }],
        ];
        if ($id) {
            $rules['email'] = 'required|email|unique:students,email,' . $id;
            if ($this->password) {
                $rules['password'] = 'min:6';
            } else {
                unset($rules['password']);
            }
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => 'Tên học viên',
            'email' => 'Email',
            'password' => 'Mật khẩu',
            'status' => 'Trạng thái',
        ];
    }
}
