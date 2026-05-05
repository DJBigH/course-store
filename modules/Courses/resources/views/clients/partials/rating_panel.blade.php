@php
    $averageRating = round((float) ($course->ratings_avg_rating ?? 0), 1);
    $ratingCount = (int) ($course->ratings_count ?? 0);
    $currentRating = $viewerCourseRating !== null ? (float) $viewerCourseRating : null;
    $ratingOptions = [1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5];
    
    // Calculate percentages for each star level (5 down to 1)
    $breakdownData = [];
    for ($i = 5; $i >= 1; $i--) {
        // We might have half-star ratings in the DB, so we group them into the nearest integer for the bar
        // Or we just sum up everything that's >= i and < i+1 ?
        // Usually, breakdown is just for 5, 4, 3, 2, 1 levels.
        // Let's sum up ratings that are exactly $i or $i.5
        $count = (int)($ratingBreakdown[$i] ?? 0) + (int)($ratingBreakdown[$i + 0.5] ?? 0);
        $percent = $ratingCount > 0 ? ($count / $ratingCount) * 100 : 0;
        $breakdownData[$i] = [
            'count' => $count,
            'percent' => $percent
        ];
    }

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

<div class="course-rating-section p-4 rounded-4 shadow-sm border bg-white mb-4" data-course-rating-panel>
    <div class="row align-items-center g-4">
        <!-- Average Score -->
        <div class="col-md-4 text-center border-end border-light">
            <div class="display-3 fw-bold text-dark mb-0 lh-1">{{ number_format($averageRating, 1) }}</div>
            <div class="rating-stars-large fs-4 my-2" style="color: #f59e0b;">
                {!! $renderStars($averageRating) !!}
            </div>
            <div class="text-muted fw-medium">{{ __('courses::clients/common.rating_count', ['count' => $ratingCount]) }}</div>
        </div>

        <!-- Progress Bars Breakdown -->
        <div class="col-md-8">
            <div class="d-grid gap-2">
                @foreach ($breakdownData as $star => $data)
                    <div class="d-flex align-items-center gap-3">
                        <div class="star-label d-flex align-items-center gap-1 text-nowrap fw-semibold" style="width: 60px;">
                            {{ $star }} <i class="fa-solid fa-star text-warning small"></i>
                        </div>
                        <div class="progress flex-grow-1" style="height: 10px; background-color: #f1f5f9;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $data['percent'] }}%; background: linear-gradient(90deg, #f59e0b, #fbbf24); border-radius: 999px;" 
                                 aria-valuenow="{{ $data['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="star-percent text-muted small text-end" style="width: 40px;">
                            {{ round($data['percent']) }}%
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Rating Submission Section -->
    <div class="rating-interaction-wrap mt-5">
        @if (auth('students')->check() && $canRate && $currentRating === null)
            <div class="rating-submission-card card border-0 shadow-sm rounded-4 overflow-hidden" 
                 style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(251, 191, 36, 0.05) 100%);">
                <div class="card-body p-4">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-5">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="icon-box bg-warning text-white rounded-3 p-2 shadow-sm">
                                    <i class="fa-solid fa-star-half-stroke fs-4"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-0">{{ __('courses::clients/common.rating_title') }}</h5>
                            </div>
                            <p class="text-muted small mb-0">{{ __('courses::clients/common.rating_hint') }}</p>
                        </div>
                        
                        <div class="col-lg-7">
                            <form class="course-rating-form d-flex flex-wrap align-items-center justify-content-lg-end gap-4" data-course-rating-form
                                action="{{ route('courses.rating.store', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="rating" value="" data-rating-input>

                                <div class="d-flex flex-column align-items-center align-items-lg-end">
                                    <div class="teacher-public-rating__picker" data-rating-picker>
                                        <div class="teacher-public-rating__track" data-rating-track>
                                            <div class="teacher-public-rating__stars-base">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="fa-regular fa-star"></i>
                                                @endfor
                                            </div>
                                            <div class="teacher-public-rating__stars-fill" data-rating-fill style="width: 0%;">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="fa-solid fa-star"></i>
                                                @endfor
                                            </div>
                                            <div class="teacher-public-rating__hotspots">
                                                @foreach ([0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5] as $val)
                                                    <button type="button" class="teacher-public-rating__hotspot" 
                                                        data-rating-option data-value="{{ $val }}"></button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-warning fw-bold small mt-2" data-rating-current-label>
                                        {{ __('courses::clients/common.rating_hint_label') }}
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-bold border-0" 
                                        style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                    <i class="fa-solid fa-paper-plane me-2"></i> {{ __('courses::clients/common.rating_submit') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @elseif (auth('students')->check() && $currentRating !== null)
            <div class="user-rating-result card border-0 shadow-sm rounded-4 overflow-hidden bg-success-subtle/20 border border-success-subtle">
                <div class="card-body p-4 d-flex align-items-center gap-4">
                    <div class="rating-badge bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                        <i class="fa-solid fa-check fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-success mb-1">
                            {{ __('courses::clients/common.rating_submitted_once', ['rating' => rtrim(rtrim(number_format($currentRating, 1, '.', ''), '0'), '.')]) }}
                        </h6>
                        <div class="stars text-warning small mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= floor($currentRating))
                                    <i class="fa-solid fa-star"></i>
                                @elseif ($i - 0.5 <= $currentRating)
                                    <i class="fa-solid fa-star-half-stroke"></i>
                                @else
                                    <i class="fa-regular fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <p class="mb-0 small text-secondary">{{ __('courses::clients/common.rating_success') ?? 'Cảm ơn bạn đã đóng góp ý kiến!' }}</p>
                    </div>
                </div>
            </div>
        @elseif (auth('students')->check())
            <div class="purchase-required-notice card border-0 shadow-sm rounded-4 overflow-hidden bg-light border border-dashed">
                <div class="card-body p-4 text-center py-5">
                    <div class="icon-circle bg-white shadow-sm mx-auto mb-3" style="width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="fa-solid fa-lock text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">{{ __('courses::clients/common.rating_need_purchase') }}</h6>
                    <p class="text-muted small mb-0 px-4">{{ __('courses::clients/common.rating_login_required') }}</p>
                </div>
            </div>
        @else
            <div class="login-required-notice card border-0 shadow-sm rounded-4 overflow-hidden bg-light border border-dashed">
                <div class="card-body p-4 text-center py-5">
                    <div class="icon-circle bg-white shadow-sm mx-auto mb-3" style="width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="fa-solid fa-user-lock text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">{{ __('courses::clients/common.rating_login_required') }}</h6>
                    <a href="{{ route('students.login', ['locale' => app()->getLocale()]) }}" class="btn btn-dark btn-sm rounded-pill px-4 mt-2">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> {{ __('students::auth.login_title') ?? 'Đăng nhập ngay' }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
