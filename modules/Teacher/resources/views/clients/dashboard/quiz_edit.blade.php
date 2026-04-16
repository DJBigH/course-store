@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-2">Chỉnh sửa quiz</h3>
                <p class="text-muted mb-0">{{ $quiz->title }}</p>
            </div>
            <a href="{{ route('teacher.dashboard.quizzes.index', $course->id) }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        {{-- Cài đặt quiz --}}
        <form method="POST" action="{{ route('teacher.dashboard.quizzes.update', [$course->id, $quiz->id]) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Tiêu đề</label>
                    <input name="title" class="form-control" value="{{ $quiz->title }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Lesson liên kết</label>
                    <select name="lesson_id" class="form-select">
                        <option value="">Không gắn lesson</option>
                        @foreach ($lessons as $lesson)
                            <option value="{{ $lesson->id }}" @selected($quiz->lesson_id == $lesson->id)>{{ $lesson->name_locale }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Điểm đạt (%)</label>
                    <input name="passing_score" type="number" class="form-control" value="{{ $quiz->passing_score }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Số lần làm</label>
                    <input name="max_attempts" type="number" class="form-control" value="{{ $quiz->max_attempts }}" placeholder="Không giới hạn">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Thời gian (phút)</label>
                    <input name="time_limit_minutes" type="number" class="form-control" value="{{ $quiz->time_limit_minutes }}" placeholder="Vô thời hạn">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hạn nộp</label>
                    <input name="deadline_at" type="datetime-local" class="form-control" value="{{ optional($quiz->deadline_at)->format('Y-m-d\TH:i') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="2">{{ $quiz->description }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch p-0 d-flex align-items-center gap-4">
                        <label class="form-check-label fw-bold mb-0">Hiển thị đáp án đúng cho học viên sau khi nộp bài</label>
                        <input name="show_answers_after" type="checkbox" class="form-check-input ms-0" value="1" style="width: 50px; height: 24px;" @checked($quiz->show_answers_after)>
                    </div>
                    <div class="form-text mt-1 text-info"><i class="fas fa-info-circle me-1"></i> Nếu bật, học viên sẽ thấy đáp án đúng và giải thích (nếu có) ngay sau khi bấm nộp.</div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        {{-- Danh sách câu hỏi --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Câu hỏi ({{ $quiz->questions->count() }} câu)</h5>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('teacher.dashboard.quizzes.import.template', [$course->id, $quiz->id]) }}" class="btn btn-outline-secondary btn-sm" title="Tải file CSV mẫu">
                    📥 Tải template CSV
                </a>
                <a href="{{ route('teacher.dashboard.quizzes.export', [$course->id, $quiz->id]) }}" class="btn btn-outline-success btn-sm">
                    📤 Export câu hỏi
                </a>
                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#importForm">
                    📁 Import CSV
                </button>
                @if(\Modules\Settings\src\Models\Setting::getValue('ai_quiz_enabled') !== '0')
                <button class="btn btn-outline-info btn-sm text-info fw-bold" type="button" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
                    ✨ Tạo bằng AI
                </button>
                @endif
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#addQuestionForm">
                    + Thêm câu hỏi
                </button>
            </div>
        </div>

        {{-- Import form --}}
        <div class="collapse mb-3" id="importForm">
            <div class="border rounded p-3">
                <h6 class="mb-3">Import câu hỏi từ file CSV</h6>
                <form method="POST" action="{{ route('teacher.dashboard.quizzes.import', [$course->id, $quiz->id]) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex gap-3 align-items-end flex-wrap">
                        <div class="flex-grow-1">
                            <label class="form-label form-label-sm">File CSV</label>
                            <input type="file" name="file" class="form-control form-control-sm" accept=".csv,.txt" required>
                            <div class="form-text">Định dạng: question, question_type, points, choice_a, choice_b, choice_c, choice_d, correct_choices (A/B/C/D hoặc A,B,D)</div>
                        </div>
                        <button class="btn btn-sm btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Form thêm câu hỏi --}}
        <div class="collapse mb-3" id="addQuestionForm">
            <div class="border rounded p-3">
                <h6 class="mb-3">Thêm câu hỏi mới</h6>
                <form method="POST" action="{{ route('teacher.dashboard.quizzes.question.store', [$course->id, $quiz->id]) }}" id="addQuestionFormEl">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nội dung câu hỏi <span class="text-danger">*</span></label>
                            <textarea name="question" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Loại câu hỏi</label>
                            <select name="question_type" class="form-select" id="questionTypeSelect">
                                <option value="single_choice">Trắc nghiệm 1 đáp án</option>
                                <option value="multiple_choice">Trắc nghiệm nhiều đáp án</option>
                                <option value="true_false">Đúng / Sai</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Điểm</label>
                            <input name="points" type="number" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Các lựa chọn</label>
                            <div id="choicesList" class="d-grid gap-2">
                                @foreach (['A','B','C','D'] as $idx => $letter)
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold" style="width:40px">{{ $letter }}</span>
                                        <input type="text" name="choices[{{ $idx }}][text]" class="form-control" placeholder="Nhập lựa chọn {{ $letter }}">
                                        <div class="input-group-text">
                                            <label class="mb-0 d-flex align-items-center gap-1">
                                                <input type="checkbox" name="choices[{{ $idx }}][is_correct]" value="1"> Đúng
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">Thêm câu hỏi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(\Modules\Settings\src\Models\Setting::getValue('ai_quiz_enabled') !== '0')
        <div class="modal fade" id="aiGenerateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content text-light" style="background: #1e293b; border-color: #334155;">
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title"><i class="fas fa-magic text-info me-2"></i>Tạo câu hỏi tự động (AI)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="aiAlertBox" class="alert d-none"></div>
                        <div class="mb-3">
                            <label class="form-label text-info">Chủ đề của bộ câu hỏi</label>
                            <input type="text" id="aiTopic" class="form-control" placeholder="VD: Khái niệm về Interface trong PHP" style="background: rgba(15, 23, 42, 0.5); color: #fff; border-color: #475569;">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small">Số câu (Tối đa 10)</label>
                                <select id="aiAmount" class="form-select" style="background: rgba(15, 23, 42, 0.5); color: #fff; border-color: #475569;">
                                    <option value="3">3 câu</option>
                                    <option value="5" selected>5 câu</option>
                                    <option value="10">10 câu</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">Độ khó</label>
                                <select id="aiDifficulty" class="form-select" style="background: rgba(15, 23, 42, 0.5); color: #fff; border-color: #475569;">
                                    <option value="Dễ">Mức Cơ Bản</option>
                                    <option value="Trung bình" selected>Trung Bình</option>
                                    <option value="Khó">Nâng cao</option>
                                </select>
                            </div>
                        </div>
                        <div class="alert alert-warning py-2 mb-3 border-warning border-opacity-50" style="background: rgba(245, 158, 11, 0.1);">
                            <div class="small fw-semibold text-warning"><i class="fas fa-bolt text-warning me-1"></i> Tính năng Beta - Dùng ngay kẻo lỡ!</div>
                            <div class="small text-warning opacity-75">Hệ thống AI miễn phí có thể quá tải vào giờ cao điểm, mỗi ngày bạn chỉ có ngân sách tạo vài Quiz theo gói. Hãy là người nhanh tay sử dụng ngay bây giờ trước khi hạn mức chung cạn kiệt!</div>
                        </div>
                        <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> Quá trình kết nối AI có thể mất 15-30 giây. Xin vui lòng không tắt trang trong lúc thực hiện.</p>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-info px-4 fw-bold" id="btnAiGenerate" onclick="generateAi()">
                            Bắt đầu tạo <i class="fas fa-arrow-right ms-1"></i>
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
                                Loại: <span class="badge bg-secondary">{{ $q->question_type }}</span> • Điểm: <span class="badge bg-primary">{{ $q->points }}</span>
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
                            <button class="btn btn-outline-primary btn-sm" onclick="toggleEdit({{ $q->id }})">Sửa</button>
                            <form method="POST" action="{{ route('teacher.dashboard.quizzes.question.delete', [$course->id, $quiz->id, $q->id]) }}" onsubmit="return confirm('Xóa câu hỏi này?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">Xóa</button>
                            </form>
                        </div>
                    </div>

                    {{-- Form sửa câu hỏi (ẩn mặc định) --}}
                    <div id="question-edit-{{ $q->id }}" class="d-none mt-2 pt-3 border-top">
                        <form method="POST" action="{{ route('teacher.dashboard.quizzes.question.update', [$course->id, $quiz->id, $q->id]) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Nội dung câu hỏi</label>
                                    <textarea name="question" class="form-control form-control-sm" rows="2" required>{{ $q->question }}</textarea>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">Loại</label>
                                    <select name="question_type" class="form-select form-select-sm" onchange="handleTypeChange(this, {{ $q->id }})">
                                        <option value="single_choice" @selected($q->question_type == 'single_choice')>Trắc nghiệm 1 đáp án</option>
                                        <option value="multiple_choice" @selected($q->question_type == 'multiple_choice')>Trắc nghiệm nhiều đáp án</option>
                                        <option value="true_false" @selected($q->question_type == 'true_false')>Đúng / Sai</option>
                                        <option value="short_answer" @selected($q->question_type == 'short_answer')>Tự luận (ngắn)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Điểm</label>
                                    <input name="points" type="number" class="form-control form-control-sm" value="{{ $q->points }}" min="1">
                                </div>
                                <div class="col-12 choices-container" id="choices-{{ $q->id }}" @if($q->question_type == 'short_answer') style="display:none" @endif>
                                    <label class="form-label small fw-bold">Lựa chọn giải pháp</label>
                                    <div class="d-grid gap-2">
                                        @php($choices = $q->choices->sortBy('position'))
                                        @for ($i = 0; $i < 4; $i++)
                                            @php($c = $choices->values()[$i] ?? null)
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text fw-bold" style="width:35px">{{ chr(65 + $i) }}</span>
                                                <input type="text" name="choices[{{ $i }}][text]" class="form-control" value="{{ $c?->choice_text }}" placeholder="Lựa chọn {{ chr(65 + $i) }}">
                                                <div class="input-group-text">
                                                    <input type="{{ $q->question_type == 'multiple_choice' ? 'checkbox' : 'radio' }}" 
                                                           name="{{ $q->question_type == 'multiple_choice' ? "choices[$i][is_correct]" : "correct_choice_$q->id" }}" 
                                                           value="{{ $q->question_type == 'multiple_choice' ? '1' : $i }}"
                                                           @if($q->question_type == 'multiple_choice') @checked($c?->is_correct) @else @checked($c?->is_correct) @endif>
                                                    <span class="ms-1 small">Đúng</span>
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
                                    <button class="btn btn-primary btn-sm">Lưu cập nhật</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleEdit({{ $q->id }})">Hủy</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="alert alert-info border-secondary-subtle" style="background: rgba(148, 163, 184, 0.05);">
                    Chưa có câu hỏi nào. Hãy thêm câu hỏi hoặc import từ CSV để bắt đầu.
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
        @if(\Modules\Settings\src\Models\Setting::getValue('ai_quiz_enabled') !== '0')
        <script>
            function generateAi() {
                const btn = document.getElementById('btnAiGenerate');
                const box = document.getElementById('aiAlertBox');
                const topic = document.getElementById('aiTopic').value.trim();
                
                if(!topic) {
                    box.className = 'alert alert-danger';
                    box.innerText = 'Vui lòng nhập chủ đề.';
                    box.classList.remove('d-none');
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang tạo...';
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
                        box.innerText = data.message + ' Đang tải lại...';
                        box.classList.remove('d-none');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        box.className = 'alert alert-warning border border-warning';
                        box.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + (data.message || 'Lỗi không xác định từ AI.');
                        box.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerHTML = 'Thử lại <i class="fas fa-redo ms-1"></i>';
                    }
                })
                .catch(err => {
                    box.className = 'alert alert-danger border border-danger';
                    box.innerHTML = '<i class="fas fa-times-circle me-2"></i>Lỗi kết nối máy chủ.';
                    box.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = 'Thử lại <i class="fas fa-redo ms-1"></i>';
                });
            }
        </script>
        @endif
    </div>
@endsection
