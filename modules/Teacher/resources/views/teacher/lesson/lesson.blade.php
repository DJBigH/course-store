@extends('layouts.teacher')

@section('stylesheets')
    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
    <style>
        .lesson-preview-modal .modal-content {
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
        }

        .lesson-preview-modal .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 1.5rem;
        }

        .lesson-preview-modal .modal-title {
            color: #f8fafc;
            font-weight: 700;
        }

        .lesson-preview-modal .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            background: #000;
        }

        .video-container iframe,
        .video-container .video-js {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .video-container .video-js {
            padding-top: 0 !important;
        }

        .preview-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.8);
            z-index: 10;
            color: #fff;
        }
    </style>
@endsection

@section('content')
    @php
        $stateCourseLimit = $teacher->getFeatureState('course_limit');
        $stateQuizzes = $teacher->getFeatureState('can_manage_quizzes');
        $stateImport = $teacher->getFeatureState('can_import_export');
    @endphp
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::teacher/lesson/list.title', ['course' => $course->name_locale]) }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::teacher/lesson/list.description') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('teacher.dashboard.courses') }}" class="btn btn-outline-secondary">
                    {{ __('teacher::teacher/course/common.actions.back') }}
                </a>
                @if($stateCourseLimit['is_maintenance'])
                    <button class="btn btn-outline-secondary" disabled>
                        {{ __('teacher::teacher/lesson/common.actions.trash') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                    </button>
                @else
                    <a href="{{ route('teacher.dashboard.lessons.trash', $course->id) }}" class="btn btn-outline-secondary">
                        {{ __('teacher::teacher/lesson/common.actions.trash') }}
                    </a>
                @endif

                @if ($stateQuizzes['can_use'])
                    <a href="{{ route('teacher.dashboard.quizzes.index', $course->id) }}" class="btn btn-outline-primary">
                        {{ __('teacher::teacher/lesson/common.actions.create_quiz') }}
                    </a>
                @elseif ($stateQuizzes['is_maintenance'])
                    <button class="btn btn-outline-primary" disabled>
                        {{ __('teacher::teacher/lesson/common.actions.create_quiz') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                    </button>
                @else
                    <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-outline-warning" title="{{ __('packages::teacher.feature_locked') }}">
                        {{ __('teacher::teacher/lesson/common.actions.create_quiz') }} <span class="ms-1 badge bg-warning text-dark">{{ __('teacher::teacher/lesson/common.labels.locked_badge') }}</span>
                    </a>
                @endif

                @if($stateCourseLimit['is_maintenance'])
                    <button class="btn btn-primary" disabled>
                        {{ __('teacher::teacher/lesson/common.actions.create') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                    </button>
                @else
                    <a href="{{ route('teacher.dashboard.lessons.create', $course->id) }}" class="btn btn-primary">
                        {{ __('teacher::teacher/lesson/common.actions.create') }}
                    </a>
                @endif
            </div>
        </div>

        @if (!$stateQuizzes['can_use'])
            @include('teacher::clients.dashboard.partials.package_feature_notice', [
                'featureState' => $stateQuizzes,
                'message' => __('packages::teacher.package_features.quiz_management_locked_desc'),
            ])
        @endif

        @if (!$stateImport['can_use'])
            @include('teacher::clients.dashboard.partials.package_feature_notice', [
                'featureState' => $stateImport,
                'message' => __('teacher::teacher/course/common.warnings.import_export_locked'),
            ])
        @endif

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        @if ($teacher->packageHasFeature('can_import_export'))
            <div class="teacher-panel mb-4">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                    <div>
                        <h4 class="h5 mb-1">{{ __('teacher::teacher/lesson/common.import.title') }}</h4>
                        <p class="text-muted mb-0">{{ __('teacher::teacher/lesson/common.import.description') }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($stateImport['is_maintenance'])
                            <button class="btn btn-outline-secondary" disabled>
                                {{ __('teacher::teacher/lesson/common.actions.export_csv') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                            </button>
                        @else
                            <a href="{{ route('teacher.dashboard.lessons.export', [$course->id, 'format' => 'csv']) }}" class="btn btn-outline-secondary">
                                {{ __('teacher::teacher/lesson/common.actions.export_csv') }}
                            </a>
                        @endif
                        <a href="{{ route('teacher.dashboard.lessons.import.example', [$course->id, 'format' => 'csv']) }}" class="btn btn-outline-secondary">
                            {{ __('teacher::teacher/lesson/common.actions.download_example_csv') }}
                        </a>
                        <a href="{{ route('teacher.dashboard.lessons.import.example', [$course->id, 'format' => 'xlsx']) }}" class="btn btn-outline-secondary">
                            {{ __('teacher::teacher/lesson/common.actions.download_example_xlsx') }}
                        </a>
                    </div>
                </div>

                @if (session('lesson_import_summary'))
                    @php
                        $importSummary = session('lesson_import_summary');
                    @endphp
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="teacher-import-stat">
                                <span>{{ __('teacher::teacher/lesson/common.import.summary_total') }}</span>
                                <strong>{{ $importSummary['total_rows'] ?? 0 }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="teacher-import-stat">
                                <span>{{ __('teacher::teacher/lesson/common.import.summary_modules') }}</span>
                                <strong>{{ $importSummary['imported_modules'] ?? 0 }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="teacher-import-stat">
                                <span>{{ __('teacher::teacher/lesson/common.import.summary_lessons') }}</span>
                                <strong>{{ $importSummary['imported_lessons'] ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('lesson_import_errors'))
                    <div class="alert alert-danger mb-3">
                        <div class="fw-semibold mb-2">{{ __('teacher::teacher/lesson/common.import.errors_title') }}</div>
                        <ul class="mb-0 ps-3">
                            @foreach (session('lesson_import_errors', []) as $errorMessage)
                                <li>{{ $errorMessage }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-xl-6">
                        <div class="teacher-import-card h-100">
                            <div class="teacher-import-card__label">{{ __('teacher::teacher/lesson/common.import.selectors_title') }}</div>
                            @if (!empty($existingModuleSelectors))
                                <select class="form-select" size="{{ min(max(count($existingModuleSelectors), 3), 8) }}" disabled>
                                    @foreach ($existingModuleSelectors as $selector)
                                        <option>{{ $selector['label'] }}</option>
                                    @endforeach
                                </select>
                            @else
                                <p class="text-muted mb-0">{{ __('teacher::teacher/lesson/common.import.selectors_empty') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="teacher-import-card h-100">
                            <div class="teacher-import-card__label">{{ __('teacher::teacher/lesson/common.import.quick_guide_title') }}</div>
                            <div class="teacher-import-guide">
                                <div class="teacher-import-guide__item">
                                    <strong>1.</strong>
                                    <span>{{ __('teacher::teacher/lesson/common.import.quick_guide_example') }}</span>
                                </div>
                                <div class="teacher-import-guide__item">
                                    <strong>2.</strong>
                                    <span>{{ __('teacher::teacher/lesson/common.import.quick_guide_parent') }}</span>
                                </div>
                                <div class="teacher-import-guide__item">
                                    <strong>3.</strong>
                                    <span>{{ __('teacher::teacher/lesson/common.import.quick_guide_limit') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('teacher.dashboard.lessons.import.preview', $course->id) }}" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-bold">{{ __('teacher::teacher/lesson/common.import.file_label') }}</label>
                    </div>
                    <div class="row g-3 align-items-start">
                        <div class="col-lg-8">
                            <input type="file" name="lesson_import_file" accept=".csv,.txt,.xlsx" class="form-control @error('lesson_import_file') is-invalid @enderror">
                            <div class="form-text mt-1 text-muted">{{ __('teacher::teacher/lesson/common.import.file_help') }}</div>
                            @error('lesson_import_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4">
                            <button type="submit" class="btn btn-primary w-100" {{ $stateImport['is_maintenance'] ? 'disabled' : '' }}>
                                <i class="fas fa-upload me-1"></i>
                                {{ __('teacher::teacher/lesson/common.actions.import') }}
                                @if($stateImport['is_maintenance']) ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }}) @endif
                            </button>
                        </div>
                    </div>
                </form>

                @if (!empty($lessonImportPreview['rows']))
                    <div class="teacher-import-card mt-4">
                        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                            <div>
                                <div class="teacher-import-card__label mb-1">{{ __('teacher::teacher/lesson/common.import.preview_title') }}</div>
                                <p class="text-muted mb-0">{{ __('teacher::teacher/lesson/common.import.preview_description') }}</p>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('teacher.dashboard.lessons.import.confirm', $course->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('teacher::teacher/lesson/common.actions.confirm_submit') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('teacher.dashboard.lessons.import.clear', $course->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary">
                                        {{ __('teacher::teacher/lesson/common.actions.clear_preview') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="teacher-import-stat">
                                    <span>{{ __('teacher::teacher/lesson/common.import.file_name') }}</span>
                                    <strong class="fs-6">{{ $lessonImportPreview['file_name'] ?? 'n/a' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="teacher-import-stat">
                                    <span>{{ __('teacher::teacher/lesson/common.import.format') }}</span>
                                    <strong class="text-uppercase fs-6">{{ $lessonImportPreview['detected_format'] ?? 'n/a' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="teacher-import-stat">
                                    <span>{{ __('teacher::teacher/lesson/common.import.generated_at') }}</span>
                                    <strong class="fs-6">{{ $lessonImportPreview['generated_at'] ?? 'n/a' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('teacher::teacher/lesson/common.form.name') }}</th>
                                        <th>{{ __('teacher::teacher/lesson/common.import.preview_type') }}</th>
                                        <th>{{ __('teacher::teacher/lesson/common.import.preview_parent') }}</th>
                                        <th>{{ __('teacher::teacher/lesson/common.import.preview_release') }}</th>
                                        <th>{{ __('teacher::teacher/course/common.form.position') }}</th>
                                        <th>{{ __('teacher::teacher/course/common.form.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lessonImportPreview['rows'] as $previewRow)
                                        @php
                                            $isModule = ($previewRow['type'] ?? '') === 'module';
                                            $releaseMode = $previewRow['release_mode'] ?? 'immediate';
                                            $releaseLabel = match ($releaseMode) {
                                                'datetime' => __('teacher::teacher/lesson/common.import.release_labels.datetime', ['date' => $previewRow['release_at'] ?? '-']),
                                                'days_after_enrollment' => __('teacher::teacher/lesson/common.import.release_labels.days_after_enrollment', ['days' => $previewRow['release_after_days'] ?? 0]),
                                                'after_previous_completed' => __('teacher::teacher/lesson/common.import.release_labels.after_previous_completed'),
                                                default => __('teacher::teacher/lesson/common.import.release_labels.immediate'),
                                            };
                                            $rowClass = $isModule
                                                ? 'teacher-import-row teacher-import-row--module'
                                                : 'teacher-import-row teacher-import-row--lesson';
                                            $typeClass = $isModule
                                                ? 'teacher-import-type--module'
                                                : 'teacher-import-type--lesson';
                                            $typeLabel = $isModule
                                                ? __('teacher::teacher/lesson/common.labels.module')
                                                : __('teacher::teacher/lesson/common.labels.lesson');
                                            $statusLabel = (string) ($previewRow['status'] ?? 1) === '1'
                                                ? __('teacher::teacher/course/common.status.published')
                                                : __('teacher::teacher/course/common.status.draft');
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>{{ $previewRow['line'] ?? '-' }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $previewRow['name'] ?? '-' }}</div>
                                                @if (!empty($previewRow['module_ref']))
                                                    <div class="small text-muted">ref: {{ $previewRow['module_ref'] }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="teacher-import-type {{ $typeClass }}">
                                                    {{ $typeLabel }}
                                                </span>
                                            </td>
                                            <td>{{ $previewRow['parent_selector'] ?? '-' }}</td>
                                            <td>
                                                <div>{{ $releaseLabel }}</div>
                                                <div class="small text-muted">{{ $releaseMode }}</div>
                                            </td>
                                            <td>{{ $previewRow['position'] ?? '-' }}</td>
                                            <td>{{ $statusLabel }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @forelse ($modules as $module)
            <section class="teacher-panel mb-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="text-uppercase small text-muted mb-1">{{ __('lessons::teacher/messages.module_label') }}</div>
                        <h4 class="h5 mb-1">{{ $module->name_locale }}</h4>
                        <p class="text-muted mb-0">{{ __('lessons::teacher/messages.labels.position', ['position' => $module->position]) }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($stateCourseLimit['is_maintenance'])
                            <button class="dropdown-item disabled" type="button">
                                {{ __('teacher::teacher/lesson/common.actions.edit') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                            </button>
                            <button class="dropdown-item disabled" type="button">
                                {{ __('teacher::teacher/lesson/common.actions.add_child') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                            </button>
                            <button class="dropdown-item disabled text-danger" type="button">
                                {{ __('teacher::teacher/lesson/common.actions.delete') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                            </button>
                        @else
                             <a href="{{ route('teacher.dashboard.lessons.edit', [$course->id, $module->id]) }}" class="btn btn-sm btn-outline-primary">
                                {{ __('teacher::teacher/lesson/common.actions.edit') }}
                            </a>
                            <a href="{{ route('teacher.dashboard.lessons.create', [$course->id, 'module' => $module->id]) }}" class="btn btn-sm btn-outline-secondary">
                                {{ __('teacher::teacher/lesson/common.actions.add_child') }}
                            </a>
                            <form method="POST" action="{{ route('teacher.dashboard.lessons.delete', [$course->id, $module->id]) }}" onsubmit="return confirm('{{ __('teacher::teacher/lesson/list.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    {{ __('teacher::teacher/lesson/common.actions.delete') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if ($module->subLessons->isNotEmpty())
                    <div class="row g-3">
                        @foreach ($module->subLessons->values() as $lesson)
                            <div class="col-lg-6">
                                @include('teacher::clients.dashboard.partials.lesson_item', [
                                    'lesson' => $lesson,
                                    'course' => $course,
                                    'previousLesson' => $loop->index > 0 ? $module->subLessons->values()->get($loop->index - 1) : null,
                                    'maintCourse' => $stateCourseLimit['is_maintenance'],
                                    'maintQuizzes' => $stateQuizzes['is_maintenance'],
                                ])
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">{{ __('lessons::teacher/messages.empty_module') }}</p>
                @endif
            </section>
        @empty
            <div class="teacher-panel">
                <p class="text-muted mb-0">{{ __('lessons::teacher/messages.empty') }}</p>
            </div>
        @endforelse
    </div>

    <style>
        .teacher-import-card {
            padding: 1rem;
            border-radius: 18px;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="dark"] .teacher-import-card {
            background: rgba(148, 163, 184, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.14);
        }

        .teacher-import-card__label {
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 0.85rem;
        }

        .teacher-import-columns {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .teacher-import-columns__chip {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            color: inherit;
            font-size: 0.85rem;
        }

        .teacher-import-stat {
            height: 100%;
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="dark"] .teacher-import-stat {
            background: rgba(148, 163, 184, 0.08);
            border: 0;
        }

        .teacher-import-stat span {
            display: block;
            font-size: 0.82rem;
            color: var(--admin-text-soft);
            margin-bottom: 0.35rem;
        }

        .teacher-import-stat strong {
            font-size: 1.4rem;
            color: var(--admin-text);
        }

        .teacher-import-guide {
            display: grid;
            gap: 0.85rem;
        }

        .teacher-import-guide__item {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .teacher-import-guide__item strong {
            width: 1.25rem;
            color: #60a5fa;
        }

        .teacher-import-row--module {
            background: rgba(59, 130, 246, 0.08);
        }

        .teacher-import-row--lesson {
            background: rgba(148, 163, 184, 0.04);
        }

        .teacher-import-type {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .teacher-import-type--module {
            background: rgba(37, 99, 235, 0.14);
            color: #1d4ed8;
        }

        html[data-theme="dark"] .teacher-import-type--module {
            color: #93c5fd;
        }

        .teacher-import-type--lesson {
            background: rgba(16, 185, 129, 0.14);
            color: #047857;
        }

        html[data-theme="dark"] .teacher-import-type--lesson {
            color: #86efac;
        }

        /* Dark mode fixes for import section */
        html[data-theme="dark"] .teacher-import-card {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        html[data-theme="dark"] .teacher-import-stat {
            background: rgba(15, 23, 42, 0.5);
        }

        html[data-theme="dark"] .form-control:disabled,
        html[data-theme="dark"] .form-select:disabled {
            background-color: rgba(15, 23, 42, 0.6);
            color: rgba(255, 255, 255, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        html[data-theme="dark"] .teacher-import-guide__item span {
            color: rgba(255, 255, 255, 0.8);
        }

        html[data-theme="dark"] .form-text.text-muted {
            color: rgba(255, 255, 255, 0.5) !important;
        }
    </style>

    <!-- Lesson Preview Modal -->
    <div class="modal fade lesson-preview-modal" id="lessonPreviewModal" tabindex="-1" aria-labelledby="lessonPreviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lessonPreviewModalLabel">{{ __('lessons::teacher/messages.modal.preview_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="video-container" id="previewVideoContainer">
                        <div class="preview-loading d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">{{ __('lessons::teacher/messages.modal.loading') }}</span>
                            </div>
                        </div>
                        <div id="previewPlayerArea"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
    <script>
        $(document).ready(function() {
            const $modal = $('#lessonPreviewModal');
            const $playerArea = $('#previewPlayerArea');
            const $loading = $('.preview-loading');
            let player = null;

            $('.js-lesson-preview-btn').on('click', function() {
                const url = $(this).data('url');
                const title = $(this).attr('title') || "{{ __('lessons::teacher/messages.modal.preview_title') }}";

                $modal.find('.modal-title').text(title);
                $modal.modal('show');
                $loading.removeClass('d-none');
                $playerArea.empty();

                if (player) {
                    player.dispose();
                    player = null;
                }

                $.get(url, function(response) {
                    $loading.addClass('d-none');
                    if (response.success) {
                        const videoData = response.data.video;
                        $modal.find('.modal-title').text(response.data.name);

                        if (videoData.type === 'embed') {
                            $playerArea.html(`<iframe src="${videoData.url}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`);
                        } else if (videoData.type === 'file') {
                            const videoId = 'preview-player-' + response.data.id;
                            $playerArea.html(`<video id="${videoId}" class="video-js vjs-big-play-centered vjs-fluid" controls preload="auto">
                                <source src="${videoData.url}" type="video/mp4">
                            </video>`);

                            player = videojs(videoId, {
                                autoplay: true,
                                fluid: true
                            });
                        }
                    } else {
                        $playerArea.html(`<div class="p-5 text-center text-white">
                            <i class="fa-solid fa-circle-exclamation fs-1 mb-3 text-warning"></i>
                            <p>${response.message || "{{ __('teacher::teacher/lesson/common.modal.error_video') }}"}</p>
                        </div>`);
                    }
                }).fail(function() {
                    $loading.addClass('d-none');
                    $playerArea.html(`<div class="p-5 text-center text-white">
                        <i class="fa-solid fa-triangle-exclamation fs-1 mb-3 text-danger"></i>
                        <p>{{ __('teacher::teacher/lesson/common.modal.error_server') }}</p>
                    </div>`);
                });
            });

            $modal.on('hidden.bs.modal', function() {
                if (player) {
                    player.dispose();
                    player = null;
                }
                $playerArea.empty();
            });
        });
    </script>
@endsection
