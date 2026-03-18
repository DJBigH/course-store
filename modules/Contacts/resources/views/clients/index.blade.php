@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $contactDefaults = [
            'name' => old('name', $studentData->name ?? ''),
            'phone' => old('phone', $studentData->phone ?? ''),
            'email' => old('email', $studentData->email ?? ''),
            'message' => old('message'),
        ];
    @endphp

    <section class="contact-page">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="contact-info">
                        <h3>{{ __('contacts::clients/common.contact_me') }}</h3>
                        <p>{{ __('contacts::clients/common.listen') }}</p>

                        <div class="info-item">
                            <i class="fas fa-phone-alt"></i>
                            <div>
                                <span>{{ __('contacts::clients/common.hotline') }}</span>
                                <strong>{{ setting('phone', '012345678') }}</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <span>{{ __('contacts::clients/common.email') }}</span>
                                <strong>{{ setting('email', 'bigk@gmail.com') }}</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span>{{ __('contacts::clients/common.address') }}</span>
                                <strong>{{ setting('address', 'Việt Nam') }}</strong>
                            </div>
                        </div>

                        <div class="contact-social">
                            <a href="{{ setting_url('facebook') }}" {!! setting_target('facebook') !!}><i
                                    class="fab fa-facebook-f"></i></a>
                            <a href="{{ setting_url('instagram') }}" {!! setting_target('instagram') !!}><i
                                    class="fab fa-instagram"></i></a>
                            <a href="{{ setting_url('youtube') }}" {!! setting_target('youtube') !!}><i
                                    class="fab fa-youtube"></i></a>
                            <a href="{{ setting_url('tiktok') }}" {!! setting_target('tiktok') !!}><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="contact-form">
                        <h4>{{ __('contacts::clients/common.register') }}</h4>
                        <p>{{ __('contacts::clients/common.info') }}</p>

                        <div class="contact-form-alerts" data-contact-alerts>
                            @if (session('msg'))
                                <div class="alert alert-success-custom">
                                    <i class="fas fa-check-circle"></i>
                                    <span>{{ session('msg') }}</span>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-error-custom">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <div>
                                        <strong>{{ __('contacts::clients/messages.error.any.title') }}</strong>
                                        <p>{{ __('contacts::clients/messages.error.any.content') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <form method="POST"
                            action="{{ route('contacts.post-contacts', ['locale' => app()->getLocale()]) }}"
                            class="js-contact-form">
                            @csrf

                            <div class="form-group">
                                <input type="text"
                                    class="form-control title {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    name="name" placeholder="{{ __('contacts::clients/common.name_placeholder') }}"
                                    value="{{ $contactDefaults['name'] }}">
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <input type="text"
                                    class="form-control title {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                    name="phone" placeholder="{{ __('contacts::clients/common.phone_placeholder') }}"
                                    value="{{ $contactDefaults['phone'] }}">
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <input type="text"
                                    class="form-control title {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                    name="email" placeholder="{{ __('contacts::clients/common.email_placeholder') }}"
                                    value="{{ $contactDefaults['email'] }}">
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <textarea name="message" rows="4" placeholder="{{ __('contacts::clients/common.course') }}"
                                    class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}">{{ $contactDefaults['message'] }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div
                                    class="contact-captcha-panel {{ $errors->has('g-recaptcha-response') ? 'is-invalid' : '' }}">
                                    <div class="contact-captcha-box">
                                        <div id="contact-captcha" class="g-recaptcha"
                                            data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                                    </div>
                                </div>
                                @error('g-recaptcha-response')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn-submit"
                                data-submit-label="{{ __('contacts::clients/common.submit_form') }}">
                                {{ __('contacts::clients/common.submit_form') }} ->
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
        .contact-page {
            background: #f9fafb;
            padding: 80px 0;
        }

        .contact-info {
            background: #fff;
            border-radius: 18px;
            padding: 40px;
            height: 100%;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
            animation: fadeLeft 0.8s ease;
        }

        .contact-info h3 {
            font-weight: 800;
            color: #111827;
            margin-bottom: 10px;
        }

        .contact-info p {
            color: #6b7280;
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
        }

        .info-item i {
            width: 44px;
            height: 44px;
            background: #e5e7eb;
            color: #111827;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-item span {
            font-size: 13px;
            color: #6b7280;
        }

        .info-item strong {
            display: block;
            font-size: 15px;
            color: #111827;
        }

        .contact-social {
            margin-top: 30px;
            display: flex;
            gap: 12px;
        }

        .contact-social a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .contact-social a:hover {
            background: #111827;
            color: #fff;
            transform: translateY(-3px);
        }

        .contact-form {
            background: #fff;
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
            animation: fadeRight 0.8s ease;
        }

        .contact-form h4 {
            font-weight: 800;
            margin-bottom: 5px;
        }

        .contact-form p {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 14px;
            transition: 0.25s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #111827;
            box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.08);
            outline: none;
        }

        .contact-captcha-panel {
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
        }

        .contact-captcha-panel.is-invalid {
            border: 0;
        }

        .contact-captcha-box {
            display: flex;
            justify-content: center;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
        }

        .btn-submit {
            width: 100%;
            border: none;
            background: #111827;
            color: #fff;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background: #000;
            transform: translateY(-2px);
        }

        @keyframes fadeLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 768px) {
            .contact-page {
                padding: 40px 0;
            }

            .contact-captcha-box {
                width: 100%;
                overflow-x: auto;
                justify-content: flex-start;
            }
        }

        html[data-theme="dark"] .contact-page {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 24%),
                linear-gradient(180deg, #07111f 0%, #0a1628 100%);
        }

        html[data-theme="dark"] .contact-info,
        html[data-theme="dark"] .contact-form {
            background: linear-gradient(180deg, #0f1b2d 0%, #132238 100%);
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 18px 40px rgba(2, 6, 23, 0.28);
        }

        html[data-theme="dark"] .contact-info h3,
        html[data-theme="dark"] .contact-form h4,
        html[data-theme="dark"] .info-item strong {
            color: #eff6ff;
        }

        html[data-theme="dark"] .contact-info p,
        html[data-theme="dark"] .contact-form p,
        html[data-theme="dark"] .info-item span {
            color: #9fb4cb;
        }

        html[data-theme="dark"] .info-item i,
        html[data-theme="dark"] .contact-social a {
            background: rgba(148, 163, 184, 0.12);
            color: #cfe3ff;
        }

        html[data-theme="dark"] .contact-social a:hover {
            background: rgba(59, 130, 246, 0.24);
            color: #eff6ff;
        }

        html[data-theme="dark"] .form-group input,
        html[data-theme="dark"] .form-group textarea {
            background: #091321;
            border-color: rgba(148, 163, 184, 0.16);
            color: #eff6ff;
        }

        html[data-theme="dark"] .form-group input::placeholder,
        html[data-theme="dark"] .form-group textarea::placeholder {
            color: #8fa8c5;
        }

        html[data-theme="dark"] .form-group input:focus,
        html[data-theme="dark"] .form-group textarea:focus {
            border-color: rgba(96, 165, 250, 0.48);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.14);
        }

        html[data-theme="dark"] .contact-captcha-panel {
            background: transparent;
            border-color: transparent;
        }

        html[data-theme="dark"] .contact-captcha-box {
            background: transparent;
            border-color: transparent;
        }

        html[data-theme="dark"] .btn-submit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #eff6ff;
        }

        html[data-theme="dark"] .btn-submit:hover {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
        }

        html[data-theme="dark"] .alert-success-custom {
            background: rgba(16, 185, 129, 0.12);
            color: #bbf7d0;
            border-color: rgba(52, 211, 153, 0.22);
        }

        html[data-theme="dark"] .alert-error-custom {
            background: rgba(239, 68, 68, 0.12);
            color: #fecaca;
            border-color: rgba(248, 113, 113, 0.24);
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            animation: slideDown 0.4s ease;
        }

        .btn-submit.is-loading {
            opacity: 0.8;
            pointer-events: none;
        }

        .alert-success-custom {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-success-custom i {
            color: #10b981;
        }

        .alert-error-custom {
            background: #fef2f2;
            color: #7f1d1d;
            border: 1px solid #fecaca;
        }

        .alert-error-custom i {
            color: #ef4444;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.js-contact-form');

            if (!form) {
                return;
            }

            const alertBox = document.querySelector('[data-contact-alerts]');
            const submitButton = form.querySelector('.btn-submit');
            const preservedValues = {
                name: form.elements.name?.value ?? '',
                phone: form.elements.phone?.value ?? '',
                email: form.elements.email?.value ?? '',
            };

            const renderAlert = (type, title, message) => {
                if (!alertBox) {
                    return;
                }

                const isSuccess = type === 'success';

                alertBox.innerHTML = `
                    <div class="alert ${isSuccess ? 'alert-success-custom' : 'alert-error-custom'}">
                        <i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
                        <div>
                            <strong>${title}</strong>
                            <p>${message}</p>
                        </div>
                    </div>
                `;
            };

            const clearFieldErrors = () => {
                form.querySelectorAll('.is-invalid').forEach((element) => {
                    element.classList.remove('is-invalid');
                });

                form.querySelectorAll('.invalid-feedback[data-generated="true"]').forEach((element) => {
                    element.remove();
                });

                const captchaPanel = form.querySelector('.contact-captcha-panel');
                if (captchaPanel) {
                    captchaPanel.classList.remove('is-invalid');
                }
            };

            const showFieldError = (name, message) => {
                const field = form.elements[name];

                if (name === 'g-recaptcha-response') {
                    const captchaPanel = form.querySelector('.contact-captcha-panel');

                    if (captchaPanel) {
                        captchaPanel.classList.add('is-invalid');
                    }

                    captchaPanel?.insertAdjacentHTML('afterend',
                        `<div class="invalid-feedback d-block" data-generated="true">${message}</div>`);
                    return;
                }

                if (!field) {
                    return;
                }

                field.classList.add('is-invalid');
                field.insertAdjacentHTML('afterend',
                    `<div class="invalid-feedback d-block" data-generated="true">${message}</div>`);
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearFieldErrors();

                if (alertBox) {
                    alertBox.innerHTML = '';
                }

                submitButton.classList.add('is-loading');
                submitButton.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        },
                        body: new FormData(form),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.ok) {
                        form.reset();
                        form.elements.name.value = preservedValues.name;
                        form.elements.phone.value = preservedValues.phone;
                        form.elements.email.value = preservedValues.email;

                        if (window.grecaptcha) {
                            window.grecaptcha.reset();
                        }

                        renderAlert(
                            'success',
                            'Thành công',
                            data.message ||
                            '{{ __('contacts::clients/messages.success.request') }}'
                        );

                        return;
                    }

                    if (response.status === 422 && data.errors) {
                        renderAlert(
                            'error',
                            '{{ __('contacts::clients/messages.error.any.title') }}',
                            '{{ __('contacts::clients/messages.error.any.content') }}'
                        );

                        Object.entries(data.errors).forEach(([field, messages]) => {
                            if (messages.length) {
                                showFieldError(field, messages[0]);
                            }
                        });
                    } else {
                        renderAlert(
                            'error',
                            '{{ __('contacts::clients/messages.error.any.title') }}',
                            data.message ||
                            '{{ __('contacts::clients/messages.error.any.content') }}'
                        );
                    }
                } catch (error) {
                    renderAlert(
                        'error',
                        '{{ __('contacts::clients/messages.error.any.title') }}',
                        '{{ __('contacts::clients/messages.error.any.content') }}'
                    );
                } finally {
                    submitButton.classList.remove('is-loading');
                    submitButton.disabled = false;
                }
            });
        });
    </script>
@endsection
