@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-2">Kết quả quiz</h3>
                <p class="text-muted mb-0">{{ $quiz->title }}</p>
            </div>
            <a href="{{ route('teacher.dashboard.quizzes.index', $course->id) }}" class="btn btn-outline-secondary">Quay lại danh sách quiz</a>
        </div>

        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-3">
                <input type="text" name="student" value="{{ request('student') }}" class="form-control" placeholder="Lọc theo học viên">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="passed" @selected(request('status') === 'passed')>Đã đạt</option>
                    <option value="failed" @selected(request('status') === 'failed')>Chưa đạt</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">Lọc</button>
                <a href="{{ route('teacher.dashboard.quizzes.results', [$course->id, $quiz->id]) }}" class="btn btn-outline-secondary">Reset</a>
                <a href="{{ route('teacher.dashboard.quizzes.results.export', [$course->id, $quiz->id]) }}?{{ http_build_query(request()->query()) }}" class="btn btn-outline-success">Export CSV</a>
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="teacher-panel text-center"><div class="small text-muted">Tổng submissions</div><div class="fs-4 fw-bold">{{ $submissions->count() }}</div></div></div>
            <div class="col-md-3"><div class="teacher-panel text-center"><div class="small text-muted">Đã đạt</div><div class="fs-4 fw-bold">{{ $submissions->where('passed', true)->count() }}</div></div></div>
            <div class="col-md-3"><div class="teacher-panel text-center"><div class="small text-muted">Chưa đạt</div><div class="fs-4 fw-bold">{{ $submissions->where('passed', false)->count() }}</div></div></div>
            <div class="col-md-3"><div class="teacher-panel text-center"><div class="small text-muted">Điểm TB</div><div class="fs-4 fw-bold">{{ number_format((float) $submissions->avg('score'), 1) }}%</div></div></div>
        </div>

        <div class="d-grid gap-3">
            @forelse ($submissions as $submission)
                <div class="teacher-panel">
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                        <div>
                            <div class="fw-semibold">{{ $submission->student?->name ?: 'Học viên' }} <span class="text-muted small">#{{ $submission->student_id }}</span></div>
                            <div class="small text-muted">Student ID: #{{ $submission->student_id }} • Lượt {{ $submission->attempt_no }} • Nộp lúc {{ optional($submission->submitted_at)->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-4">{{ $submission->score }}%</div>
                            <span class="badge bg-{{ $submission->passed ? 'success' : 'danger' }}">{{ $submission->passed ? 'Đạt' : 'Chưa đạt' }}</span>
                        </div>
                    </div>

                    <div class="mt-3">
                        @foreach ($submission->answers as $answer)
                            <div class="border rounded p-3 mb-2">
                                <div class="fw-semibold">{{ $answer->question?->question }}</div>
                                @if($answer->question?->question_type === 'short_answer')
                                    <div class="small bg-warning-subtle text-warning-emphasis p-2 mt-2 rounded border border-warning">
                                        <i class="fas fa-exclamation-circle me-1"></i> <strong>Câu trả lời tự luận:</strong><br>
                                        {{ $answer->answer_text ?: '(Không trả lời)' }}
                                    </div>
                                    <div class="small mt-2">
                                        <span class="badge bg-warning text-dark">Pending Review</span>
                                    </div>
                                @else
                                    <div class="small text-muted mt-1">{{ $answer->choice?->choice_text ?: $answer->answer_text ?: 'Không trả lời' }}</div>
                                    <div class="small mt-1">Điểm: {{ $answer->points_earned }} • <span class="badge {{ $answer->is_correct ? 'bg-success' : 'bg-danger' }}">{{ $answer->is_correct ? 'Đúng' : 'Sai' }}</span></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="alert alert-info">Chưa có bài nộp nào.</div>
            @endforelse
        </div>
    </div>
@endsection
