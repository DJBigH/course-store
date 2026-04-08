@php
    $averageRating = round((float) ($course->ratings_avg_rating ?? 0), 1);
    $ratingCount = (int) ($course->ratings_count ?? 0);
    $currentRating = $viewerCourseRating !== null ? (float) $viewerCourseRating : null;
    $ratingOptions = [1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5];
    $renderStars = function (float $rating) {
        $html = '';
        for ($star = 1; $star <= 5; $star++) {
            if ($rating >= $star) {
                $html .= '<i class="fa-solid fa-star"></i>';
            } elseif ($rating >= ($star - 0.5)) {
                $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
            } else {
                $html .= '<i class="fa-regular fa-star"></i>';
            }
        }

        return $html;
    };
@endphp

<div class="course-rating-panel" data-course-rating-panel>
    <div class="course-rating-panel__summary">
        <div>
            <div class="course-rating-panel__title">{{ __('courses::clients/common.rating_title') }}</div>
            <div class="course-rating-panel__stars">{!! $renderStars($averageRating) !!}</div>
        </div>
        <div class="course-rating-panel__score">
            <strong>{{ $ratingCount > 0 ? number_format($averageRating, 1) : '0.0' }}</strong>
            <span>{{ __('courses::clients/common.rating_count', ['count' => $ratingCount]) }}</span>
        </div>
    </div>

    @if (auth('students')->check() && $canRate)
        <form class="course-rating-form mt-3" data-course-rating-form
            action="{{ route('courses.rating.store', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}"
            method="POST">
            @csrf
            <input type="hidden" name="rating" value="{{ $currentRating !== null ? number_format($currentRating, 1, '.', '') : '' }}" data-rating-input>

            <div class="course-rating-picker" data-rating-picker>
                @foreach ($ratingOptions as $ratingOption)
                    <button
                        type="button"
                        class="course-rating-picker__option {{ $currentRating !== null && abs($currentRating - $ratingOption) < 0.001 ? 'is-active' : '' }}"
                        data-rating-option
                        data-value="{{ number_format($ratingOption, 1, '.', '') }}">
                        <i class="fa-solid fa-star"></i>
                        <span>{{ rtrim(rtrim(number_format($ratingOption, 1, '.', ''), '0'), '.') }}</span>
                    </button>
                @endforeach
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <small class="text-muted" data-rating-current-label>
                    @if ($currentRating !== null)
                        {{ __('courses::clients/common.rating_selected', ['rating' => rtrim(rtrim(number_format($currentRating, 1, '.', ''), '0'), '.')]) }}
                    @else
                        {{ __('courses::clients/common.rating_hint') }}
                    @endif
                </small>
                <button type="submit" class="btn btn-warning btn-sm">{{ __('courses::clients/common.rating_submit') }}</button>
            </div>
        </form>
    @elseif (auth('students')->check())
        <div class="alert alert-info mt-3 mb-0">{{ __('courses::clients/common.rating_need_purchase') }}</div>
    @else
        <div class="alert alert-info mt-3 mb-0">{{ __('courses::clients/common.rating_login_required') }}</div>
    @endif
</div>
