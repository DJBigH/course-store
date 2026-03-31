<?php

namespace Modules\Home\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Home\src\Http\Requests\ChatbotKnowledgeRequest;
use Modules\Home\src\Models\ChatbotKnowledge;
use Modules\Home\src\Models\ChatbotUnresolvedQuestion;
use Modules\Home\src\Support\GeminiHealthService;

class ChatbotKnowledgeController extends Controller
{
    public function __construct(private GeminiHealthService $geminiHealthService)
    {
    }

    public function index(Request $request)
    {
        $pageTitle = 'Train bot thường';
        $status = (string) $request->input('status', 'all');
        $search = trim((string) $request->input('q', ''));

        $knowledgeItems = ChatbotKnowledge::query()
            ->withCount('unresolvedQuestions')
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('question', 'like', '%' . $search . '%')
                        ->orWhere('keywords', 'like', '%' . $search . '%')
                        ->orWhere('answer', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('is_active')
            ->orderByDesc('priority')
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $geminiHealth = $this->geminiHealthService->snapshot();

        return view('home::chatbot_knowledge.lists', compact('pageTitle', 'knowledgeItems', 'status', 'search', 'geminiHealth'));
    }

    public function create(Request $request)
    {
        $pageTitle = 'Thêm tri thức chatbot';
        $fromLog = null;
        $prefill = [
            'question' => '',
            'keywords' => '',
            'answer' => '',
        ];

        if ($request->filled('from_log')) {
            $fromLog = ChatbotUnresolvedQuestion::query()->with(['student:id,name,email'])->find($request->integer('from_log'));

            if ($fromLog) {
                $prefill = [
                    'question' => $fromLog->message,
                    'keywords' => $this->buildPrefillKeywords($fromLog),
                    'answer' => $this->buildPrefillAnswer($fromLog),
                ];
            }
        }

        return view('home::chatbot_knowledge.create', compact('pageTitle', 'fromLog', 'prefill'));
    }

    public function store(ChatbotKnowledgeRequest $request): RedirectResponse
    {
        $knowledge = ChatbotKnowledge::query()->create([
            'question' => trim((string) $request->input('question')),
            'keywords' => trim((string) $request->input('keywords', '')) ?: null,
            'answer' => trim((string) $request->input('answer')),
            'priority' => (int) $request->input('priority', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->filled('unresolved_id')) {
            $unresolved = ChatbotUnresolvedQuestion::query()->find($request->integer('unresolved_id'));

            if ($unresolved) {
                $unresolved->update([
                    'status' => 'handled',
                    'knowledge_id' => $knowledge->id,
                    'resolved_at' => now(),
                ]);
            }
        }

        activity_log(
            action: 'create',
            subject: $knowledge,
            properties: ['data' => $knowledge->toArray()],
            logName: 'Chatbot knowledge',
            description: 'Thêm tri thức chatbot'
        );

        return redirect()->route('chatbot-knowledge.index')->with('msg', 'Đã thêm tri thức chatbot thành công.');
    }

    public function edit($id)
    {
        $pageTitle = 'Sửa tri thức chatbot';
        $knowledge = ChatbotKnowledge::query()->findOrFail($id);

        return view('home::chatbot_knowledge.edit', compact('pageTitle', 'knowledge'));
    }

    public function update(ChatbotKnowledgeRequest $request, $id): RedirectResponse
    {
        $knowledge = ChatbotKnowledge::query()->findOrFail($id);
        $oldData = $knowledge->toArray();

        $knowledge->update([
            'question' => trim((string) $request->input('question')),
            'keywords' => trim((string) $request->input('keywords', '')) ?: null,
            'answer' => trim((string) $request->input('answer')),
            'priority' => (int) $request->input('priority', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        activity_log(
            action: 'update',
            subject: $knowledge,
            properties: [
                'old' => $oldData,
                'new' => $knowledge->fresh()->toArray(),
            ],
            logName: 'Chatbot knowledge',
            description: 'Cập nhật tri thức chatbot'
        );

        return back()->with('msg', 'Đã cập nhập tri thức chatbot thành công.');
    }

    public function destroy($id): RedirectResponse
    {
        $knowledge = ChatbotKnowledge::query()->findOrFail($id);
        $snapshot = $knowledge->toArray();

        $knowledge->delete();

        activity_log(
            action: 'delete',
            subject: $knowledge,
            properties: ['data' => $snapshot],
            logName: 'Chatbot knowledge',
            description: 'Xóa tri thức chatbot'
        );

        return redirect()->route('chatbot-knowledge.index')->with('msg', 'Đã xóa tri thức chatbot.');
    }

    public function unresolved(Request $request)
    {
        $pageTitle = 'Log bot Không hiểu';
        $status = (string) $request->input('status', 'pending');
        $search = trim((string) $request->input('q', ''));
        $source = (string) $request->input('source', 'all');
        $studentSearch = trim((string) $request->input('student', ''));
        $minHits = max(0, (int) $request->input('min_hits', 0));

        $logs = ChatbotUnresolvedQuestion::query()
            ->with(['student:id,name,email', 'knowledge:id,question'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($source !== 'all', fn ($query) => $query->where('source', $source))
            ->when($minHits > 0, fn ($query) => $query->where('hit_count', '>=', $minHits))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('message', 'like', '%' . $search . '%')
                        ->orWhere('resolved_message', 'like', '%' . $search . '%')
                        ->orWhere('normalized_message', 'like', '%' . $search . '%');
                });
            })
            ->when($studentSearch !== '', function ($query) use ($studentSearch) {
                $query->where(function ($subQuery) use ($studentSearch) {
                    if (is_numeric($studentSearch)) {
                        $subQuery->orWhere('student_id', (int) $studentSearch);
                    }

                    $subQuery->orWhereHas('student', function ($studentQuery) use ($studentSearch) {
                        $studentQuery->where('name', 'like', '%' . $studentSearch . '%')
                            ->orWhere('email', 'like', '%' . $studentSearch . '%');
                    });
                });
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'handled' THEN 1 ELSE 2 END")
            ->orderByDesc('hit_count')
            ->orderByDesc('last_asked_at')
            ->paginate(15)
            ->withQueryString();

        $geminiHealth = $this->geminiHealthService->snapshot();

        return view('home::chatbot_knowledge.unresolved', compact(
            'pageTitle',
            'logs',
            'status',
            'search',
            'source',
            'studentSearch',
            'minHits',
            'geminiHealth'
        ));
    }

    public function updateUnresolvedStatus(Request $request, $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,handled,ignored'],
        ]);

        $log = ChatbotUnresolvedQuestion::query()->findOrFail($id);
        $log->update([
            'status' => $data['status'],
            'resolved_at' => $data['status'] === 'pending' ? null : now(),
        ]);

        return back()->with('msg', 'Đã cập nhập trạng thái log chatbot.');
    }

    private function buildPrefillKeywords(ChatbotUnresolvedQuestion $log): string
    {
        $tags = collect($log->intent_tags ?? [])
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->values();

        if ($tags->isNotEmpty()) {
            return $tags->implode(', ');
        }

        return trim((string) ($log->normalized_message ?? ''));
    }

    private function buildPrefillAnswer(ChatbotUnresolvedQuestion $log): string
    {
        $parts = [];

        if (!empty($log->resolved_message)) {
            $parts[] = (string) $log->resolved_message;
        }

        if (!empty($log->intent_tags)) {
            $parts[] = 'Ngữ cảnh quan tâm: ' . implode(', ', (array) $log->intent_tags) . '.';
        }

        if ($log->student) {
            $parts[] = 'Mẫu hỏi này đến từ học viên: ' . $log->student->name . ($log->student->email ? ' (' . $log->student->email . ')' : '') . '.';
        }

        $parts[] = 'Hãy chỉnh lại câu trả lời này trước khi lưu để bot thường trả lời tự nhiên và chính xác hơn.';

        return implode("\n\n", array_filter($parts));
    }
}
