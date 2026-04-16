@extends('layouts.client')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                {{-- Kết quả tổng quan --}}
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-0">
                        <div class="p-4 p-md-5 text-center {{ $submission->passed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                            <div class="display-1 fw-bold mb-3">
                                {{ $submission->score }}%
                            </div>
                            <h2 class="fw-bold mb-2">
                                {{ $submission->passed ? '🎉 CHÚC MỪNG! BẠN ĐÃ VƯỢT QUA' : '😔 RẤT TIẾC! BẠN CHƯA ĐẠT' }}
                            </h2>
                            <p class="mb-0 opacity-75">
                                Quiz: <strong>{{ $quiz->title }}</strong> • 
                                Đạt: {{ $quiz->passing_score }}% • 
                                Ngày nộp: {{ $submission->submitted_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="p-4 border-top bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('teacher.dashboard.courses') }}" class="btn btn-outline-secondary rounded-pill px-4">Quay lại khóa học</a>
                            </div>
                            <div class="d-flex gap-2">
                                @if (!$submission->passed && ($quiz->max_attempts == 0 || $submission->attempt_no < $quiz->max_attempts))
                                    <a href="{{ route('teacher.dashboard.quizzes.show', [$course->id, $quiz->id]) }}" class="btn btn-primary rounded-pill px-4">Làm lại bài thi</a>
                                @endif
                                <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4">
                                    <i class="fas fa-print me-1"></i> In kết quả
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-2"><span class="badge bg-success">Không giới hạn thời gian</span></div>
                @endif
            </div>
            <div class="text-end">
                <div class="fw-bold fs-4">{{ $submission->score }}%</div>
                <div class="badge bg-{{ $submission->passed ? 'success' : 'danger' }}">{{ $submission->passed ? 'Đạt' : 'Chưa đạt' }}</div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="teacher-panel text-center"><div class="small text-muted">Số câu</div><div class="fs-4 fw-bold">{{ $quiz->questions->count() }}</div></div></div>
            <div class="col-md-4"><div class="teacher-panel text-center"><div class="small text-muted">Điểm đạt</div><div class="fs-4 fw-bold">{{ $quiz->passing_score }}%</div></div></div>
            <div class="col-md-4"><div class="teacher-panel text-center"><div class="small text-muted">Lần làm</div><div class="fs-4 fw-bold">{{ $submission->attempt_no }}</div></div></div>
        </div>

        <div class="d-grid gap-3">
            @foreach ($submission->answers as $answer)
                <div class="teacher-panel">
                    <div class="fw-semibold mb-2">{{ $answer->question?->question }}</div>
                    <div class="small text-muted">Điểm: {{ $answer->points_earned }}</div>
                    <div class="mt-2">{{ $answer->answer_text ?: $answer->choice?->choice_text ?: 'Không trả lời' }}</div>
                    <div class="mt-2 badge bg-{{ $answer->is_correct ? 'success' : 'secondary' }}">{{ $answer->is_correct ? 'Đúng' : 'Sai' }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
