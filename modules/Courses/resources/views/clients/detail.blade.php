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

        .course-comments-shell {
            border-radius: 18px;
        }

        .course-comments-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        .course-comment-form textarea {
            border-radius: 14px;
            resize: vertical;
            min-height: 88px;
        }

        .course-rating-panel {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 16px;
            background: #fffaf0;
        }

        .course-rating-panel__summary {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: start;
        }

        .course-rating-panel__title {
            font-weight: 800;
            color: #111827;
            margin-bottom: 6px;
        }

        .course-rating-panel__stars {
            color: #f59e0b;
            display: flex;
            gap: 4px;
        }

        .course-rating-panel__score {
            text-align: right;
        }

        .course-rating-panel__score strong {
            display: block;
            font-size: 1.75rem;
            color: #111827;
            line-height: 1;
        }

        .course-rating-picker {
            display: block;
        }

        .course-rating-picker__track {
            position: relative;
            width: min(100%, 270px);
            padding: 0.9rem 1rem;
            border-radius: 18px;
            border: 1px solid rgba(245, 158, 11, 0.24);
            background: linear-gradient(180deg, #ffffff, #fff7ed);
            box-shadow: 0 10px 24px rgba(245, 158, 11, 0.1);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        }

        .course-rating-picker__track:hover,
        .course-rating-picker__track.is-preview {
            transform: translateY(-2px) scale(1.03);
            border-color: rgba(245, 158, 11, 0.45);
            background: linear-gradient(180deg, #fff7ed, #ffedd5);
            box-shadow: 0 14px 24px rgba(245, 158, 11, 0.16);
        }

        .course-rating-picker__track.is-active {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 16px 28px rgba(249, 115, 22, 0.28);
        }

        .course-rating-picker__stars-base,
        .course-rating-picker__stars-fill {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 1.75rem;
            line-height: 1;
            white-space: nowrap;
        }

        .course-rating-picker__stars-base {
            color: #cbd5e1;
        }

        .course-rating-picker__stars-fill {
            position: absolute;
            inset: 0.9rem auto auto 1rem;
            overflow: hidden;
            color: #f59e0b;
            pointer-events: none;
            transition: width 0.16s ease;
        }

        .course-rating-picker__track.is-active .course-rating-picker__stars-base {
            color: rgba(255, 255, 255, 0.32);
        }

        .course-rating-picker__track.is-active .course-rating-picker__stars-fill {
            color: #fff8e1;
        }

        .course-rating-picker__hotspots {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            z-index: 2;
        }

        .course-rating-picker__hotspot {
            border: 0;
            background: transparent;
            padding: 0;
            margin: 0;
            cursor: pointer;
        }

        .course-rating-picker__hotspot:focus-visible {
            outline: 2px solid rgba(249, 115, 22, 0.6);
            outline-offset: -3px;
        }

        .admin-reply-form {
            margin-left: 72px;
            margin-top: 12px;
        }

        .admin-reply-form textarea {
            min-height: 68px;
            background: #f8fafc;
        }

        .comment-thread + .comment-thread {
            margin-top: 18px;
        }

        .comment-card {
            display: flex;
            gap: 14px;
            padding: 16px;
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .comment-card.is-student {
            border-left: 4px solid #22c55e;
        }

        .comment-card.is-admin {
            border-left: 4px solid #2563eb;
            background: #f8fbff;
        }

        .reply-card {
            margin-top: 10px;
        }

        .comment-replies {
            margin-left: 72px;
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .comment-avatar {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .comment-main {
            flex: 1;
            min-width: 0;
        }

        .comment-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .comment-time {
            font-size: 12px;
            color: #64748b;
        }

        .comment-content {
            white-space: pre-wrap;
            line-height: 1.65;
            color: #0f172a;
        }

        .comment-actions {
            margin-top: 12px;
        }

        .comment-flag-note {
            margin-top: 8px;
            font-size: 12px;
            color: #b91c1c;
        }

        .is-hidden-comment {
            opacity: 0.72;
        }

        .empty-comments {
            padding: 18px;
            text-align: center;
            border-radius: 16px;
            background: #f8fafc;
            color: #64748b;
            border: 1px dashed #cbd5e1;
        }

        html[data-theme="dark"] .course-comments-head {
            border-bottom-color: rgba(148, 163, 184, 0.18);
        }

        html[data-theme="dark"] .comment-card {
            background: #0f1b2d;
            border-color: rgba(148, 163, 184, 0.18);
            box-shadow: 0 12px 28px rgba(2, 6, 23, 0.24);
        }

        html[data-theme="dark"] .comment-card.is-admin {
            background: linear-gradient(180deg, #132238 0%, #0f1b2d 100%);
        }

        html[data-theme="dark"] .comment-content {
            color: #d6e3f3;
        }

        html[data-theme="dark"] .empty-comments {
            background: rgba(96, 165, 250, 0.08);
            color: #9fb4cb;
            border-color: rgba(148, 163, 184, 0.22);
        }

        html[data-theme="dark"] .admin-reply-form textarea {
            background: #091321;
        }

        @media (max-width: 768px) {
            .comment-replies,
            .admin-reply-form {
                margin-left: 0;
            }

            .course-profile {
                margin-top: 24px;
            }

            .comment-card {
                padding: 14px;
            }
        }

        @media (max-width: 575.98px) {
            .course-comments-head,
            .comment-meta {
                gap: 6px;
            }

            .course-comment-form .d-flex.justify-content-between {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px;
            }

            .course-comment-form .btn,
            .admin-reply-form .btn {
                width: 100%;
            }

            .comment-card {
                gap: 10px;
                padding: 12px;
            }

            .comment-avatar {
                width: 38px;
                height: 38px;
                border-radius: 12px;
            }
        }
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
        window.addEventListener('DOMContentLoaded', () => {
            const wrap = document.getElementById('course-comments-wrap');
            let editorIndex = 0;

            if (!wrap) {
                return;
            }

            const token = document.querySelector('meta[name="csrf_token"]')?.getAttribute('content') || '';

            const initCommentEditors = () => {
                if (typeof window.CKEDITOR === 'undefined') {
                    return;
                }

                wrap.querySelectorAll('textarea[data-rich-editor]').forEach((textarea) => {
                    if (!textarea.id) {
                        editorIndex += 1;
                        textarea.id = `course-comment-editor-${editorIndex}`;
                    }

                    if (window.CKEDITOR.instances[textarea.id]) {
                        return;
                    }

                    window.CKEDITOR.replace(textarea.id, {
                        height: 120,
                        resize_enabled: false,
                        removePlugins: 'elementspath',
                        toolbar: [
                            ['Bold', 'Italic', 'Underline', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink'],
                        ],
                    });
                });
            };

            const syncCommentEditors = (scope) => {
                if (typeof window.CKEDITOR === 'undefined') {
                    return;
                }

                scope.querySelectorAll('textarea[data-rich-editor]').forEach((textarea) => {
                    const editor = textarea.id ? window.CKEDITOR.instances[textarea.id] : null;

                    if (editor) {
                        editor.updateElement();
                    }
                });
            };

            const destroyCommentEditors = () => {
                if (typeof window.CKEDITOR === 'undefined') {
                    return;
                }

                wrap.querySelectorAll('textarea[data-rich-editor]').forEach((textarea) => {
                    const editor = textarea.id ? window.CKEDITOR.instances[textarea.id] : null;

                    if (editor) {
                        editor.destroy(true);
                    }
                });
            };

            const submitAsyncForm = async (form) => {
                const submitButton = form.querySelector('button[type="submit"]');
                const originalText = submitButton ? submitButton.innerText : '';

                syncCommentEditors(form);

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerText = @js(__('courses::clients/common.comment_submitting'));
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

                    if (!response.ok || !result.success) {
                        alert(result.message || @js(__('courses::clients/common.comment_submit_error')));
                        return;
                    }

                    destroyCommentEditors();
                    wrap.innerHTML = result.html;
                    initCommentEditors();
                } catch (error) {
                    alert(@js(__('courses::clients/common.comment_submit_error')));
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerText = originalText;
                    }
                }
            };

            const updateRatingVisual = (form, value, state = 'idle') => {
                if (!form || !wrap.contains(form)) {
                    return;
                }

                const track = form.querySelector('[data-rating-track]');
                const fill = form.querySelector('[data-rating-fill]');
                const numericValue = Math.max(0, Math.min(Number(value || 0), 5));

                if (fill) {
                    fill.style.width = `${numericValue * 20}%`;
                }

                if (track) {
                    track.classList.toggle('is-preview', state === 'preview' && numericValue > 0);
                    track.classList.toggle('is-active', numericValue > 0);
                }
            };

            document.addEventListener('submit', (event) => {
                const form = event.target instanceof HTMLFormElement
                    ? event.target
                    : event.target?.closest?.('[data-comment-form]');

                if (!form || !wrap.contains(form) || !form.matches('[data-comment-form]')) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                submitAsyncForm(form);
            }, true);

            wrap.addEventListener('click', async (event) => {
                const ratingOption = event.target.closest('[data-rating-option]');
                if (ratingOption) {
                    const ratingForm = ratingOption.closest('form');
                    if (ratingForm && wrap.contains(ratingForm)) {
                        ratingForm.querySelector('[data-rating-input]').value = ratingOption.dataset.value;
                        updateRatingVisual(ratingForm, ratingOption.dataset.value, 'selected');

                        const currentLabel = ratingForm.querySelector('[data-rating-current-label]');
                        if (currentLabel) {
                            currentLabel.textContent = @js(__('courses::clients/common.rating_selected_label')) + ' ' + ratingOption.dataset.value;
                        }
                    }

                    return;
                }

                const toggleButton = event.target.closest('[data-visibility-form]');

                if (!toggleButton) {
                    return;
                }

                event.preventDefault();

                try {
                    const response = await fetch(toggleButton.dataset.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        alert(result.message || @js(__('courses::clients/common.comment_toggle_error')));
                        return;
                    }

                    destroyCommentEditors();
                    wrap.innerHTML = result.html;
                    initCommentEditors();
                } catch (error) {
                    alert(@js(__('courses::clients/common.comment_toggle_error')));
                }
            });

            wrap.addEventListener('mouseover', (event) => {
                const ratingOption = event.target.closest('[data-rating-option]');
                if (!ratingOption) {
                    return;
                }

                const ratingForm = ratingOption.closest('form');
                if (!ratingForm || !wrap.contains(ratingForm)) {
                    return;
                }

                updateRatingVisual(ratingForm, ratingOption.dataset.value, 'preview');

                const currentLabel = ratingForm.querySelector('[data-rating-current-label]');
                if (currentLabel) {
                    currentLabel.textContent = @js(__('courses::clients/common.rating_selected_label')) + ' ' + ratingOption.dataset.value;
                }
            });

            wrap.addEventListener('mouseout', (event) => {
                const ratingForm = event.target.closest('[data-course-rating-form]');
                if (!ratingForm || !wrap.contains(ratingForm)) {
                    return;
                }

                if (event.relatedTarget && ratingForm.contains(event.relatedTarget)) {
                    return;
                }

                const selectedValue = ratingForm.querySelector('[data-rating-input]')?.value || '';
                updateRatingVisual(ratingForm, selectedValue, selectedValue ? 'selected' : 'idle');

                const currentLabel = ratingForm.querySelector('[data-rating-current-label]');
                if (currentLabel) {
                    currentLabel.textContent = selectedValue
                        ? @js(__('courses::clients/common.rating_selected_label')) + ' ' + selectedValue
                        : @js(__('courses::clients/common.rating_hint'));
                }
            });

            wrap.addEventListener('focusin', (event) => {
                const ratingOption = event.target.closest('[data-rating-option]');
                if (!ratingOption) {
                    return;
                }

                const ratingForm = ratingOption.closest('form');
                if (!ratingForm || !wrap.contains(ratingForm)) {
                    return;
                }

                updateRatingVisual(ratingForm, ratingOption.dataset.value, 'preview');
            });

            wrap.addEventListener('focusout', (event) => {
                const ratingForm = event.target.closest('[data-course-rating-form]');
                if (!ratingForm || !wrap.contains(ratingForm)) {
                    return;
                }

                if (event.relatedTarget && ratingForm.contains(event.relatedTarget)) {
                    return;
                }

                const selectedValue = ratingForm.querySelector('[data-rating-input]')?.value || '';
                updateRatingVisual(ratingForm, selectedValue, selectedValue ? 'selected' : 'idle');
            });

            wrap.addEventListener('submit', async (event) => {
                const form = event.target.closest('[data-course-rating-form]');
                if (!form || !wrap.contains(form)) {
                    return;
                }

                event.preventDefault();

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

                    if (!response.ok || !result.success) {
                        alert(result.message || @js(__('courses::clients/common.rating_submit_error')));
                        return;
                    }

                    const ratingWrap = document.getElementById('course-rating-wrap');
                    if (ratingWrap && result.html) {
                        ratingWrap.innerHTML = result.html;
                    }
                } catch (error) {
                    alert(@js(__('courses::clients/common.rating_submit_error')));
                }
            });

            initCommentEditors();
            const countdown = () => {
                const timerEl = document.querySelector('.countdown-timer');
                if (!timerEl) return;

                const targetDate = new Date(timerEl.dataset.time).getTime();
                const update = () => {
                    const now = new Date().getTime();
                    const diff = targetDate - now;

                    if (diff <= 0) {
                        window.location.reload();
                        return;
                    }

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

            countdown();
        });
    </script>
@endsection
