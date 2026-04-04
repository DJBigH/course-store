<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeacherProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'display_name' => trim((string) $this->input('display_name')),
            'headline' => trim((string) $this->input('headline')),
            'phone' => preg_replace('/\s+/', '', (string) $this->input('phone')),
            'address' => trim((string) $this->input('address')),
            'image' => trim((string) $this->input('image')),
            'bio' => trim((string) $this->input('bio')),
            'specialties' => trim((string) $this->input('specialties')),
            'portfolio_url' => trim((string) $this->input('portfolio_url')),
            'facebook_url' => trim((string) $this->input('facebook_url')),
            'youtube_url' => trim((string) $this->input('youtube_url')),
            'linkedin_url' => trim((string) $this->input('linkedin_url')),
            'intro_video_url' => trim((string) $this->input('intro_video_url')),
            'cv_file' => trim((string) $this->input('cv_file')),
            'identity_file' => trim((string) $this->input('identity_file')),
        ]);
    }

    public function rules(): array
    {
        $section = (string) $this->input('profile_section', 'all');
        $isAccountSection = in_array($section, ['all', 'account'], true);
        $isProfessionalSection = in_array($section, ['all', 'professional'], true);
        $isPasswordSection = $section === 'password';
        $isPasswordAttempt = $isPasswordSection
            || filled($this->input('current_password'))
            || filled($this->input('password'))
            || filled($this->input('password_confirmation'));

        return [
            'profile_section' => ['nullable', 'string'],

            'name' => [Rule::requiredIf($isAccountSection), 'nullable', 'string', 'min:2', 'max:225'],
            'phone' => [Rule::requiredIf($isAccountSection), 'nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => [Rule::requiredIf($isAccountSection), 'nullable', 'string', 'max:225'],

            'display_name' => [Rule::requiredIf($isProfessionalSection), 'nullable', 'string', 'min:2', 'max:100'],
            'headline' => [Rule::requiredIf($isProfessionalSection), 'nullable', 'string', 'min:2', 'max:255'],
            'experience_years' => [Rule::requiredIf($isProfessionalSection), 'nullable', 'integer', 'min:0', 'max:80'],
            'specialties' => [Rule::requiredIf($isProfessionalSection), 'nullable', 'string', 'min:2', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],

            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'intro_video_url' => ['nullable', 'url', 'max:255'],
            'cv_file' => ['nullable', 'string', 'max:255'],
            'identity_file' => ['nullable', 'string', 'max:255'],

            'current_password' => [
                'nullable',
                Rule::requiredIf($isPasswordAttempt),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (blank($this->input('password'))) {
                        return;
                    }

                    $student = Auth::guard('students')->user();
                    if (!$student || !Hash::check((string) $value, (string) $student->password)) {
                        $fail(__('students::clients/validation.password-invalid'));
                    }
                },
            ],
            'password' => [Rule::requiredIf($isPasswordAttempt), 'nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => [Rule::requiredIf($isPasswordAttempt), 'nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => __('students::clients/validation.required'),
            'required_with' => __('students::clients/validation.required'),
            'max' => __('students::clients/validation.max'),
            'min' => __('students::clients/validation.min'),
            'phone.regex' => __('students::clients/validation.regex'),
            'password.confirmed' => __('students::clients/validation.same'),
            'portfolio_url.url' => 'Portfolio URL khong hop le.',
            'facebook_url.url' => 'Facebook URL khong hop le.',
            'youtube_url.url' => 'YouTube URL khong hop le.',
            'linkedin_url.url' => 'LinkedIn URL khong hop le.',
            'intro_video_url.url' => 'Video gioi thieu URL khong hop le.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('students::clients/validation.attributes.name'),
            'image' => __('teacher::dashboard.profile.fields.avatar'),
            'display_name' => __('teacher::dashboard.profile.fields.display_name'),
            'headline' => __('teacher::dashboard.profile.fields.headline'),
            'phone' => __('students::clients/validation.attributes.phone'),
            'address' => __('students::clients/account.profile.address'),
            'bio' => __('teacher::dashboard.profile.fields.bio'),
            'experience_years' => __('teacher::dashboard.profile.fields.experience_years'),
            'specialties' => __('teacher::dashboard.profile.fields.specialties'),
            'portfolio_url' => __('teacher::dashboard.profile.fields.portfolio_url'),
            'facebook_url' => __('teacher::dashboard.profile.fields.facebook_url'),
            'youtube_url' => __('teacher::dashboard.profile.fields.youtube_url'),
            'linkedin_url' => __('teacher::dashboard.profile.fields.linkedin_url'),
            'intro_video_url' => __('teacher::dashboard.profile.fields.intro_video_url'),
            'cv_file' => __('teacher::dashboard.profile.fields.cv_file'),
            'identity_file' => __('teacher::dashboard.profile.fields.identity_file'),
            'current_password' => __('students::clients/validation.attributes.old_password'),
            'password' => __('students::clients/validation.attributes.password'),
            'password_confirmation' => __('students::clients/validation.attributes.confirm_password'),
        ];
    }
}
