<?php

namespace Modules\Contacts\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
    public function rules()
    {
        return [
            'name' => 'required|max:225',
            'email' => 'email|nullable',
            'phone' => 'required|regex:/(0)[0-9]{9}/',
            'message' => 'max:225'
        ];
    }

    public function messages()
    {
        return [
            'required' => __('contacts::validation.required'),
            'email' => __('contacts::validation.email'),
            'integer' => __('contacts::validation.integer'),
            'max' => __('contacts::validation.max'),
            'regex' => __('contacts::validation.regex'),
        ];
    }

    public function attributes()
    {
        return __(key: 'contacts::validation.attributes');
    }
}
