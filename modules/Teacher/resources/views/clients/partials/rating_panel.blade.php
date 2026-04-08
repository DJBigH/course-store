@php
    $averageRating = round((float) ($teacher->ratings_avg_rating ?? 0), 1);
    $ratingCount = (int) ($teacher->ratings_count ?? 0);
    $currentRating = $viewerTeacherRating !== null ? (float) $viewerTeacherRating : null;
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

<div class="teacher-public-rating" data-teacher-rating-panel>
    <div class="teacher-public-rating__summary">
        <div>
            <div class="teacher-public-rating__eyebrow">{{ __('teacher::public.rating_title') }}</div>
            <div class="teacher-public-rating__stars">{!! $renderStars($averageRating) !!}</div>
        </div>
        <div class="teacher-public-rating__score">
            <strong>{{ $ratingCount > 0 ? number_format($averageRating, 1) : '0.0' }}</strong>
            <span>{{ __('teacher::public.rating_count', ['count' => $ratingCount]) }}</span>
        </div>
    </div>

    @if (auth('students')->check() && $canRateTeacher)
        <form class="teacher-public-rating__form mt-3" data-teacher-rating-form
            action="{{ route('teacher.public.rate', ['locale' => app()->getLocale(), 'slug' => $teacher->slug_locale]) }}"
            method="POST">
            @csrf
            <input type="hidden" name="rating" value="{{ $currentRating !== null ? number_format($currentRating, 1, '.', '') : '' }}" data-rating-input>

            <div class="teacher-public-rating__picker" data-rating-picker>
                @foreach ($ratingOptions as $ratingOption)
                    <button
                        type="button"
                        class="teacher-public-rating__option {{ $currentRating !== null && abs($currentRating - $ratingOption) < 0.001 ? 'is-active' : '' }}"
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
                        {{ __('teacher::public.rating_selected', ['rating' => rtrim(rtrim(number_format($currentRating, 1, '.', ''), '0'), '.')]) }}
                    @else
                        {{ __('teacher::public.rating_hint') }}
                    @endif
                </small>
                <button type="submit" class="btn btn-warning btn-sm">{{ __('teacher::public.rating_submit') }}</button>
            </div>
        </form>
    @elseif (auth('students')->check())
        <div class="alert alert-info mt-3 mb-0">{{ __('teacher::public.rating_need_purchase') }}</div>
    @else
        <div class="alert alert-info mt-3 mb-0">{{ __('teacher::public.rating_login_required') }}</div>
    @endif
</div>
