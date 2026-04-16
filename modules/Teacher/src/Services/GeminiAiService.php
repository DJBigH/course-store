<?php

namespace Modules\Teacher\src\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    /**
     * Generate quiz questions based on a topic using Gemini API.
     *
     * @param string $topic
     * @param int $amount
     * @param string $difficulty
     * @param string $language
     * @return array
     */
    public function generateQuizQuestions(string $topic, int $amount = 5, string $difficulty = 'Medium', string $language = 'Vietnamese'): array
    {
        $apiKey = config('services.gemini.api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('Chưa cấu hình GEMINI_API_KEY. Quản trị viên vui lòng kiểm tra file cấu hình môi trường.');
        }

        $model = config('services.gemini.model', 'gemini-flash-latest');
        $endpoint = config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent');
        $url = sprintf($endpoint, $model) . '?key=' . $apiKey;

        $prompt = $this->buildPrompt($topic, $amount, $difficulty, $language);

        $timeout = config('services.gemini.timeout');
        if (!$timeout || $timeout < 30) {
            $timeout = 60;
        }

        try {
            $response = Http::timeout((int) $timeout)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ],
                ]);

            if ($response->status() === 429) {
                Log::warning("Gemini AI API Rate Limit Exceeded.");
                throw new \Exception('Hệ thống AI hiện đang chịu tải cao (nhiều yêu cầu) do sử dụng bản giới hạn. Vui lòng thử lại sau 1-2 phút!');
            }

            if ($response->failed()) {
                Log::error("Gemini AI API Error: " . $response->body());
                throw new \Exception('Lỗi khi kết nối với máy chủ AI: ' . $response->json('error.message', 'Lỗi không xác định.'));
            }

            $body = $response->json();
            
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if (empty($text)) {
                 throw new \Exception('AI không tạo được nội dung hợp lệ! Vui lòng thử lại.');
            }

            $parsedData = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($parsedData['questions'])) {
                throw new \Exception('AI trả về cấu trúc dữ liệu không chuẩn. Vui lòng thử lại.');
            }

            return $parsedData['questions'];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Gemini AI Connection Error: " . $e->getMessage());
            $timeoutMsg = str_contains($e->getMessage(), 'timed out') 
                ? 'Máy chủ AI đang phải xử lý quá nhiều dữ liệu nên phản hồi chậm. Vui lòng thử lại với số lượng câu hỏi ít hơn!'
                : 'Không thể kết nối đến máy chủ AI lúc này. Vui lòng thử lại sau.';
            throw new \Exception($timeoutMsg);
        }
    }

    private function buildPrompt(string $topic, int $amount, string $difficulty, string $language): string
    {
        return <<<PROMPT
You are an expert educator. Create a multiple-choice quiz about "{$topic}".
Difficulty level: {$difficulty}.
Number of questions: {$amount}.
Language: {$language}.

Output MUST be exclusively a valid JSON object matching the schema below. Do not wrap it in markdown code blocks. Do not add any conversational text.

Schema:
{
  "questions": [
    {
      "question": "The question text",
      "question_type": "single_choice",
      "points": 1,
      "explanation": "A brief explanation",
      "choices": [
        {
          "text": "First option",
          "is_correct": true
        },
        {
          "text": "Second option",
          "is_correct": false
        }
      ]
    }
  ]
}

Note:
- All questions must be "single_choice".
- Provide exactly 4 choices per question.
- Exactly 1 choice must have "is_correct": true.
- Ensure the JSON is well-formed.
PROMPT;
    }
}
