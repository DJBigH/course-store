@php
    $phone = setting('phone', '012345678');
    $email = setting('email', 'bigk@gmail.com');
    $address = setting('address', 'Viet Nam');
    $phoneHref = 'tel:' . preg_replace('/[^\d+]/', '', (string) $phone);
    $addressHref = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode((string) $address);
@endphp

<footer>
    <div class="container">
        <div class="row align-items-start">
            <div class="col-12 col-xl-4">
                <div class="footer-brand">
                    <a class="footer-brand__logo" href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                        <img src="{{ setting('logo') ? asset('storage/' . setting('logo')) : asset('clients/assets/logo.png') }}"
                            alt="BigK Udemy">
                    </a>
                    <h3>{{ trans('home::clients/static_pages.about.page_title') }}</h3>
                    <p>{{ trans('home::clients/static_pages.about.footer_summary') }}</p>
                    <div class="footer-brand__actions">
                        <a href="{{ route('home.about', ['locale' => app()->getLocale()]) }}">
                            <i class="fa-solid fa-circle-info"></i>
                            {{ trans('home::clients/static_pages.about.page_title') }}
                        </a>
                        <a href="{{ route('contacts.home', ['locale' => app()->getLocale()]) }}">
                            <i class="fa-solid fa-paper-plane"></i>
                            {{ __('common.contact') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-2 mt-4 mt-xl-0">
                <div class="footer-group">
                    <h3>{{ __('common.student_support') }}</h3>
                    <ul>
                        <li>
                            <a href="{{ route('home.student-support', ['locale' => app()->getLocale()]) }}">{{ __('common.student_support') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('home.faq', ['locale' => app()->getLocale()]) }}">{{ __('common.question') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('home.testimonials', ['locale' => app()->getLocale()]) }}">{{ __('common.feel_student') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('home.about', ['locale' => app()->getLocale()]) }}">{{ trans('home::clients/static_pages.about.page_title') }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 mt-4 mt-xl-0">
                <div class="footer-group">
                    <h3>{{ __('common.terms_and_conditions') }}</h3>
                    <ul>
                        <li>
                            <a href="{{ route('home.payment-policy', ['locale' => app()->getLocale()]) }}">{{ __('common.payment_policy') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('home.refund-policy', ['locale' => app()->getLocale()]) }}">{{ trans('home::clients/static_pages.refund_policy.page_title') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('home.terms-of-service', ['locale' => app()->getLocale()]) }}">{{ __('common.terms_of_service') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('home.privacy-policy', ['locale' => app()->getLocale()]) }}">{{ __('common.privacy_policy') }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 mt-4 mt-xl-0">
                <div class="footer-group">
                    <h3>{{ __('common.contact') }}</h3>
                    <ul>
                        <li>
                            <a href="{{ $phoneHref }}">
                                <i class="fa-solid fa-mobile"></i>
                                {{ $phone }}
                            </a>
                        </li>
                        <li>
                            <a href="mailto:{{ $email }}">
                                <i class="fa-solid fa-envelope"></i>
                                {{ $email }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ setting_url('facebook') }}" {!! setting_target('facebook') !!}>
                                <i class="fa-brands fa-facebook-f"></i>
                                Facebook
                            </a>
                        </li>
                        <li>
                            <a href="{{ setting_url('youtube') }}" {!! setting_target('youtube') !!}>
                                <i class="fa-brands fa-youtube"></i>
                                YouTube
                            </a>
                        </li>
                        <li>
                            <a href="{{ $addressHref }}" target="_blank" rel="noopener noreferrer">
                                <i class="fa-solid fa-house"></i>
                                {{ $address }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <p>
        Made with
        <span>
            <i class="fa-solid fa-heart"></i>
        </span>
        by BigK
    </p>
</footer>
