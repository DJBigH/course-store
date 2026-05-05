<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseQuiz;
use Modules\Courses\src\Models\CourseQuizAssignment;
use Modules\Courses\src\Models\CourseQuizChoice;
use Modules\Courses\src\Models\CourseQuizQuestion;
use Modules\Courses\src\Models\CourseQuizSubmission;
use Modules\Courses\src\Policies\CourseQuizPolicy;
use Modules\Lessons\src\Models\Lesson;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Notifications\QuizAssignedNotification;
use Modules\Students\src\Models\Student;

class TeacherQuizController extends Controller
{
    use TeacherDashboardHelpers;

    public function index(int $courseId)
    {
        $teacher = $this->resolveTeacher();
        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $this->authorizeQuizAccess($teacher, 'viewAny', $course);

        $quizzes = CourseQuiz::query()
            ->with(['lesson', 'questions.choices'])
            ->withCount(['questions', 'submissions'])
            ->where('course_id', $course->id)
            ->orderBy('position')
            ->orderByDesc('id')
            ->get();

        $assignments = CourseQuizAssignment::query()
            ->with(['quiz', 'quiz.lesson'])
            ->whereHas('quiz', fn ($query) => $query->where('course_id', $course->id))
            ->latest('id')
            ->get();

        $submissions = CourseQuizSubmission::query()
            ->with(['quiz.lesson', 'student', 'answers.question', 'answers.choice'])
            ->whereHas('quiz', fn ($query) => $query->where('course_id', $course->id))
            ->latest('id')
            ->get();

        $submissionsPaginator = new LengthAwarePaginator(
            $submissions->take(10)->values(),
            $submissions->count(),
            10,
            1,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );

        $assignedStudentIds = CourseQuizAssignment::query()
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->pluck('student_id')
            ->unique()
            ->values();

        $studentSearch = trim((string) request('student_search', ''));
        $buyers = $course->students()
            ->select('students.id', 'students.name', 'students.email', 'students.phone')
            ->when($studentSearch !== '', function ($query) use ($studentSearch) {
                $term = '%' . $studentSearch . '%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('students.name', 'like', $term)
                        ->orWhere('students.email', 'like', $term);
                });
            })
            ->orderBy('students.name')
            ->limit(50)
            ->get();

        $pageTitle = __('quizzes::teacher/messages.page_title', ['name' => $course->name]);
        $pageName = __('quizzes::teacher/messages.page_name');

        return view('teacher::teacher.quiz.quiz', compact(
            'teacher',
            'course',
            'quizzes',
            'assignments',
            'submissionsPaginator',
            'buyers',
            'assignedStudentIds',
            'studentSearch',
            'pageTitle',
            'pageName'
        ) + [
            'lessons' => $this->resolveCourseLessons($course),
        ]);
    }

    public function store(Request $request, int $courseId)
    {
        $teacher = $this->resolveTeacher();
        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $this->authorizeQuizAccess($teacher, 'create', $course);

        $data = $request->validate([
            'lesson_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'deadline_at' => ['nullable', 'date'],
            'show_answers_after' => ['nullable', 'boolean'],
            'questions' => ['nullable', 'array'],
        ]);

        $quiz = CourseQuiz::query()->create([
            'course_id' => $course->id,
            'lesson_id' => !empty($data['lesson_id']) ? (int) $data['lesson_id'] : null,
            'title' => trim((string) $data['title']),
            'slug' => Str::slug((string) $data['title']) ?: null,
            'description' => $data['description'] ?? null,
            'passing_score' => (int) ($data['passing_score'] ?? 70),
            'max_attempts' => $data['max_attempts'] ?? null,
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'show_answers_after' => (bool) ($data['show_answers_after'] ?? true),
            'status' => 1,
            'published_at' => now(),
            'deadline_at' => $data['deadline_at'] ?? null,
            'created_by' => $teacher->student_id,
        ]);

        $this->logTeacherQuizActivity(
            $teacher,
            $course,
            $quiz,
            'quiz_created',
            'quizzes::teacher/messages.history.quiz_created',
            ['initial_data' => $data]
        );

        return redirect()->route('teacher.dashboard.quizzes.index', $course->id)->with('msg_success', __('quizzes::teacher/messages.flash.created'));
    }

    public function edit(int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz = CourseQuiz::query()->with(['lesson', 'questions.choices'])->where('course_id', $course->id)->findOrFail($quizId);
        $this->authorizeQuizAccess($teacher, 'update', $quiz);

        $pageTitle = __('quizzes::teacher/messages.edit.title', ['title' => $quiz->title]);
        $pageName = __('quizzes::teacher/messages.edit.breadcrumb');

        return view('teacher::teacher.quiz.edit', [
            'teacher' => $teacher,
            'course' => $course,
            'quiz' => $quiz,
            'pageTitle' => $pageTitle,
            'pageName' => $pageName,
            'lessons' => $this->resolveCourseLessons($course),
        ]);
    }

    public function assign(Request $request, int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz = CourseQuiz::query()->where('course_id', $course->id)->findOrFail($quizId);
        $this->authorizeQuizAccess($teacher, 'update', $quiz);

        $data = $request->validate([
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer'],
            'deadline_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $buyerIds = $course->students()->pluck('students.id')->map(fn ($id) => (int) $id)->all();
        $selectedIds = collect($data['student_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $buyerIds, true))
            ->unique()
            ->values();

        $studentIds = $selectedIds->isNotEmpty() ? $selectedIds : collect($buyerIds);

        if ($studentIds->isEmpty()) {
            return back()->with('msg_danger', __('quizzes::teacher/messages.flash.no_buyers'));
        }

        DB::transaction(function () use ($teacher, $quiz, $studentIds, $data) {
            foreach ($studentIds as $studentId) {
                CourseQuizAssignment::query()->updateOrCreate(
                    ['quiz_id' => $quiz->id, 'student_id' => $studentId],
                    [
                        'assigned_by' => $teacher->student_id,
                        'deadline_at' => $data['deadline_at'] ?? null,
                        'status' => 'assigned',
                        'note' => trim((string) ($data['note'] ?? '')) ?: null,
                    ]
                );
            }
        });

        // Gửi thông báo cho học viên
        $quizUrl = route('teacher.dashboard.quizzes.show', [
            'course' => $course->id,
            'quiz' => $quiz->id
        ]);

        foreach ($studentIds as $studentId) {
            $student = Student::query()->find($studentId);
            if ($student) {
                $student->notify(new QuizAssignedNotification($quiz, $course, $quizUrl));
            }
        }

        $this->logTeacherQuizActivity(
            $teacher,
            $course,
            $quiz,
            'quiz_assigned',
            'quizzes::teacher/messages.history.quiz_assigned',
            ['student_ids' => $studentIds->all()]
        );

        return redirect()->route('teacher.dashboard.quizzes.index', $course->id)->with('msg_success', __('quizzes::teacher/messages.flash.assigned'));
    }

    public function results(Request $request, int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz = CourseQuiz::query()->with(['lesson', 'questions.choices'])->where('course_id', $course->id)->findOrFail($quizId);
        $this->authorizeQuizAccess($teacher, 'view', $quiz);

        $submissions = CourseQuizSubmission::query()
            ->with(['student', 'answers.question', 'answers.choice'])
            ->where('quiz_id', $quiz->id)
            ->when($request->filled('student'), function ($query) use ($request) {
                $term = '%' . trim($request->string('student')) . '%';
                $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', $term));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->string('status')->toString() === 'passed') {
                    $query->where('passed', true);
                } elseif ($request->string('status')->toString() === 'failed') {
                    $query->where('passed', false);
                }
            })
            ->when($request->filled('from'), fn ($query) => $query->whereDate('submitted_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('submitted_at', '<=', $request->date('to')))
            ->latest('id')
            ->get();

        $pageTitle = __('quizzes::teacher/messages.results.title', ['title' => $quiz->title]);
        $pageName = __('quizzes::teacher/messages.results.breadcrumb');

        return view('teacher::teacher.quiz.results', compact('teacher', 'course', 'quiz', 'submissions', 'pageTitle', 'pageName'));
    }

    public function exportResults(Request $request, int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz = CourseQuiz::query()->where('course_id', $course->id)->findOrFail($quizId);

        if (!$teacher->packageHasFeature('can_import_export')) {
            return redirect()
                ->route('teacher.dashboard.quizzes.results', [$course->id, $quiz->id])
                ->with('msg_danger', __('packages::teacher.package_features.import_export_locked'));
        }

        $this->authorizeQuizAccess($teacher, 'view', $quiz);

        $submissions = CourseQuizSubmission::query()
            ->with(['student'])
            ->where('quiz_id', $quiz->id)
            ->latest('id')
            ->get();

        $filename = 'quiz-results-' . $quiz->id . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($submissions) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                __('quizzes::teacher/messages.results.table.student'),
                __('quizzes::teacher/messages.results.table.attempt'),
                __('quizzes::teacher/messages.results.table.score'),
                __('quizzes::teacher/messages.results.table.result'),
                __('quizzes::teacher/messages.results.table.date')
            ]);

            foreach ($submissions as $submission) {
                fputcsv($handle, [
                    $submission->student?->name,
                    $submission->attempt_no,
                    $submission->score,
                    $submission->passed 
                        ? __('quizzes::teacher/messages.results.item.passed') 
                        : __('quizzes::teacher/messages.results.item.failed'),
                    optional($submission->submitted_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function update(Request $request, int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz = CourseQuiz::query()->with('course')->where('course_id', $course->id)->findOrFail($quizId);
        $this->authorizeQuizAccess($teacher, 'update', $quiz);

        $data = $request->validate([
            'title'              => ['sometimes', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'status'             => ['nullable', 'integer', 'in:0,1'],
            'passing_score'      => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_attempts'       => ['nullable', 'integer', 'min:1'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'deadline_at'        => ['nullable', 'date'],
            'show_answers_after' => ['nullable', 'boolean'],
        ]);

        $updateData = array_filter([
            'title'              => $data['title'] ?? null,
            'description'        => $data['description'] ?? null,
            'status'             => isset($data['status']) ? (int) $data['status'] : null,
            'passing_score'      => isset($data['passing_score']) ? (int) $data['passing_score'] : null,
            'max_attempts'       => !empty($data['max_attempts']) ? (int) $data['max_attempts'] : null,
            'time_limit_minutes' => !empty($data['time_limit_minutes']) ? (int) $data['time_limit_minutes'] : null,
            'deadline_at'        => $data['deadline_at'] ?? null,
            'show_answers_after' => $request->has('show_answers_after'),
        ], fn ($value) => $value !== null);

        $quiz->update($updateData);

        $this->logTeacherQuizActivity(
            $teacher,
            $course,
            $quiz,
            'quiz_updated',
            'quizzes::teacher/messages.history.quiz_updated',
            ['changes' => $updateData]
        );

        return redirect()->route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id])
            ->with('msg_success', __('quizzes::teacher/messages.flash.updated'));
    }

    public function destroy(int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz = CourseQuiz::query()->where('course_id', $course->id)->findOrFail($quizId);
        $this->authorizeQuizAccess($teacher, 'delete', $quiz);

        $quiz->delete();

        $this->logTeacherQuizActivity(
            $teacher,
            $course,
            $quiz,
            'quiz_deleted',
            'quizzes::teacher/messages.history.quiz_deleted'
        );

        return redirect()->route('teacher.dashboard.quizzes.index', $course->id)
            ->with('msg_success', __('quizzes::teacher/messages.flash.deleted'));
    }

    // ────────────────── Câu hỏi ──────────────────

    public function storeQuestion(Request $request, int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course  = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz    = CourseQuiz::query()->where('course_id', $course->id)->findOrFail($quizId);
        $this->authorizeQuizAccess($teacher, 'update', $quiz);

        $data = $request->validate([
            'question'             => ['required', 'string', 'max:2000'],
            'question_type'        => ['required', 'string', 'in:single_choice,multiple_choice,true_false,short_answer'],
            'points'               => ['nullable', 'integer', 'min:1', 'max:100'],
            'choices'              => ['nullable', 'array'],
            'choices.*.text'       => ['required_with:choices', 'string', 'max:500'],
            'choices.*.is_correct' => ['nullable'],
        ]);

        $questionType = $data['question_type'];
        $choices      = $data['choices'] ?? [];

        // Câu hỏi không phải short_answer bắt buộc phải có lựa chọn
        if ($questionType !== 'short_answer') {
            $nonEmptyChoices = collect($choices)->filter(fn ($c) => trim((string) ($c['text'] ?? '')) !== '');
            if ($nonEmptyChoices->count() < 2) {
                return back()->withErrors(['choices' => __('quizzes::teacher/messages.errors.choices_min')])->withInput();
            }

            $hasCorrect = $nonEmptyChoices->contains(fn ($c) => !empty($c['is_correct']));
            if (! $hasCorrect) {
                return back()->withErrors(['choices' => __('quizzes::teacher/messages.errors.choices_correct')])->withInput();
            }
        }

        $position = (int) CourseQuizQuestion::query()->where('quiz_id', $quiz->id)->max('position') + 1;

        $question = CourseQuizQuestion::query()->create([
            'quiz_id'       => $quiz->id,
            'question'      => trim((string) $data['question']),
            'question_type' => $questionType,
            'points'        => (int) ($data['points'] ?? 1),
            'position'      => $position,
        ]);

        foreach ($choices as $i => $choiceData) {
            $text = trim((string) ($choiceData['text'] ?? ''));
            if ($text === '') {
                continue; // Bỏ qua ô trống
            }
            CourseQuizChoice::query()->create([
                'question_id' => $question->id,
                'choice_text' => $text,
                'is_correct'  => !empty($choiceData['is_correct']),
                'position'    => $i,
            ]);
        }

        $this->logTeacherQuizActivity(
            $teacher,
            $course,
            $quiz,
            'question_created',
            'quizzes::teacher/messages.history.question_created',
            ['question_id' => $question->id]
        );

        return redirect()->route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id])
            ->with('msg_success', __('quizzes::teacher/messages.flash.question_created'));
    }

    public function updateQuestion(Request $request, int $courseId, int $quizId, int $questionId)
    {
        $teacher  = $this->resolveTeacher();
        $course   = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz     = CourseQuiz::query()->where('course_id', $course->id)->findOrFail($quizId);
        $question = CourseQuizQuestion::query()->where('quiz_id', $quiz->id)->findOrFail($questionId);
        $this->authorizeQuizAccess($teacher, 'update', $quiz);

        $data = $request->validate([
            'question'             => ['required', 'string', 'max:2000'],
            'question_type'        => ['required', 'string', 'in:single_choice,multiple_choice,true_false,short_answer'],
            'points'               => ['nullable', 'integer', 'min:1', 'max:100'],
            'choices'              => ['nullable', 'array'],
            'choices.*.text'       => ['required_with:choices', 'string', 'max:500'],
            'choices.*.is_correct' => ['nullable'],
        ]);

        $questionType = $data['question_type'];
        $choices      = $data['choices'] ?? [];

        if ($questionType !== 'short_answer') {
            $nonEmptyChoices = collect($choices)->filter(fn ($c) => trim((string) ($c['text'] ?? '')) !== '');
            if ($nonEmptyChoices->count() < 2) {
                return back()->withErrors(['choices' => __('quizzes::teacher/messages.errors.choices_min')])->withInput();
            }
            $hasCorrect = $nonEmptyChoices->contains(fn ($c) => !empty($c['is_correct']));
            if (! $hasCorrect) {
                return back()->withErrors(['choices' => __('quizzes::teacher/messages.errors.choices_correct')])->withInput();
            }
        }

        $question->update([
            'question'      => trim((string) $data['question']),
            'question_type' => $questionType,
            'points'        => (int) ($data['points'] ?? 1),
        ]);

        // Cập nhật choices: Cách đơn giản nhất là xóa và tạo lại để đảm bảo thứ tự
        $question->choices()->delete();

        foreach ($choices as $i => $choiceData) {
            $text = trim((string) ($choiceData['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            CourseQuizChoice::query()->create([
                'question_id' => $question->id,
                'choice_text' => $text,
                'is_correct'  => !empty($choiceData['is_correct']),
                'position'    => $i,
            ]);
        }

        $this->logTeacherQuizActivity(
            $teacher,
            $course,
            $quiz,
            'question_updated',
            'quizzes::teacher/messages.history.question_updated',
            ['question_id' => $question->id]
        );

        return redirect()->route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id])
            ->with('msg_success', __('quizzes::teacher/messages.flash.question_updated'));
    }

    public function deleteQuestion(int $courseId, int $quizId, int $questionId)
    {
        $teacher  = $this->resolveTeacher();
        $course   = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz     = CourseQuiz::query()->where('course_id', $course->id)->findOrFail($quizId);
        $this->authorizeQuizAccess($teacher, 'update', $quiz);

        CourseQuizQuestion::query()
            ->where('quiz_id', $quiz->id)
            ->findOrFail($questionId)
            ->delete();

        $this->logTeacherQuizActivity(
            $teacher,
            $course,
            $quiz,
            'question_deleted',
            'quizzes::teacher/messages.history.question_deleted',
            ['question_id' => $questionId]
        );

        return redirect()->route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id])
            ->with('msg_success', __('quizzes::teacher/messages.flash.question_deleted'));
    }

    public function generateAiQuestions(Request $request, int $courseId, int $quizId, \Modules\Teacher\src\Services\GeminiAiService $aiService)
    {
        $teacher = $this->resolveTeacher();
        $course  = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz    = CourseQuiz::query()->where('course_id', $course->id)->findOrFail($quizId);
        $this->authorizeQuizAccess($teacher, 'update', $quiz);
        
        if (!$teacher->packageHasFeature('can_use_ai_quiz')) {
            return response()->json([
                'success' => false,
                'message' => __('quizzes::teacher/messages.flash.ai_locked')
            ], 403);
        }

        $aiLimit = $teacher->currentPackage()?->effective_ai_quiz_limit;
        $maxAttempts = $aiLimit ?? 999;
        
        // Use date-based key for midnight reset (e.g., ai_quiz_5_2026-04-17)
        $limiterKey = 'ai_quiz_' . $teacher->id . '_' . now()->format('Y-m-d');

        if ($aiLimit !== null && RateLimiter::tooManyAttempts($limiterKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            return response()->json([
                'success' => false,
                'message' => __('quizzes::teacher/messages.flash.ai_limit', ['max' => $maxAttempts])
            ], 429);
        }

        $request->validate([
            'topic'      => ['required', 'string', 'max:255'],
            'amount'     => ['required', 'integer', 'min:1', 'max:20'],
            'difficulty' => ['required', 'string', 'in:' . implode(',', [
                __('quizzes::teacher/messages.edit.ai_modal.difficulty_easy'),
                __('quizzes::teacher/messages.edit.ai_modal.difficulty_medium'),
                __('quizzes::teacher/messages.edit.ai_modal.difficulty_hard')
            ])],
            'language'   => ['required', 'string', 'in:Vietnamese,English'],
        ]);

        try {
            // Map difficulty from localized labels to English for the service
            $difficultyMap = [
                __('quizzes::teacher/messages.edit.ai_modal.difficulty_easy') => 'Easy',
                __('quizzes::teacher/messages.edit.ai_modal.difficulty_medium') => 'Medium',
                __('quizzes::teacher/messages.edit.ai_modal.difficulty_hard') => 'Hard'
            ];
            $mappedDifficulty = $difficultyMap[$request->input('difficulty')] ?? 'Medium';

            $questions = $aiService->generateQuizQuestions(
                $request->input('topic'),
                $request->input('amount'),
                $mappedDifficulty,
                $request->input('language')
            );

            $position = CourseQuizQuestion::query()->where('quiz_id', $quiz->id)->max('position') ?? 0;

            foreach ($questions as $qData) {
                $position++;
                $question = CourseQuizQuestion::query()->create([
                    'quiz_id'       => $quiz->id,
                    'question'      => $qData['question'],
                    'question_type' => $qData['question_type'] ?? 'single_choice',
                    'points'        => $qData['points'] ?? 1,
                    'position'      => $position,
                ]);

                if (!empty($qData['choices']) && is_array($qData['choices'])) {
                    foreach ($qData['choices'] as $cIndex => $cData) {
                        CourseQuizChoice::query()->create([
                            'question_id' => $question->id,
                            'choice_text' => $cData['text'],
                            'is_correct'  => !empty($cData['is_correct']),
                            'position'    => $cIndex,
                        ]);
                    }
                }
            }

            if ($aiLimit !== null) {
                RateLimiter::hit($limiterKey, 86400);
            }

            return response()->json([
                'success' => true,
                'message' => __('quizzes::teacher/messages.flash.ai_success', ['count' => count($questions)])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ────────────────── Import / Export câu hỏi ──────────────────

    public function importTemplate(int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course  = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz    = CourseQuiz::query()->where('course_id', $course->id)->findOrFail($quizId);

        if (!$teacher->packageHasFeature('can_import_export')) {
            return redirect()
                ->route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id])
                ->with('msg_danger', __('packages::teacher.package_features.import_export_locked'));
        }

        $this->authorizeQuizAccess($teacher, 'update', $quiz);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="quiz-import-template.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['question', 'question_type', 'points', 'choice_a', 'choice_b', 'choice_c', 'choice_d', 'correct_choices']);
            fputcsv($handle, [
                __('quizzes::teacher/messages.samples.question_1'),
                'single_choice',
                '1',
                __('quizzes::teacher/messages.samples.choice_1a'),
                __('quizzes::teacher/messages.samples.choice_1b'),
                __('quizzes::teacher/messages.samples.choice_1c'),
                __('quizzes::teacher/messages.samples.choice_1d'),
                'A',
            ]);
            fputcsv($handle, [
                __('quizzes::teacher/messages.samples.question_2'),
                'multiple_choice',
                '2',
                'PHP',
                'Python',
                'CSS',
                'Java',
                'A,B,D',
            ]);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importQuestions(Request $request, int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course  = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz    = CourseQuiz::query()->where('course_id', $course->id)->findOrFail($quizId);

        if (!$teacher->packageHasFeature('can_import_export')) {
            return redirect()
                ->route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id])
                ->with('msg_danger', __('packages::teacher.package_features.import_export_locked'));
        }

        $this->authorizeQuizAccess($teacher, 'update', $quiz);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path     = $request->file('file')->getRealPath();
        $handle   = fopen($path, 'r');
        $headers  = null;
        $imported = 0;
        $errors   = [];

        $choiceKeys = ['choice_a', 'choice_b', 'choice_c', 'choice_d'];
        $choiceMap  = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3];

        $startPosition = (int) CourseQuizQuestion::query()->where('quiz_id', $quiz->id)->max('position');

        while (($row = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map('strtolower', array_map('trim', $row));
                continue;
            }

            $data = array_combine($headers, $row);
            if (empty($data) || trim((string) ($data['question'] ?? '')) === '') {
                continue;
            }

            $questionText  = trim((string) $data['question']);
            $questionType  = in_array($data['question_type'] ?? '', ['single_choice', 'multiple_choice', 'true_false', 'short_answer'], true)
                ? $data['question_type']
                : 'single_choice';
            $points        = max(1, (int) ($data['points'] ?? 1));
            $correctRaw    = strtolower(trim((string) ($data['correct_choices'] ?? 'a')));
            $correctLetters = array_filter(array_map('trim', explode(',', $correctRaw)));

            $question = CourseQuizQuestion::query()->create([
                'quiz_id'       => $quiz->id,
                'question'      => $questionText,
                'question_type' => $questionType,
                'points'        => $points,
                'position'      => ++$startPosition,
            ]);

            foreach ($choiceKeys as $i => $key) {
                $choiceText = trim((string) ($data[$key] ?? ''));
                if ($choiceText === '') {
                    continue;
                }

                $letter    = chr(ord('a') + $i);
                $isCorrect = in_array($letter, $correctLetters, true);

                CourseQuizChoice::query()->create([
                    'question_id' => $question->id,
                    'choice_text' => $choiceText,
                    'is_correct'  => $isCorrect,
                    'position'    => $i,
                ]);
            }

            $imported++;
        }

        fclose($handle);

        return redirect()->route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id])
            ->with('msg_success', __('quizzes::teacher/messages.flash.import_success', ['count' => $imported]));
    }

    public function exportQuestions(int $courseId, int $quizId)
    {
        $teacher = $this->resolveTeacher();
        $course  = $this->resolveOwnedCourse($teacher, $courseId);
        $quiz    = CourseQuiz::query()->with(['questions.choices'])->where('course_id', $course->id)->findOrFail($quizId);

        if (!$teacher->packageHasFeature('can_import_export')) {
            return redirect()
                ->route('teacher.dashboard.quizzes.edit', [$course->id, $quiz->id])
                ->with('msg_danger', __('packages::teacher.package_features.import_export_locked'));
        }

        $this->authorizeQuizAccess($teacher, 'view', $quiz);

        $filename = 'quiz-' . $quiz->id . '-questions.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($quiz) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['question', 'question_type', 'points', 'choice_a', 'choice_b', 'choice_c', 'choice_d', 'correct_choices']);

            foreach ($quiz->questions->sortBy('position') as $question) {
                $choices     = $question->choices->sortBy('position')->values();
                $choiceTexts = [];
                $correctLetters = [];

                foreach ($choices as $idx => $choice) {
                    $choiceTexts[] = $choice->choice_text;
                    if ($choice->is_correct) {
                        $correctLetters[] = chr(ord('A') + $idx);
                    }
                }

                $row = [
                    $question->question,
                    $question->question_type,
                    $question->points,
                    $choiceTexts[0] ?? '',
                    $choiceTexts[1] ?? '',
                    $choiceTexts[2] ?? '',
                    $choiceTexts[3] ?? '',
                    implode(',', $correctLetters),
                ];

                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function resolveTeacher(): Teacher
    {
        $teacher = Teacher::query()
            ->where('student_id', auth('students')->id())
            ->where('status', 'active')
            ->firstOrFail();

        return $teacher->loadMissing('application.package');
    }

    private function resolveOwnedCourse(Teacher $teacher, int $courseId): Courses
    {
        return Courses::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->where('teacher_id', $teacher->id)
            ->findOrFail($courseId);
    }

    private function authorizeQuizAccess(Teacher $teacher, string $ability, $subject): void
    {
        $policy = app(CourseQuizPolicy::class);

        if (! $policy->{$ability}($teacher, $subject)) {
            abort(403);
        }
    }

    private function resolveCourseLessons(Courses $course)
    {
        return Lesson::query()
            ->where('course_id', $course->id)
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh']);
    }
}
