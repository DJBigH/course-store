@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="contact-page">
        <div class="container">
            <div class="row g-4">

                <!-- LEFT INFO -->
                <div class="col-12 col-lg-5">
                    <div class="contact-info">
                        <h3>{{ __('contacts::clients/common.contact_me') }}</h3>
                        <p>
                            {{ __('contacts::clients/common.listen') }}
                        </p>

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
                            <a href="{{ setting_url('tiktok') }}" {!! setting_target('tiktok') !!}>
                                <i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>
                </div>

                <!-- FORM -->
                <div class="col-12 col-lg-7">
                    <div class="contact-form">
                        <h4>🚀 {{ __('contacts::clients/common.register') }}</h4>
                        <p>{{ __('contacts::clients/common.info') }}</p>
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

                        @if (!empty($studentData))
                            <form method="POST"
                                action="{{ route('contacts.post-contacts', ['locale' => app()->getLocale()]) }}">
                                @csrf

                                <div class="form-group">
                                    <input type="text"
                                        class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}"
                                        name="name" placeholder="{{ __('contacts::clients/common.name_placeholder') }}"
                                        value="{{ old('name', $studentData->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input type="text"
                                        class="form-control title {{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                        name="phone" placeholder="{{ __('contacts::clients/common.phone_placeholder') }}"
                                        value="{{ old('phone', $studentData->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input type="text"
                                        class="form-control title {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        name="email" placeholder="{{ __('contacts::clients/common.email_placeholder') }}"
                                        value="{{ old('email', $studentData->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <textarea name="message" rows="4" placeholder="{{ __('contacts::clients/common.course') }}"
                                        class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}">{{ old('message') }}</textarea>

                                    @error('message')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>


                                <button type="submit" class="btn-submit">
                                    {{ __('contacts::clients/common.submit_form') }} →
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('contacts.post-contacts') }}">
                                @csrf

                                <div class="form-group">
                                    <input type="text"
                                        class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}"
                                        name="name" placeholder="{{ __('contacts::clients/common.name_placeholder') }}"
                                        value="{{ old('name') }}">
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input type="text"
                                        class="form-control title {{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                        name="phone" placeholder="{{ __('contacts::clients/common.phone_placeholder') }}"
                                        value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input type="text"
                                        class="form-control title {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        name="email" placeholder="{{ __('contacts::clients/common.email_placeholder') }}"
                                        value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <textarea name="message" rows="4" placeholder="{{ __('contacts::clients/common.course') }}"
                                        class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}">{{ old('message') }}</textarea>

                                    @error('message')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>


                                <button type="submit" class="btn-submit">
                                    {{ __('contacts::clients/common.submit_form') }} →
                                </button>
                            </form>
                        @endif
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

        /* LEFT */
        .contact-info {
            background: #fff;
            border-radius: 18px;
            padding: 40px;
            height: 100%;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .05);
            animation: fadeLeft .8s ease;
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
            transition: .3s;
        }

        .contact-social a:hover {
            background: #111827;
            color: #fff;
            transform: translateY(-3px);
        }

        /* FORM */
        .contact-form {
            background: #fff;
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .05);
            animation: fadeRight .8s ease;
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
            transition: .25s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #111827;
            box-shadow: 0 0 0 3px rgba(17, 24, 39, .08);
            outline: none;
        }

        .btn-submit {
            width: 100%;
            border: none;
            background: #111827;
            color: #fff;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            transition: .3s;
        }

        .btn-submit:hover {
            background: #000;
            transform: translateY(-2px);
        }

        /* ANIMATION */
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
            gap: 12px;
            padding: 16px 18px;
            border-radius: 14px;
            margin-bottom: 22px;
            animation: shakeIn .4s ease;
        }

        .alert-error-custom {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #7f1d1d;
        }

        .alert-error-custom i {
            font-size: 22px;
            color: #ef4444;
            margin-top: 2px;
        }

        .alert-error-custom strong {
            display: block;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .alert-error-custom p {
            font-size: 14px;
            margin: 0;
            color: #991b1b;
        }

        .alert-error-custom ul {
            margin: 6px 0 0;
            padding-left: 18px;
            font-size: 13px;
        }

        .alert-error-custom li {
            margin-bottom: 4px;
        }

        /* animation */
        @keyframes shakeIn {
            0% {
                opacity: 0;
                transform: translateX(-10px);
            }

            50% {
                transform: translateX(10px);
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ALERT BASE */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            animation: slideDown .4s ease;
        }

        /* SUCCESS */
        .alert-success-custom {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-success-custom i {
            color: #10b981;
        }

        /* ERROR */
        .alert-error-custom {
            background: #fef2f2;
            color: #7f1d1d;
            border: 1px solid #fecaca;
        }

        .alert-error-custom i {
            color: #ef4444;
        }

        /* ANIMATION */
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
