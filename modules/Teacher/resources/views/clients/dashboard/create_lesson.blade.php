@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <div class="teacher-panel">
            <div class="teacher-section-title mb-4">
                <div>
                    <h3 class="fw-bold mb-2">{{ $lesson ? __('teacher::teacher/lesson/edit.edit_title', ['lesson' => $lesson->name_locale]) : __('teacher::teacher/lesson/add.create_title', ['course' => $course->name_locale]) }}</h3>
                    <p class="text-muted mb-0">{{ __('teacher::teacher/lesson/add.create_description', ['course' => $course->name_locale]) }}</p>
                </div>
                <a href="{{ route('teacher.dashboard.lessons.index', $course->id) }}" class="btn btn-outline-secondary">
                    {{ __('teacher::teacher/course/common.actions.back') }}
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">{{ __('teacher::teacher/course/common.warnings.validation_summary') }}</div>
            @endif

            <form method="POST" action="{{ $formAction }}" class="teacher-course-form">
                @csrf

                <div class="teacher-panel mb-4">
                    <div class="teacher-section-title mb-3">
                        <div>
                            <h4 class="h5 mb-1">{{ __('teacher::teacher/lesson/add.content_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('teacher::teacher/lesson/add.content_description') }}</p>
                        </div>
                        <div class="btn-group" role="group" aria-label="Lesson language tabs">
                            @foreach (['vi', 'en', 'ko', 'ja', 'zh'] as $locale)
                                <input type="radio" class="btn-check" name="lesson_content_lang" id="teacher_lesson_lang_{{ $locale }}" @checked($locale === 'vi')>
                                <label class="btn btn-outline-primary" for="teacher_lesson_lang_{{ $locale }}">{{ strtoupper($locale) }}</label>
                            @endforeach
                        </div>
                    </div>

                    @foreach (['vi', 'en', 'ko', 'ja', 'zh'] as $locale)
                        @php
                            $suffix = $locale === 'vi' ? '' : '_' . $locale;
                            $isDefault = $locale === 'vi';
                            $nameField = 'name' . $suffix;
                            $descriptionField = 'description' . $suffix;
                        @endphp
                        <div class="teacher-lang-block {{ $isDefault ? '' : 'd-none' }}" data-lesson-lang-block="{{ $locale }}">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">{{ __('teacher::teacher/lesson/add.form.name_label', ['locale' => strtoupper($locale)]) }}</label>
                                    <input type="text" name="{{ $nameField }}" class="form-control @error($nameField) is-invalid @enderror" value="{{ old($nameField, data_get($lesson, $nameField)) }}">
                                    @error($nameField)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('teacher::teacher/lesson/add.form.description_label', ['locale' => strtoupper($locale)]) }}</label>
                                    <textarea name="{{ $descriptionField }}" class="form-control @error($descriptionField) is-invalid @enderror" rows="5">{{ old($descriptionField, data_get($lesson, $descriptionField)) }}</textarea>
                                    @error($descriptionField)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row g-4">
                    <div class="col-xl-7">
                        <div class="teacher-panel h-100">
                            <h4 class="h5 mb-3">{{ __('teacher::teacher/lesson/add.settings_title') }}</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                        <option value="0">{{ __('teacher::teacher/lesson/add.form.parent_none') }}</option>
                                        @foreach ($modules as $module)
                                            <option value="{{ $module->id }}" @selected(old('parent_id', $defaultParentId) == $module->id)>
                                                {{ $module->name_locale }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('teacher::teacher/course/common.form.position') }}</label>
                                    <input type="number" name="position" min="1" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $position) }}">
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('teacher::teacher/course/common.form.status') }}</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="0" @selected(old('status', $lesson?->status ?? 1) == 0)>{{ __('teacher::teacher/course/common.status.draft') }}</option>
                                        <option value="1" @selected(old('status', $lesson?->status ?? 1) == 1)>{{ __('teacher::teacher/course/common.status.published') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::teacher/lesson/add.form.video') }}</label>
                                    <input type="text" name="video" class="form-control @error('video') is-invalid @enderror" value="{{ old('video', $lesson?->video?->url) }}" placeholder="https://...">
                                    @error('video')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if ($lesson?->video?->url)
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="1" id="remove_video" name="remove_video">
                                            <label class="form-check-label" for="remove_video">{{ __('teacher::teacher/lesson/add.form.remove_video') }}</label>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::teacher/lesson/add.form.document') }}</label>
                                    <input type="text" name="document" class="form-control @error('document') is-invalid @enderror" value="{{ old('document', $lesson?->document?->url) }}" placeholder="https://...">
                                    @error('document')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if ($lesson?->document?->url)
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="1" id="remove_document" name="remove_document">
                                            <label class="form-check-label" for="remove_document">{{ __('teacher::teacher/lesson/add.form.remove_document') }}</label>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::teacher/lesson/add.form.is_trial') }}</label>
                                    <select name="is_trial" class="form-select @error('is_trial') is-invalid @enderror">
                                        <option value="0" @selected(old('is_trial', $lesson?->is_trial ?? 0) == 0)>{{ __('teacher::teacher/course/common.status.not_active') }}</option>
                                        <option value="1" @selected(old('is_trial', $lesson?->is_trial ?? 0) == 1)>{{ __('teacher::teacher/course/common.status.active') }}</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="teacher-lesson-release-card">
                                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                                            <div>
                                                <h5 class="h6 mb-1">{{ __('teacher::teacher/lesson/add.scheduling.title') }}</h5>
                                                <p class="text-muted mb-0">{{ __('teacher::teacher/lesson/add.scheduling.description') }}</p>
                                            </div>
                                            <span class="teacher-chip">{{ $lesson?->releaseSummary() ?: __('teacher::teacher/lesson/add.scheduling.immediate') }}</span>
                                        </div>

                                        @if ($canScheduleContent)
                                            @php
                                                $releaseMode = old('release_mode', $lesson?->release_mode ?: 'immediate');
                                                $releaseAtValue = old('release_at', $lesson?->release_at?->format('Y-m-d\TH:i'));
                                            @endphp
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">{{ __('teacher::teacher/lesson/add.form.release_mode') }}</label>
                                                    <select name="release_mode" class="form-select @error('release_mode') is-invalid @enderror" data-lesson-release-mode>
                                                        <option value="immediate" @selected($releaseMode === 'immediate')>{{ __('teacher::teacher/lesson/add.scheduling.immediate') }}</option>
                                                        <option value="datetime" @selected($releaseMode === 'datetime')>{{ __('teacher::teacher/lesson/add.scheduling.datetime') }}</option>
                                                        <option value="days_after_enrollment" @selected($releaseMode === 'days_after_enrollment')>{{ __('teacher::teacher/lesson/add.scheduling.days_after_enrollment') }}</option>
                                                        <option value="after_previous_completed" @selected($releaseMode === 'after_previous_completed')>{{ __('teacher::teacher/lesson/add.scheduling.after_previous_completed') }}</option>
                                                    </select>
                                                    @error('release_mode')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6" data-release-group="datetime">
                                                    <label class="form-label">{{ __('teacher::teacher/lesson/add.form.release_at') }}</label>
                                                    <input type="datetime-local" name="release_at" class="form-control @error('release_at') is-invalid @enderror" value="{{ $releaseAtValue }}">
                                                    @error('release_at')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6" data-release-group="days_after_enrollment">
                                                    <label class="form-label">{{ __('teacher::teacher/lesson/add.form.release_after_days') }}</label>
                                                    <input type="number" min="1" max="3650" name="release_after_days" class="form-control @error('release_after_days') is-invalid @enderror" value="{{ old('release_after_days', $lesson?->release_after_days) }}" placeholder="{{ __('teacher::teacher/lesson/add.form.release_after_days_placeholder') }}">
                                                    @error('release_after_days')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-12 d-none" data-release-group="after_previous_completed">
                                                    <div class="alert alert-info mb-0">
                                                        {{ __('teacher::teacher/lesson/add.scheduling.after_previous_help') }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info mb-0">
                                                {{ __('teacher::teacher/lesson/add.scheduling.notice_locked') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="teacher-panel h-100">
                            <h4 class="h5 mb-3">{{ __('teacher::teacher/lesson/add.tips_title') }}</h4>
                            <ul class="text-muted mb-0 teacher-lesson-tips">
                                <li>{{ __('teacher::teacher/lesson/add.tips.module') }}</li>
                                <li>{{ __('teacher::teacher/lesson/add.tips.lesson') }}</li>
                                <li>{{ __('teacher::teacher/lesson/add.tips.media') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <a href="{{ route('teacher.dashboard.lessons.index', $course->id) }}" class="btn btn-outline-secondary">
                        {{ __('teacher::teacher/course/common.actions.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-lesson-tips {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding-left: 1.15rem;
        }

        .teacher-lesson-release-card {
            padding: 1rem;
            border-radius: 18px;
            border: 1px solid rgba(96, 165, 250, 0.18);
            background: rgba(15, 23, 42, 0.18);
        }

        html[data-theme="light"] .teacher-lesson-release-card {
            background: rgba(248, 250, 252, 0.92);
            border-color: rgba(148, 163, 184, 0.22);
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const supported = ['vi', 'en', 'ko', 'ja', 'zh'];
            const getLangInput = (locale) => document.getElementById(`teacher_lesson_lang_${locale}`);

            const showLang = (locale) => {
                document.querySelectorAll('[data-lesson-lang-block]').forEach((block) => {
                    block.classList.toggle('d-none', block.dataset.lessonLangBlock !== locale);
                });
                localStorage.setItem('teacher_lesson_lang', locale);
            };

            const saved = localStorage.getItem('teacher_lesson_lang');
            const initial = supported.includes(saved) ? saved : 'vi';
            const input = getLangInput(initial);
            if (input) {
                input.checked = true;
            }
            showLang(initial);

            supported.forEach((locale) => {
                const radio = getLangInput(locale);
                if (!radio) {
                    return;
                }
                radio.addEventListener('change', () => showLang(locale));
            });

            const releaseModeInput = document.querySelector('[data-lesson-release-mode]');
            const releaseGroups = document.querySelectorAll('[data-release-group]');

            const syncReleaseMode = () => {
                if (!releaseModeInput) {
                    return;
                }

                const mode = releaseModeInput.value || 'immediate';
                releaseGroups.forEach((group) => {
                    const shouldShow = group.dataset.releaseGroup === mode;
                    group.classList.toggle('d-none', !shouldShow);

                    group.querySelectorAll('input, select, textarea').forEach((field) => {
                        field.disabled = !shouldShow;
                    });
                });
            };

            releaseModeInput?.addEventListener('change', syncReleaseMode);
            syncReleaseMode();
        })();
    </script>
@endsection
