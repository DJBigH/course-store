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
                                    <img src="{{ $course->teacher->image }}" alt="{{ $course->teacher->name_locale }}"
                                        class="rounded-circle">
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1 small">{{ __('courses::clients/common.instructor') }}</p>

                                    <h5 class="instructor-name mb-1 fw-semibold">
                                        <a href="/giang-vien/{{ $course->teacher->slug_locale }}"
                                            class="text-decoration-none text-dark hover-primary">
                                            {{ $course->teacher->name_locale }}
                                        </a>
                                    </h5>

                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i class="bi bi-mortarboard"></i>
                                        <span>{{ $course->teacher->exp }}
                                            {{ __('courses::clients/common.experience_years') }}</span>
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
                                    @if ($course->sale_price)
                                        <span class="text-muted text-decoration-line-through me-2">
                                            {{ moneyLocale($course->price) }}
                                        </span>
                                        <span class="fw-bold text-danger fs-5">
                                            {{ moneyLocale($course->sale_price) }}
                                        </span>
                                    @else
                                        <span class="fw-bold fs-5 text-danger">
                                            {{ moneyLocale($course->price) }}
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

                            @if ($hasCourse && $firstLesson)
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
    <style>
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

        @media (max-width: 768px) {
            .comment-replies,
            .admin-reply-form {
                margin-left: 0;
            }
        }
    </style>
@endsection

@section('scripts')
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

            initCommentEditors();
        });
    </script>
@endsection
