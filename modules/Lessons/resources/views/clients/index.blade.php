@extends('layouts.client')

@section('stylesheets')
    <style>
        :root {
            --cp-bg: #f8fafc;
            --cp-sidebar-bg: #ffffff;
            --cp-text-main: #1e293b;
            --cp-text-muted: #64748b;
            --cp-border: #e2e8f0;
            --cp-card-bg: #ffffff;
            --cp-nav-bg: #f1f5f9;
            --cp-tab-active: var(--primary-color);
            --cp-video-bg: #000000;
        }

        [data-theme='dark'] {
            --cp-bg: #0f172a;
            --cp-sidebar-bg: #1e293b;
            --cp-text-main: #f1f5f9;
            --cp-text-muted: #94a3b8;
            --cp-border: #334155;
            --cp-card-bg: #1e293b;
            --cp-nav-bg: #0f172a;
            --cp-video-bg: #000000;
        }

        /* Restore site-wide header and footer */
        body > .header, body > footer, .site-announcement {
            display: block !important;
        }

        main {
            padding: 2rem 0 !important;
            margin: 0 !important;
        }

        .course-player {
            background-color: var(--cp-bg);
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid var(--cp-border);
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .cp-header {
            background-color: var(--cp-sidebar-bg);
            border-bottom: 1px solid var(--cp-border);
            padding: 0 1.5rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            flex-shrink: 0;
        }

        .cp-header-left {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .cp-breadcrumb {
            font-size: 0.75rem;
            color: var(--cp-text-muted);
            margin-bottom: 0.125rem;
            display: flex;
            align-items: center;
            white-space: nowrap;
            overflow: hidden;
        }

        .cp-breadcrumb a {
            color: var(--cp-text-muted);
            text-decoration: none;
        }

        .cp-breadcrumb i {
            font-size: 0.625rem;
            margin: 0 0.5rem;
            flex-shrink: 0;
        }

        .cp-title {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cp-main {
            display: flex;
            align-items: stretch;
            flex-wrap: nowrap;
            background-color: var(--cp-sidebar-bg);
            min-height: 600px;
        }

        .cp-video-section {
            flex: 1;
            min-width: 0;
            background-color: var(--cp-bg);
            display: flex;
            flex-direction: column;
        }

        .cp-video-content {
            display: flex;
            flex-direction: column;
        }

        .cp-video-wrapper {
            width: 100%;
            background-color: #000;
            aspect-ratio: 16/9;
            flex-shrink: 0;
        }

        .cp-video-nav {
            padding: 1rem 1.5rem;
            background-color: var(--cp-sidebar-bg);
            border-bottom: 1px solid var(--cp-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .cp-sidebar {
            width: 400px;
            background-color: var(--cp-sidebar-bg);
            border-left: 1px solid var(--cp-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        @media (max-width: 1200px) {
            .cp-sidebar {
                width: 100%;
                height: auto;
                position: static;
                border-left: none;
                border-top: 1px solid var(--cp-border);
            }
        }

        .cp-progress-section {
            padding: 1.5rem;
            border-bottom: 1px solid var(--cp-border);
        }

        .cp-tabs-nav {
            display: flex;
            background-color: var(--cp-nav-bg);
            padding: 0.25rem;
            margin: 1rem 1.5rem;
            border-radius: 0.75rem;
        }

        .cp-tab-btn {
            flex: 1;
            padding: 0.625rem;
            border: none;
            background: none;
            color: var(--cp-text-muted);
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.625rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cp-tab-btn:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--cp-text-main);
            transform: translateY(-1px);
        }

        [data-theme='light'] .cp-tab-btn:hover:not(.active) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .cp-tab-btn.active {
            background-color: var(--cp-sidebar-bg);
            color: var(--cp-tab-active);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: scale(1.05);
        }

        .cp-tab-content {
            flex: 1;
            overflow-y: auto;
            padding: 0 1.5rem 1.5rem;
        }

        .tab-pane {
            animation: cpFadeIn 0.4s ease-out;
        }

        @keyframes cpFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .cp-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
        }

        .cp-btn-outline {
            border: 1px solid var(--cp-border);
            color: var(--cp-text-main);
            background-color: var(--cp-sidebar-bg);
        }

        .cp-btn-outline:hover {
            background-color: var(--cp-nav-bg);
            color: var(--cp-tab-active);
        }

        .cp-btn-primary {
            background-color: var(--cp-tab-active);
            color: #fff;
            border: none;
        }

        .cp-btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Progress Bar Premium */
        .cp-progress-bar-wrapper {
            height: 8px;
            background-color: var(--cp-nav-bg);
            border-radius: 999px;
            overflow: hidden;
            margin: 0.75rem 0;
        }

        /* Trial Badge Premium */
        .cp-trial-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            color: #ffffff;
            font-size: 0.625rem;
            font-weight: 800;
            padding: 0.125rem 0.5rem;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
            white-space: nowrap;
            margin-left: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        [data-theme='dark'] .cp-trial-badge {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .cp-progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--cp-tab-active) 0%, #3b82f6 100%);
            border-radius: 999px;
            transition: width 0.5s ease-out;
        }

        .cp-progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .cp-progress-count {
            color: var(--cp-text-muted);
            font-weight: 400;
        }

        /* Certificate Card Premium */
        .cp-cert-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #fff;
            padding: 1.25rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .cp-cert-card::after {
            content: '\f0a3';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.1;
            transform: rotate(-15deg);
        }
        /* Course Modules & Lessons Premium */
        .cp-module {
            margin-bottom: 0.5rem;
            border: 1px solid var(--cp-border);
            border-radius: 0.75rem;
            overflow: hidden;
            background-color: var(--cp-sidebar-bg);
        }

        .cp-module-header {
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .cp-module-header:hover {
            background-color: var(--cp-nav-bg);
        }

        .cp-module-header.active {
            background-color: var(--cp-nav-bg);
        }

        .cp-module-name {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
        }

        .cp-module-meta {
            font-size: 0.75rem;
            color: var(--cp-text-muted);
        }

        .cp-module-icon {
            font-size: 0.75rem;
            transition: transform 0.3s;
            color: var(--cp-text-muted);
        }

        .cp-module-header:not(.collapsed) .cp-module-icon {
            transform: rotate(180deg);
        }

        .cp-lesson-list {
            padding: 0.25rem 0;
            border-top: 1px solid var(--cp-border);
        }

        .cp-lesson-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1.25rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .cp-lesson-item:hover {
            background-color: var(--cp-nav-bg);
        }

        .cp-lesson-item.active {
            background-color: rgba(59, 130, 246, 0.08);
            border-left-color: var(--cp-tab-active);
        }

        .cp-lesson-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            min-width: 0;
        }

        .cp-lesson-check {
            width: 18px;
            height: 18px;
            border: 2px solid var(--cp-border);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            padding: 0;
            cursor: pointer;
            transition: all 0.2s;
            color: #fff;
            flex-shrink: 0;
        }

        .cp-lesson-check.checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .cp-lesson-check i {
            font-size: 10px;
        }

        .cp-lesson-status {
            color: var(--cp-text-muted);
            font-size: 0.875rem;
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .cp-lesson-content {
            flex: 1;
            min-width: 0;
        }

        .cp-lesson-link {
            display: block;
            font-size: 0.875rem;
            color: var(--cp-text-main);
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
        }

        .cp-lesson-item.active .cp-lesson-link {
            color: var(--cp-tab-active);
            font-weight: 700;
        }

        .cp-lesson-link.locked {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .cp-lesson-alert {
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.125rem;
        }

        .cp-lesson-right {
            margin-left: 1rem;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .cp-lesson-duration {
            font-size: 0.75rem;
            color: var(--cp-text-muted);
        }
        /* Notes Premium */
        .cp-note-item {
            padding: 1rem;
            background-color: var(--cp-nav-bg);
            border-radius: 0.75rem;
            margin-bottom: 0.75rem;
            border: 1px solid var(--cp-border);
            transition: transform 0.2s;
        }

        .cp-note-item:hover {
            transform: translateX(4px);
        }

        .cp-note-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .cp-note-time {
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
            background-color: var(--cp-tab-active);
            padding: 0.25rem 0.625rem;
            border-radius: 999px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .cp-note-time:hover {
            opacity: 0.9;
        }

        .cp-note-delete {
            background: none;
            border: none;
            color: var(--cp-text-muted);
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s;
        }

        .cp-note-delete:hover {
            color: #ef4444;
        }

        .cp-note-content {
            font-size: 0.875rem;
            line-height: 1.5;
            color: var(--cp-text-main);
            word-break: break-word;
        }
    </style>
@endsection

@section('content')
    <div class="container py-4">
        <div class="course-player">
            <header class="cp-header">
                <div class="cp-header-left">
                    <nav class="cp-breadcrumb">
                        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}">{{ __('common.home') }}</a>
                        <i class="fa-solid fa-angle-right"></i>
                        <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}">
                            {{ $course->name_locale }}
                        </a>
                        <i class="fa-solid fa-angle-right"></i>
                        <span>{{ $lesson->name_locale }}</span>
                    </nav>
                    <h1 class="cp-title">{{ $lesson->name_locale }}</h1>
                </div>
                <div class="cp-header-right">
                    <div class="cp-theme-toggle" id="theme-toggle" title="Toggle Light/Dark Mode">
                        <i class="fa-solid fa-moon"></i>
                    </div>
                </div>
            </header>

            <div class="cp-main">
                <section class="cp-video-section">
                    <div class="cp-video-content">
                        <div class="cp-video-wrapper">
                    @if (session('msg') && session('msgType', 'info') !== 'success')
                        <div class="alert alert-{{ session('msgType', 'info') }} m-3">
                            {{ session('msg') }}
                        </div>
                    @endif
                    <div class="alert alert-danger m-3 d-none" id="lesson-toggle-error" role="alert"></div>

                    @if ($lessonScheduleLocked ?? false)
                        <div class="lesson-release-alert m-5">
                            <div class="lesson-release-alert__icon">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                            <div>
                                <strong class="d-block mb-2">{{ __('lessons::clients/common.lesson_not_released') ?? 'Bài học chưa tới lịch mở' }}</strong>
                                <p class="mb-0">{{ $lessonScheduleMessage }}</p>
                            </div>
                        </div>
                    @else
                        @php
                            $videoUrl = trim((string) ($lesson->video?->url ?? ''));
                            $videoMeta = videoPlaybackMeta($videoUrl);
                        @endphp

                        @if ($videoMeta['type'] === 'embed' && !empty($videoMeta['url']))
                            <div class="ratio ratio-16x9">
                                <iframe id="youtube-player" src="{{ $videoMeta['url'] }}" title="Lesson video"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            </div>
                        @elseif ($videoMeta['type'] === 'file' && !empty($videoMeta['url']))
                            <video id="my-video" class="video-js vjs-default-skin vjs-big-play-centered w-100 h-100" controls preload="auto">
                                <source src="{{ $videoMeta['url'] }}" type="video/mp4" />
                                <p class="vjs-no-js">{{ __('lessons::clients/common.help') }}</p>
                            </video>
                        @else
                            <div class="alert alert-warning m-5">{{ __('lessons::clients/common.no_video') }}</div>
                        @endif
                    @endif
                    </div>
                </div>

                <div class="cp-video-nav">
                    <div>
                        @if ($prevLesson && ($lessonAvailabilityMap[$prevLesson->id]['can_open'] ?? ($hasCourse || (int) $prevLesson->is_trial === 1)))
                            <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $prevLesson->slug_locale]) }}"
                                class="cp-btn cp-btn-outline">
                                <i class="fa-solid fa-chevron-left"></i>
                                <span>{{ __('lessons::clients/common.prev_lesson') }}</span>
                            </a>
                        @endif
                    </div>

                    <div>
                        @if ($nextLesson && ($lessonAvailabilityMap[$nextLesson->id]['can_open'] ?? ($hasCourse || (int) $nextLesson->is_trial === 1)))
                            <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $nextLesson->slug_locale]) }}"
                                class="cp-btn cp-btn-primary">
                                <span>{{ __('lessons::clients/common.next_lesson') }}</span>
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            <aside class="cp-sidebar">
                @if ($hasCourse)
                    <div class="cp-progress-section">
                        <div class="cp-progress-text">
                            <span>{{ __('lessons::clients/common.course_progress') }}</span>
                            <span data-progress-percent>{{ $courseProgress['progress_percent'] }}%</span>
                        </div>
                        <div class="cp-progress-bar-wrapper">
                            <div class="cp-progress-bar-fill" data-progress-bar style="width: {{ $courseProgress['progress_percent'] }}%"></div>
                        </div>
                        <div class="cp-progress-count" data-progress-meta
                            data-progress-template="{{ __('lessons::clients/common.completed_lessons', ['completed' => '__completed__', 'total' => '__total__']) }}">
                            {{ __('lessons::clients/common.completed_lessons', [
                                'completed' => $courseProgress['completed_lessons'],
                                'total' => $courseProgress['total_lessons'],
                            ]) }}
                        </div>

                        @if($studentCertificate)
                            <div class="cp-cert-card mt-3" data-certificate-card>
                                <h6 class="mb-1">Chúc mừng!</h6>
                                <p class="small mb-3 opacity-75">Bạn đã nhận được chứng chỉ hoàn thành khóa học này.</p>
                                <a href="{{ route('students.account.certificates.show', ['locale' => app()->getLocale(), 'id' => $studentCertificate->id]) }}"
                                    class="btn btn-sm btn-light w-100 fw-bold" data-certificate-link>
                                    Xem chứng chỉ
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="cp-tabs-nav" id="cp-tabs">
                    <button class="cp-tab-btn active" data-tab="lessons" title="{{ __('lessons::clients/common.lesson') }}">
                        <i class="fa-solid fa-list-ul"></i>
                    </button>
                    <button class="cp-tab-btn" data-tab="documents" title="{{ __('lessons::clients/common.document') }}">
                        <i class="fa-solid fa-file-lines"></i>
                    </button>
                    <button class="cp-tab-btn" data-tab="quizzes" title="{{ __('quizzes::clients.tab_quiz') }}">
                        <i class="fa-solid fa-vial"></i>
                    </button>
                    <button class="cp-tab-btn" data-tab="notes" title="{{ __('lessons::clients/common.notes') }}">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>

                <div class="cp-tab-content">
                    <div class="tab-pane active" id="pane-lessons">
                        @include('lessons::clients.lesson')
                    </div>
                    <div class="tab-pane d-none" id="pane-documents">
                        @include('lessons::clients.document')
                    </div>
                    <div class="tab-pane d-none" id="pane-quizzes">
                        @include('lessons::clients.quiz')
                    </div>
                    <div class="tab-pane d-none" id="pane-notes">
                        @include('lessons::clients.notes')
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://vjs.zencdn.net/8.23.4/video.min.js"></script>
    <script>
        // Global variables
        const myVideoEl = document.querySelector('#my-video');
        const lessonToggleError = document.getElementById('lesson-toggle-error');
        const progressPercentEl = document.querySelector('[data-progress-percent]');
        const progressBarEl = document.querySelector('[data-progress-bar]');
        const progressMetaEl = document.querySelector('[data-progress-meta]');
        const certificateCardEl = document.querySelector('[data-certificate-card]');
        const certificateLinkEl = document.querySelector('[data-certificate-link]');
        
        const lessonToggleFallbackError = @json(__('lessons::clients/common.completion_error'));
        const lessonPurchaseRequiredMessage = @json(__('courses::clients/common.lesson_purchase_required'));

        let ytPlayer = null;
        let vjsPlayer = null;
        let isPlayerReady = false;
        const videoMetaType = @json($videoMeta['type'] ?? '');
        const isYoutube = videoMetaType === 'embed';
        let activePlayer = isYoutube ? 'youtube' : (myVideoEl ? 'videojs' : null);

        // 1. Video Players Initialization
        if (myVideoEl) {
            vjsPlayer = videojs(myVideoEl, {}, function() {
                isPlayerReady = true;
            });
        }

        if (isYoutube) {
            if (window.YT && window.YT.Player) {
                initializeYoutubePlayer();
            } else {
                const tag = document.createElement('script');
                tag.src = "https://www.youtube.com/iframe_api";
                const firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                window.onYouTubeIframeAPIReady = initializeYoutubePlayer;
            }
        }

        function initializeYoutubePlayer() {
            ytPlayer = new YT.Player('youtube-player', {
                events: {
                    'onReady': () => { isPlayerReady = true; },
                }
            });
        }

        function getCurrentTime() {
            if (isYoutube && ytPlayer && typeof ytPlayer.getCurrentTime === 'function') {
                try { return Math.floor(ytPlayer.getCurrentTime() || 0); } catch (e) { return 0; }
            }
            if (!isYoutube && typeof videojs !== 'undefined' && myVideoEl) {
                try { return Math.floor(videojs(myVideoEl).currentTime() || 0); } catch (e) { return 0; }
            }
            return 0;
        }

        function seekTo(seconds) {
            if (isYoutube && ytPlayer && typeof ytPlayer.seekTo === 'function') {
                ytPlayer.seekTo(seconds, true);
            } else if (!isYoutube && typeof videojs !== 'undefined' && myVideoEl) {
                const player = videojs(myVideoEl);
                player.currentTime(seconds);
                player.play();
            }
        }

        // 2. Tab Switching Logic
        const tabBtns = document.querySelectorAll('.cp-tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.tab;
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                tabPanes.forEach(pane => {
                    if (pane.id === `pane-${target}`) {
                        pane.classList.remove('d-none');
                        pane.classList.add('active');
                    } else {
                        pane.classList.add('d-none');
                        pane.classList.remove('active');
                    }
                });
            });
        });

        // 3. Lesson Completion Logic
        const setLessonToggleError = (message) => {
            if (!lessonToggleError) return;
            lessonToggleError.textContent = message || '';
            lessonToggleError.classList.toggle('d-none', !message);
        };

        document.querySelectorAll('.lesson-toggle-form').forEach((form) => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                setLessonToggleError('');
                const button = form.querySelector('.cp-lesson-check');
                const lessonItem = form.closest('.cp-lesson-item');
                if (!button || !lessonItem) return;

                button.disabled = true;
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: new FormData(form),
                    });
                    const payload = await response.json();
                    if (!response.ok || !payload.success) throw new Error(payload.message || lessonToggleFallbackError);

                    const completed = Boolean(payload.completed);
                    lessonItem.classList.toggle('completed', completed);
                    button.classList.toggle('checked', completed);
                    button.innerHTML = completed ? '<i class="fa-solid fa-check"></i>' : '';

                    if (payload.course_progress) {
                        if (progressPercentEl) progressPercentEl.textContent = `${payload.course_progress.progress_percent}%`;
                        if (progressBarEl) progressBarEl.style.width = `${payload.course_progress.progress_percent}%`;
                        if (progressMetaEl) {
                            const template = progressMetaEl.dataset.progressTemplate || '';
                            progressMetaEl.textContent = template
                                .replace('__completed__', payload.course_progress.completed_lessons)
                                .replace('__total__', payload.course_progress.total_lessons);
                        }
                    }

                    if (payload.certificate && certificateCardEl && certificateLinkEl) {
                        certificateCardEl.classList.remove('d-none');
                        certificateLinkEl.href = payload.certificate.url;
                    }
                } catch (err) {
                    setLessonToggleError(err.message || lessonToggleFallbackError);
                } finally {
                    button.disabled = false;
                }
            });
        });

        // 4. Locked Lessons
        document.querySelectorAll('.js-locked-lesson').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                alert(link.dataset.message || lessonPurchaseRequiredMessage);
            });
        });

        // 5. Notes Logic
        const notesList = document.getElementById('notes-list');
        const noteContent = document.getElementById('note-content');
        const saveNoteBtn = document.getElementById('save-note-btn');
        const currentTimeDisplay = document.getElementById('current-video-time');
        const notesIndexUrl = "{{ route('lessons.notes.index', ['locale' => app()->getLocale(), 'lessonId' => $lesson->id]) }}";
        const notesDestroyBaseUrl = "{{ url(app()->getLocale() . '/bai-hoc/ghi-chu') }}";

        function getCurrentTime() {
            if (activePlayer === 'youtube' && ytPlayer && typeof ytPlayer.getCurrentTime === 'function') {
                return Math.floor(ytPlayer.getCurrentTime());
            }
            if (activePlayer === 'videojs' && vjsPlayer) {
                return Math.floor(vjsPlayer.currentTime());
            }
            return 0;
        }

        window.seekTo = function(seconds) {
            if (activePlayer === 'youtube' && ytPlayer && typeof ytPlayer.seekTo === 'function') {
                ytPlayer.seekTo(seconds);
            }
            if (activePlayer === 'videojs' && vjsPlayer) {
                vjsPlayer.currentTime(seconds);
            }
        };

        setInterval(() => {
            if (currentTimeDisplay) {
                currentTimeDisplay.textContent = formatTime(getCurrentTime());
            }
        }, 1000);

        function formatTime(seconds) {
            if (isNaN(seconds)) return "00:00";
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            return h > 0 
                ? `${h}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
                : `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }

        async function loadNotes() {
            try {
                const response = await fetch(notesIndexUrl);
                const result = await response.json();
                if (result.success) renderNotes(result.data);
            } catch (e) { console.error('Load notes error', e); }
        }

        function renderNotes(notes) {
            if (!notesList) return;
            if (notes.length === 0) {
                notesList.innerHTML = `<div class="text-center py-5 text-muted small opacity-75">${@json(__('lessons::clients/common.no_notes'))}</div>`;
                return;
            }
            notesList.innerHTML = notes.map(note => `
                <div class="cp-note-item" data-id="${note.id}">
                    <div class="cp-note-header">
                        <span class="cp-note-time" onclick="seekTo(${note.time_at})">
                            <i class="fa-solid fa-play"></i> ${note.time_formatted}
                        </span>
                        <button class="cp-note-delete" onclick="deleteNote(${note.id})">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                    <div class="cp-note-content">${note.content}</div>
                </div>
            `).join('');
        }

        async function saveNote() {
            const content = noteContent.value.trim();
            if (!content) { alert(@json(__('lessons::clients/common.note_empty'))); return; }

            const timeAt = getCurrentTime();
            saveNoteBtn.disabled = true;
            const originalHtml = saveNoteBtn.innerHTML;
            saveNoteBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            try {
                const response = await fetch(notesIndexUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf_token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ content, time_at: timeAt })
                });
                const result = await response.json();
                if (result.success) {
                    noteContent.value = '';
                    loadNotes();
                } else { alert(result.message); }
            } catch (e) { alert('Server error'); } finally {
                saveNoteBtn.disabled = false;
                saveNoteBtn.innerHTML = originalHtml;
            }
        }

        async function deleteNote(id) {
            if (!confirm(@json(__('lessons::clients/common.confirm_delete_note')))) return;
            try {
                const response = await fetch(`${notesDestroyBaseUrl}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf_token"]')?.getAttribute('content'), 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (result.success) loadNotes();
            } catch (e) { console.error('Delete error', e); }
        }

        // Initialize and Timers
        if (notesList) {
            loadNotes();
            setInterval(() => {
                if (currentTimeDisplay) currentTimeDisplay.textContent = formatTime(getCurrentTime());
            }, 1000);
        }
        if (saveNoteBtn) saveNoteBtn.addEventListener('click', saveNote);

        window.seekTo = seekTo;
        window.deleteNote = deleteNote;
    </script>
@endsection
