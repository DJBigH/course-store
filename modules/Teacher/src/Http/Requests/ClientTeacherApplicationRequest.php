<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientTeacherApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:100'],
            'display_name' => ['nullable', 'string', 'max:100'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'specialties' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:100'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'intro_video_url' => ['nullable', 'url', 'max:255'],
            'cv_file' => ['nullable', 'string', 'max:255'],
            'identity_file' => ['nullable', 'string', 'max:255'],
            'package_id' => ['required', 'exists:teacher_packages,id'],
            'payment_method' => ['nullable', 'in:bank_transfer,vnpay,momo'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
