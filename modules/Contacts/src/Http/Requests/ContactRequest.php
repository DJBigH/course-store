<?php

namespace Modules\Contacts\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;

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
            'message' => 'required|max:225',
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

    public function messages()
    {
        return [
            'required' => __('contacts::clients/validation.required'),
            'email' => __('contacts::clients/validation.email'),
            'integer' => __('contacts::clients/validation.integer'),
            'max' => __('contacts::clients/validation.max'),
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
