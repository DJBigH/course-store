@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
            <div>
                <h3 class="fw-bold mb-2">Quiz của khóa học</h3>
                <p class="text-muted mb-0">Tạo quiz gắn với bài học và giao cho học viên.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('teacher.dashboard.lessons.index', $course->id) }}" class="btn btn-outline-secondary">Quay lại bài học</a>
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#quizCreateForm">Tạo quiz mới</button>
            </div>
        </div>

        <div class="teacher-panel mb-4 quiz-search-panel">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Tìm học viên đã mua khóa học này</label>
                    <input type="text" name="student_search" value="{{ $studentSearch ?? '' }}" class="form-control" placeholder="Nhập tên hoặc email để tìm nhanh">
                    <div class="form-text">Chỉ hiển thị học viên đã mua khóa học hiện tại.</div>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-grow-1" type="submit">Tìm kiếm</button>
                    <a href="{{ route('teacher.dashboard.quizzes.index', $course->id) }}" class="btn btn-outline-secondary">Xóa lọc</a>
                </div>
            </form>

            <div class="mt-3">
                <div class="small text-muted mb-2">Học viên đã mua khóa này</div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse ($buyers as $buyer)
                        <span class="badge rounded-pill {{ in_array($buyer->id, ($assignedStudentIds ?? collect())->all(), true) ? 'bg-success' : 'bg-light text-dark border' }}">
                            #{{ $buyer->id }} • {{ $buyer->name }} • {{ $buyer->email }}
                        </span>
                    @empty
                        <span class="text-muted small">Không tìm thấy học viên phù hợp.</span>
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
                            <label class="form-label">Tiêu đề</label>
                            <input name="title" class="form-control" placeholder="Quiz bài 1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gắn vào bài học</label>
                            <select name="lesson_id" class="form-select">
                                <option value="">Không gắn lesson</option>
                                @foreach ($lessons as $lesson)
                                    <option value="{{ $lesson->id }}">#{{ $lesson->id }} • {{ $lesson->name_locale }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Điểm đạt (%)</label><input name="passing_score" type="number" class="form-control" value="70"></div>
                        <div class="col-md-4"><label class="form-label">Số lần làm</label><input name="max_attempts" type="number" class="form-control" placeholder="Không giới hạn"></div>
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label">Thời gian làm bài (phút)</label>
                            <input name="time_limit_minutes" type="number" class="form-control" placeholder="Để trống = vô thời hạn">
                            <div class="form-text">Dùng để đếm ngược thời gian cho một lượt làm quiz.</div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label">Hạn nộp bài</label>
                            <input name="deadline_at" type="datetime-local" class="form-control">
                            <div class="form-text">Để trống = không có deadline. Quá mốc này học viên sẽ không nộp được nữa.</div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-secondary border small mb-0 text-light" style="background: rgba(148, 163, 184, 0.12);">
                                <strong>Gợi ý nhanh:</strong> <span class="text-muted">Thời gian làm bài là giới hạn phút cho mỗi lượt làm. Hạn nộp bài là mốc ngày giờ cuối cùng quiz còn được phép nộp.</span>
                            </div>
                        </div>
                        <div class="col-12"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                        <div class="col-12">
                            <button class="btn btn-primary">Lưu quiz</button>
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
                                        Gắn với toàn course
                                    @endif
                                </div>
                            </div>
                            <span class="badge bg-{{ $quiz->status ? 'success' : 'secondary' }}">{{ $quiz->status ? 'Active' : 'Hidden' }}</span>
                        </div>
                        @php
                            $quizAssignments = $assignments->where('quiz_id', $quiz->id);
                            $assignmentDeadline = $quizAssignments->pluck('deadline_at')->filter()->sort()->first();
                            $quizDeadline = $quiz->deadline_at;
                            $nearestDeadline = $quizDeadline ?: $assignmentDeadline;
                            $deadlineBadge = null;

                            if (! $nearestDeadline) {
                                $deadlineBadge = ['label' => 'Không giới hạn thời gian', 'class' => 'bg-success'];
                            } elseif (now()->greaterThan($nearestDeadline)) {
                                $deadlineBadge = ['label' => 'Đã hết hạn', 'class' => 'bg-danger'];
                            } elseif (now()->diffInHours($nearestDeadline, false) <= 24) {
                                $deadlineBadge = ['label' => 'Sắp hết hạn', 'class' => 'bg-warning text-dark'];
                            } else {
                                $deadlineBadge = ['label' => 'Còn hạn', 'class' => 'bg-primary'];
                            }
                        @endphp
                        <div class="mt-3 small text-muted">
                            {{ $quiz->questions_count }} câu hỏi • {{ $quiz->submissions_count }} bài nộp • passing {{ $quiz->passing_score }}% • attempts {{ $quiz->max_attempts ?: '∞' }}
                        </div>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <span class="badge {{ $deadlineBadge['class'] }}">{{ $deadlineBadge['label'] }}</span>
                            @if ($nearestDeadline)
                                <span class="badge bg-dark text-light border border-secondary">Hết hạn: {{ $nearestDeadline->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('teacher.dashboard.quizzes.results', [$course->id, $quiz->id]) }}" class="btn btn-outline-success">Xem kết quả</a>
                            <a href="{{ route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id]) }}" class="btn btn-outline-secondary">Sửa</a>
                            <span class="small text-muted align-self-center">Quiz ID: #{{ $quiz->id }}</span>
                            <form method="POST" action="{{ route('teacher.dashboard.quizzes.assign', [$course->id, $quiz->id]) }}" class="d-flex gap-2 flex-wrap w-100 mt-2 quiz-assign-form" data-quiz-assign-form data-buyers='@json($buyers->values())' data-quiz-id="{{ $quiz->id }}">
                                @csrf
                                <div class="flex-grow-1" style="min-width: 320px;">
                                    <input type="text" class="form-control mb-2" placeholder="Nhập tên hoặc email..." data-quiz-buyer-search style="color: #e5e7eb; background: rgba(15, 23, 42, 0.5);">
                                    <div class="quiz-buyer-results" data-quiz-buyer-results></div>
                                    <div class="small text-muted mt-2" data-quiz-selected-summary>Chưa chọn học viên nào. Bấm "Giao cho tất cả" để gán toàn bộ.</div>
                                    <div data-quiz-selected-inputs></div>
                                </div>
                                <button class="btn btn-outline-primary btn-assign-selected" type="submit">Giao cho học viên được chọn</button>
                                <button class="btn btn-primary btn-assign-all" type="submit">Giao cho tất cả học viên đã mua</button>
                                <div class="small text-muted w-100">Có thể chọn nhiều học viên; bỏ chọn từng học viên bằng cách bấm lại vào tên của họ.</div>
                            </form>
                            <form method="POST" action="{{ route('teacher.dashboard.quizzes.destroy', [$course->id, $quiz->id]) }}" onsubmit="return confirm('Xóa quiz này?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger">Xóa</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">Chưa có quiz nào.</div></div>
            @endforelse
        </div>
    </div>

    <style>
        .quiz-search-panel .form-label,
        .quiz-search-panel .form-text,
        .quiz-search-panel .small,
        .quiz-search-panel .text-muted {
            color: #cbd5e1 !important;
        }

        .quiz-search-panel .badge.bg-light {
            color: #e2e8f0 !important;
            background: rgba(148, 163, 184, 0.16) !important;
            border-color: rgba(148, 163, 184, 0.35) !important;
        }

        .quiz-search-panel .badge.rounded-pill {
            color: #0f172a !important;
        }

        .quiz-search-panel .form-control,
        .quiz-search-panel .btn-outline-primary,
        .quiz-search-panel .btn-outline-secondary {
            border-color: rgba(148, 163, 184, 0.28);
        }

        .quiz-search-panel .form-control {
            color: #e2e8f0;
            background: rgba(15, 23, 42, 0.55);
        }

        .quiz-search-panel .form-control::placeholder {
            color: #94a3b8;
        }

        .quiz-search-panel .btn-outline-primary,
        .quiz-search-panel .btn-outline-secondary {
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
            color: #94a3b8;
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
                            ? `Đã chọn ${selected.size} học viên.`
                            : 'Chưa chọn học viên nào. Bấm "Giao cho tất cả" để gán toàn bộ.';
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
                                        ${isSelected ? 'Bỏ chọn' : 'Chọn'}
                                    </span>
                                </button>`;
                        }).join('')
                        : '<div class="text-muted small py-1">Không tìm thấy học viên phù hợp.</div>';

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
                    assignAllButton.title = 'Gán quiz cho toàn bộ học viên đã mua khóa học này.';
                    assignAllButton.addEventListener('click', () => {
                        // Xóa student_ids để controller hiểu là gán tất cả
                        selected.clear();
                        syncHiddenInputs();
                    });
                }

                if (assignSelectedBtn) {
                    assignSelectedBtn.title = 'Gán quiz chỉ cho học viên bạn đã chọn.';
                    assignSelectedBtn.addEventListener('click', (e) => {
                        if (selected.size === 0) {
                            e.preventDefault();
                            alert('Vui lòng chọn ít nhất 1 học viên trước khi giao.');
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