@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $contactDefaults = [
            'name' => old('name', $studentData->name ?? ''),
            'phone' => old('phone', $studentData->phone ?? ''),
            'email' => old('email', $studentData->email ?? ''),
            'subject' => old('subject'),
            'submission_type' => old('submission_type', 'feedback'),
            'category' => old('category'),
            'message' => old('message'),
            'page_url' => old('page_url', url()->current()),
        ];
        $captchaEnabled = (int) setting('captcha_enabled', '1') === 1;
        $captchaSiteKey = config('services.recaptcha.site_key');
    @endphp

    <section class="contact-page">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="contact-info">
                        <span class="contact-chip">{{ __('contacts::clients/common.page_badge') }}</span>
                        <h3>{{ __('contacts::clients/common.contact_me') }}</h3>
                        <p>{{ __('contacts::clients/common.listen') }}</p>

                        <div class="contact-guide">
                            <div class="contact-guide__item">
                                <strong>{{ __('contacts::clients/common.feedback_title') }}</strong>
                                <span>{{ __('contacts::clients/common.feedback_description') }}</span>
                            </div>
                            <div class="contact-guide__item">
                                <strong>{{ __('contacts::clients/common.report_title') }}</strong>
                                <span>{{ __('contacts::clients/common.report_description') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="contact-form">
                        <h4>{{ __('contacts::clients/common.register') }}</h4>
                        <p>{{ __('contacts::clients/common.info') }}</p>

                        <div class="contact-form-alerts" data-contact-alerts>
                            {{-- Messages will be injected via JS --}}
                        </div>

                        <form method="POST" action="{{ route('contacts.post-support', ['locale' => app()->getLocale()]) }}" class="js-contact-form">
                            @csrf
                            <input type="hidden" name="page_url" value="{{ $contactDefaults['page_url'] }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('contacts::clients/common.type_label') }}</label>
                                    <select name="submission_type" class="form-control {{ $errors->has('submission_type') ? 'is-invalid' : '' }}">
                                        @foreach ($submissionTypes as $type)
                                            @if ($type !== 'contact')
                                                <option value="{{ $type }}" @selected($contactDefaults['submission_type'] === $type)>
                                                    {{ __('contacts::clients/common.types.' . $type) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('contacts::clients/common.category_label') }}</label>
                                    <select name="category" class="form-control {{ $errors->has('category') ? 'is-invalid' : '' }}">
                                        <option value="">{{ __('contacts::clients/common.category_placeholder') }}</option>
                                        @foreach ($categories as $category)
                                            @if ($category !== 'general_contact')
                                                <option value="{{ $category }}" @selected($contactDefaults['category'] === $category)>
                                                    {{ __('contacts::clients/common.categories.' . $category) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name"
                                    placeholder="{{ __('contacts::clients/common.name_placeholder') }}" value="{{ $contactDefaults['name'] }}">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" name="phone"
                                            placeholder="{{ __('contacts::clients/common.phone_placeholder') }}" value="{{ $contactDefaults['phone'] }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email"
                                            placeholder="{{ __('contacts::clients/common.email_placeholder') }}" value="{{ $contactDefaults['email'] }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <input type="text" class="form-control {{ $errors->has('subject') ? 'is-invalid' : '' }}" name="subject"
                                    placeholder="{{ __('contacts::clients/common.subject_placeholder') }}" value="{{ $contactDefaults['subject'] }}">
                            </div>

                            <div class="form-group">
                                <textarea name="message" rows="5" placeholder="{{ __('contacts::clients/common.message_placeholder') }}"
                                    class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}">{{ $contactDefaults['message'] }}</textarea>
                            </div>

                            @if ($captchaEnabled && !blank($captchaSiteKey))
                                <div class="form-group">
                                    <div class="contact-captcha-panel {{ $errors->has('g-recaptcha-response') ? 'is-invalid' : '' }}">
                                        <div class="contact-captcha-box">
                                            <div id="contact-captcha" class="g-recaptcha" data-sitekey="{{ $captchaSiteKey }}"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <button type="submit" class="btn-submit">
                                <span class="btn-text">{{ __('contacts::clients/common.submit_form') }} -></span>
                                <span class="btn-loader d-none"><i class="fas fa-spinner fa-spin"></i> {{ __('common.processing') }}...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .contact-page { background: #f9fafb; padding: 80px 0; }
        .contact-info, .contact-form { background: #fff; border-radius: 18px; padding: 40px; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05); height: 100%; }
        .contact-chip { display: inline-flex; padding: 0.35rem 0.75rem; border-radius: 999px; background: #dbeafe; color: #1d4ed8; font-size: 0.78rem; font-weight: 700; margin-bottom: 14px; }
        .contact-guide { margin-top: 24px; display: grid; gap: 12px; }
        .contact-guide__item { border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px 16px; background: #f8fafc; }
        .contact-guide__item strong { display: block; margin-bottom: 4px; }
        .form-label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 700; color: #0f172a; }
        .form-group { margin-bottom: 18px; }
        .form-control { width: 100%; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; font-size: 14px; }
        .btn-submit { width: 100%; border: none; background: #111827; color: #fff; padding: 14px; border-radius: 12px; font-weight: 700; }
        .alert { display: flex; align-items: center; gap: 10px; padding: 14px 18px; border-radius: 12px; font-size: 14px; margin-bottom: 20px; }
        .alert-success-custom { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error-custom { background: #fef2f2; color: #7f1d1d; border: 1px solid #fecaca; }
    </style>
@endsection

@section('scripts')
    @if ($captchaEnabled && !blank($captchaSiteKey))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.js-contact-form');
            const alertContainer = document.querySelector('[data-contact-alerts]');
            const submitBtn = form.querySelector('.btn-submit');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoader = submitBtn.querySelector('.btn-loader');

            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Clear previous alerts and errors
                alertContainer.innerHTML = '';
                form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                // Loading state
                submitBtn.disabled = true;
                btnText.classList.add('d-none');
                btnLoader.classList.remove('d-none');

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (response.ok) {
                        // Success
                        alertContainer.innerHTML = `
                            <div class="alert alert-success-custom">
                                <i class="fas fa-check-circle"></i>
                                <span>${result.message}</span>
                            </div>
                        `;
                        form.reset();
                        if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
                    } else if (response.status === 422) {
                        // Validation errors
                        alertContainer.innerHTML = `
                            <div class="alert alert-error-custom">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>{{ __('contacts::clients/messages.error.any.title') }}</strong>
                                    <p>{{ __('contacts::clients/messages.error.any.content') }}</p>
                                </div>
                            </div>
                        `;

                        for (const [field, messages] of Object.entries(result.errors)) {
                            let input = form.querySelector(`[name="${field}"]`);
                            if (!input && field === 'g-recaptcha-response') {
                                input = form.querySelector('.contact-captcha-panel');
                            }
                            
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback d-block';
                                errorDiv.textContent = messages[0];
                                input.closest('.form-group') ? input.closest('.form-group').appendChild(errorDiv) : input.parentNode.appendChild(errorDiv);
                            }
                        }
                    } else {
                        throw new Error(result.message || 'Something went wrong');
                    }
                } catch (error) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-error-custom">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>${error.message}</span>
                        </div>
                    `;
                } finally {
                    submitBtn.disabled = false;
                    btnText.classList.remove('d-none');
                    btnLoader.classList.add('d-none');
                }
            });
        });
    </script>
@endsection
