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

                                @if ($course->is_on_flash_sale)
                                    <div class="flash-sale-timer-wrapper mb-3 p-2 rounded border border-danger bg-danger bg-opacity-10">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="flash-sale-badge">FLASH SALE</div>
                                            <span class="small text-danger fw-bold">{{ __('courses::clients/common.ending_in') ?? 'Kết thúc sau:' }}</span>
                                        </div>
                                        <div class="countdown-timer d-flex justify-content-center gap-1" 
                                             data-time="{{ $course->end_at->toIso8601String() }}">
                                            <div class="time-item"><span class="days">00</span><small>d</small></div>
                                            <div class="time-item"><span class="hours">00</span><small>h</small></div>
                                            <div class="time-item"><span class="minutes">00</span><small>m</small></div>
                                            <div class="time-item"><span class="seconds">00</span><small>s</small></div>
                                        </div>
                                    </div>
                                @endif
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

                                    <button class="btn btn-primary w-100 fw-semibold payment js-buy-btn">
                                        <span class="btn-text">
                                            <i class="fa-solid fa-cart-shopping me-1"></i>
                                            {{ __('courses::clients/common.buy_course') }}
                                        </span>
                                        <span class="btn-loader d-none">
                                            <i class="fas fa-spinner fa-spin me-1"></i>
                                            {{ __('common.processing') }}...
                                        </span>
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

        .flash-sale-badge {
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 0.05em;
        }

        .countdown-timer .time-item {
            background: #fff;
            padding: 4px 8px;
            border-radius: 6px;
            min-width: 45px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .countdown-timer .time-item span {
            display: block;
            font-weight: 800;
            font-size: 16px;
            line-height: 1;
            color: #1e293b;
        }

        .countdown-timer .time-item small {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
        }

        html[data-theme="dark"] .countdown-timer .time-item {
            background: #334155;
        }

        html[data-theme="dark"] .countdown-timer .time-item span {
            color: #f1f5f9;
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

        @keyframes rating-shake {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-6px); }
            40%       { transform: translateX(6px); }
            60%       { transform: translateX(-4px); }
            80%       { transform: translateX(4px); }
        }
        .rating-shake { animation: rating-shake .45s ease; }


        html[data-theme="dark"] .comment-card-modern,
        html[data-theme="dark"] .reply-card-modern,
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

            // ─── Star Picker ─────────────────────────────────────────────────
            const initCourseRatingPicker = () => {
                const form = document.querySelector('[data-course-rating-form]');
                if (!form) return;

                const input   = form.querySelector('[data-rating-input]');
                const label   = form.querySelector('[data-rating-current-label]');
                const spots   = form.querySelectorAll('[data-rating-option]');
                const icons   = form.querySelectorAll('[data-star-icon]');  // 5 <i> elements
                const errBox  = document.getElementById('rp-error-box');
                const errTxt  = document.getElementById('rp-error-text');

                const LABELS = {
                    0.5:'😕 Rất tệ', 1:'😕 Rất tệ',
                    1.5:'😐 Tệ',     2:'😐 Tệ',
                    2.5:'🙂 Bình thường', 3:'🙂 Bình thường',
                    3.5:'😊 Tốt',    4:'😊 Tốt',
                    4.5:'🤩 Tuyệt vời', 5:'🤩 Xuất sắc!',
                };
                let chosen = input?.value ? parseFloat(input.value) : null;

                // Use inline style.color — beats any CSS specificity conflict
                const FILLED_COLOR = '#f59e0b';
                const EMPTY_COLOR  = document.documentElement.getAttribute('data-theme') === 'dark' ? '#475569' : '#cbd5e1';

                const paintStars = (val) => {
                    icons.forEach((icon, idx) => {
                        const n = idx + 1;
                        const empty = document.documentElement.getAttribute('data-theme') === 'dark' ? '#475569' : '#cbd5e1';
                        if (val >= n) {
                            icon.className = 'rp-star-icon fa-solid fa-star';
                            icon.style.color = FILLED_COLOR;
                        } else if (val >= n - 0.5) {
                            icon.className = 'rp-star-icon fa-solid fa-star-half-stroke';
                            icon.style.color = FILLED_COLOR;
                        } else {
                            icon.className = 'rp-star-icon fa-regular fa-star';
                            icon.style.color = empty;
                        }
                    });
                };

                const setLabel = (val, locked=false) => {
                    if (!label) return;
                    if (val > 0) {
                        label.textContent = locked
                            ? `✅ Đã chọn ${val} sao – ${LABELS[val] || val + ' sao'}`
                            : `${val} sao – ${LABELS[val] || ''}`;
                        label.classList.add('is-active');
                    } else {
                        label.textContent = @js(__('courses::clients/common.rating_hint_label'));
                        label.classList.remove('is-active');
                    }
                };

                const showErr = (msg) => {
                    if (errBox && errTxt) { errTxt.textContent = msg; errBox.classList.remove('d-none'); }
                    else if (window.showMessage) window.showMessage(msg, 'error');
                };
                const hideErr = () => { if (errBox) errBox.classList.add('d-none'); };

                // Hover
                spots.forEach(s => {
                    s.addEventListener('mouseenter', () => {
                        const v = parseFloat(s.dataset.value);
                        paintStars(v);
                        setLabel(v);
                    });
                    // Click → lock selection
                    s.addEventListener('click', () => {
                        chosen = parseFloat(s.dataset.value);
                        if (input) input.value = chosen;
                        paintStars(chosen);
                        setLabel(chosen, true);
                        hideErr();
                    });
                });

                // Mouse leave picker → restore chosen (or reset)
                const picker = form.querySelector('[data-rating-track]');
                if (picker) {
                    picker.addEventListener('mouseleave', () => {
                        paintStars(chosen || 0);
                        chosen ? setLabel(chosen, true) : setLabel(0);
                    });
                }

                // Submit validation (capture phase — runs before global handler)
                form.addEventListener('submit', (e) => {
                    const v = input ? parseFloat(input.value) : 0;
                    if (!v || v < 0.5 || v > 5) {
                        e.stopImmediatePropagation(); e.preventDefault();
                        showErr(@js(__('courses::clients/common.rating_invalid')));
                        const p = form.querySelector('.rp-picker');
                        if (p) { p.classList.add('rp-shake'); setTimeout(() => p.classList.remove('rp-shake'), 500); }
                    }
                }, true);
            };

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

                // The rating form has its own inline validator (runs first via capture phase).
                // If input is still empty after that, bail out silently (error already shown).
                const isRatingForm = form.matches('[data-course-rating-form]');
                if (isRatingForm) {
                    const ratingVal = parseFloat(form.querySelector('[data-rating-input]')?.value || '0');
                    if (!ratingVal || ratingVal < 0.5) {
                        e.preventDefault();
                        return;
                    }
                }

                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalContent = submitBtn?.innerHTML;

                if (form.matches('[data-comment-form]')) {
                    syncCommentEditors(form);
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${
                        isRatingForm
                            ? (@js(__('courses::clients/common.rating_submit') ?? 'Đang lưu...'))
                            : (@js(__('courses::clients/common.comment_submitting') ?? 'Đang gửi...'))
                    }`;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: new FormData(form),
                    });
                    const result = await response.json();

                    if (!result.success) {
                        const msg = result.message || @js(__('courses::clients/common.rating_submit_error') ?? 'Có lỗi xảy ra.');
                        if (window.showMessage) window.showMessage(msg, 'error');

                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalContent;
                        }
                        return;
                    }

                    if (window.showMessage) {
                        window.showMessage(
                            result.message || @js(__('courses::clients/common.rating_success') ?? 'Thành công!'),
                            'success'
                        );
                    }

                    if (isRatingForm) {
                        // Only swap the rating panel, not the whole comments section
                        const ratingRoot = document.getElementById('course-rating-panel-root');
                        if (ratingRoot && result.html) {
                            ratingRoot.outerHTML = result.html;
                        } else {
                            // Fallback: refresh full wrap
                            const wrap = getCommentsWrap();
                            if (wrap) {
                                destroyCommentEditors(wrap);
                                wrap.innerHTML = result.html;
                                reinitAll(wrap);
                            }
                        }
                    } else {
                        // Comment: replace full wrap
                        const wrap = getCommentsWrap();
                        if (wrap) {
                            destroyCommentEditors(wrap);
                            wrap.innerHTML = result.html;
                            reinitAll(wrap);
                        }
                    }
                } catch (err) {
                    console.error(err);
                    if (window.showMessage) {
                        window.showMessage(@js(__('courses::clients/common.rating_submit_error') ?? 'Có lỗi kết nối.'), 'error');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                    }
                }
            });

            initCommentEditors();

            // Buy button loader
            const buyBtn = document.querySelector('.js-buy-btn');
            if (buyBtn) {
                buyBtn.addEventListener('click', () => {
                    const text = buyBtn.querySelector('.btn-text');
                    const loader = buyBtn.querySelector('.btn-loader');
                    if (text && loader) {
                        buyBtn.disabled = true;
                        text.classList.add('d-none');
                        loader.classList.remove('d-none');
                        buyBtn.closest('form').submit();
                    }
                });
            }
        });
    </script>
@endsection