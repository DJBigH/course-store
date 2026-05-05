@extends('layouts.client')
@section('content')
    @include('part.clients.page_title')
    <section class="course-detal">
        <div class="container">
            <div class="row relative">
                <div class="col-12 col-lg-9">
                    <div class="submenu">
                        <ul>
                            <li>
                                <a href="#information">
                                    <i class="fa-solid fa-file"></i> {{ __('courses::clients/common.information') }}
                                </a>
                            </li>
                            <li>
                                <a href="#curriculum">
                                    <i class="fa-solid fa-book"></i>
                                    {{ __('courses::clients/common.curriculum') }}
                                </a>
                            </li>
                            <li>
                                <a href="#author">
                                    <i class="fa-solid fa-user"></i>
                                    {{ __('courses::clients/common.author') }}
                                </a>
                            </li>
                            <li>
                                <a href="#evaluate">
                                    <i class="fa-solid fa-comment"></i>
                                    {{ __('courses::clients/common.evaluate') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="course-descreption instructor-box" id="information">
                        <div class="course-content">
                            {!! $course->detail_locale !!}
                        </div>
                    </div>

                    <div class="accordion instructor-box" id="curriculum">
                        <div class="accordion-top px-2">
                            <p>
                                <i class="fa-solid fa-book me-1"></i>
                                {{ __('courses::clients/common.include') }}: {{ getLessonCount($course)->module }}
                                {{ __('courses::clients/common.portion') }} - {{ getLessonCount($course)->lessons }}
                                {{ __('courses::clients/common.lessons') }}
                            </p>
                            <p>
                                <i class="fa-solid fa-clock me-1"></i>
                                {{ __('courses::clients/common.duration') }} {{ getTime($course->durations) }}
                            </p>
                        </div>
                        @include('courses::clients.lesson')
                    </div>

                    @if ($course->teacher)
                        <div class="course-video instructor-box mb-4" id="author">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 instructor-avatar">
                                    <img src="{{ teacherAvatarUrl($course->teacher) }}" alt="{{ $course->teacher->name_locale }}"
                                        class="rounded-circle">
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1 small">{{ __('courses::clients/common.instructor') }}</p>

                                    <h5 class="instructor-name mb-1 fw-semibold">
                                        <a href="{{ route('teacher.public.show', ['locale' => app()->getLocale(), 'slug' => $course->teacher->slug_locale]) }}"
                                            class="text-decoration-none text-dark hover-primary">
                                            {{ $course->teacher->name_locale }}
                                        </a>
                                    </h5>
                                    @if (!empty($course->teacher->badge_labels))
                                        <div class="teacher-mini-badges mt-2">
                                            @foreach ($course->teacher->badge_labels as $badge)
                                                <span class="teacher-mini-badge teacher-mini-badge--{{ $badge['key'] }}">{{ $badge['label'] }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i class="bi bi-mortarboard"></i>
                                        <span>{{ $course->teacher->exp }}
                                            {{ __('courses::clients/common.experience_years') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 text-warning mt-2">
                                        <i class="fa-solid fa-star"></i>
                                        <strong>{{ $course->teacher->ratings_count > 0 ? number_format((float) $course->teacher->ratings_avg_rating, 1) : '0.0' }}</strong>
                                        <span class="text-muted">{{ __('courses::clients/common.rating_count', ['count' => (int) ($course->teacher->ratings_count ?? 0)]) }}</span>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <div class="course-content-infor instructor-desc">
                                {!! $course->teacher->description_locale !!}
                            </div>
                        </div>
                    @endif


                    <div class="course-video mb-4 instructor-box" id="evaluate">
                        <div id="course-comments-wrap">
                            @include('courses::clients.comments_thread', [
                                'course' => $course,
                                'threads' => $threads,
                                'canComment' => $canComment,
                                'canRate' => $canRate,
                                'viewerCourseRating' => $viewerCourseRating,
                                'ratingBreakdown' => $ratingBreakdown,
                                'viewerIsAdmin' => $viewerIsAdmin,
                            ])
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-3">
                    <div class="course-profile shadow-sm rounded mb-4">
                        <!-- Thumbnail -->
                        <div class="course-thumb">
                            <img src="{{ $course->thumbnail }}" alt="{{ $course->name_locale }}">
                        </div>

                        <!-- Content -->
                        <div class="course-info p-3">
                            <!-- Price -->
                            @unless ($hasCourse)
                                <div class="course-price mb-3">
                                    <i class="fa-solid fa-tag text-primary me-1"></i>
                                    @if ($course->sale_price_locale)
                                        <span class="text-muted text-decoration-line-through me-2">
                                            {{ moneyLocale($course->price_locale) }}
                                        </span>
                                        <span class="fw-bold text-danger fs-5">
                                            {{ moneyLocale($course->sale_price_locale) }}
                                        </span>
                                    @else
                                        <span class="fw-bold fs-5 text-danger">
                                            {{ moneyLocale($course->price_locale) }}
                                        </span>
                                    @endif
                                </div>
                            @endunless

                            <!-- Info list -->
                            <ul class="course-meta list-unstyled mb-3">
                                <li>
                                    <i class="fa-solid fa-bookmark text-warning"></i>
                                    <span>{{ __('courses::clients/common.course_code') }}:</span>
                                    <strong>{{ $course->code }}</strong>
                                </li>

                                <li>
                                    <i class="fa-solid fa-user-graduate text-primary"></i>
                                    <span>{{ __('courses::clients/common.instructor') }}:</span>
                                    <strong>{{ $course->teacher->name_locale }}</strong>
                                    @if (!empty($course->teacher->badge_labels))
                                        <span class="teacher-inline-badges">
                                            @foreach ($course->teacher->badge_labels as $badge)
                                                <span class="teacher-inline-badge teacher-inline-badge--{{ $badge['key'] }}">{{ $badge['label'] }}</span>
                                            @endforeach
                                        </span>
                                    @endif
                                    <small class="text-muted">({{ $course->teacher->exp }}
                                        {{ __('courses::clients/common.exp') }})</small>
                                </li>

                                <li>
                                    <i class="fa-solid fa-clock text-success"></i>
                                    <span>{{ __('courses::clients/common.duration') }}:</span>
                                    <strong>{{ getTime($course->durations) }}</strong>
                                </li>

                                {{-- ✅ THÊM: Cập nhật gần nhất --}}
                                <li>
                                    <i class="fa-solid fa-calendar-check text-secondary"></i>
                                    <span>{{ __('courses::clients/common.updated_at') }}:</span>
                                    <strong>{{ format_date_dmy($course->updated_at) }}</strong>
                                </li>

                                {{-- ✅ THÊM: Tổng số học viên --}}
                                <li>
                                    <i class="fa-solid fa-users text-info"></i>
                                    <span>{{ __('courses::clients/common.students') }}:</span>
                                    <strong>{{ number_format($course->students_count ?? 0) }}</strong>
                                </li>

                                <li>
                                    <i class="fa-solid fa-headset text-info"></i>
                                    <span>{{ __('courses::clients/common.support') }}:</span>
                                    <strong>{!! $course->supports_locale !!}</strong>
                                </li>

                                <li class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-file-lines text-info"></i>
                                    <span>{{ __('courses::clients/common.attachments') }}:</span>

                                    @if ($course->is_document == 1)
                                        <span class="badge bg-success">
                                            <i class="fa-solid fa-check me-1"></i> {{ __('courses::clients/common.yes') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fa-solid fa-xmark me-1"></i> {{ __('courses::clients/common.no') }}
                                        </span>
                                    @endif
                                </li>
                            </ul>


                            @php
                                $firstLesson = $course->lessons->whereNotNull('parent_id')->first();
                            @endphp

                            @if ($course->is_coming_soon && $course->coming_soon_start_at && $course->coming_soon_start_at->isFuture())
                                <div class="coming-soon-wrapper mb-3 text-center p-3 rounded bg-light border border-warning">
                                    <h6 class="text-warning fw-bold mb-2">
                                        <i class="fa-solid fa-clock-rotate-left"></i> {{ __('courses::clients/common.coming_soon') }}
                                    </h6>
                                    <div class="countdown-timer d-flex justify-content-center gap-2" 
                                         data-time="{{ $course->coming_soon_start_at->toIso8601String() }}">
                                        <div class="time-item"><span class="days">00</span><small>{{ __('courses::clients/common.countdown_days') }}</small></div>
                                        <div class="time-item"><span class="hours">00</span><small>{{ __('courses::clients/common.countdown_hours') }}</small></div>
                                        <div class="time-item"><span class="minutes">00</span><small>{{ __('courses::clients/common.countdown_minutes') }}</small></div>
                                        <div class="time-item"><span class="seconds">00</span><small>{{ __('courses::clients/common.countdown_seconds') }}</small></div>
                                    </div>
                                    <small class="text-muted mt-2 d-block">{{ __('courses::clients/common.coming_soon_desc') }}</small>
                                </div>
                                <button class="btn btn-secondary w-100 fw-semibold" disabled>
                                    <i class="fa-solid fa-hourglass-start me-1"></i>
                                    {{ __('courses::clients/common.coming_soon') }}
                                </button>
                            @elseif ($hasCourse && $firstLesson)
                                <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $firstLesson->slug_locale]) }}"
                                    class="btn btn-success w-100 fw-semibold">
                                    <i class="fa-solid fa-play me-1"></i>
                                    {{ __('courses::clients/common.start_learning') }}
                                </a>
                            @elseif ($hasCourse)
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fa-solid fa-circle-info me-1"></i>
                                    {{ __('courses::clients/common.no_lectures') }}

                                </button>
                            @else
                                <form action="{{ route('courses.create', ['locale' => app()->getLocale()]) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                                    <button class="btn btn-primary w-100 fw-semibold payment">
                                        <i class="fa-solid fa-cart-shopping me-1"></i>
                                        {{ __('courses::clients/common.buy_course') }}
                                    </button>
                                </form>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <link href="https://vjs.zencdn.net/8.23.4/video-js.css" rel="stylesheet" />
    <style>
        .teacher-mini-badges,
        .teacher-inline-badges {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .teacher-mini-badge,
        .teacher-inline-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid transparent;
            line-height: 1;
        }

        .teacher-mini-badge::before,
        .teacher-inline-badge::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 11px;
        }

        .teacher-mini-badge--verified,
        .teacher-inline-badge--verified {
            background: rgba(37, 99, 235, 0.1);
            color: #1d4ed8;
            border-color: rgba(37, 99, 235, 0.18);
        }

        .teacher-mini-badge--verified::before,
        .teacher-inline-badge--verified::before {
            content: "\f058";
        }

        .teacher-mini-badge--premium,
        .teacher-inline-badge--premium {
            background: rgba(245, 158, 11, 0.12);
            color: #b45309;
            border-color: rgba(245, 158, 11, 0.18);
        }

        .teacher-mini-badge--premium::before,
        .teacher-inline-badge--premium::before {
            content: "\f005";
        }

        .teacher-inline-badges {
            margin-left: 8px;
            vertical-align: middle;
        }

        /* ✅ Redesigned Rating & Review Styles */
        .course-rating-section { border-color: #f1f5f9 !important; }
        .rating-stars-large .fa-star, .rating-stars-large .fa-star-half-stroke { filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.2)); }
        .progress-bar { transition: width 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .icon-circle { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; flex-shrink: 0; }
        .alert-success-soft { background-color: #f0fdf4; color: #15803d; }
        .alert-info-soft { background-color: #f0f9ff; color: #0369a1; }
        .alert-warning-soft { background-color: #fffbeb; color: #a16207; }
        .comment-card-modern, .reply-card-modern { transition: all 0.2s ease; }
        .comment-card-modern:hover { border-color: #cbd5e1 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important; }
        .reply-line::after { content: ''; position: absolute; left: -24px; top: 50%; width: 12px; height: 2px; background-color: #f1f5f9; }
        .bg-primary-subtle\/5 { background-color: rgba(37, 99, 235, 0.05); }

        .teacher-public-rating__picker {
            position: relative;
            user-select: none;
            padding: 10px 0;
        }

        .teacher-public-rating__track {
            position: relative;
            display: inline-block;
            cursor: pointer;
            font-size: 2rem;
            line-height: 1;
        }

        .teacher-public-rating__stars-base {
            position: relative;
            color: #e2e8f0;
            display: flex;
            gap: 4px;
            z-index: 1;
            pointer-events: none;
        }

        .teacher-public-rating__stars-fill {
            position: absolute;
            top: 0;
            left: 0;
            white-space: nowrap;
            overflow: hidden;
            color: #f59e0b;
            display: flex;
            gap: 4px;
            transition: width 0.1s ease;
            pointer-events: none;
            z-index: 2;
        }

        .teacher-public-rating__hotspots {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            z-index: 10;
        }

        .teacher-public-rating__hotspot {
            flex: 1;
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            outline: none !important;
            cursor: pointer;
            z-index: 11;
        }

        html[data-theme="dark"] .teacher-public-rating__stars-base {
            color: #334155;
        }

        html[data-theme="dark"] .course-rating-section,
        html[data-theme="dark"] .comment-card-modern,
        html[data-theme="dark"] .reply-card-modern,
        html[data-theme="dark"] .rating-submission-card,
        html[data-theme="dark"] .user-rating-result,
        html[data-theme="dark"] .purchase-required-notice,
        html[data-theme="dark"] .login-required-notice,
        html[data-theme="dark"] .empty-reviews-state {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9;
        }

        html[data-theme="dark"] .instructor-box {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9;
        }

        html[data-theme="dark"] .instructor-box h5,
        html[data-theme="dark"] .instructor-box h6,
        html[data-theme="dark"] .instructor-box .fw-bold {
            color: #f1f5f9 !important;
        }

        html[data-theme="dark"] .instructor-box .text-dark {
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .comment-submission-card {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        html[data-theme="dark"] .comment-submission-card textarea {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9;
        }

        html[data-theme="dark"] .icon-circle,
        html[data-theme="dark"] .icon-box {
            background-color: #334155 !important;
        }

        html[data-theme="dark"] .alert-info-soft { background-color: rgba(3, 105, 161, 0.1) !important; color: #7dd3fc !important; }
        html[data-theme="dark"] .alert-warning-soft { background-color: rgba(161, 98, 7, 0.1) !important; color: #fcd34d !important; }
        html[data-theme="dark"] .alert-success-soft { background-color: rgba(21, 128, 61, 0.1) !important; color: #86efac !important; }
        html[data-theme="dark"] .bg-light { background-color: #0f172a !important; }
        html[data-theme="dark"] .bg-light\/50 { background-color: rgba(15, 23, 42, 0.5) !important; }
        html[data-theme="dark"] .rating-submission-card { background: linear-gradient(135deg, rgba(30, 41, 59, 0.5) 0%, rgba(15, 23, 42, 0.5) 100%) !important; }
        html[data-theme="dark"] .text-secondary { color: #94a3b8 !important; }
        html[data-theme="dark"] .progress { background-color: #334155 !important; }

        @media (max-width: 768px) {
            .replies-list, .admin-reply-box { margin-left: 20px !important; }
            .reply-line { display: none; }
        }

        /* Coming Soon Styles */
        .coming-soon-wrapper {
            background: linear-gradient(145deg, #fffcf0, #fff9db) !important;
        }
        .countdown-timer .time-item {
            background: #fff;
            padding: 8px;
            border-radius: 8px;
            min-width: 50px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .countdown-timer .time-item span {
            display: block;
            font-size: 1.25rem;
            font-weight: 800;
            color: #d97706;
            line-height: 1;
        }
        .countdown-timer .time-item small {
            font-size: 10px;
            text-transform: uppercase;
            color: #92400e;
            font-weight: 700;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://vjs.zencdn.net/8.23.4/video.min.js"></script>
    <script src="{{ asset('backend/plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const getCommentsWrap = () => document.getElementById('course-comments-wrap');

            const initCommentEditors = (scope = document) => {
                if (typeof window.CKEDITOR === 'undefined') return;
                scope.querySelectorAll('textarea[data-rich-editor]').forEach((textarea) => {
                    if (!textarea.id) textarea.id = 'editor-' + Math.random().toString(36).substr(2, 9);
                    if (window.CKEDITOR.instances[textarea.id]) {
                        window.CKEDITOR.instances[textarea.id].destroy(true);
                    }
                    window.CKEDITOR.replace(textarea.id, { 
                        height: 120, 
                        removePlugins: 'elementspath,resize', 
                        toolbar: [['Bold', 'Italic', 'Underline', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink']] 
                    });
                });
            };

            const syncCommentEditors = (scope = document) => {
                if (typeof window.CKEDITOR === 'undefined') return;
                scope.querySelectorAll('textarea[data-rich-editor]').forEach((textarea) => {
                    const editor = textarea.id ? window.CKEDITOR.instances[textarea.id] : null;
                    if (editor) editor.updateElement();
                });
            };

            const destroyCommentEditors = (scope = document) => {
                if (typeof window.CKEDITOR === 'undefined') return;
                scope.querySelectorAll('textarea[data-rich-editor]').forEach((textarea) => {
                    const editor = textarea.id ? window.CKEDITOR.instances[textarea.id] : null;
                    if (editor) editor.destroy(true);
                });
            };

            // --- Enhanced Rating Interaction (Aligned with Teacher Profile) ---
            const initCourseRatingPicker = () => {
                const form = document.querySelector('[data-course-rating-form]');
                if (!form) return;

                const track = form.querySelector('[data-rating-track]');
                const fill = form.querySelector('[data-rating-fill]');
                const input = form.querySelector('[data-rating-input]');
                const label = form.querySelector('[data-rating-current-label]');
                const options = form.querySelectorAll('[data-rating-option]');
                const submitBtn = form.querySelector('button[type="submit"]');

                let selectedRating = input.value ? parseFloat(input.value) : null;

                const updateStars = (val) => {
                    if (fill) fill.style.width = (val / 5 * 100) + '%';
                };

                const updateLabel = (val, isSelected = false) => {
                    if (!label) return;
                    if (val > 0) {
                        const text = isSelected 
                            ? @js(__('courses::clients/common.rating_selected_label')) 
                            : @js(__('courses::clients/common.rating_hint_label'));
                        label.textContent = `${text} ${val} sao`;
                        label.classList.remove('text-warning');
                        label.classList.add('text-primary', 'fw-bold');
                    } else {
                        label.textContent = @js(__('courses::clients/common.rating_hint_label'));
                        label.classList.remove('text-primary', 'fw-bold');
                        label.classList.add('text-warning');
                    }
                };

                options.forEach(opt => {
                    opt.addEventListener('mouseenter', () => {
                        updateStars(parseFloat(opt.dataset.value));
                    });
                    
                    opt.addEventListener('click', () => {
                        selectedRating = parseFloat(opt.dataset.value);
                        input.value = selectedRating;
                        updateStars(selectedRating);
                        updateLabel(selectedRating, true);
                    });
                });

                if (track) {
                    track.addEventListener('mouseleave', () => {
                        updateStars(selectedRating || 0);
                    });
                }
            };

            // Initial call
            initCourseRatingPicker();
            
            // Re-init on AJAX success
            const reinitAll = (scope) => {
                initCommentEditors(scope);
                initCourseRatingPicker();
            };

            const handleVisibilityToggle = async (btn) => {
                try {
                    const response = await fetch(btn.dataset.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (result.success) {
                        const wrap = getCommentsWrap();
                        if (wrap) {
                            destroyCommentEditors(wrap);
                            wrap.innerHTML = result.html;
                            reinitAll(wrap);
                        }
                    }
                } catch (err) { console.error(err); }
            };

            document.addEventListener('click', (e) => {
                const toggleBtn = e.target.closest('[data-visibility-form]');
                if (toggleBtn) {
                    e.preventDefault();
                    handleVisibilityToggle(toggleBtn);
                }
            });

            document.addEventListener('submit', async (e) => {
                const form = e.target.closest('[data-comment-form], [data-course-rating-form]');
                if (!form) return;
                
                e.preventDefault();

                const isRatingForm = form.matches('[data-course-rating-form]');
                if (isRatingForm) {
                    const rating = form.querySelector('[data-rating-input]')?.value;
                    if (!rating || parseFloat(rating) < 0.5) {
                        alert(@js(__('courses::clients/common.rating_invalid') ?? 'Vui lòng chọn mức đánh giá.'));
                        return;
                    }
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalContent = submitBtn?.innerHTML;
                
                if (form.matches('[data-comment-form]')) {
                    syncCommentEditors(form);
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${isRatingForm ? @js(__('courses::clients/common.rating_submit')) : @js(__('courses::clients/common.comment_submitting'))}`;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': token, 
                            'X-Requested-With': 'XMLHttpRequest', 
                            'Accept': 'application/json' 
                        },
                        body: new FormData(form)
                    });
                    const result = await response.json();

                    if (!result.success) {
                        alert(result.message || 'Error');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalContent;
                        }
                        return;
                    }

                    const wrap = getCommentsWrap();
                    if (wrap) {
                        destroyCommentEditors(wrap);
                        wrap.innerHTML = result.html;
                        reinitAll(wrap);
                    }

                    if (isRatingForm) {
                        // Refresh page to update top stats after rating success
                        setTimeout(() => window.location.reload(), 1500);
                    }
                } catch (err) { 
                    console.error(err); 
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                    }
                }
            });

            initCommentEditors();
            
            // Countdown Timer
            const initCountdown = () => {
                const timerEl = document.querySelector('.countdown-timer');
                if (!timerEl) return;
                const targetDate = new Date(timerEl.dataset.time).getTime();
                const update = () => {
                    const now = new Date().getTime();
                    const diff = targetDate - now;
                    if (diff <= 0) { window.location.reload(); return; }
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    timerEl.querySelector('.days').innerText = String(days).padStart(2, '0');
                    timerEl.querySelector('.hours').innerText = String(hours).padStart(2, '0');
                    timerEl.querySelector('.minutes').innerText = String(minutes).padStart(2, '0');
                    timerEl.querySelector('.seconds').innerText = String(seconds).padStart(2, '0');
                };
                update();
                setInterval(update, 1000);
            };
            initCountdown();
        });
    </script>
@endsection
