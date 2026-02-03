<section class="banner">
    <div class="container padding">
        <div class="row">
            <div class="d-none d-md-block col-md-4 col-lg-3">
                <div class="banner-left">
                    <div class="course-group">
                        <p>khoá học free</p>
                        @if (!empty($courseFree))
                            @foreach ($courseFree as $item)
                                <ul>
                                    <li><a href="{{ route('courses.detail', $item->slug) }}">{{ $item->name }}</a></li>
                                </ul>
                            @endforeach
                        @else
                            <ul>
                                <li><a href="#" class="text-muted">Không có khóa học nào miễn phí</a></li>
                            </ul>
                        @endif

                    </div>
                    <div class="course-group pt-3">
                        <p>khoá học nổi bật</p>
                        @if (!empty($courseView))
                            @foreach ($courseView as $item)
                                <ul>
                                    <li><a href="{{ route('courses.detail', $item->slug) }}">{{ $item->name }}</a>
                                    </li>
                                </ul>
                            @endforeach
                        @else
                            <ul>
                                <li><a href="#" class="text-muted">Không có khóa học nào nổi bật</a></li>
                            </ul>
                        @endif
                    </div>

                    <div class="course-group pt-3">
                        <p>khoá học mới</p>
                        @if (!empty($courseNew))
                            @foreach ($courseNew as $item)
                                <ul>
                                    <li><a href="{{ route('courses.detail', $item->slug) }}">{{ $item->name }}</a>
                                    </li>
                                </ul>
                            @endforeach
                        @else
                            <ul>
                                <li><a href="#" class="text-muted">Không có khóa học nào mới</a></li>
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
