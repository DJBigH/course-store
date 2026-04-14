@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.lessons.title', ['course' => $course->name_locale]) }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.description') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('teacher.dashboard.courses') }}" class="btn btn-outline-secondary">
                    {{ __('teacher::dashboard.common.back') }}
                </a>
                <a href="{{ route('teacher.dashboard.lessons.trash', $course->id) }}" class="btn btn-outline-secondary">
                    {{ __('teacher::dashboard.lessons.actions.trash') }}
                </a>
                <a href="{{ route('teacher.dashboard.lessons.create', $course->id) }}" class="btn btn-primary">
                    {{ __('teacher::dashboard.lessons.actions.create') }}
                </a>
            </div>
        </div>

        @if (!$canImportExportLessons)
            @include('teacher::clients.dashboard.partials.package_feature_notice', [
                'message' => __('teacher::dashboard.package_features.lessons_locked_import_export'),
            ])
        @endif

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        @if ($canImportExportLessons)
            <div class="teacher-panel mb-4">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                    <div>
                        <h4 class="h5 mb-1">{{ __('teacher::dashboard.lessons.import.title') }}</h4>
                        <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.import.description') }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('teacher.dashboard.lessons.export', [$course->id, 'format' => 'csv']) }}" class="btn btn-outline-secondary">
                            {{ __('teacher::dashboard.lessons.actions.export_csv') }}
                        </a>
                        <a href="{{ route('teacher.dashboard.lessons.import.example', [$course->id, 'format' => 'csv']) }}" class="btn btn-outline-secondary">
                            {{ __('teacher::dashboard.lessons.actions.download_example_csv') }}
                        </a>
                        <a href="{{ route('teacher.dashboard.lessons.import.example', [$course->id, 'format' => 'xlsx']) }}" class="btn btn-outline-secondary">
                            {{ __('teacher::dashboard.lessons.actions.download_example_xlsx') }}
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
                                <span>{{ __('teacher::dashboard.lessons.import.summary_total') }}</span>
                                <strong>{{ $importSummary['total_rows'] ?? 0 }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="teacher-import-stat">
                                <span>{{ __('teacher::dashboard.lessons.import.summary_modules') }}</span>
                                <strong>{{ $importSummary['imported_modules'] ?? 0 }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="teacher-import-stat">
                                <span>{{ __('teacher::dashboard.lessons.import.summary_lessons') }}</span>
                                <strong>{{ $importSummary['imported_lessons'] ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('lesson_import_errors'))
                    <div class="alert alert-danger mb-3">
                        <div class="fw-semibold mb-2">{{ __('teacher::dashboard.lessons.import.errors_title') }}</div>
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
                            <div class="teacher-import-card__label">{{ __('teacher::dashboard.lessons.import.selectors_title') }}</div>
                            @if (!empty($existingModuleSelectors))
                                <select class="form-select" size="{{ min(max(count($existingModuleSelectors), 3), 8) }}" disabled>
                                    @foreach ($existingModuleSelectors as $selector)
                                        <option>{{ $selector['label'] }}</option>
                                    @endforeach
                                </select>
                            @else
                                <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.import.selectors_empty') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="teacher-import-card h-100">
                            <div class="teacher-import-card__label">{{ __('teacher::dashboard.lessons.import.quick_guide_title') }}</div>
                            <div class="teacher-import-guide">
                                <div class="teacher-import-guide__item">
                                    <strong>1.</strong>
                                    <span>{{ __('teacher::dashboard.lessons.import.quick_guide_example') }}</span>
                                </div>
                                <div class="teacher-import-guide__item">
                                    <strong>2.</strong>
                                    <span>{{ __('teacher::dashboard.lessons.import.quick_guide_parent') }}</span>
                                </div>
                                <div class="teacher-import-guide__item">
                                    <strong>3.</strong>
                                    <span>{{ __('teacher::dashboard.lessons.import.quick_guide_limit') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('teacher.dashboard.lessons.import.preview', $course->id) }}" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-8">
                            <label class="form-label">{{ __('teacher::dashboard.lessons.import.file_label') }}</label>
                            <input type="file" name="lesson_import_file" accept=".csv,.txt,.xlsx" class="form-control @error('lesson_import_file') is-invalid @enderror">
                            <div class="form-text">{{ __('teacher::dashboard.lessons.import.file_help') }}</div>
                            @error('lesson_import_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4">
                            <button type="submit" class="btn btn-primary w-100">
                                {{ __('teacher::dashboard.lessons.actions.import_submit') }}
                            </button>
                        </div>
                    </div>
                </form>

                @if (!empty($lessonImportPreview['rows']))
                    <div class="teacher-import-card mt-4">
                        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                            <div>
                                <div class="teacher-import-card__label mb-1">{{ __('teacher::dashboard.lessons.import.preview_title') }}</div>
                                <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.import.preview_description') }}</p>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('teacher.dashboard.lessons.import.confirm', $course->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('teacher::dashboard.lessons.actions.confirm_submit') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('teacher.dashboard.lessons.import.clear', $course->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary">
                                        {{ __('teacher::dashboard.lessons.actions.clear_preview') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="teacher-import-stat">
                                    <span>{{ __('teacher::dashboard.lessons.import.file_name') }}</span>
                                    <strong class="fs-6">{{ $lessonImportPreview['file_name'] ?? 'n/a' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="teacher-import-stat">
                                    <span>{{ __('teacher::dashboard.lessons.import.format') }}</span>
                                    <strong class="text-uppercase fs-6">{{ $lessonImportPreview['detected_format'] ?? 'n/a' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="teacher-import-stat">
                                    <span>{{ __('teacher::dashboard.lessons.import.generated_at') }}</span>
                                    <strong class="fs-6">{{ $lessonImportPreview['generated_at'] ?? 'n/a' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('teacher::dashboard.lessons.table.name') }}</th>
                                        <th>{{ __('teacher::dashboard.lessons.import.preview_type') }}</th>
                                        <th>{{ __('teacher::dashboard.lessons.import.preview_parent') }}</th>
                                        <th>{{ __('teacher::dashboard.lessons.import.preview_release') }}</th>
                                        <th>{{ __('teacher::dashboard.lessons.form.position') }}</th>
                                        <th>{{ __('teacher::dashboard.lessons.form.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lessonImportPreview['rows'] as $previewRow)
                                        @php
                                            $isModule = ($previewRow['type'] ?? '') === 'module';
                                            $releaseMode = $previewRow['release_mode'] ?? 'immediate';
                                            $releaseLabel = match ($releaseMode) {
                                                'datetime' => 'Mo vao: ' . ($previewRow['release_at'] ?? '-'),
                                                'days_after_enrollment' => 'Mo sau ' . ($previewRow['release_after_days'] ?? 0) . ' ngay',
                                                'after_previous_completed' => 'Mo sau khi hoc xong bai truoc',
                                                default => 'Mo ngay lap tuc',
                                            };
                                            $rowClass = $isModule
                                                ? 'teacher-import-row teacher-import-row--module'
                                                : 'teacher-import-row teacher-import-row--lesson';
                                            $typeClass = $isModule
                                                ? 'teacher-import-type--module'
                                                : 'teacher-import-type--lesson';
                                            $typeLabel = $isModule
                                                ? __('teacher::dashboard.lessons.module_label')
                                                : __('teacher::dashboard.lessons.lesson_label');
                                            $statusLabel = (string) ($previewRow['status'] ?? 1) === '1'
                                                ? __('teacher::dashboard.common.active')
                                                : __('teacher::dashboard.common.no');
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
                        <div class="text-uppercase small text-muted mb-1">{{ __('teacher::dashboard.lessons.module_label') }}</div>
                        <h4 class="h5 mb-1">{{ $module->name_locale }}</h4>
                        <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.labels.position', ['position' => $module->position]) }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('teacher.dashboard.lessons.edit', [$course->id, $module->id]) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('teacher::dashboard.lessons.actions.edit') }}
                        </a>
                        <a href="{{ route('teacher.dashboard.lessons.create', [$course->id, 'module' => $module->id]) }}" class="btn btn-sm btn-outline-secondary">
                            {{ __('teacher::dashboard.lessons.actions.add_child') }}
                        </a>
                        <form method="POST" action="{{ route('teacher.dashboard.lessons.delete', [$course->id, $module->id]) }}" onsubmit="return confirm('{{ __('teacher::dashboard.lessons.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                {{ __('teacher::dashboard.lessons.actions.delete') }}
                            </button>
                        </form>
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
                                ])
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.empty_module') }}</p>
                @endif
            </section>
        @empty
            <div class="teacher-panel">
                <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.empty') }}</p>
            </div>
        @endforelse
    </div>

    <style>
        .teacher-import-card {
            padding: 1rem;
            border-radius: 18px;
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
            background: rgba(148, 163, 184, 0.08);
        }

        .teacher-import-stat span {
            display: block;
            font-size: 0.82rem;
            color: var(--admin-text-soft);
            margin-bottom: 0.35rem;
        }

        .teacher-import-stat strong {
            font-size: 1.4rem;
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
            color: #93c5fd;
        }

        .teacher-import-type--lesson {
            background: rgba(16, 185, 129, 0.14);
            color: #86efac;
        }
    </style>
@endsection
