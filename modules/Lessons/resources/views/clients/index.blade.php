@extends('layouts.client')
@section('content')
    @include('part.clients.page_title')
    <section class="video">
        <div class="container">
            @if (session('msg') && session('msgType', 'info') !== 'success')
                <div class="alert alert-{{ session('msgType', 'info') }} mb-3">
                    {{ session('msg') }}
                </div>
            @endif
            <div class="alert alert-danger mb-3 d-none" id="lesson-toggle-error" role="alert"></div>
            <h3>{{ $lesson->name_locale }}</h3>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="video-detail">
                        @php
                            $videoUrl = trim((string) ($lesson->video?->url ?? ''));
                            $videoMeta = videoPlaybackMeta($videoUrl);
                        @endphp

                        @if ($videoMeta['type'] === 'embed' && !empty($videoMeta['url']))
                            <div class="ratio ratio-16x9">
                                <iframe src="{{ $videoMeta['url'] }}" title="Lesson video"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            </div>
                        @elseif ($videoMeta['type'] === 'file' && !empty($videoMeta['url']))
                            <video id="my-video" class="video-js" controls preload="auto" data-setup="{}">
                                <source src="{{ $videoMeta['url'] }}" type="video/mp4" />
                                <p class="vjs-no-js">{{ __('lessons::clients/common.help') }}</p>
                            </video>
                        @else
                            <div class="alert alert-warning">{{ __('lessons::clients/common.no_video') }}</div>
                        @endif
                    </div>

                    <div class="lesson-nav d-flex justify-content-between mt-4">
                        <div>
                            @if ($prevLesson && ($hasCourse || (int) $prevLesson->is_trial === 1))
                                <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $prevLesson->slug_locale]) }}"
                                    class="btn-lesson btn-prev">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>{{ __('lessons::clients/common.back') }}</span>
                                </a>
                            @endif
                        </div>

                        <div>
                            @if ($nextLesson && ($hasCourse || (int) $nextLesson->is_trial === 1))
                                <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $nextLesson->slug_locale]) }}"
                                    class="btn-lesson btn-next">
                                    <span>{{ __('lessons::clients/common.next') }}</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="col-12 col-lg-4">
                    @if ($hasCourse)
                        <div class="lesson-progress-card mb-3">
                            <div class="lesson-progress-card__head">
                                <strong>{{ __('lessons::clients/common.course_progress') }}</strong>
                                <span data-progress-percent>{{ $courseProgress['progress_percent'] }}%</span>
                            </div>
                            <div class="lesson-progress-card__bar">
                                <span data-progress-bar style="width: {{ $courseProgress['progress_percent'] }}%"></span>
                            </div>
                            <div class="lesson-progress-card__meta" data-progress-meta
                                data-progress-template="{{ __('lessons::clients/common.completed_lessons', ['completed' => '__completed__', 'total' => '__total__']) }}">
                                {{ __('lessons::clients/common.completed_lessons', [
                                    'completed' => $courseProgress['completed_lessons'],
                                    'total' => $courseProgress['total_lessons'],
                                ]) }}
                            </div>
                        </div>
                    @endif
                    <div class="nav flex">
                        <p class="lesson active">{{ __('lessons::clients/common.lesson') }}</p>
                        <p class="document">{{ __('lessons::clients/common.document') }}</p>
                    </div>
                    <div class="group">
                        <div class="accordion active title">
                            @include('lessons::clients.lesson')
                        </div>
                        <div class="document-title title">
                            @include('lessons::clients.document')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('scripts')
    <script src="https://vjs.zencdn.net/8.23.4/video.min.js"></script>
    <script>
        const myVideoEl = document.querySelector('#my-video');
        if (myVideoEl) videojs(myVideoEl);

        const lessonToggleError = document.getElementById('lesson-toggle-error');
        const progressPercentEl = document.querySelector('[data-progress-percent]');
        const progressBarEl = document.querySelector('[data-progress-bar]');
        const progressMetaEl = document.querySelector('[data-progress-meta]');
        const lessonToggleFallbackError = @json(__('lessons::clients/common.completion_error'));
        const lockedLessonList = document.querySelectorAll('.js-locked-lesson');
        const lessonPurchaseRequiredMessage = @json(__('courses::clients/common.lesson_purchase_required'));

        const setLessonToggleError = (message) => {
            if (!lessonToggleError) {
                return;
            }

            if (!message) {
                lessonToggleError.classList.add('d-none');
                lessonToggleError.textContent = '';
                return;
            }

            lessonToggleError.textContent = message;
            lessonToggleError.classList.remove('d-none');
        };

        document.querySelectorAll('.lesson-toggle-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                setLessonToggleError('');

                const button = form.querySelector('.lesson-toggle-check');
                const lessonItem = form.closest('.lesson-item');

                if (!button || !lessonItem) {
                    return;
                }

                button.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || lessonToggleFallbackError);
                    }

                    const completed = Boolean(payload.completed);
                    lessonItem.classList.toggle('is-completed', completed);
                    button.classList.toggle('is-completed', completed);
                    button.setAttribute(
                        'aria-label',
                        completed ? button.dataset.labelIncomplete : button.dataset.labelComplete
                    );
                    button.innerHTML = completed ? '<i class="fa-solid fa-check"></i>' : '';

                    if (progressPercentEl && payload.course_progress) {
                        progressPercentEl.textContent = `${payload.course_progress.progress_percent}%`;
                    }

                    if (progressBarEl && payload.course_progress) {
                        progressBarEl.style.width = `${payload.course_progress.progress_percent}%`;
                    }

                    if (progressMetaEl && payload.course_progress) {
                        const template = progressMetaEl.dataset.progressTemplate || '';
                        progressMetaEl.textContent = template
                            .replace('__completed__', payload.course_progress.completed_lessons)
                            .replace('__total__', payload.course_progress.total_lessons);
                    }
                } catch (error) {
                    setLessonToggleError(error.message || lessonToggleFallbackError);
                } finally {
                    button.disabled = false;
                }
            });
        });

        lockedLessonList.forEach((lessonLink) => {
            lessonLink.addEventListener('click', (event) => {
                event.preventDefault();
                alert(lessonLink.dataset.message || lessonPurchaseRequiredMessage);
            });
        });
    </script>
@endsection


@section('stylesheets')
    <link href="https://vjs.zencdn.net/8.23.4/video-js.css" rel="stylesheet" />
    <style>
        .group {
            /* position: relative; */
            display: block;
            gap: 20px !important;
            padding: 0px !important;
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            /* transition: all 0.35s ease; */
            height: auto;
        }

        .group:hover {
            border-color: #2563eb;
            transform: translateY(0px) !important;
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.12);
        }

        .lesson-progress-card {
            padding: 18px 20px;
            border-radius: 18px;
            border: 1px solid #dbeafe;
            background: linear-gradient(135deg, #eff6ff, #f8fbff);
        }

        .lesson-progress-card__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            color: #1e3a8a;
        }

        .lesson-progress-card__bar {
            height: 10px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(59, 130, 246, 0.14);
        }

        .lesson-progress-card__bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #2563eb, #38bdf8);
        }

        .lesson-progress-card__meta {
            margin-top: 10px;
            font-size: 14px;
            color: #475569;
        }

        .lesson-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            color: #94a3b8;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .lesson-toggle-form {
            display: inline-flex;
            align-items: center;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .lesson-toggle-check {
            width: 18px;
            height: 18px;
            min-width: 18px;
            min-height: 18px;
            flex: 0 0 18px;
            box-sizing: border-box;
            border: 2px solid rgba(148, 163, 184, 0.85);
            border-radius: 3px;
            background: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            transition: all 0.2s ease;
            padding: 0;
        }

        .lesson-toggle-check:hover {
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.12);
        }

        .lesson-toggle-check.is-completed {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .lesson-toggle-check i {
            font-size: 11px;
            line-height: 1;
        }

        .lesson-status--youtube {
            color: #ef4444;
        }

        .accordion .accordion-detail .card-accordion {
            padding: 0;
            border-top: 1px solid rgba(148, 163, 184, 0.14);
        }

        .accordion .accordion-detail .card-accordion:last-child {
            border-bottom: 0;
        }

        .card-accordion {
            padding: 0;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .card-accordion.active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(191, 219, 254, 0.28));
            border-left: 3px solid #3b82f6;
        }

        .card-accordion:hover {
            background: rgba(59, 130, 246, 0.05);
        }

        .lesson-item {
            width: 100%;
        }

        .accordion .accordion-detail .card-accordion>div.lesson-item {
            display: block;
            cursor: default;
        }

        .lesson-left {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            min-width: 0;
            padding: 14px 12px;
        }

        .accordion .accordion-detail .card-accordion>div .lesson-left {
            display: grid;
            grid-template-columns: 20px 22px minmax(0, 1fr) auto;
            align-items: center;
            column-gap: 10px;
        }

        .accordion .accordion-detail .card-accordion>div .lesson-left.lesson-left--readonly {
            grid-template-columns: 22px minmax(0, 1fr) auto;
        }

        .accordion .accordion-detail .card-accordion>div .lesson-left.lesson-left--readonly.lesson-left--trial {
            grid-template-columns: 22px minmax(0, 1fr) auto auto;
        }

        .lesson-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            flex: 1 1 auto;
            text-align: left;
            padding-left: 4px;
        }

        .accordion .accordion-detail .card-accordion>div .lesson-text {
            display: flex;
            flex: 1 1 auto;
            min-width: 0;
            text-align: left;
            justify-self: stretch;
        }

        .lesson-title {
            display: block;
            min-width: 0;
            line-height: 1.45;
            color: #1e293b;
            word-break: break-word;
            text-align: left;
        }

        .lesson-time {
            flex: 0 0 auto;
            min-width: 64px;
            text-align: right;
            font-size: 13px;
            line-height: 1.45;
            color: #64748b;
            white-space: nowrap;
        }

        .accordion .accordion-detail .card-accordion>div .lesson-time {
            display: block;
            flex: 0 0 auto;
            text-align: right;
        }

        .lesson-item.is-completed .lesson-title,
        .lesson-item.is-completed .lesson-time {
            color: rgba(51, 65, 85, 0.7);
            text-decoration: line-through;
            text-decoration-thickness: 2px;
            text-decoration-color: rgba(51, 65, 85, 0.42);
            text-decoration-skip-ink: none;
        }

        .lesson-item.is-completed .lesson-time {
            color: rgba(100, 116, 139, 0.82);
        }

        .card-accordion.active .lesson-title {
            color: #0f172a;
        }

        .card-accordion.active .lesson-time {
            color: #1d4ed8;
        }

        html[data-theme="dark"] .lesson-progress-card {
            border-color: rgba(96, 165, 250, 0.22);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.94));
        }

        html[data-theme="dark"] .lesson-progress-card__head {
            color: #dbeafe;
        }

        html[data-theme="dark"] .lesson-progress-card__meta {
            color: #94a3b8;
        }

        html[data-theme="dark"] .card-accordion.active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.14), rgba(30, 41, 59, 0.04));
        }

        html[data-theme="dark"] .card-accordion:hover {
            background: rgba(148, 163, 184, 0.08);
        }

        html[data-theme="dark"] .lesson-title {
            color: #e2e8f0;
        }

        html[data-theme="dark"] .lesson-time {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .lesson-item.is-completed .lesson-title,
        html[data-theme="dark"] .lesson-item.is-completed .lesson-time {
            color: rgba(226, 232, 240, 0.72);
            text-decoration-color: rgba(226, 232, 240, 0.55);
        }

        html[data-theme="dark"] .lesson-item.is-completed .lesson-time {
            color: rgba(148, 163, 184, 0.82);
        }

        html[data-theme="dark"] .card-accordion.active .lesson-title {
            color: #f8fafc;
        }

        html[data-theme="dark"] .card-accordion.active .lesson-time {
            color: #dbeafe;
        }

        .video-detail .ratio {
            overflow: hidden;
            border-radius: 18px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.2);
            background: #020617;
        }

        .accordion-title {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            gap: 10px;
        }

        .lesson-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            font-size: 12px;
            line-height: 1.2;
            color: #64748b;
        }

        @media (max-width: 991.98px) and (min-width: 576px) {
            .lesson-nav {
                margin-top: 20px !important;
                margin-bottom: 18px;
                align-items: center;
            }

            .lesson-nav>div {
                display: flex;
            }

            .btn-lesson {
                min-height: 48px;
                padding: 12px 18px;
                border-radius: 14px;
            }

            .accordion .accordion-detail .card-accordion>div .lesson-left {
                grid-template-columns: 20px 22px minmax(0, 1fr) auto auto;
                column-gap: 10px;
                row-gap: 0;
                padding: 14px 12px;
            }

            .accordion .accordion-detail .card-accordion>div .lesson-left.lesson-left--readonly {
                grid-template-columns: 22px minmax(0, 1fr) auto;
            }

            .accordion .accordion-detail .card-accordion>div .lesson-left.lesson-left--readonly.lesson-left--trial {
                grid-template-columns: 22px minmax(0, 1fr) auto auto;
            }

            .lesson-text {
                padding-left: 2px;
            }

            .lesson-title {
                font-size: 0.96rem;
                line-height: 1.4;
                word-break: normal;
                overflow-wrap: anywhere;
            }

            .lesson-time {
                min-width: 72px;
                text-align: right;
                font-size: 12px;
            }

            .lesson-left .preview.trial-btn {
                min-width: 54px;
                margin-left: 2px;
                padding: 4px 8px;
                font-size: 10px;
            }
        }

        @media (max-width: 575.98px) {
            .video {
                padding-bottom: 78px;
            }

            .video .row {
                row-gap: 14px;
            }

            .video h3 {
                margin-bottom: 14px;
                font-size: 1.05rem;
                line-height: 1.3;
                letter-spacing: -0.01em;
            }

            .video-detail .ratio {
                border-radius: 14px;
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
            }

            .lesson-nav {
                gap: 8px;
                margin-top: 14px !important;
            }

            .lesson-nav>div {
                width: 100%;
            }

            .lesson-nav>div:empty {
                display: none;
            }

            .btn-lesson {
                min-height: 46px;
                padding: 10px 14px;
                border-radius: 14px;
                gap: 8px;
                font-size: 0.92rem;
            }

            .lesson-progress-card {
                padding: 14px;
                border-radius: 14px;
                margin-bottom: 12px !important;
            }

            .lesson-progress-card__head strong,
            .lesson-progress-card__head span {
                font-size: 0.92rem;
            }

            .lesson-progress-card__meta {
                margin-top: 8px;
                font-size: 12px;
            }

            .nav p {
                min-height: 44px;
                padding: 10px;
                font-size: 0.92rem;
            }

            .lesson-left {
                align-items: flex-start;
            }

            .accordion .accordion-detail .card-accordion>div .lesson-left {
                grid-template-columns: 20px 22px minmax(0, 1fr);
                row-gap: 6px;
                column-gap: 10px;
                padding: 12px 10px;
            }

            .accordion .accordion-detail .card-accordion>div .lesson-left.lesson-left--readonly {
                grid-template-columns: 22px minmax(0, 1fr) auto;
            }

            .accordion .accordion-detail .card-accordion>div .lesson-left.lesson-left--readonly.lesson-left--trial {
                grid-template-columns: 22px minmax(0, 1fr) auto;
            }

            .lesson-time {
                grid-column: 3;
                min-width: auto;
                width: auto;
                text-align: left;
                font-size: 12px;
            }

            .lesson-left.lesson-left--readonly .lesson-time {
                grid-column: 3;
            }

            .lesson-left.lesson-left--readonly .preview.trial-btn {
                grid-column: 3;
                justify-self: start;
                margin-left: 0;
            }

            .lesson-title {
                font-size: 0.92rem;
                line-height: 1.38;
            }

            .lesson-status {
                width: 20px;
                justify-content: center;
            }

            .accordion-title {
                padding: 14px 10px;
                font-size: 1rem;
                line-height: 1.32;
            }

            .lesson-count {
                align-self: start;
                font-size: 11px;
                padding-left: 4px;
            }

            .group {
                border-radius: 16px;
            }
        }
    </style>
@endsection
