<section class="banner">
    <div class="container padding">
        <div class="row">
            <div class="d-none d-md-block col-md-4 col-lg-3">
                <div class="banner-left">
                    <div class="course-group">
                        <p>{{ __('home::clients/common.free_course') }}</p>
                        @if (!empty($courseFree))
                            @foreach ($courseFree as $item)
                                <ul>
                                    <li>
                                        <a
                                            href="{{ route('courses.detail', [
                                                'locale' => app()->getLocale(),
                                                'slug' => $item->slug_locale,
                                            ]) }}">
                                            {{ $item->name_locale }}
                                        </a>
                                    </li>

                                </ul>
                            @endforeach
                        @else
                            <ul>
                                <li><a href="#"
                                        class="text-muted">{{ __('home::clients/common.no_course_free') }}</a></li>
                            </ul>
                        @endif

                    </div>
                    <div class="course-group pt-3">
                        <p>{{ __('home::clients/common.course_view') }}</p>
                        @if (!empty($courseView))
                            @foreach ($courseView as $item)
                                <ul>
                                    <li><a
                                            href="{{ route('courses.detail', [
                                                'locale' => app()->getLocale(),
                                                'slug' => $item->slug_locale,
                                            ]) }}">{{ $item->name_locale }}</a>
                                    </li>
                                </ul>
                            @endforeach
                        @else
                            <ul>
                                <li><a href="#"
                                        class="text-muted">{{ __('home::clients/common.no_course_view') }}</a></li>
                            </ul>
                        @endif
                    </div>

                    <div class="course-group pt-3">
                        <p>{{ __('home::clients/common.course_new') }}</p>
                        @if (!empty($courseNew))
                            @foreach ($courseNew as $item)
                                <ul>
                                    <li><a
                                            href="{{ route('courses.detail', [
                                                'locale' => app()->getLocale(),
                                                'slug' => $item->slug_locale,
                                            ]) }}">{{ $item->name_locale }}</a>
                                    </li>
                                    </li>
                                </ul>
                            @endforeach
                        @else
                            <ul>
                                <li><a href="#"
                                        class="text-muted">{{ __('home::clients/common.no_course_new') }}</a></li>
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-8 col-lg-6">
                <div class="banner-slider">
                    @if (!empty(json_decode(setting('banner_slider'), true)))
                        @foreach (json_decode(setting('banner_slider'), true) ?? [] as $img)
                            <img src="{{ asset('storage/' . $img) }}">
                        @endforeach
                    @else
                        <div class="banner-slider-inner">
                            <img src="/clients/assets/slider-1.jpeg" alt="" />
                        </div>
                        <div class="banner-slider-inner">
                            <img src="/clients/assets/slider-2.jpeg" alt="" />
                        </div>
                        <div class="banner-slider-inner">
                            <img src="/clients/assets/slider-3.jpeg" alt="" />
                        </div>
                    @endif
                </div>
            </div>
            <div class="d-none d-lg-block col-lg-3">
                <div class="banner-right">
                    @if (!empty(json_decode(setting('banner_right'), true)))
                        @foreach (json_decode(setting('banner_right'), true) ?? [] as $img)
                            <img src="{{ asset('storage/' . $img) }}">
                        @endforeach
                    @else
                        <div class="banner-right__img">
                            <img src="/clients/assets/banner.png" alt="" />
                        </div>
                        <div class="banner-right__img">
                            <img src="/clients/assets/banner.png" alt="" />
                        </div>
                        <div class="banner-right__img">
                            <img src="/clients/assets/banner.png" alt="" />
                        </div>
                    @endif
                </div>
            </div>
            <div class="banner-full">
                @if (setting('banner_full'))
                    <img src="{{ asset('storage/' . setting('banner_full')) }}" alt="">
                @else
                    <img src="{{ asset('clients/assets/banner-full.jpeg') }}" alt="">
                @endif
            </div>
        </div>
    </div>
</section>
