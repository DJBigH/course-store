<?php

namespace Modules\Contacts\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Modules\Contacts\src\Models\Contacts;

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
        $captchaEnabled = (int) setting('captcha_enabled', '1') === 1;
        $rules = [
            'name' => 'required|max:225',
            'email' => 'email|nullable',
            'phone' => 'required|regex:/(0)[0-9]{9}/',
            'subject' => 'required|max:150',
            'submission_type' => ['required', Rule::in(Contacts::submissionTypes())],
            'category' => ['required', Rule::in(Contacts::categories())],
            'message' => 'required|max:2000',
            'page_url' => 'nullable|max:500',
        ];

        if ($captchaEnabled) {
            $rules['g-recaptcha-response'] = [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->passesRecaptcha($value)) {
                        $fail(__('contacts::clients/validation.recaptcha'));
                    }
                },
            ];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->routeIs('contacts.post-contacts')) {
            $this->merge([
                'submission_type' => Contacts::TYPE_CONTACT,
                'category' => 'general_contact',
                'subject' => $this->input('subject') ?: 'Liên hệ tư vấn',
                'page_url' => $this->input('page_url') ?: url()->current(),
            ]);
        }
    }

    public function messages()
    {
        return [
            'required' => __('contacts::clients/validation.required'),
            'email' => __('contacts::clients/validation.email'),
            'integer' => __('contacts::clients/validation.integer'),
            'max' => __('contacts::clients/validation.max'),
            'in' => __('contacts::clients/validation.select'),
            'regex' => __('contacts::clients/validation.regex'),
        ];
    }

    public function attributes()
    {
        return __(key: 'contacts::clients/validation.attributes');
    }

    protected function passesRecaptcha(?string $token): bool
    {
        if (blank($token)) {
            return false;
        }

        $secretKey = config('services.recaptcha.secret_key');
        $verifyUrl = config('services.recaptcha.verify_url');

        if (blank($secretKey) || blank($verifyUrl)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(8)->post($verifyUrl, [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $this->ip(),
            ]);

            return (bool) data_get($response->json(), 'success', false);
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
