@forelse ($courseAll as $item)
    @php
        $thumbnail = $item->thumbnail
            ? (\Illuminate\Support\Str::startsWith($item->thumbnail, ['http://', 'https://']) ? $item->thumbnail : asset($item->thumbnail))
            : asset('clients/assets/banner-course.png');

        $teacherImage = teacherAvatarUrl($item->teacher);
    @endphp
    <div class="col-12 col-lg-6">
        <div class="course-card d-flex">
            <div class="course-thumb">
                <img src="{{ $thumbnail }}" alt="{{ $item->name_locale }}"
                    onerror="this.onerror=null;this.src='{{ asset('clients/assets/banner-course.png') }}';">
            </div>

            <div class="course-content">
                <div>
                    <div class="course-meta">
                        <span><i class="fa-solid fa-clock"></i> {{ getTime($item->durations) }}</span>
                        <span><i class="fa-solid fa-video"></i>
                            {{ getLessonCount($item)->module }} {{ __('home::common.portion') }} /
                            {{ getLessonCount($item)->lessons }} {{ __('home::common.lesson') }}
                        </span>
                        <span><i class="fa-solid fa-eye"></i>
                            {{ number_format($item->view ?? 0) }} {{ __('home::common.view') }}
                        </span>
                    </div>

                    <h5 class="course-title">
                        <a
                            href="{{ route('courses.detail', [
                                'locale' => app()->getLocale(),
                                'slug' => $item->slug_locale,
                            ]) }}">
                            {{ $item->name_locale }}
                        </a>
                    </h5>

                    <div class="course-teacher">
                        <img src="{{ $teacherImage }}" alt="{{ $item->teacher?->name_locale }}"
                            onerror="this.onerror=null;this.src='{{ asset('resources/assets/teacher.png') }}';">
                        <span>{{ $item->teacher->name_locale }}</span>
                    </div>

                    <div class="course-rating">
                        <i class="fa-solid fa-star"></i>
                        <strong>{{ $item->ratings_count > 0 ? number_format((float) $item->ratings_avg_rating, 1) : '0.0' }}</strong>
                        <span>({{ (int) ($item->ratings_count ?? 0) }})</span>
                    </div>
                </div>

                <div class="course-bottom">
                    <div class="course-price">
                        <span class="price-old">{{ moneyLocale($item->price_locale) }}</span>
                        <span class="price-new">{{ moneyLocale($item->sale_price_locale) }}</span>
                    </div>

                    <a href="{{ route('courses.detail', [
                        'locale' => app()->getLocale(),
                        'slug' => $item->slug_locale,
                    ]) }}"
                        class="btn-view">
                        {{ __('home::common.detail') }} →
                    </a>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5">
        <p>{{ __('home::common.no_course') }}</p>
    </div>
@endforelse
