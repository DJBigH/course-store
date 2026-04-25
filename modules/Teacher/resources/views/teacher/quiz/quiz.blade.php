@extends('layouts.teacher')

@section('content')
@php
    $stateQuizzes = $teacher->getFeatureState('can_manage_quizzes');
@endphp
    <div class="teacher-panel">
        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('quizzes::teacher/messages.page_name') }}</h3>
                <p class="text-muted mb-0">{{ __('quizzes::teacher/messages.description') }}</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('teacher.dashboard.lessons.index', $course->id) }}" class="btn btn-outline-secondary">{{ __('quizzes::teacher/messages.back_to_lessons') }}</a>
                @if($stateQuizzes['is_maintenance'])
                    <button class="btn btn-secondary" disabled>{{ __('quizzes::teacher/messages.create_btn') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})</button>
                @else
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#quizCreateForm">{{ __('quizzes::teacher/messages.create_btn') }}</button>
                @endif
            </div>
        </div>

        <div class="teacher-panel mb-4 quiz-search-panel">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">{{ __('quizzes::teacher/messages.search_student_label') }}</label>
                    <input type="text" name="student_search" value="{{ $studentSearch ?? '' }}" class="form-control" placeholder="{{ __('quizzes::teacher/messages.search_placeholder') }}">
                    <div class="form-text">{{ __('quizzes::teacher/messages.search_student_help') }}</div>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-grow-1" type="submit">{{ __('quizzes::teacher/messages.search_btn') }}</button>
                    <a href="{{ route('teacher.dashboard.quizzes.index', $course->id) }}" class="btn btn-outline-secondary">{{ __('quizzes::teacher/messages.clear_filter') }}</a>
                </div>
            </form>

            <div class="mt-3">
                <div class="small text-muted mb-2">{{ __('quizzes::teacher/messages.buyers_label') }}</div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse ($buyers as $buyer)
                        <span class="badge rounded-pill {{ in_array($buyer->id, ($assignedStudentIds ?? collect())->all(), true) ? 'bg-success' : 'bg-light text-dark border' }}">
                            #{{ $buyer->id }} • {{ $buyer->name }} • {{ $buyer->email }}
                        </span>
                    @empty
                        <span class="text-muted small">{{ __('quizzes::teacher/messages.no_students_found') }}</span>
                    @endforelse
                </div>
            </div>
        </div>

        @if (session('msg_success'))<div class="alert alert-success">{{ session('msg_success') }}</div>@endif
        @if (session('msg_danger'))<div class="alert alert-danger">{{ session('msg_danger') }}</div>@endif

        <div class="collapse mb-4" id="quizCreateForm">
            <div class="teacher-panel">
                <form method="POST" action="{{ route('teacher.dashboard.quizzes.store', $course->id) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('quizzes::teacher/messages.fields.title') }}</label>
                            <input name="title" class="form-control" placeholder="Quiz 1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('quizzes::teacher/messages.fields.lesson') }}</label>
                            <select name="lesson_id" class="form-select">
                                <option value="">{{ __('quizzes::teacher/messages.fields.no_lesson') }}</option>
                                @foreach ($lessons as $lesson)
                                    <option value="{{ $lesson->id }}">#{{ $lesson->id }} • {{ $lesson->name_locale }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">{{ __('quizzes::teacher/messages.fields.passing_score') }}</label><input name="passing_score" type="number" class="form-control" value="70"></div>
                        <div class="col-md-4"><label class="form-label">{{ __('quizzes::teacher/messages.fields.max_attempts') }}</label><input name="max_attempts" type="number" class="form-control" placeholder="{{ __('quizzes::teacher/messages.fields.unlimited') }}"></div>
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label">{{ __('quizzes::teacher/messages.fields.time_limit') }}</label>
                            <input name="time_limit_minutes" type="number" class="form-control" placeholder="{{ __('quizzes::teacher/messages.fields.time_limit_help') }}">
                            <div class="form-text">{{ __('quizzes::teacher/messages.fields.time_limit_help') }}</div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label">{{ __('quizzes::teacher/messages.fields.deadline') }}</label>
                            <input name="deadline_at" type="datetime-local" class="form-control">
                            <div class="form-text">{{ __('quizzes::teacher/messages.fields.deadline_help') }}</div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-secondary border small mb-0 text-light" style="background: rgba(148, 163, 184, 0.12);">
                                <strong>{{ __('quizzes::teacher/messages.tips.title') }}</strong> <span class="text-muted">{{ __('quizzes::teacher/messages.tips.content') }}</span>
                            </div>
                        </div>
                        <div class="col-12"><label class="form-label">{{ __('quizzes::teacher/messages.fields.description') }}</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                        <div class="col-12">
                            <button class="btn btn-primary">{{ __('quizzes::teacher/messages.save_btn') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($quizzes as $quiz)
                <div class="col-lg-6">
                    <div class="teacher-panel h-100">
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <h5 class="mb-1">{{ $quiz->title }}</h5>
                                <div class="text-muted small">
                                    @if ($quiz->lesson)
                                        #{{ $quiz->lesson->id }} • {{ $quiz->lesson->name_locale }}
                                    @else
                                        {{ __('quizzes::teacher/messages.assigned_to_course') }}
                                    @endif
                                </div>
                            </div>
                            <span class="badge bg-{{ $quiz->status ? 'success' : 'secondary' }}">{{ $quiz->status ? __('quizzes::teacher/messages.status_active') : __('quizzes::teacher/messages.status_hidden') }}</span>
                        </div>
                        @php
                            $quizAssignments = $assignments->where('quiz_id', $quiz->id);
                            $assignmentDeadline = $quizAssignments->pluck('deadline_at')->filter()->sort()->first();
                            $quizDeadline = $quiz->deadline_at;
                            $nearestDeadline = $quizDeadline ?: $assignmentDeadline;
                            $deadlineBadge = null;

                            if (! $nearestDeadline) {
                                $deadlineBadge = ['label' => __('quizzes::teacher/messages.no_deadline_badge'), 'class' => 'bg-success'];
                            } elseif (now()->greaterThan($nearestDeadline)) {
                                $deadlineBadge = ['label' => __('quizzes::teacher/messages.expired_badge'), 'class' => 'bg-danger'];
                            } elseif (now()->diffInHours($nearestDeadline, false) <= 24) {
                                $deadlineBadge = ['label' => __('quizzes::teacher/messages.expiring_badge'), 'class' => 'bg-warning text-dark'];
                            } else {
                                $deadlineBadge = ['label' => __('quizzes::teacher/messages.active_badge'), 'class' => 'bg-primary'];
                            }
                        @endphp
                        <div class="mt-3 small text-muted">
                            {{ __('quizzes::teacher/messages.questions_count', ['count' => $quiz->questions_count]) }} • {{ __('quizzes::teacher/messages.submissions_count', ['count' => $quiz->submissions_count]) }} • {{ __('quizzes::teacher/messages.labels.passing', ['score' => $quiz->passing_score]) }} • {{ $quiz->max_attempts ? __('quizzes::teacher/messages.labels.attempts', ['count' => $quiz->max_attempts]) : __('quizzes::teacher/messages.labels.unlimited') }}
                        </div>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <span class="badge {{ $deadlineBadge['class'] }}">{{ $deadlineBadge['label'] }}</span>
                            @if ($nearestDeadline)
                                <span class="badge bg-dark text-light border border-secondary">{{ __('quizzes::teacher/messages.expires_label') }} {{ $nearestDeadline->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('teacher.dashboard.quizzes.results', [$course->id, $quiz->id]) }}" class="btn btn-outline-success">{{ __('quizzes::teacher/messages.view_results') }}</a>
                            @if($stateQuizzes['is_maintenance'])
                                <button class="btn btn-outline-secondary" disabled>{{ __('quizzes::teacher/messages.edit_button') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})</button>
                            @else
                                <a href="{{ route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id]) }}" class="btn btn-outline-secondary">{{ __('quizzes::teacher/messages.edit_button') }}</a>
                            @endif
                            <span class="small text-muted align-self-center">{{ __('quizzes::teacher/messages.id_label', ['id' => $quiz->id]) }}</span>
                            
                            <form method="POST" action="{{ route('teacher.dashboard.quizzes.assign', [$course->id, $quiz->id]) }}" 
                                class="d-flex gap-2 flex-wrap w-100 mt-2 quiz-assign-form" 
                                data-quiz-assign-form 
                                data-buyers='@json($buyers->values())' 
                                data-quiz-id="{{ $quiz->id }}"
                                data-i18n-selected="{{ __('quizzes::teacher/messages.selected_count', ['count' => ':count']) }}"
                                data-i18n-empty="{{ __('quizzes::teacher/messages.assign_help_empty') }}"
                                data-i18n-no-students="{{ __('quizzes::teacher/messages.no_students_found') }}"
                                data-i18n-deselect="{{ __('quizzes::teacher/messages.deselect') }}"
                                data-i18n-select="{{ __('quizzes::teacher/messages.select') }}"
                                data-i18n-alert-required="{{ __('quizzes::teacher/messages.alert_select_required') }}"
                            >
                                @csrf
                                <div class="flex-grow-1" style="min-width: 320px;">
                                    <input type="text" class="form-control mb-2" placeholder="{{ __('quizzes::teacher/messages.search_placeholder') }}" data-quiz-buyer-search {{ $stateQuizzes['is_maintenance'] ? 'disabled' : '' }} style="color: #e5e7eb; background: rgba(15, 23, 42, 0.5);">
                                    <div class="quiz-buyer-results" data-quiz-buyer-results></div>
                                    <div class="small text-muted mt-2" data-quiz-selected-summary>{{ __('quizzes::teacher/messages.assign_help_empty') }}</div>
                                    <div data-quiz-selected-inputs></div>
                                </div>
                                <button class="btn btn-outline-primary btn-assign-selected" type="submit" {{ $stateQuizzes['is_maintenance'] ? 'disabled' : '' }}>
                                    {{ __('quizzes::teacher/messages.assign_selected') }} {{ $stateQuizzes['is_maintenance'] ? '('.(__('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI').')' : '' }}
                                </button>
                                <button class="btn btn-primary btn-assign-all" type="submit" {{ $stateQuizzes['is_maintenance'] ? 'disabled' : '' }}>
                                    {{ __('quizzes::teacher/messages.assign_all') }} {{ $stateQuizzes['is_maintenance'] ? '('.(__('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI').')' : '' }}
                                </button>
                                <div class="small text-muted w-100">{{ __('quizzes::teacher/messages.assign_help_hint') }}</div>
                            </form>
                            
                            @if($stateQuizzes['is_maintenance'])
                                <button class="btn btn-outline-danger" disabled>{{ __('quizzes::teacher/messages.delete') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})</button>
                            @else
                                <form method="POST" action="{{ route('teacher.dashboard.quizzes.destroy', [$course->id, $quiz->id]) }}" onsubmit="return confirm('{{ __('quizzes::teacher/messages.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger">{{ __('quizzes::teacher/messages.delete') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">{{ __('quizzes::teacher/messages.no_quizzes') }}</div></div>
            @endforelse
        </div>
    </div>

    <style>
        .quiz-search-panel .form-label,
        .quiz-search-panel .form-text,
        .quiz-search-panel .small,
        .quiz-search-panel .text-muted {
            color: var(--admin-muted) !important;
        }

        .quiz-search-panel .badge.bg-light {
            color: var(--admin-text) !important;
            background: var(--admin-subtle-bg) !important;
            border-color: var(--admin-border) !important;
        }

        .quiz-search-panel .badge.rounded-pill {
            color: #fff !important;
        }

        html[data-theme="light"] .quiz-search-panel .badge.rounded-pill.bg-success {
            color: #fff !important;
        }

        .quiz-search-panel .form-control,
        .quiz-search-panel .btn-outline-primary,
        .quiz-search-panel .btn-outline-secondary {
            border-color: rgba(148, 163, 184, 0.28);
        }

        .quiz-search-panel .form-control {
            color: var(--admin-text);
            background: var(--admin-input-bg);
        }

        .quiz-search-panel .form-control::placeholder {
            color: #94a3b8;
        }

        .quiz-search-panel .btn-outline-primary,
        .quiz-search-panel .btn-outline-secondary {
            color: var(--admin-primary);
        }

        html[data-theme="dark"] .quiz-search-panel .btn-outline-primary,
        html[data-theme="dark"] .quiz-search-panel .btn-outline-secondary {
            color: #dbeafe;
        }

        .quiz-buyer-results {
            display: grid;
            gap: 0.5rem;
            max-height: 220px;
            overflow: auto;
        }

        .quiz-buyer-result {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.8rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 14px;
            cursor: pointer;
            background: rgba(148, 163, 184, 0.06);
        }

        .quiz-buyer-result:hover,
        .quiz-buyer-result.is-selected {
            border-color: rgba(59, 130, 246, 0.35);
            background: rgba(59, 130, 246, 0.12);
        }

        .quiz-buyer-result.is-selected {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.18) inset;
        }

        .quiz-buyer-result__meta {
            font-size: 0.8rem;
            color: var(--admin-muted);
        }

        .quiz-search-panel .quiz-buyer-result__meta {
            color: #cbd5e1;
        }

        .quiz-buyer-select-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 56px;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f172a;
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
            flex-shrink: 0;
        }

        .quiz-buyer-select-pill.is-selected {
            color: #ffffff;
            background: #2563eb;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }
    </style>

    <script>
        (() => {
            document.querySelectorAll('[data-quiz-assign-form]').forEach((form) => {
                const buyers     = JSON.parse(form.dataset.buyers || '[]');
                const searchInput       = form.querySelector('[data-quiz-buyer-search]');
                const resultsContainer  = form.querySelector('[data-quiz-buyer-results]');
                const selectedSummary   = form.querySelector('[data-quiz-selected-summary]');
                const hiddenInputs      = form.querySelector('[data-quiz-selected-inputs]');
                const assignAllButton   = form.querySelector('.btn-assign-all');
                const assignSelectedBtn = form.querySelector('.btn-assign-selected');

                // i18n
                const i18n = {
                    selected: form.dataset.i18nSelected,
                    empty: form.dataset.i18nEmpty,
                    noStudents: form.dataset.i18nNoStudents,
                    deselect: form.dataset.i18nDeselect,
                    select: form.dataset.i18nSelect,
                    alertRequired: form.dataset.i18nAlertRequired
                };

                // Track selected IDs
                const selected = new Set();

                const syncHiddenInputs = () => {
                    hiddenInputs.innerHTML = '';
                    selected.forEach((id) => {
                        const inp = document.createElement('input');
                        inp.type  = 'hidden';
                        inp.name  = 'student_ids[]';
                        inp.value = id;
                        hiddenInputs.appendChild(inp);
                    });

                    if (selectedSummary) {
                        selectedSummary.textContent = selected.size > 0
                            ? i18n.selected.replace(':count', selected.size)
                            : i18n.empty;
                    }
                };

                const render = () => {
                    const term     = (searchInput.value || '').toLowerCase().trim();
                    const filtered = buyers.filter((b) => {
                        const hay = `${b.name || ''} ${b.email || ''}`.toLowerCase();
                        return term === '' || hay.includes(term);
                    }).slice(0, 10);

                    resultsContainer.innerHTML = filtered.length
                        ? filtered.map((b) => {
                            const isSelected = selected.has(String(b.id));
                            return `
                                <button type="button"
                                    class="quiz-buyer-result${isSelected ? ' is-selected' : ''}"
                                    data-buyer-id="${b.id}">
                                    <div>
                                        <div class="fw-semibold">#${b.id} • ${b.name}</div>
                                        <div class="quiz-buyer-result__meta">${b.email || ''}</div>
                                    </div>
                                    <span class="quiz-buyer-select-pill${isSelected ? ' is-selected' : ''}">
                                        ${isSelected ? i18n.deselect : i18n.select}
                                    </span>
                                </button>`;
                        }).join('')
                        : `<div class="text-muted small py-1">${i18n.noStudents}</div>`;

                    resultsContainer.querySelectorAll('[data-buyer-id]').forEach((btn) => {
                        btn.addEventListener('click', () => {
                            const id  = String(btn.dataset.buyerId);
                            if (selected.has(id)) {
                                selected.delete(id);
                            } else {
                                selected.add(id);
                            }
                            syncHiddenInputs();
                            render(); // Re-render để cập nhật trạng thái nút
                        });
                    });
                };

                // Nút "Giao cho tất cả" — không cần selected, controller tự lấy buyerIds
                if (assignAllButton) {
                    assignAllButton.addEventListener('click', () => {
                        // Xóa student_ids để controller hiểu là gán tất cả
                        selected.clear();
                        syncHiddenInputs();
                    });
                }

                if (assignSelectedBtn) {
                    assignSelectedBtn.addEventListener('click', (e) => {
                        if (selected.size === 0) {
                            e.preventDefault();
                            alert(i18n.alertRequired);
                        }
                    });
                }

                searchInput.addEventListener('input', render);
                syncHiddenInputs();
                render();
            });
        })();
    </script>
@endsection
