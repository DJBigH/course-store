<?php

namespace Modules\User\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        return [
            'name' => 'required|max:225',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'group_id'=> ['required','integer',function($attribute,$value,$fail){
                if($value == 0 ){
                    $fail('Vui lòng chọn nhóm');
                }
            }],
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute bắt buộc phải nhập',
            'email' => ':attribute không đúng định dạng',
            'unique' => ':attribute đã tồn tại',
            'max' => ':attribute quá :max ký tự',
            'min' => ':attribute phải từ :min ký tự',
            'integer' => ':attribute phải là số',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Tên',
            'email' => 'Email',
            'password' => 'Mật khẩu',
            'group_id' => 'Nhóm',
        ];
    }
}
