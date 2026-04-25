<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseQuiz;
use Modules\Courses\src\Models\CourseQuizAssignment;
use Modules\Courses\src\Models\CourseQuizQuestion;
use Modules\Courses\src\Models\CourseQuizSubmission;
use Modules\Courses\src\Models\CourseQuizSubmissionAnswer;
use Modules\Students\src\Models\Student;

class StudentQuizController extends Controller
{
    /**
     * Trang làm bài quiz — hiện thông tin quiz + lịch sử làm bài
     */
    public function show(int $courseId, int $quizId)
    {
        $student    = $this->resolveStudent();
        $course     = $this->resolveAccessibleCourse($student, $courseId);
        $quiz       = $this->resolveAccessibleQuiz($student, $course, $quizId);
        $assignment = $this->getAssignment($quiz->id, $student->id);

        // Tất cả lần làm đã hoàn thành (có submitted_at)
        $pastSubmissions = CourseQuizSubmission::query()
            ->where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->orderByDesc('attempt_no')
            ->get();

        // Lần làm đang dở (chưa nộp)
        $activeSubmission = CourseQuizSubmission::query()
            ->where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNull('submitted_at')
            ->latest('id')
            ->first();

        return view('teacher::teacher.quiz.student_quiz', compact(
            'student',
            'course',
            'quiz',
            'assignment',
            'pastSubmissions',
            'activeSubmission'
        ));
    }

    /**
     * Bắt đầu lượt làm mới
     */
    public function start(Request $request, int $courseId, int $quizId)
    {
        $student    = $this->resolveStudent();
        $course     = $this->resolveAccessibleCourse($student, $courseId);
        $quiz       = $this->resolveAccessibleQuiz($student, $course, $quizId);
        $assignment = $this->getAssignment($quiz->id, $student->id);

        // Kiểm tra deadline
        $deadline = $assignment?->deadline_at ?? $quiz->deadline_at;
        if ($deadline && now()->greaterThan($deadline)) {
            return redirect()->back()->with('msg_danger', 'Quiz đã hết hạn, không thể bắt đầu.');
        }

        // Kiểm tra max_attempts
        $attemptsUsed = CourseQuizSubmission::query()
            ->where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->count();

        if ($quiz->max_attempts && $attemptsUsed >= (int) $quiz->max_attempts) {
            return redirect()->back()->with('msg_danger', 'Bạn đã dùng hết lượt làm bài.');
        }

        // Hủy bỏ lượt làm dở còn tồn đọng (nếu có)
        CourseQuizSubmission::query()
            ->where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNull('submitted_at')
            ->delete();

        $attemptNo = $attemptsUsed + 1;

        $submission = CourseQuizSubmission::query()->create([
            'quiz_id'       => $quiz->id,
            'student_id'    => $student->id,
            'assignment_id' => $assignment?->id,
            'attempt_no'    => $attemptNo,
            'score'         => 0,
            'passed'        => false,
            'started_at'    => now(),
        ]);

        return redirect()->route('teacher.dashboard.quizzes.show', [$course->id, $quiz->id])
            ->with('msg_success', "Đã bắt đầu lượt làm thứ {$attemptNo}.");
    }

    /**
     * Nộp bài
     */
    public function submit(Request $request, int $courseId, int $quizId)
    {
        $student = $this->resolveStudent();
        $course  = $this->resolveAccessibleCourse($student, $courseId);
        $quiz    = $this->resolveAccessibleQuiz($student, $course, $quizId);

        $payload = $request->validate([
            'submission_id'            => ['required', 'integer'],
            'answers'                  => ['nullable', 'array'],
            'answers.*.question_id'    => ['required', 'integer'],
            'answers.*.choice_id'      => ['nullable', 'integer'],
            'answers.*.choice_ids'     => ['nullable', 'array'],
            'answers.*.choice_ids.*'   => ['integer'],
            'answers.*.answer_text'    => ['nullable', 'string', 'max:10000'],
        ]);

        // Lấy submission chưa nộp của học viên
        $submission = CourseQuizSubmission::query()
            ->where('id', (int) $payload['submission_id'])
            ->where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNull('submitted_at')  // Bắt buộc chưa nộp
            ->firstOrFail();

        // Kiểm tra deadline một lần nữa trước khi chấm
        $assignment = $this->getAssignment($quiz->id, $student->id);
        $deadline   = $assignment?->deadline_at ?? $quiz->deadline_at;
        if ($deadline && now()->greaterThan($deadline)) {
            return redirect()->back()->with('msg_danger', 'Quiz đã hết hạn, không thể nộp bài.');
        }

        DB::transaction(function () use ($quiz, $submission, $payload) {
            $questions = $quiz->questions()->with('choices')->get()->keyBy('id');
            $score     = 0;
            $maxScore  = max((int) $questions->sum('points'), 1);

            foreach (($payload['answers'] ?? []) as $answerData) {
                $question = $questions->get((int) $answerData['question_id']);
                if (! $question) {
                    continue;
                }

                $choice      = null;
                $isCorrect   = false;
                $earnedPoints = 0;

                if ($question->question_type === 'short_answer') {
                    $answerText   = trim((string) ($answerData['answer_text'] ?? ''));
                    // Short answer: chấm thủ công sau, tạm cho điểm nếu có nhập
                    $isCorrect    = $answerText !== '';
                    $earnedPoints = 0; // GV xem xét sau
                } elseif ($question->question_type === 'multiple_choice') {
                    $selectedIds = collect($answerData['choice_ids'] ?? [])
                        ->map(fn ($id) => (int) $id)->filter()->unique()->sort()->values();
                    $correctIds = $question->choices->where('is_correct', true)
                        ->pluck('id')->sort()->values();

                    $isCorrect    = $selectedIds->count() > 0
                        && $selectedIds->values()->all() === $correctIds->values()->all();
                    $earnedPoints = $isCorrect ? (int) $question->points : 0;

                    // Lưu choice đầu tiên được chọn (để hiển thị)
                    if ($selectedIds->isNotEmpty()) {
                        $choice = $question->choices->firstWhere('id', (int) $selectedIds->first());
                    }

                    // Lưu tất cả đáp án được chọn — 1 record/choice
                    foreach ($selectedIds as $choiceId) {
                        $ch = $question->choices->firstWhere('id', $choiceId);
                        CourseQuizSubmissionAnswer::query()->updateOrCreate(
                            ['submission_id' => $submission->id, 'question_id' => $question->id],
                            [
                                'choice_id'     => $choice?->id,
                                'answer_text'   => null,
                                'is_correct'    => $isCorrect,
                                'points_earned' => $earnedPoints,
                            ]
                        );
                    }

                    $score += $earnedPoints;
                    continue; // Đã lưu, bỏ qua block bên dưới
                } else {
                    // single_choice / true_false
                    $choiceId = (int) ($answerData['choice_id'] ?? 0);
                    if ($choiceId > 0) {
                        $choice       = $question->choices->firstWhere('id', $choiceId);
                        $isCorrect    = (bool) ($choice?->is_correct ?? false);
                        $earnedPoints = $isCorrect ? (int) $question->points : 0;
                    }
                }

                $score += $earnedPoints;

                CourseQuizSubmissionAnswer::query()->updateOrCreate(
                    ['submission_id' => $submission->id, 'question_id' => $question->id],
                    [
                        'choice_id'     => $choice?->id,
                        'answer_text'   => trim((string) ($answerData['answer_text'] ?? '')) ?: null,
                        'is_correct'    => $isCorrect,
                        'points_earned' => $earnedPoints,
                    ]
                );
            }

            $percentage = (int) round(($score * 100) / $maxScore);
            $passed     = $percentage >= (int) $quiz->passing_score;

            $submission->update([
                'score'        => $percentage,
                'passed'       => $passed,
                'finished_at'  => now(),
                'submitted_at' => now(),
            ]);
        });

        $submission->refresh();
        $msg = $submission->passed
            ? "🎉 Chúc mừng! Bạn đạt {$submission->score}% - Đạt yêu cầu!"
            : "Bạn đạt {$submission->score}% - Chưa đạt (cần {$quiz->passing_score}%).";

        return redirect()->route('teacher.dashboard.quizzes.show', [$course->id, $quiz->id])
            ->with('msg_success', $msg);
    }

    /**
     * Xem kết quả 1 lần làm cụ thể (JSON API)
     */
    public function result(int $courseId, int $quizId, int $submissionId)
    {
        $student = $this->resolveStudent();
        $course  = $this->resolveAccessibleCourse($student, $courseId);
        $quiz    = $this->resolveAccessibleQuiz($student, $course, $quizId);

        $submission = CourseQuizSubmission::query()
            ->with(['answers', 'answers.question', 'answers.choice'])
            ->where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->findOrFail($submissionId);

        return view('teacher::teacher.quiz.student_quiz_result', compact('course', 'quiz', 'submission'));
    }

    // ─── Private helpers ─────────────────────────────────────

    private function resolveStudent(): Student
    {
        return Student::query()->findOrFail(auth('students')->id());
    }

    private function resolveAccessibleCourse(Student $student, int $courseId): Courses
    {
        return Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('id', $courseId)
            ->whereHas('students', fn ($q) => $q->where('students.id', $student->id))
            ->firstOrFail();
    }

    private function resolveAccessibleQuiz(Student $student, Courses $course, int $quizId): CourseQuiz
    {
        return CourseQuiz::query()
            ->with(['lesson', 'questions.choices'])
            ->where('course_id', $course->id)
            ->where('status', 1)  // Chỉ cho làm quiz đang active
            ->where(function ($query) use ($student) {
                // Quiz được gán cho học viên này, hoặc quiz public (chưa gán ai)
                $query->whereHas('assignments', fn ($q) => $q->where('student_id', $student->id))
                    ->orWhereDoesntHave('assignments');
            })
            ->findOrFail($quizId);
    }

    private function getAssignment(int $quizId, int $studentId): ?CourseQuizAssignment
    {
        return CourseQuizAssignment::query()
            ->where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->first();
    }
}
