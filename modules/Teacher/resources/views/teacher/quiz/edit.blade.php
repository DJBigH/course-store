@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('quizzes::teacher/messages.edit.title') }}</h3>
                <p class="text-muted mb-0">{{ $quiz->title }}</p>
            </div>
            <a href="{{ route('teacher.dashboard.quizzes.index', $course->id) }}" class="btn btn-outline-secondary">{{ __('quizzes::teacher/messages.edit.back') }}</a>
        </div>

        @if (!$teacher->packageHasFeature('can_import_export'))
            @include('teacher::clients.dashboard.partials.package_feature_notice', [
                'message' => __('packages::teacher.package_features.import_export_locked'),
            ])
        @endif

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        {{-- {{ __('quizzes::teacher/messages.edit.settings_title') }} --}}
        <form method="POST" action="{{ route('teacher.dashboard.quizzes.update', [$course->id, $quiz->id]) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('quizzes::teacher/messages.fields.title') }}</label>
                    <input name="title" class="form-control" value="{{ $quiz->title }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('quizzes::teacher/messages.fields.lesson') }}</label>
                    <select name="lesson_id" class="form-select">
                        <option value="">{{ __('quizzes::teacher/messages.fields.no_lesson') }}</option>
                        @foreach ($lessons as $lesson)
                            <option value="{{ $lesson->id }}" @selected($quiz->lesson_id == $lesson->id)>{{ $lesson->name_locale }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('quizzes::teacher/messages.fields.passing_score') }}</label>
                    <input name="passing_score" type="number" class="form-control" value="{{ $quiz->passing_score }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('quizzes::teacher/messages.fields.max_attempts') }}</label>
                    <input name="max_attempts" type="number" class="form-control" value="{{ $quiz->max_attempts }}" placeholder="{{ __('quizzes::teacher/messages.fields.unlimited') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('quizzes::teacher/messages.fields.time_limit') }}</label>
                    <input name="time_limit_minutes" type="number" class="form-control" value="{{ $quiz->time_limit_minutes }}" placeholder="{{ __('quizzes::teacher/messages.fields.unlimited') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('quizzes::teacher/messages.fields.deadline') }}</label>
                    <input name="deadline_at" type="datetime-local" class="form-control" value="{{ optional($quiz->deadline_at)->format('Y-m-d\TH:i') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('quizzes::teacher/messages.fields.description') }}</label>
                    <textarea name="description" class="form-control" rows="2">{{ $quiz->description }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch p-0 d-flex align-items-center gap-4">
                        <label class="form-check-label fw-bold mb-0">{{ __('quizzes::teacher/messages.edit.show_answers_label') }}</label>
                        <input name="show_answers_after" type="checkbox" class="form-check-input ms-0" value="1" style="width: 50px; height: 24px;" @checked($quiz->show_answers_after)>
                    </div>
                    <div class="form-text mt-1 text-info"><i class="fas fa-info-circle me-1"></i> {{ __('quizzes::teacher/messages.edit.show_answers_help') }}</div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">{{ __('quizzes::teacher/messages.edit.save_changes') }}</button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        {{-- Danh sách câu hỏi --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0">{{ __('quizzes::teacher/messages.edit.questions_list_title', ['count' => $quiz->questions->count()]) }}</h5>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if ($teacher->packageHasFeature('can_import_export'))
                    <a href="{{ route('teacher.dashboard.quizzes.import.template', [$course->id, $quiz->id]) }}" class="btn btn-outline-secondary btn-sm" title="Tải file CSV mẫu">
                        📥 {{ __('quizzes::teacher/messages.edit.import_template') }}
                    </a>
                    <a href="{{ route('teacher.dashboard.quizzes.export', [$course->id, $quiz->id]) }}" class="btn btn-outline-success btn-sm">
                        📤 {{ __('quizzes::teacher/messages.edit.export_questions') }}
                    </a>
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#importForm">
                        📁 {{ __('quizzes::teacher/messages.edit.import_csv') }}
                    </button>
                @endif
                @php
                    $aiQuizStatus = \Modules\Settings\src\Models\Setting::getValue('ai_quiz_enabled');
                    $isAiMaintenance = $aiQuizStatus === '2';
                    $isAiEnabled = $aiQuizStatus !== '0';
                    $hasAiFeature = $teacher->packageHasFeature('can_use_ai_quiz');
                @endphp
                @if($isAiEnabled)
                <button class="btn btn-outline-info btn-sm text-info fw-bold" type="button" 
                    @if(!$isAiMaintenance && $hasAiFeature) data-bs-toggle="modal" data-bs-target="#aiGenerateModal" @else disabled @endif>
                    ✨ {{ __('quizzes::teacher/messages.edit.ai_generate') }}
                    @if($isAiMaintenance)
                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">BẢO TRÌ</span>
                    @elseif(!$hasAiFeature)
                        <i class="fas fa-lock ms-1 small"></i>
                    @endif
                </button>
                @endif
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#addQuestionForm">
                    + {{ __('quizzes::teacher/messages.edit.add_question') }}
                </button>
            </div>
        </div>

        {{-- Import form --}}
        <div class="collapse mb-3" id="importForm">
            <div class="border rounded p-3">
                <h6 class="mb-3">{{ __('quizzes::teacher/messages.edit.import_form_title') }}</h6>
                <form method="POST" action="{{ route('teacher.dashboard.quizzes.import', [$course->id, $quiz->id]) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex gap-3 align-items-end flex-wrap">
                        <div class="flex-grow-1">
                            <label class="form-label form-label-sm">{{ __('quizzes::teacher/messages.edit.import_file_label') }}</label>
                            <input type="file" name="file" class="form-control form-control-sm" accept=".csv,.txt" required>
                            <div class="form-text">{{ __('quizzes::teacher/messages.edit.import_format_help') }}</div>
                        </div>
                        <button class="btn btn-sm btn-primary">{{ __('quizzes::teacher/messages.edit.import_csv') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Form thêm câu hỏi --}}
        <div class="collapse mb-3" id="addQuestionForm">
            <div class="border rounded p-3">
                <h6 class="mb-3">{{ __('quizzes::teacher/messages.edit.add_form_title') }}</h6>
                <form method="POST" action="{{ route('teacher.dashboard.quizzes.question.store', [$course->id, $quiz->id]) }}" id="addQuestionFormEl">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('quizzes::teacher/messages.edit.question_content') }} <span class="text-danger">*</span></label>
                            <textarea name="question" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('quizzes::teacher/messages.edit.question_type') }}</label>
                            <select name="question_type" class="form-select" id="questionTypeSelect">
                                <option value="single_choice">{{ __('quizzes::teacher/messages.edit.types.single') }}</option>
                                <option value="multiple_choice">{{ __('quizzes::teacher/messages.edit.types.multiple') }}</option>
                                <option value="true_false">{{ __('quizzes::teacher/messages.edit.types.true_false') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('quizzes::teacher/messages.edit.points') }}</label>
                            <input name="points" type="number" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('quizzes::teacher/messages.edit.choices_label') }}</label>
                            <div id="choicesList" class="d-grid gap-2">
                                @foreach (['A','B','C','D'] as $idx => $letter)
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold" style="width:40px">{{ $letter }}</span>
                                        <input type="text" name="choices[{{ $idx }}][text]" class="form-control" placeholder="{{ __('quizzes::teacher/messages.edit.choice_placeholder', ['letter' => $letter]) }}">
                                        <div class="input-group-text">
                                            <label class="mb-0 d-flex align-items-center gap-1">
                                                <input type="checkbox" name="choices[{{ $idx }}][is_correct]" value="1"> {{ __('quizzes::teacher/messages.edit.correct_label') }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">{{ __('quizzes::teacher/messages.edit.add_question') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($isAiEnabled && !$isAiMaintenance && $hasAiFeature)
        <div class="modal fade" id="aiGenerateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content text-light" style="background: #1e293b; border-color: #334155;">
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title"><i class="fas fa-magic text-info me-2"></i>{{ __('quizzes::teacher/messages.edit.ai_modal.title') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="aiAlertBox" class="alert d-none"></div>
                        <div class="mb-3">
                            <label class="form-label text-info">{{ __('quizzes::teacher/messages.edit.ai_modal.topic_label') }}</label>
                            <input type="text" id="aiTopic" class="form-control" placeholder="{{ __('quizzes::teacher/messages.edit.ai_modal.topic_placeholder') }}" style="background: rgba(15, 23, 42, 0.5); color: #fff; border-color: #475569;">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small">{{ __('quizzes::teacher/messages.edit.ai_modal.amount_label') }}</label>
                                <select id="aiAmount" class="form-select" style="background: rgba(15, 23, 42, 0.5); color: #fff; border-color: #475569;">
                                    <option value="3">{{ __('quizzes::teacher/messages.edit.ai_modal.amount_unit', ['count' => 3]) }}</option>
                                    <option value="5" selected>{{ __('quizzes::teacher/messages.edit.ai_modal.amount_unit', ['count' => 5]) }}</option>
                                    <option value="10">{{ __('quizzes::teacher/messages.edit.ai_modal.amount_unit', ['count' => 10]) }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">{{ __('quizzes::teacher/messages.edit.ai_modal.difficulty_label') }}</label>
                                <select id="aiDifficulty" class="form-select" style="background: rgba(15, 23, 42, 0.5); color: #fff; border-color: #475569;">
                                    <option value="Dễ">{{ __('quizzes::teacher/messages.edit.ai_modal.difficulty_easy') }}</option>
                                    <option value="Trung bình" selected>{{ __('quizzes::teacher/messages.edit.ai_modal.difficulty_medium') }}</option>
                                    <option value="Khó">{{ __('quizzes::teacher/messages.edit.ai_modal.difficulty_hard') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="alert alert-warning py-2 mb-3 border-warning border-opacity-50" style="background: rgba(245, 158, 11, 0.1);">
                            <div class="small fw-semibold text-warning"><i class="fas fa-bolt text-warning me-1"></i> {{ __('quizzes::teacher/messages.edit.ai_modal.beta_title') }}</div>
                            <div class="small text-warning opacity-75">{{ __('quizzes::teacher/messages.edit.ai_modal.beta_desc') }}</div>
                        </div>
                        <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> {{ __('quizzes::teacher/messages.edit.ai_modal.wait_msg') }}</p>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('quizzes::teacher/messages.edit.ai_modal.cancel') }}</button>
                        <button type="button" class="btn btn-info px-4 fw-bold" id="btnAiGenerate" onclick="generateAi()">
                            {{ __('quizzes::teacher/messages.edit.ai_modal.generate_btn') }} <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Danh sách câu hỏi hiện có --}}
        <div class="d-grid gap-3">
            @forelse ($quiz->questions->sortBy('position') as $q)
                <div class="border rounded p-3 bg-dark-subtle" id="question-row-{{ $q->id }}">
                    <div class="d-flex justify-content-between gap-2" id="question-view-{{ $q->id }}">
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $loop->iteration }}. {{ $q->question }}</div>
                            <div class="small text-muted mt-1">
                                {{ __('quizzes::teacher/messages.edit.list.type_label') }} <span class="badge bg-secondary">{{ $q->question_type }}</span> • {{ __('quizzes::teacher/messages.edit.list.points_label') }} <span class="badge bg-primary">{{ $q->points }}</span>
                            </div>
                            @if ($q->choices->count())
                                <ul class="mt-2 mb-0 small list-unstyled d-grid gap-1">
                                    @foreach ($q->choices->sortBy('position') as $choice)
                                        <li class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $choice->is_correct ? 'bg-success' : 'bg-outline-secondary border' }}" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">
                                                {{ $choice->is_correct ? '✓' : '' }}
                                            </span>
                                            <span class="{{ $choice->is_correct ? 'text-success fw-bold' : '' }}">{{ $choice->choice_text }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="d-flex gap-2 align-items-start">
                            <button class="btn btn-outline-primary btn-sm" onclick="toggleEdit({{ $q->id }})">{{ __('quizzes::teacher/messages.edit.edit_short') }}</button>
                            <form method="POST" action="{{ route('teacher.dashboard.quizzes.question.delete', [$course->id, $quiz->id, $q->id]) }}" onsubmit="return confirm('{{ __('quizzes::teacher/messages.edit.list.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">{{ __('quizzes::teacher/messages.delete') }}</button>
                            </form>
                        </div>
                    </div>

                    {{-- Form sửa câu hỏi (ẩn mặc định) --}}
                    <div id="question-edit-{{ $q->id }}" class="d-none mt-2 pt-3 border-top">
                        <form method="POST" action="{{ route('teacher.dashboard.quizzes.question.update', [$course->id, $quiz->id, $q->id]) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold">{{ __('quizzes::teacher/messages.edit.list.edit_form_title') }}</label>
                                    <textarea name="question" class="form-control form-control-sm" rows="2" required>{{ $q->question }}</textarea>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">{{ __('quizzes::teacher/messages.edit.question_type') }}</label>
                                    <select name="question_type" class="form-select form-select-sm" onchange="handleTypeChange(this, {{ $q->id }})">
                                        <option value="single_choice" @selected($q->question_type == 'single_choice')>{{ __('quizzes::teacher/messages.edit.types.single') }}</option>
                                        <option value="multiple_choice" @selected($q->question_type == 'multiple_choice')>{{ __('quizzes::teacher/messages.edit.types.multiple') }}</option>
                                        <option value="true_false" @selected($q->question_type == 'true_false')>{{ __('quizzes::teacher/messages.edit.types.true_false') }}</option>
                                        <option value="short_answer" @selected($q->question_type == 'short_answer')>{{ __('quizzes::teacher/messages.edit.types.essay') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">{{ __('quizzes::teacher/messages.edit.points') }}</label>
                                    <input name="points" type="number" class="form-control form-control-sm" value="{{ $q->points }}" min="1">
                                </div>
                                <div class="col-12 choices-container" id="choices-{{ $q->id }}" @if($q->question_type == 'short_answer') style="display:none" @endif>
                                    <label class="form-label small fw-bold">{{ __('quizzes::teacher/messages.edit.list.choices_container_label') }}</label>
                                    <div class="d-grid gap-2">
                                        @php($choices = $q->choices->sortBy('position'))
                                        @for ($i = 0; $i < 4; $i++)
                                            @php($c = $choices->values()[$i] ?? null)
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text fw-bold" style="width:35px">{{ chr(65 + $i) }}</span>
                                                <input type="text" name="choices[{{ $i }}][text]" class="form-control" value="{{ $c?->choice_text }}" placeholder="{{ __('quizzes::teacher/messages.edit.choice_placeholder', ['letter' => chr(65 + $i)]) }}">
                                                <div class="input-group-text">
                                                    <input type="{{ $q->question_type == 'multiple_choice' ? 'checkbox' : 'radio' }}" 
                                                           name="{{ $q->question_type == 'multiple_choice' ? "choices[$i][is_correct]" : "correct_choice_$q->id" }}" 
                                                           value="{{ $q->question_type == 'multiple_choice' ? '1' : $i }}"
                                                           @if($q->question_type == 'multiple_choice') @checked($c?->is_correct) @else @checked($c?->is_correct) @endif>
                                                    <span class="ms-1 small">{{ __('quizzes::teacher/messages.edit.correct_label') }}</span>
                                                </div>
                                                {{-- Trick for radio: if it's single choice, controller needs to know which one is correct --}}
                                                @if($q->question_type !== 'multiple_choice')
                                                    <input type="hidden" name="choices[{{ $i }}][is_radio_marker]" value="1">
                                                @endif
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                                <div class="col-12 d-flex gap-2 mt-2">
                                    <button class="btn btn-primary btn-sm">{{ __('quizzes::teacher/messages.edit.list.save_update') }}</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleEdit({{ $q->id }})">{{ __('quizzes::teacher/messages.edit.list.cancel_edit') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="alert alert-info border-secondary-subtle" style="background: rgba(148, 163, 184, 0.05);">
                    {{ __('quizzes::teacher/messages.edit.list.empty') }}
                </div>
            @endforelse
        </div>

        <script>
            function toggleEdit(id) {
                const view = document.getElementById(`question-view-${id}`);
                const edit = document.getElementById(`question-edit-${id}`);
                if (edit.classList.contains('d-none')) {
                    edit.classList.remove('d-none');
                    view.classList.add('d-none');
                } else {
                    edit.classList.add('d-none');
                    view.classList.remove('d-none');
                }
            }

            function handleTypeChange(select, id) {
                const container = document.getElementById(`choices-${id}`);
                if (select.value === 'short_answer') {
                    container.style.display = 'none';
                } else {
                    container.style.display = 'block';
                    // Update input types to radio or checkbox
                    const inputs = container.querySelectorAll('input[type="radio"], input[type="checkbox"]');
                    const isMultiple = select.value === 'multiple_choice';
                    inputs.forEach((inp, idx) => {
                        inp.type = isMultiple ? 'checkbox' : 'radio';
                        if (isMultiple) {
                            inp.name = `choices[${idx}][is_correct]`;
                        } else {
                            inp.name = `correct_choice_${id}`;
                        }
                    });
                }
            }
        </script>
        @if($isAiEnabled && !$isAiMaintenance && $hasAiFeature)
        <script>
            function generateAi() {
                const btn = document.getElementById('btnAiGenerate');
                const box = document.getElementById('aiAlertBox');
                const topic = document.getElementById('aiTopic').value.trim();
                
                if(!topic) {
                    box.className = 'alert alert-danger';
                    box.innerText = "{{ __('quizzes::teacher/messages.edit.js.topic_required') }}";
                    box.classList.remove('d-none');
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> {{ __('quizzes::teacher/messages.edit.js.generating') }}';
                box.classList.add('d-none');

                fetch("{{ route('teacher.dashboard.quizzes.ai.generate', [$course->id, $quiz->id]) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        topic: topic,
                        amount: document.getElementById('aiAmount').value,
                        difficulty: document.getElementById('aiDifficulty').value,
                        language: 'Vietnamese'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        box.className = 'alert alert-success';
                        box.innerText = data.message + ' ' + "{{ __('quizzes::teacher/messages.edit.js.reload_msg') }}";
                        box.classList.remove('d-none');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        box.className = 'alert alert-warning border border-warning';
                        box.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + (data.message || "{{ __('quizzes::teacher/messages.edit.js.ai_error') }}");
                        box.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerHTML = "{{ __('quizzes::teacher/messages.edit.js.retry') }} <i class=\"fas fa-redo ms-1\"></i>";
                    }
                })
                .catch(err => {
                    box.className = 'alert alert-danger border border-danger';
                    box.innerHTML = '<i class="fas fa-times-circle me-2"></i>' + "{{ __('quizzes::teacher/messages.edit.js.conn_error') }}";
                    box.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = "{{ __('quizzes::teacher/messages.edit.js.retry') }} <i class=\"fas fa-redo ms-1\"></i>";
                });
            }
        </script>
        @endif
    </div>
@endsection
