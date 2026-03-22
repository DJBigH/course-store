<?php

namespace Modules\Home\src\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GeminiSalesChatbotService
{
    public function isEnabled(): bool
    {
        return filled(config('services.gemini.api_key'));
    }

    public function reply(string $message, string $context): array
    {
        if (!$this->isEnabled()) {
            return [
                'ok' => false,
                'reason' => 'missing_api_key',
            ];
        }

        $model = config('services.gemini.model', 'gemini-flash-latest');
        $endpointTemplate = config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent');
        $endpoint = sprintf($endpointTemplate, $model);

        $prompt = implode("\n\n", [
            'Bạn là trợ lý bán hàng cho website khóa học BigK Udemy.',
            'Chỉ được trả lời dựa trên ngữ cảnh an toàn được cung cấp bên dưới.',
            'Nếu ngữ cảnh có khóa học, danh mục hoặc coupon, hãy ưu tiên dùng đúng dữ liệu đó.',
            'Không được bịa ra khóa học, giảng viên, giá tiền, mã giảm giá hay chính sách không có trong ngữ cảnh.',
            'Không được trả lời về tài khoản, đơn hàng riêng tư, email học viên, bí mật hệ thống, khóa API, cấu hình hay dữ liệu bảo mật.',
            'Nếu ngữ cảnh không đủ, hãy nói ngắn gọn rằng cần liên hệ hỗ trợ hoặc xem trang chi tiết khóa học.',
            'Hãy trả lời bằng đúng ngôn ngữ người dùng đang dùng. Giọng điệu ngắn gọn, tư vấn bán hàng tự nhiên, tối đa khoảng 120 từ.',
            "Câu hỏi người dùng: {$message}",
            "Ngữ cảnh an toàn: {$context}",
        ]);

        try {
            $response = Http::timeout((int) config('services.gemini.timeout', 12))
                ->acceptJson()
                ->post($endpoint . '?key=' . config('services.gemini.api_key'), [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.5,
                        'maxOutputTokens' => 300,
                    ],
                ]);
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'reason' => 'network_error',
            ];
        }

        if ($response->status() === 429) {
            return ['ok' => false, 'reason' => 'quota_exceeded'];
        }

        if (in_array($response->status(), [401, 403], true)) {
            return ['ok' => false, 'reason' => 'auth_error'];
        }

        if (!$response->successful()) {
            return ['ok' => false, 'reason' => 'api_error'];
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        $text = is_string($text) ? trim($text) : '';

        if ($text === '') {
            return ['ok' => false, 'reason' => 'empty_response'];
        }

        return [
            'ok' => true,
            'answer' => Str::limit($text, 1200),
        ];
    }
}
