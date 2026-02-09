<footer>
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-4">
                <div class="footer-group">
                    <h3>{{__('common.contact')}}</h3>
                    <ul>
                        <li>
                            <a href="#">
                                <i class="fa-solid fa-mobile"></i>
                                {{ setting('phone','012345678') }}
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa-solid fa-envelope"></i>
                                {{ setting('email','bigk@gmail.com') }}
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa-solid fa-house"></i>
                               {{ setting('address','Việt Nam') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-12 col-lg-4 mt-4 mt-lg-0">
                <div class="footer-group">
                    <h3>{{ __('common.student_support') }}</h3>
                    <ul>
                        <li>
                            <a href="#"> {{ __('common.student_support') }}</a>
                        </li>
                        <li>
                            <a href="#"> {{ __('common.question') }}</a>
                        </li>
                        <li>
                            <a href="#"> {{ __('common.feel_student') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-12 col-lg-4 mt-4 mt-lg-0">
                <div class="footer-group">
                    <h3>{{ __('common.terms_and_conditions') }}</h3>
                    <ul>
                        <li>
                            <a href="#"> {{ __('common.affiliate') }}</a>
                        </li>
                        <li>
                            <a href="#"> {{ __('common.terms_of_service') }}</a>
                        </li>
                        <li>
                            <a href="#"> {{ __('common.privacy_policy') }}</a>
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
