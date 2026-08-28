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
            'coupon_code' => strtoupper(trim((string) $this->input('coupon_code'))),
            'note' => $this->normalizeText($this->input('note')),
        ]);
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $this->route('application');
        // Since store handles both, and it's a POST, we check if we have an existing application in session or for student
        $hasApplication = false;
        if (auth('students')->check()) {
            $hasApplication = \Modules\Teacher\src\Models\TeacherApplication::where('student_id', auth('students')->id())->exists();
        } else {
            $hasApplication = session()->has('teacher_guest_application_id');
        }

        return [
            'full_name' => ['required', 'string', 'regex:/^[\p{L}\s\'\-\.]+$/u', 'min:2', 'max:100'],
            'display_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'headline' => ['required', 'string', 'min:5', 'max:255'],
            'bio' => ['required', 'string', 'min:50', 'max:5000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'specialties' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-().]{8,20}$/'],
            'email' => array_merge(
                ['required', 'string', 'email:rfc', 'max:100'],
                !auth('students')->check() ? ['unique:students,email'] : []
            ),
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255', 'regex:/facebook\.com|fb\.com/i'],
            'youtube_url' => ['nullable', 'url', 'max:255', 'regex:/youtube\.com|youtu\.be/i'],
            'linkedin_url' => ['nullable', 'url', 'max:255', 'regex:/linkedin\.com/i'],
            'intro_video_url' => ['nullable', 'url', 'max:255', 'regex:/youtube\.com|youtu\.be|vimeo\.com/i'],
            'cv_file' => [$hasApplication ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'package_id' => ['required', 'exists:teacher_packages,id'],
            'payment_method' => ['nullable', 'in:bank_transfer,vnpay,momo'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
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
            'full_name.regex' => 'Họ và tên không được chứa số hoặc ký tự đặc biệt.',
            'full_name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'display_name.min' => 'Tên hiển thị phải có ít nhất 2 ký tự.',
            'headline.required' => 'Vui lòng nhập tiêu đề chuyên môn.',
            'headline.min' => 'Tiêu đề chuyên môn phải có ít nhất 5 ký tự.',
            'bio.required' => 'Vui lòng nhập giới thiệu bản thân.',
            'bio.min' => 'Giới thiệu bản thân phải có ít nhất 50 ký tự.',
            'bio.max' => 'Giới thiệu bản thân không được vượt quá 5000 ký tự.',
            'experience_years.required' => 'Vui lòng nhập số năm kinh nghiệm.',
            'experience_years.integer' => 'Số năm kinh nghiệm phải là số nguyên.',
            'specialties.required' => 'Vui lòng nhập lĩnh vực chuyên môn.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng bởi một tài khoản khác.',
            'portfolio_url.url' => 'Portfolio không hợp lệ.',
            'facebook_url.regex' => 'Link Facebook phải đúng định dạng (facebook.com hoặc fb.com).',
            'youtube_url.regex' => 'Link YouTube phải đúng định dạng (youtube.com hoặc youtu.be).',
            'linkedin_url.regex' => 'Link LinkedIn phải đúng định dạng (linkedin.com).',
            'intro_video_url.regex' => 'Link video giới thiệu phải là YouTube hoặc Vimeo.',
            'cv_file.required' => 'Vui lòng tải lên hồ sơ năng lực (CV).',
            'cv_file.file' => 'Hồ sơ năng lực phải là một tập tin.',
            'cv_file.mimes' => 'CV chỉ chấp nhận định dạng: PDF, JPG, PNG.',
            'cv_file.max' => 'Dung lượng CV không được vượt quá 5MB.',
            'package_id.required' => 'Vui lòng chọn một gói giảng viên.',
            'package_id.exists' => 'Gói giảng viên không hợp lệ.',
        ];
    }

    private function normalizeText(mixed $value): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $value));
    }
}
