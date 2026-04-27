@php
    $modules = getModuleByPosition($course);
    $student = Auth::guard('students')->user();
    $isAdmin = auth('web')->check() && auth('web')->user()->hasPermission('dashboard.view') && !auth('students')->check();
    $isImpersonating = session()->has('admin_impersonator');
    
    $isCourseOwner = false;
    if ($student && $student->teacher && $course->teacher_id !== null) {
        $teacher = $student->teacher;
        if ($teacher->status === \Modules\Teacher\src\Models\Teacher::STATUS_ACTIVE && (int) $teacher->id === (int) $course->teacher_id) {
            $isCourseOwner = true;
        }
    }

    $hasCourseAccess = $isAdmin || $isImpersonating || $isCourseOwner || ($student && $student->courses()->where('courses.id', $course->id)->wherePivot('status', 1)->exists());
@endphp

@if ($modules->isEmpty())
    <div class="alert alert-info text-center">
        {{ __('courses::clients/common.no_lessons') }}
    </div>
@else
    @php $hasLesson = false; @endphp

    @foreach ($modules as $key => $module)
        @php
            $lessons = getLessonByPosition($course, $module->id);
            if ($lessons->count()) {
                $hasLesson = true;
            }
        @endphp

        <div class="accordion-group">
            <h4 class="accordion-title {{ $key == 0 ? 'active' : '' }}">
                {{ $module->name_locale }}
                <span class="lesson-count">
                    {{ $module->children->count() }} {{ __('courses::clients/common.lesson') }}
                </span>
            </h4>

            <div class="accordion-detail" style="{{ $key == 0 ? 'display:block;' : '' }}">
                @forelse ($lessons as $lesson)
                    @php
                        $canOpenLesson = $hasCourseAccess || (int) $lesson->is_trial === 1;
                    @endphp
                    <div class="card-accordion">
                        <div class="lesson-item">
                            <div class="lesson-left">
                                <i class="fa-brands fa-youtube"></i>
                                @if ($canOpenLesson)
                                    <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $lesson->slug_locale]) }}"
                                        class="lesson-title">
                                        {{ __('lessons::clients/common.lesson_item') . ' ' . ++$index . ': ' . $lesson->name_locale }}
                                    </a>
                                @elseif ($student)
                                    <a href="#"
                                        class="lesson-title text-muted js-locked-lesson"
                                        data-message="{{ __('courses::clients/common.lesson_purchase_required') }}">
                                        {{ __('lessons::clients/common.lesson_item') . ' ' . ++$index . ': ' . $lesson->name_locale }}
                                    </a>
                                @else
                                    <a href="#"
                                        class="lesson-title text-muted js-login-required-lesson"
                                        data-message="{{ __('courses::clients/common.lesson_login_required') }}">
                                        {{ __('lessons::clients/common.lesson_item') . ' ' . ++$index . ': ' . $lesson->name_locale }}
                                    </a>
                                @endif

                                @if ($lesson->is_trial)
                                    <p class="preview trial-btn" data-id="{{ $lesson->id }}">
                                        {{ __('courses::clients/common.trial') }}
                                    </p>
                                @endif
                            </div>
                            <span class="lesson-time">
                                {{ getTime($lesson->durations) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small px-3">
                        {{ __('courses::clients/common.no_lessons_in_module') }}
                    </p>
                @endforelse
            </div>
        </div>
    @endforeach

    @if (!$hasLesson)
        <div class="alert alert-info text-center mt-3">
            {{ __('courses::clients/common.no_lessons') }}
        </div>
    @endif
@endif

@section('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('modal');
            const Modal = new bootstrap.Modal(modalEl);
            const trialBtnList = document.querySelectorAll('.trial-btn');
            const lockedLessonList = document.querySelectorAll('.js-locked-lesson');
            const loginRequiredLessonList = document.querySelectorAll('.js-login-required-lesson');
            const activeBtnMap = new Map();
            const initialTexts = new WeakMap();
            const messages = {
                opening: @json(__('courses::clients/common.trial_opening')),
                loginRequired: @json(__('courses::clients/common.trial_login_required')),
                lessonLoginRequired: @json(__('courses::clients/common.lesson_login_required')),
                lessonPurchaseRequired: @json(__('courses::clients/common.lesson_purchase_required')),
                unavailable: @json(__('courses::clients/common.trial_unavailable')),
                noVideo: @json(__('courses::clients/common.trial_no_video')),
            };

            const renderTrialContent = (video) => {
                if (!video || !video.type || !video.url) {
                    return '';
                }

                if (video.type === 'embed') {
                    return `
                        <div class="ratio ratio-16x9">
                            <iframe src="${video.url}" title="Trial video"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                        </div>
                    `;
                }

                return `
                    <video id="my-video" class="video-js" controls preload="auto" data-setup="{}">
                        <source src="${video.url}" type="video/mp4"/>
                    </video>
                `;
            };

            trialBtnList.forEach(trialBtn => {
                trialBtn.addEventListener('click', async (e) => {
                    const button = e.currentTarget;
                    const id = button.dataset.id;

                    if (!id) {
                        return alert(messages.unavailable);
                    }

                    if (!initialTexts.has(button)) {
                        initialTexts.set(button, button.innerText);
                    }

                    button.innerText = messages.opening;
                    activeBtnMap.set('current', button);

                    try {
                        const response = await fetch(
                            "{{ route('courses.data.trial', ['locale' => app()->getLocale()]) }}/" +
                            id);
                        const {
                            success,
                            data,
                            requires_login: requiresLogin,
                            message
                        } = await response.json();
                        if (!success && requiresLogin) {
                            alert(message || messages.loginRequired);
                            return;
                        }

                        if (!success || data.is_trial !== 1) {
                            return alert(messages.unavailable);
                        }

                        if (!data.video || !data.video.url) {
                            return alert(messages.noVideo);
                        }

                        modalEl.querySelector('.modal-title').innerText = data.name;
                        modalEl.querySelector('.modal-body').innerHTML = renderTrialContent(data.video);

                        Modal.show();
                        const videoEl = modalEl.querySelector('#my-video');
                        if (videoEl) {
                            videojs(videoEl);
                        }
                    } catch (error) {
                        alert(messages.unavailable);
                    } finally {
                        button.innerText = initialTexts.get(button) ?? '{{ __('courses::clients/common.trial') }}';
                    }
                });
            });

            lockedLessonList.forEach((lessonLink) => {
                lessonLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    alert(lessonLink.dataset.message || messages.lessonPurchaseRequired);
                });
            });

            loginRequiredLessonList.forEach((lessonLink) => {
                lessonLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    alert(lessonLink.dataset.message || messages.lessonLoginRequired);
                });
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                const videoEl = modalEl.querySelector('#my-video');
                if (videoEl && videojs.getPlayer(videoEl.id)) {
                    videojs.getPlayer(videoEl.id).dispose();
                }
                modalEl.querySelector('.modal-title').innerText = '';
                modalEl.querySelector('.modal-body').innerHTML = '';

                const activeBtn = activeBtnMap.get('current');
                if (activeBtn) {
                    activeBtn.innerText = initialTexts.get(activeBtn) ?? '{{ __('courses::clients/common.trial') }}';
                    activeBtnMap.delete('current');
                }

                if (!document.querySelector('.modal.show')) {
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            });
        });
    </script>
@endsection
