<?php

namespace Modules\Settings\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
        return [
            'site_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'currency_rate_usd' => ['nullable', 'numeric', 'gt:0'],
            'currency_rate_krw' => ['nullable', 'numeric', 'gt:0'],
            'currency_rate_jpy' => ['nullable', 'numeric', 'gt:0'],
            'currency_rate_cny' => ['nullable', 'numeric', 'gt:0'],
            'banner_slider' => ['nullable', 'array'],
            'banner_slider.*' => ['file', 'image', 'max:2048'],
            'banner_right' => ['nullable', 'array', 'max:3'],
            'banner_right.*' => ['file', 'image', 'max:2048'],
            'banner_full' => ['nullable', 'file', 'image', 'max:4096'],
            'logo' => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
       return [
            'banner_right.max' => 'Banner ben phai chi duoc toi da 3 anh.',
       ];
    }

}
