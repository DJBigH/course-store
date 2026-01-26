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
                                    <li><a href="{{ route('courses.detail',$item->slug) }}">{{ $item->name }}</a></li>
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
                                    <li><a href="{{ route('courses.detail',$item->slug) }}">{{ $item->name }}</a></li>
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
                                    <li><a href="{{ route('courses.detail',$item->slug) }}">{{ $item->name }}</a></li>
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
                    <div class="banner-slider-inner">
                        <img src="/clients/assets/slider-1.jpeg" alt="" />
                    </div>
                    <div class="banner-slider-inner">
                        <img src="/clients/assets/slider-2.jpeg" alt="" />
                    </div>
                    <div class="banner-slider-inner">
                        <img src="/clients/assets/slider-3.jpeg" alt="" />
                    </div>
                </div>
            </div>
            <div class="d-none d-lg-block col-lg-3">
                <div class="banner-right">
                    <div class="banner-right__img">
                        <img src="/clients/assets/banner.png" alt="" />
                    </div>
                    <div class="banner-right__img">
                        <img src="/clients/assets/banner.png" alt="" />
                    </div>
                    <div class="banner-right__img">
                        <img src="/clients/assets/banner.png" alt="" />
                    </div>
                </div>
            </div>
            <div class="banner-full">
                <img src="/clients/assets/banner-full.jpeg" alt="" />
            </div>
        </div>
    </div>
</section>
