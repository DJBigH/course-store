@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $contactDefaults = [
            'name' => old('name', $studentData->name ?? ''),
            'phone' => old('phone', $studentData->phone ?? ''),
            'email' => old('email', $studentData->email ?? ''),
            'subject' => old('subject'),
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
                        <h3>{{ __('contacts::clients/common.contact_only_title') }}</h3>
                        <p>{{ __('contacts::clients/common.contact_only_description') }}</p>

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
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="contact-form">
                        <h4>{{ __('contacts::clients/common.contact_form_title') }}</h4>
                        <p>{{ __('contacts::clients/common.contact_form_description') }}</p>

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

                        <form method="POST" action="{{ route('contacts.post-contacts', ['locale' => app()->getLocale()]) }}" class="js-contact-form">
                            @csrf
                            <input type="hidden" name="page_url" value="{{ $contactDefaults['page_url'] }}">

                            <div class="form-group">
                                <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    name="name" placeholder="{{ __('contacts::clients/common.name_placeholder') }}"
                                    value="{{ $contactDefaults['name'] }}">
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                            name="phone" placeholder="{{ __('contacts::clients/common.phone_placeholder') }}"
                                            value="{{ $contactDefaults['phone'] }}">
                                        @error('phone')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                            name="email" placeholder="{{ __('contacts::clients/common.email_placeholder') }}"
                                            value="{{ $contactDefaults['email'] }}">
                                        @error('email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <input type="text" class="form-control {{ $errors->has('subject') ? 'is-invalid' : '' }}"
                                    name="subject" placeholder="{{ __('contacts::clients/common.subject_placeholder') }}"
                                    value="{{ $contactDefaults['subject'] }}">
                                @error('subject')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <textarea name="message" rows="5" placeholder="{{ __('contacts::clients/common.course') }}"
                                    class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}">{{ $contactDefaults['message'] }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @if ($captchaEnabled && !blank($captchaSiteKey))
                                <div class="form-group">
                                    <div class="contact-captcha-panel {{ $errors->has('g-recaptcha-response') ? 'is-invalid' : '' }}">
                                        <div class="contact-captcha-box">
                                            <div id="contact-captcha" class="g-recaptcha" data-sitekey="{{ $captchaSiteKey }}"></div>
                                        </div>
                                    </div>
                                    @error('g-recaptcha-response')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <button type="submit" class="btn-submit">
                                {{ __('contacts::clients/common.contact_submit_form') }} ->
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

        .contact-info,
        .contact-form {
            background: #fff;
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .contact-info h3,
        .contact-form h4 {
            font-weight: 800;
            color: #111827;
        }

        .contact-info p,
        .contact-form p {
            color: #6b7280;
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

        .form-group {
            margin-bottom: 18px;
        }

        .form-control {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 14px;
        }

        .btn-submit {
            width: 100%;
            border: none;
            background: #111827;
            color: #fff;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .alert-success-custom {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error-custom {
            background: #fef2f2;
            color: #7f1d1d;
            border: 1px solid #fecaca;
        }

        html[data-theme="dark"] .contact-page {
            background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 24%), linear-gradient(180deg, #07111f 0%, #0a1628 100%);
        }

        html[data-theme="dark"] .contact-info,
        html[data-theme="dark"] .contact-form {
            background: linear-gradient(180deg, #0f1b2d 0%, #132238 100%);
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 18px 40px rgba(2, 6, 23, 0.28);
        }

        html[data-theme="dark"] .contact-info h3,
        html[data-theme="dark"] .contact-form h4 {
            color: #eff6ff;
        }

        html[data-theme="dark"] .contact-info p,
        html[data-theme="dark"] .contact-form p {
            color: #9fb4cb;
        }

        html[data-theme="dark"] .info-item i {
            background: rgba(148, 163, 184, 0.1);
            color: #cfe3ff;
        }

        html[data-theme="dark"] .form-control {
            background: #091321;
            border-color: rgba(148, 163, 184, 0.16);
            color: #eff6ff;
        }

        html[data-theme="dark"] .btn-submit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
    </style>
@endsection

@section('scripts')
    @if ($captchaEnabled && !blank($captchaSiteKey))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
@endsection
