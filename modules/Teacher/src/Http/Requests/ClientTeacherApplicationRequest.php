<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Packages\src\Models\Package;

class ClientTeacherApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => $this->normalizeText($this->input('full_name')),
            'display_name' => $this->normalizeText($this->input('display_name')),
            'headline' => $this->normalizeText($this->input('headline')),
            'specialties' => $this->normalizeText($this->input('specialties')),
            'phone' => preg_replace('/\s+/', '', (string) $this->input('phone')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'portfolio_url' => trim((string) $this->input('portfolio_url')),
            'facebook_url' => trim((string) $this->input('facebook_url')),
            'youtube_url' => trim((string) $this->input('youtube_url')),
            'linkedin_url' => trim((string) $this->input('linkedin_url')),
            'intro_video_url' => trim((string) $this->input('intro_video_url')),
            'cv_file' => trim((string) $this->input('cv_file')),
            'identity_file' => trim((string) $this->input('identity_file')),
            'coupon_code' => strtoupper(trim((string) $this->input('coupon_code'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:100'],
            'display_name' => ['nullable', 'string', 'max:100'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'specialties' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\-().]{8,20}$/'],
            'email' => ['required', 'string', 'email:rfc', 'max:100'],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $packageId = (int) $this->input('package_id', 0);
            if ($packageId <= 0) {
                return;
            }

            $package = Package::query()->selectable()->find($packageId);
            if (!$package) {
                $validator->errors()->add('package_id', 'Gói đăng ký này hiện không khả dụng.');
                return;
            }

            if ((float) $package->price > 0 && !$this->filled('payment_method')) {
                $validator->errors()->add('payment_method', 'Vui lòng chọn phương thức thanh toán cho gói có phí.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'portfolio_url.url' => 'Portfolio không hợp lệ.',
            'facebook_url.url' => 'Facebook URL không hợp lệ.',
            'youtube_url.url' => 'YouTube URL không hợp lệ.',
            'linkedin_url.url' => 'LinkedIn không hợp lệ.',
            'intro_video_url.url' => 'Video giới thiệu không hợp lệ.',
            'package_id.required' => 'Vui lòng chọn một gói giảng viên.',
            'package_id.exists' => 'Gói giảng viên không hợp lệ.',
        ];
    }

    private function normalizeText(mixed $value): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $value));
    }
}
