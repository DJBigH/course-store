<?php

namespace Modules\Home\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\NewContactNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Contacts\src\Models\Contacts;
use Modules\Home\src\Support\GeminiSalesChatbotService;
use Modules\Home\src\Support\SalesChatbotService;
use Modules\User\src\Models\User;

class ChatbotController extends Controller
{
    private const MEMORY_KEY = 'sales_chatbot.memory';
    private const MEMORY_UPDATED_AT_KEY = 'sales_chatbot.updated_at';
    private const TAGS_KEY = 'sales_chatbot.tags';
    private const MEMORY_LIMIT = 6;

    public function __construct(
        private SalesChatbotService $salesChatbotService,
        private GeminiSalesChatbotService $geminiSalesChatbotService,
    ) {
    }

    public function reply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $message = trim((string) $data['message']);
        $memory = $this->getChatMemory();
        $localReply = $this->salesChatbotService->reply($message, $memory);

        if ($this->salesChatbotService->isSensitiveRequest($message)) {
            $finalPayload = array_merge($localReply, [
                'source' => 'local',
                'fallback_reason' => 'sensitive_request',
            ]);

            $this->rememberChatState($message, $finalPayload['intent_tags'] ?? []);
            $this->logWeakReplyIfNeeded($request, $message, $finalPayload);

            return response()->json($finalPayload);
        }

        $safeContext = $this->salesChatbotService->buildSafeContext($message, $localReply, $memory);
        $geminiReply = $this->geminiSalesChatbotService->reply($message, $safeContext);

        if (!($geminiReply['ok'] ?? false)) {
            $finalPayload = array_merge($localReply, [
                'source' => 'local',
                'fallback_reason' => $geminiReply['reason'] ?? 'fallback_local',
            ]);

            $this->rememberChatState($message, $finalPayload['intent_tags'] ?? []);
            $this->logWeakReplyIfNeeded($request, $message, $finalPayload);

            return response()->json($finalPayload);
        }

        $finalPayload = array_merge($localReply, [
            'answer' => $geminiReply['answer'],
            'source' => 'gemini',
            'fallback_reason' => null,
        ]);

        $this->rememberChatState($message, $finalPayload['intent_tags'] ?? []);

        return response()->json($finalPayload);
    }

    public function saveLead(Request $request): JsonResponse
    {
        $student = Auth::guard('students')->user();

        $data = $request->validate([
            'name' => [$student ? 'nullable' : 'required', 'string', 'max:225'],
            'phone' => [$student ? 'nullable' : 'required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:225'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $name = trim((string) ($data['name'] ?? ($student->name ?? '')));
        $phone = trim((string) ($data['phone'] ?? ($student->phone ?? '')));
        $email = trim((string) ($data['email'] ?? ($student->email ?? '')));
        $message = trim((string) ($data['message'] ?? ''));

        if ($name === '' || $phone === '') {
            return response()->json([
                'message' => 'Vui lòng cung cấp họ tên và số điện thoại để bộ phận hỗ trợ liên hệ lại.',
                'errors' => [
                    'name' => $name === '' ? ['Họ tên là bắt buộc.'] : [],
                    'phone' => $phone === '' ? ['Số điện thoại là bắt buộc.'] : [],
                ],
            ], 422);
        }

        if (!preg_match('/^(0|\+84)[0-9]{8,10}$/', $phone)) {
            return response()->json([
                'message' => 'Số điện thoại chưa đúng định dạng.',
                'errors' => [
                    'phone' => ['Số điện thoại chưa đúng định dạng.'],
                ],
            ], 422);
        }

        $leadMessage = $this->formatLeadMessage($message, $student !== null);

        $lead = Contacts::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email !== '' ? $email : null,
            'message' => $leadMessage,
            'status' => 0,
        ]);

        User::query()->inGroup('super_admin')->get()->each(function ($admin) use ($lead) {
            $admin->notify(new NewContactNotification($lead));
        });

        return response()->json([
            'message' => 'Đã lưu thông tin liên hệ. Bộ phận hỗ trợ sẽ xem và phản hồi nhanh nhất có thể.',
        ]);
    }

    private function getChatMemory(): array
    {
        $updatedAt = (int) session(self::MEMORY_UPDATED_AT_KEY, 0);
        $ttlSeconds = max(300, $this->chatMemoryTtlMinutes() * 60);

        if ($updatedAt > 0 && (time() - $updatedAt) > $ttlSeconds) {
            session()->forget([self::MEMORY_KEY, self::MEMORY_UPDATED_AT_KEY, self::TAGS_KEY]);
            return [];
        }

        return collect(session(self::MEMORY_KEY, []))
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->take(-self::MEMORY_LIMIT)
            ->values()
            ->all();
    }

    private function rememberChatState(string $message, array $tags = []): void
    {
        $memory = $this->getChatMemory();
        $memory[] = $message;

        session([
            self::MEMORY_KEY => array_slice(array_values(array_unique(array_filter($memory))), -self::MEMORY_LIMIT),
            self::MEMORY_UPDATED_AT_KEY => time(),
            self::TAGS_KEY => array_values(array_unique(array_merge(session(self::TAGS_KEY, []), $tags))),
        ]);
    }

    private function formatLeadMessage(string $message, bool $fromLoggedInStudent = false): string
    {
        $parts = [];
        $tags = collect(session(self::TAGS_KEY, []))->filter()->values()->all();
        $memory = $this->getChatMemory();

        $parts[] = $message !== ''
            ? $message
            : 'Khách hàng để lại thông tin từ chatbot bán hàng.';

        if ($fromLoggedInStudent) {
            $parts[] = 'Nguồn: học viên đã đăng nhập xác nhận cần được liên hệ.';
        }

        if (!empty($tags)) {
            $parts[] = 'Tags quan tâm: ' . implode(', ', $tags) . '.';
        }

        if (!empty($memory)) {
            $parts[] = 'Ngữ cảnh chat gần đây: ' . implode(' | ', array_slice($memory, -4)) . '.';
        }

        return trim(implode("\n", $parts));
    }

    private function logWeakReplyIfNeeded(Request $request, string $message, array $payload): void
    {
        if (!$this->salesChatbotService->isWeakReply($payload)) {
            return;
        }

        Log::info('sales_chatbot.weak_reply', [
            'message' => $message,
            'resolved_message' => $payload['resolved_message'] ?? null,
            'intent_tags' => $payload['intent_tags'] ?? [],
            'source' => $payload['source'] ?? 'local',
            'fallback_reason' => $payload['fallback_reason'] ?? null,
            'student_id' => optional(Auth::guard('students')->user())->id,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'locale' => app()->getLocale(),
        ]);
    }

    private function chatMemoryTtlMinutes(): int
    {
        return max(5, (int) setting('chatbot_message_ttl_minutes', env('CHATBOT_MESSAGE_TTL_MINUTES', 10)));
    }
}
