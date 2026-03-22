<?php

namespace Modules\Home\src\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Categories\src\Models\Category;
use Modules\Courses\src\Models\Courses;
use Modules\Students\src\Models\Coupons;

class SalesChatbotService
{
    private const RESULT_LIMIT = 4;
    private const CHEAP_PRICE_THRESHOLD = 500000;

    private array $sensitiveKeywords = [
        'password',
        'mật khẩu',
        'mat khau',
        'token',
        'secret',
        'api key',
        'private key',
        'env',
        '.env',
        'config',
        'database',
        'db',
        'admin email',
        'email admin',
        'email học viên',
        'đơn hàng',
        'order',
        'thanh toán',
        'payment',
        'billing',
        'bank',
        'stk',
        'số tài khoản',
        'tài khoản admin',
        'backup',
    ];

    private array $courseKeywords = [
        'khóa học',
        'khoa hoc',
        'course',
        'học gì',
        'lộ trình',
        'react',
        'javascript',
        'frontend',
        'backend',
        'html',
        'css',
        'typescript',
        'php',
        'laravel',
        'node',
        'sql',

        'danh mục',
        'category',
        'giá',
        'dưới',
        'trên',

        'rẻ nhất',
        'sale',
        'giảm giá',
        'khuyến mãi',

        'người mới',
        'newbie',
        'beginner',
        'cơ bản',
        'miễn phí',
        'free',

        'nổi bật',
        'mới nhất',
        'phổ biến',

        'fullstack',
        'full stack',
        'full-stack',
        'devops',
        'ui ux',
        'ui/ux',

        'thực tập',
        'đi làm',
        'từ số 0',
        'mất gốc',
    ];

    private array $couponKeywords = [
        'coupon',
        'discount',
        'giảm giá',
        'khuyến mãi',
        'mã giảm',
        'mã khuyến mãi',
        'ưu đãi',
        'km',
    ];
    private array $supportKeywords = [
        'liên hệ',
        'hotline',
        'facebook',
        'email',
        'support',
        'hỗ trợ',
        'admin',
    ];

    private array $adviceKeywords = [
        'nên học gì',
        'nên học khóa nào',
        'nên bắt đầu từ đâu',
        'học gì trước',
        'phù hợp nhất',
        'khóa nào hợp',
        'khóa nào phù hợp',
        'đáng mua',
        'gợi ý giúp mình',
        'tư vấn giúp mình',
        'tư vấn',
        'chọn giúp',
    ];

    private array $comparisonKeywords = [
        'hay',
        'so với',
        'khác gì',
        'khac gi',
        'nên chọn',
        'nen chon',
        'cái nào tốt hơn',
        'cai nao tot hon',
    ];

    private array $thanksKeywords = [
        'cảm ơn',
        'cam on',
        'thanks',
        'thank you',
        'tks',
    ];

    private array $goodbyeKeywords = [
        'tạm biệt',
        'tam biet',
        'bye',
        'goodbye',
        'see you',
        'hẹn gặp lại',
    ];

    public function isSensitiveRequest(string $message): bool
    {
        $normalized = $this->normalize($message);
        $intent = $this->normalizeForIntent($normalized);

        $isPublicPaymentFaq =
            Str::contains($intent, ['chinh sach thanh toan', 'phuong thuc thanh toan', 'hinh thuc thanh toan'])
            || (
                Str::contains($intent, ['thanh toan', 'payment'])
                && Str::contains($intent, ['chinh sach', 'policy', 'momo', 'vnpay', 'chuyen khoan'])
            );

        if ($isPublicPaymentFaq) {
            return false;
        }

        return $this->containsAny($normalized, $this->sensitiveKeywords);
    }

    public function contextualizeMessage(string $message, array $memory = []): array
    {
        $normalized = $this->normalize($message);
        $intent = $this->normalizeForIntent($normalized);

        $memory = collect($memory)
            ->filter(fn($item) => is_string($item) && trim($item) !== '')
            ->map(fn($item) => trim($item))
            ->values()
            ->all();

        $memoryContext = $this->extractMemoryContext($memory);
        $resolvedMessage = $this->mergeFollowUpMessageWithMemory($message, $memoryContext);

        $signals = $this->inferSignals($resolvedMessage, $memory);

        return [
            'message' => $resolvedMessage,
            'memory_context' => $memoryContext,
            'tags' => $signals['tags'],
            'goal' => $signals['goal'],
            'is_follow_up' => $this->looksLikeFollowUp($intent),
        ];
    }

    public function reply(string $message, array $memory = []): array
    {
        $context = $this->contextualizeMessage($message, $memory);
        $normalized = $this->normalize($context['message']);

        if ($normalized === '') {
            return $this->baseResponse(
                'Bạn có thể hỏi mình về khóa học, danh mục, mức giá, mã giảm giá hoặc cách liên hệ hỗ trợ nhé.',
                ['intent_tags' => $context['tags'], 'resolved_message' => $context['message']]
            );
        }

        if ($this->isSensitiveRequest($message)) {
            return $this->baseResponse(
                'Mình chỉ hỗ trợ thông tin công khai về khóa học, giá bán, ưu đãi và kênh hỗ trợ. Những dữ liệu nhạy cảm hoặc bảo mật mình không thể cung cấp.',
                ['intent_tags' => $context['tags'], 'resolved_message' => $context['message']]
            );
        }

        if ($this->looksLikeGreeting($normalized)) {
            return $this->baseResponse(
                'Chào bạn, mình là trợ lý bán hàng của BigK Udemy. Mình có thể giúp bạn tìm khóa học phù hợp, xem ưu đãi hiện có và chọn cách liên hệ hỗ trợ khi cần.',
                ['intent_tags' => $context['tags'], 'resolved_message' => $context['message']]
            );
        }

        if ($this->isThanksIntent($normalized)) {
            return $this->appendMeta($this->buildThanksReply(), $context);
        }

        if ($this->isGoodbyeIntent($normalized)) {
            return $this->appendMeta($this->buildGoodbyeReply(), $context);
        }

        $intent = $this->normalizeForIntent($normalized);

        if ($faqReply = $this->buildFaqReply($intent, $context['tags'])) {
            return $this->appendMeta($faqReply, $context);
        }

        if ($this->isSupportIntent($normalized, $intent)) {
            return $this->appendMeta($this->buildSupportReply(), $context);
        }

        if ($this->containsAny($normalized, $this->couponKeywords)) {
            return $this->appendMeta($this->buildCouponReply($normalized), $context);
        }

        if ($this->isComparisonIntent($intent)) {
            return $this->appendMeta($this->buildComparisonReply($normalized), $context);
        }

        if ($this->isAdviceIntent($normalized, $intent)) {
            return $this->appendMeta($this->buildAdviceReply($normalized), $context);
        }

        $courseFilters = $this->extractCourseFilters($normalized);

        $isCourseSearchIntent = $this->containsAny($intent, $this->courseKeywords)
            || !empty($courseFilters['category_ids'])
            || (($courseFilters['min_price'] ?? null) !== null)
            || (($courseFilters['max_price'] ?? null) !== null)
            || !empty($courseFilters['discount_only'])
            || !empty($courseFilters['newbie_only'])
            || !empty($courseFilters['free_only'])
            || !empty($courseFilters['sort_cheapest'])
            || !empty($courseFilters['goal'])
            || !empty($courseFilters['lead_tags'])
            || (
                !empty($courseFilters['keywords'])
                && $this->containsAny($intent, [
                    'khoa hoc',
                    'course',
                    'backend',
                    'frontend',
                    'fullstack',
                    'devops',
                    'php',
                    'laravel',
                    'react',
                    'javascript',
                    'html',
                    'css',
                    'sql',
                    'danh muc',
                    'gia',
                    'mien phi',
                    'giam gia',
                    'sale',
                    'nguoi moi',
                    'co ban',
                    'thuc tap',
                    'di lam',
                ])
            );

        if ($isCourseSearchIntent) {
            return $this->appendMeta($this->buildCourseReply($normalized), $context);
        }

        return $this->baseResponse(
            'Mình hiện hỗ trợ tư vấn và tìm khóa học theo tên, danh mục, mức giá, khóa học cho người mới, khóa học đang giảm giá, hoặc các tiêu chí để đi làm hay thực tập, và mã khuyến mãi đang còn hiệu lực.',
            ['intent_tags' => $context['tags'], 'resolved_message' => $context['message']]
        );
    }

    public function buildSafeContext(string $message, array $reply, array $memory = []): string
    {
        $parts = [
            'Website: BigK Udemy.',
            'Chỉ sử dụng dữ liệu công khai về khóa học, danh mục, giá bán, mã giảm giá, hotline, email hỗ trợ và Facebook hỗ trợ.',
            'Không trả lời về tài khoản riêng tư, email học viên, đơn hàng chưa công khai, thanh toán nội bộ, token, khóa API, cấu hình hệ thống hay dữ liệu bảo mật.',
            'Câu trả lời local được đề xuất: ' . ($reply['answer'] ?? ''),
        ];

        $memory = collect($memory)->filter()->values()->slice(-4)->values()->all();
        if (!empty($memory)) {
            $parts[] = 'Ngữ cảnh hội thoại gần đây: ' . implode(' | ', $memory);
        }

        if (!empty($reply['intent_tags'])) {
            $parts[] = 'Tín hiệu nhu cầu: ' . implode(', ', $reply['intent_tags']);
        }

        if (!empty($reply['courses'])) {
            $courseLines = collect($reply['courses'])->map(function (array $course) {
                $price = $course['sale_price'] ?? $course['price'] ?? null;

                return implode(' | ', array_filter([
                    $course['name'] ?? null,
                    !empty($course['teacher']) ? 'Giảng viên: ' . $course['teacher'] : null,
                    !empty($price) ? 'Giá: ' . $price : null,
                    !empty($course['duration']) ? 'Thời lượng: ' . $course['duration'] : null,
                    !empty($course['categories']) ? 'Danh mục: ' . implode(', ', $course['categories']) : null,
                    !empty($course['summary']) ? 'Mô tả: ' . $course['summary'] : null,
                ]));
            })->implode("\n");

            $parts[] = "Khóa học liên quan:\n" . $courseLines;
        }

        if (!empty($reply['coupons'])) {
            $couponLines = collect($reply['coupons'])->map(function (array $coupon) {
                return implode(' | ', array_filter([
                    !empty($coupon['code']) ? 'Mã: ' . $coupon['code'] : null,
                    !empty($coupon['discount_text']) ? 'Giảm: ' . $coupon['discount_text'] : null,
                    !empty($coupon['total_condition']) ? 'Điều kiện tối thiểu: ' . $coupon['total_condition'] : null,
                    !empty($coupon['end_date']) ? 'Hết hạn: ' . $coupon['end_date'] : null,
                    !empty($coupon['course_names']) ? 'Áp dụng cho: ' . implode(', ', $coupon['course_names']) : null,
                ]));
            })->implode("\n");

            $parts[] = "Coupon liên quan:\n" . $couponLines;
        }

        $parts[] = 'Hotline hỗ trợ: ' . setting('hotline', '0123456789');
        $parts[] = 'Email hỗ trợ: ' . setting('email', 'bigkudemy@gmail.com');
        $parts[] = 'Facebook hỗ trợ: ' . setting('facebook', 'https://www.facebook.com/');
        $parts[] = 'Câu hỏi hiện tại của người dùng: ' . $this->cleanPublicText($message);

        return implode("\n\n", $parts);
    }

    public function isWeakReply(array $reply): bool
    {
        $answer = $this->normalizeForIntent((string) ($reply['answer'] ?? ''));
        $hasStructuredResults = !empty($reply['courses']) || !empty($reply['coupons']);

        if ($hasStructuredResults) {
            return false;
        }

        return Str::contains($answer, [
            'chua tim thay',
            'hien ho tro',
            'ban co the hoi minh',
            'can lien he ho tro',
            'khong the cung cap',
        ]);
    }

    private function appendMeta(array $reply, array $context): array
    {
        $reply['intent_tags'] = array_values(array_unique(array_merge(
            $reply['intent_tags'] ?? [],
            $context['tags'] ?? []
        )));

        $reply['resolved_message'] = $context['message'] ?? null;

        if (empty($reply['suggestions'])) {
            $reply['suggestions'] = $this->buildFollowUpAwareSuggestions($context);
        }

        return $reply;
    }

    private function buildFaqReply(string $intent, array $tags = []): ?array
    {
        $aboutUrl = route('home.about', ['locale' => app()->getLocale()]);
        $faqUrl = route('home.faq', ['locale' => app()->getLocale()]);
        $paymentUrl = route('home.payment-policy', ['locale' => app()->getLocale()]);
        $refundUrl = route('home.refund-policy', ['locale' => app()->getLocale()]);
        $privacyUrl = route('home.privacy-policy', ['locale' => app()->getLocale()]);
        $supportUrl = route('home.student-support', ['locale' => app()->getLocale()]);

        if (Str::contains($intent, ['ban la ai', 'website ve gi', 'website la gi', 'bigk udemy'])) {
            return $this->baseResponse(
                "Mình là trợ lý bán hàng của BigK Udemy. Website này tập trung vào các khóa học kỹ năng, công nghệ và lộ trình học thực tế. Bạn có thể xem thêm tại: {$aboutUrl}",
                ['intent_tags' => $tags]
            );
        }

        if (Str::contains($intent, [
            'phuong thuc thanh toan',
            'hinh thuc thanh toan',
            'thanh toan',
            'payment',
            'vnpay',
            'momo',
            'chuyen khoan',

            // 👉 thêm cái này
            'chinh sach thanh toan',
            'payment policy',
        ])) {
            return $this->baseResponse(
                "Bạn có thể xem chi tiết chính sách thanh toán tại đây: {$paymentUrl}",
                ['intent_tags' => $tags]
            );
        }

        if (Str::contains($intent, ['bao mat', 'privacy', 'thong tin ca nhan', 'du lieu ca nhan'])) {
            return $this->baseResponse(
                "Website có chia sẻ các thông tin cần thiết liên quan đến bảo mật và dữ liệu cá nhân của người dùng. Bạn có thể xem chính sách bảo mật tại: {$privacyUrl}",
                ['intent_tags' => $tags]
            );
        }

        $isRefundIntent =
            Str::contains($intent, [
                'hoan tien',
                'refund',
                'huy don',
                'huy order',
                'cancel',
                'huy hoan',
                'huy/hoan',
            ]) || (
                Str::contains($intent, ['chinh sach'])
                && Str::contains($intent, ['huy', 'hoan'])
            );

        if ($isRefundIntent) {
            return $this->baseResponse(
                "Chính sách hủy đơn và hoàn tiền được mô tả công khai trên website. Bạn xem chi tiết tại: {$refundUrl}",
                ['intent_tags' => $tags]
            );
        }

        if (Str::contains($intent, ['cau hoi thuong gap', 'faq', 'thuong gap'])) {
            return $this->baseResponse(
                "Bạn có thể xem trang câu hỏi thường gặp tại: {$faqUrl}. Nếu cần hỗ trợ thêm, bạn xem tại: {$supportUrl}",
                ['intent_tags' => $tags]
            );
        }

        return null;
    }

    private function buildCourseReply(string $normalized): array
    {
        $filters = $this->extractCourseFilters($normalized);
        $intent = $this->normalizeForIntent($normalized);

        if (!empty($filters['free_only'])) {
            return $this->baseResponse(
                'Mình thấy một số khóa học miễn phí hoặc có giá 0đ phù hợp để bạn bắt đầu nhanh. Bạn xem danh sách bên dưới nhé.',
                [
                    'courses' => $this->searchCourses('mien phi free 0d')->values()->all(),
                    'intent_tags' => $filters['lead_tags'] ?? [],
                ]
            );
        }

        if (Str::contains($intent, ['noi bat', 'pho bien', 'hot']) && empty($filters['keywords'])) {
            return $this->baseResponse(
                'Đây là một số khóa học nổi bật đang được nhiều người học quan tâm trên website.',
                [
                    'courses' => $this->latestCourses('popular')->values()->all(),
                    'intent_tags' => $filters['lead_tags'] ?? [],
                ]
            );
        }

        if (Str::contains($intent, ['moi nhat', 'newest']) && empty($filters['keywords'])) {
            return $this->baseResponse(
                'Mình gửi bạn một số khóa học mới cập nhật gần đây để bạn tham khảo.',
                [
                    'courses' => $this->latestCourses('newest')->values()->all(),
                    'intent_tags' => $filters['lead_tags'] ?? [],
                ]
            );
        }

        $searchText = !empty($filters['keywords'])
            ? implode(' ', $filters['keywords'])
            : $normalized;

        $courses = $this->searchCourses($searchText);

        // 🔥 fallback 1: bỏ bớt filter, chỉ giữ keyword chính
        if ($courses->isEmpty() && !empty($filters['keywords'])) {
            $courses = $this->searchCourses(implode(' ', $filters['keywords']));
        }

        // 🔥 fallback 2: chỉ giữ goal (backend / frontend)
        if ($courses->isEmpty() && !empty($filters['goal'])) {
            if ($filters['goal'] === 'đi làm backend') {
                $courses = $this->searchCourses('backend php laravel');
            } elseif ($filters['goal'] === 'đi làm frontend') {
                $courses = $this->searchCourses('frontend react javascript');
            }
        }

        // 🔥 fallback 3: bỏ hết → lấy khóa phổ biến
        if ($courses->isEmpty()) {
            $courses = $this->latestCourses('popular');
        }

        if ($courses->isEmpty()) {
            return $this->baseResponse(
                $this->buildCourseAnswer([], $filters, true),
                ['courses' => [], 'intent_tags' => $filters['lead_tags'] ?? []]
            );
        }

        return $this->baseResponse(
            $this->buildCourseAnswer($courses->all(), $filters),
            [
                'courses' => $courses->values()->all(),
                'intent_tags' => $filters['lead_tags'] ?? [],
                'suggestions' => [
                    'Có khóa nào rẻ hơn không?',
                    'Cho mình loại cho người mới',
                    'Còn khóa nào khác không?',
                ],
            ]
        );
    }

    private function buildFreeCoursesReply(array $filters = []): array
    {
        $courses = $this->searchCourses('miễn phí');

        if ($courses->isEmpty()) {
            return $this->baseResponse(
                'Hiện mình chưa thấy khóa học miễn phí phù hợp trong dữ liệu công khai trên website.',
                ['intent_tags' => $filters['lead_tags'] ?? []]
            );
        }

        return $this->baseResponse(
            'Mình thấy một số khóa học miễn phí hoặc đang có giá 0đ phù hợp để bạn bắt đầu nhanh. Bạn xem danh sách bên dưới nhé.',
            ['courses' => $courses->values()->all(), 'intent_tags' => $filters['lead_tags'] ?? []]
        );
    }

    private function buildPopularCoursesReply(array $filters = []): array
    {
        $courses = $this->latestCourses('popular');

        return $this->baseResponse(
            'Đây là một số khóa học nổi bật đang được nhiều người học quan tâm trên website.',
            ['courses' => $courses->values()->all(), 'intent_tags' => $filters['lead_tags'] ?? []]
        );
    }

    private function buildNewestCoursesReply(array $filters = []): array
    {
        $courses = $this->latestCourses('newest');

        return $this->baseResponse(
            'Mình gửi bạn một số khóa học mới cập nhật gần đây để bạn tham khảo.',
            ['courses' => $courses->values()->all(), 'intent_tags' => $filters['lead_tags'] ?? []]
        );
    }

    private function buildCouponReply(string $normalized): array
    {
        $signals = $this->inferSignals($normalized);
        $terms = $this->extractSearchTerms($normalized);

        $isGenericCouponQuestion = empty($terms)
            || $this->containsAny($this->normalizeForIntent($normalized), [
                'ma giam gia',
                'coupon',
                'discount',
                'khuyen mai',
                'uu dai',
                'ma khuyen mai',
            ]);

        $coupons = $isGenericCouponQuestion
            ? $this->getActiveCoupons()
            : $this->searchCoupons($normalized);

        if ($coupons->isEmpty()) {
            return $this->baseResponse(
                'Hiện mình chưa thấy mã khuyến mãi công khai nào đang còn hiệu lực. Bạn có thể xem thêm các khóa học đang giảm giá hoặc hỏi mình theo tên khóa học để mình kiểm tra ưu đãi sát hơn.',
                ['intent_tags' => $signals['tags']]
            );
        }

        return $this->baseResponse(
            'Mình thấy một số mã khuyến mãi còn hiệu lực trong dữ liệu công khai trên website. Bạn xem bên dưới nhé.',
            ['coupons' => $coupons->values()->all(), 'intent_tags' => $signals['tags']]
        );
    }

    private function buildSupportReply(): array
    {
        $hotline = setting('hotline', '0123456789');
        $email = setting('email', 'bigkudemy@gmail.com');
        $facebook = setting('facebook', 'https://www.facebook.com/');

        return $this->baseResponse(
            "Nếu cần hỗ trợ thêm, bạn có thể liên hệ qua các kênh chính thức sau:\n- Hotline: {$hotline}\n- Email: {$email}\n- Facebook: {$facebook}\nBạn cũng có thể hỏi mình về khóa học phù hợp hoặc mã giảm giá đang áp dụng trước khi mua.",
            [
                'suggestions' => [
                    'Khóa nào phù hợp cho người mới?',
                    'Cho mình xem khóa học nổi bật',
                    'Hiện có mã giảm giá nào không?',
                    'Khóa nào đang giảm giá?',
                ]
            ]
        );
    }

    private function searchCourses(string $normalized): Collection
    {
        $intent = $this->normalizeForIntent($normalized);
        $filters = $this->extractCourseFilters($normalized);
        $query = Courses::query()->with(['teacher', 'categories'])->withCount('students');

        if (!empty($filters['category_ids'])) {
            $query->whereHas('categories', function ($subQuery) use ($filters) {
                $subQuery->whereIn('categories.id', $filters['category_ids']);
            });
        }

        if (!empty($filters['keywords'])) {
            foreach ($filters['keywords'] as $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $likeKeyword = '%' . $keyword . '%';
                    $subQuery->where('name', 'like', $likeKeyword)
                        ->orWhere('name_en', 'like', $likeKeyword)
                        ->orWhere('name_ko', 'like', $likeKeyword)
                        ->orWhere('name_ja', 'like', $likeKeyword)
                        ->orWhere('name_zh', 'like', $likeKeyword)
                        ->orWhere('detail', 'like', $likeKeyword)
                        ->orWhere('detail_en', 'like', $likeKeyword)
                        ->orWhere('detail_ko', 'like', $likeKeyword)
                        ->orWhere('detail_ja', 'like', $likeKeyword)
                        ->orWhere('detail_zh', 'like', $likeKeyword)
                        ->orWhereHas('categories', function ($categoryQuery) use ($likeKeyword) {
                            $categoryQuery->where('name', 'like', $likeKeyword)
                                ->orWhere('name_en', 'like', $likeKeyword)
                                ->orWhere('name_ko', 'like', $likeKeyword)
                                ->orWhere('name_ja', 'like', $likeKeyword)
                                ->orWhere('name_zh', 'like', $likeKeyword);
                        });
                });
            }
        }

        if (($filters['min_price'] ?? null) !== null) {
            $query->whereRaw($this->effectivePriceExpression() . ' >= ?', [$filters['min_price']]);
        }

        if (($filters['max_price'] ?? null) !== null) {
            $query->whereRaw($this->effectivePriceExpression() . ' <= ?', [$filters['max_price']]);
        }

        if (!empty($filters['discount_only'])) {
            $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
        }

        if (!empty($filters['free_only'])) {
            $query->whereRaw($this->effectivePriceExpression() . ' = 0');
        }

        if (!empty($filters['newbie_only'])) {
            $query->where(function ($subQuery) {
                $subQuery->where('name', 'like', '%cơ bản%')
                    ->orWhere('name', 'like', '%cho người mới%')
                    ->orWhere('name', 'like', '%mất gốc%')
                    ->orWhere('name', 'like', '%từ số 0%')
                    ->orWhere('detail', 'like', '%người mới%')
                    ->orWhere('detail', 'like', '%cơ bản%')
                    ->orWhere('detail', 'like', '%mất gốc%')
                    ->orWhere('detail', 'like', '%từ số 0%');
            });
        }

        if (!empty($filters['sort_cheapest']) || Str::contains($intent, ['re nhat', 'gia thap', 'gia re', 'thap nhat', 'cheap'])) {
            $query->whereRaw($this->effectivePriceExpression() . ' < ?', [self::CHEAP_PRICE_THRESHOLD])
                ->orderByRaw($this->effectivePriceExpression() . ' asc')->orderByDesc('view');
        } elseif (Str::contains($intent, ['moi nhat', 'newest'])) {
            $query->latest('id');
        } elseif (Str::contains($intent, ['noi bat', 'pho bien', 'hot', 'di lam', 'xin viec'])) {
            $query->orderByDesc('view')->orderByDesc('students_count');
        } else {
            $query->orderByDesc('view')->orderByDesc('id');
        }

        return $this->formatCourses($query->limit(self::RESULT_LIMIT)->get());
    }

    private function latestCourses(string $type = 'newest'): Collection
    {
        $query = Courses::query()->with(['teacher', 'categories'])->withCount('students');

        if ($type === 'popular') {
            $query->orderByDesc('view')->orderByDesc('students_count');
        } else {
            $query->latest('id');
        }

        return $this->formatCourses($query->limit(self::RESULT_LIMIT)->get());
    }

    private function searchCoupons(string $normalized): Collection
    {
        $terms = $this->extractSearchTerms($normalized);
        $query = Coupons::query()->active()->with(['courses.categories']);

        if (!empty($terms)) {
            $query->where(function ($subQuery) use ($terms) {
                foreach ($terms as $term) {
                    $likeTerm = '%' . $term . '%';
                    $subQuery->orWhere('code', 'like', $likeTerm)
                        ->orWhereHas('courses', function ($courseQuery) use ($likeTerm) {
                            $courseQuery->where('name', 'like', $likeTerm)
                                ->orWhere('name_en', 'like', $likeTerm)
                                ->orWhere('name_ko', 'like', $likeTerm)
                                ->orWhere('name_ja', 'like', $likeTerm)
                                ->orWhere('name_zh', 'like', $likeTerm);
                        });
                }
            });
        }

        return $query->latest('id')->limit(self::RESULT_LIMIT)->get()->map(function (Coupons $coupon) {
            $courseNames = $coupon->courses
                ->map(fn(Courses $course) => $this->cleanPublicText($course->name_locale))
                ->filter()
                ->values()
                ->all();

            $discountText = $coupon->discount_type === 'percent'
                ? ((float) $coupon->discount_value) . '%'
                : $this->formatMoney((float) $coupon->discount_value);

            return [
                'code' => $this->cleanPublicText($coupon->code),
                'discount_text' => $discountText,
                'total_condition' => $coupon->total_condition ? $this->formatMoney((float) $coupon->total_condition) : null,
                'end_date' => $coupon->end_date ? date('d/m/Y', strtotime((string) $coupon->end_date)) : null,
                'course_names' => $courseNames,
                'applies_to_all' => empty($courseNames),
            ];
        });
    }

    private function formatCourses(iterable $courses): Collection
    {
        return collect($courses)->map(function (Courses $course) {
            $teacher = $this->cleanPublicText(
                optional($course->teacher)->name_locale ?? optional($course->teacher)->name ?? ''
            );

            $duration = $course->durations > 0
                ? rtrim(rtrim(number_format((float) $course->durations, 2, '.', ''), '0'), '.') . ' phút'
                : '';

            $categories = $course->categories
                ->map(fn(Category $category) => $this->cleanPublicText($category->name_locale))
                ->filter()
                ->values()
                ->all();

            return [
                'name' => $this->cleanPublicText($course->name_locale),
                'teacher' => $teacher,
                'price' => $this->formatMoney((float) $course->price),
                'sale_price' => $course->sale_price && (float) $course->sale_price > 0
                    ? $this->formatMoney((float) $course->sale_price)
                    : null,
                'duration' => $duration,
                'students_count' => (int) ($course->students_count ?? 0),
                'categories' => $categories,
                'summary' => $this->resolveCourseSummary($course, $teacher, $duration, $categories),
                'slug' => $course->slug_locale,
                'url' => route('courses.detail', [
                    'locale' => app()->getLocale(),
                    'slug' => $course->slug_locale
                ]),
            ];
        })->values();
    }

    private function extractCourseFilters(string $normalized): array
    {
        $intent = $this->normalizeForIntent($normalized);
        $signals = $this->inferSignals($normalized);
        $keywords = $this->expandKeywordsByGoal(
            $this->extractSearchTerms($normalized),
            $signals['goal'],
            $intent
        );
        $categoryIds = $this->matchCategories($keywords, $normalized);
        $priceFilter = $this->extractPriceFilter($normalized);
        $sortCheapest = Str::contains($intent, ['re nhat', 'gia re', 'gia thap', 'thap nhat', 'cheap', 're']);

        return array_merge($priceFilter, [
            'keywords' => array_values(array_unique($keywords)),
            'category_ids' => $categoryIds,
            'discount_only' => Str::contains($intent, ['giam gia', 'sale', 'khuyen mai']),
            'newbie_only' => Str::contains($intent, ['nguoi moi', 'co ban', 'beginner', 'newbie', 'tu so 0', 'mat goc'])
                || in_array('quan tâm học từ số 0', $signals['tags'], true),
            'free_only' => Str::contains($intent, ['mien phi', 'free', '0d']),
            'sort_cheapest' => $sortCheapest,
            'goal' => $signals['goal'],
            'lead_tags' => $signals['tags'],
        ]);
    }

    private function matchCategories(array $keywords, string $normalized): array
    {
        $terms = array_values(array_unique(array_filter(array_merge($keywords, [$normalized]))));

        if (empty($terms)) {
            return [];
        }

        return Category::query()->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                $likeTerm = '%' . $term . '%';
                $query->orWhere('name', 'like', $likeTerm)
                    ->orWhere('name_en', 'like', $likeTerm)
                    ->orWhere('name_ko', 'like', $likeTerm)
                    ->orWhere('name_ja', 'like', $likeTerm)
                    ->orWhere('name_zh', 'like', $likeTerm);
            }
        })->limit(10)->pluck('id')->map(fn($id) => (int) $id)->all();
    }

    private function extractPriceFilter(string $normalized): array
    {
        $filter = ['min_price' => null, 'max_price' => null];

        if (preg_match('/(?:từ|tu)\s+([\d.,]+\s*(?:k|nghìn|nghin|ngàn|ngan|tr|triệu|trieu)?)\s*(?:-|đến|den)\s*([\d.,]+\s*(?:k|nghìn|nghin|ngàn|ngan|tr|triệu|trieu)?)/u', $normalized, $matches)) {
            $filter['min_price'] = $this->parseMoneyAmount($matches[1]);
            $filter['max_price'] = $this->parseMoneyAmount($matches[2]);
            return $filter;
        }

        if (preg_match('/([\d.,]+\s*(?:k|nghìn|nghin|ngàn|ngan|tr|triệu|trieu)?)\s*-\s*([\d.,]+\s*(?:k|nghìn|nghin|ngàn|ngan|tr|triệu|trieu)?)/u', $normalized, $matches)) {
            $filter['min_price'] = $this->parseMoneyAmount($matches[1]);
            $filter['max_price'] = $this->parseMoneyAmount($matches[2]);
            return $filter;
        }

        if (preg_match('/(?:tầm|tam|khoảng|khoang)\s*([\d.,]+\s*(?:k|nghìn|nghin|ngàn|ngan|tr|triệu|trieu))/u', $normalized, $matches)) {
            $center = $this->parseMoneyAmount($matches[1]);
            if ($center !== null) {
                $filter['min_price'] = $center * 0.8;
                $filter['max_price'] = $center * 1.2;
                return $filter;
            }
        }

        if (preg_match('/(?:dưới|duoi|<=|<)\s*([\d.,]+\s*(?:k|nghìn|nghin|ngàn|ngan|tr|triệu|trieu)?)/u', $normalized, $matches)) {
            $filter['max_price'] = $this->parseMoneyAmount($matches[1]);
            return $filter;
        }

        if (preg_match('/(?:trên|tren|>=|>)\s*([\d.,]+\s*(?:k|nghìn|nghin|ngàn|ngan|tr|triệu|trieu)?)/u', $normalized, $matches)) {
            $filter['min_price'] = $this->parseMoneyAmount($matches[1]);
            return $filter;
        }

        return $filter;
    }

    private function looksLikeGreeting(string $normalized): bool
    {
        return $this->containsAny($normalized, [
            'xin chào',
            'xin chao',
            'chào',
            'chao',
            'chào bạn',
            'chao ban',
            'hello',
            'hi',
            'alo',
        ]);
    }

    private function looksLikeFollowUp(string $intent): bool
    {
        return Str::contains($intent, [
            'con khoa nao',
            'con loai nao',
            'the con',
            'cai nao',
            'cai do',
            'con ma nao',
            'duoi',
            'tren',
            'tam',
            'khoang',
            're hon',
            'gia re hon',
            'gia bao nhieu',
            'loai nay',
            'loai kia',
            'cai khac',
            'khac khong',
            'con cai nao khac',
            'con khoa nao khac',
            'de hon',
            'cho nguoi moi',
            'nguoi moi thoi',
            'backend thoi',
            'frontend thoi',
            'fullstack thoi',
            'chi backend',
            'chi frontend',
            'chi fullstack',
            'thoi',
            'loai nao phu hop hon',
        ]);
    }

    private function extractMemoryContext(array $memory): ?string
    {
        return collect(array_reverse($memory))
            ->first(function ($item) {
                $normalized = $this->normalize((string) $item);
                $intent = $this->normalizeForIntent($normalized);

                return $this->containsAny($intent, [
                    'backend',
                    'frontend',
                    'fullstack',
                    'devops',
                    'php',
                    'laravel',
                    'react',
                    'javascript',
                    'node',
                    'sql',
                    'khoa hoc',
                    'course',
                    'gia',
                    'mien phi',
                    'giam gia',
                    'sale',
                    'nguoi moi',
                    'co ban',
                    'tu so 0',
                    'mat goc',
                    'thuc tap',
                    'di lam',
                ]) || $this->looksLikeFollowUp($intent);
            });
    }

    private function inferSignals(string $message, array $memory = []): array
    {
        $intent = $this->normalizeForIntent($this->normalize($message . ' ' . implode(' ', $memory)));
        $tags = [];
        $goal = null;

        if (Str::contains($intent, ['backend', 'server side'])) {
            $tags[] = 'quan tâm backend';
            $goal = $goal ?? 'đi làm backend';
        }

        if (Str::contains($intent, ['frontend', 'ui ux'])) {
            $tags[] = 'quan tâm frontend';
            $goal = $goal ?? 'đi làm frontend';
        }

        if (Str::contains($intent, ['fullstack'])) {
            $tags[] = 'quan tâm fullstack';
            $goal = $goal ?? 'đi làm fullstack';
        }

        if (Str::contains($intent, ['devops'])) {
            $tags[] = 'quan tâm devops';
        }

        if (Str::contains($intent, ['re nhat', 'gia re', 'gia thap', 'duoi', 'tam', 'khoang'])) {
            $tags[] = 'quan tâm giá rẻ';
        }

        if (Str::contains($intent, ['giam gia', 'khuyen mai', 'coupon', 'discount'])) {
            $tags[] = 'quan tâm khuyến mãi';
        }

        if (Str::contains($intent, ['nguoi moi', 'tu so 0', 'mat goc', 'co ban'])) {
            $tags[] = 'quan tâm học từ số 0';
            $goal = $goal ?? 'học từ số 0';
        }

        if (Str::contains($intent, ['thuc tap', 'intern', 'internship'])) {
            $tags[] = 'quan tâm thực tập';
            $goal = $goal ?? 'thực tập';
        }

        if (Str::contains($intent, ['di lam', 'xin viec', 'job ready'])) {
            $tags[] = 'quan tâm đi làm';
            $goal = $goal ?? 'đi làm';
        }

        return [
            'tags' => array_values(array_unique($tags)),
            'goal' => $goal,
        ];
    }

    private function expandKeywordsByGoal(array $keywords, ?string $goal, string $intent): array
    {
        $expanded = $keywords;

        if (Str::contains($intent, ['backend'])) {
            $expanded[] = 'backend';
        }

        if (Str::contains($intent, ['frontend'])) {
            $expanded[] = 'frontend';
        }

        if (Str::contains($intent, ['fullstack'])) {
            $expanded[] = 'fullstack';
        }

        if ($goal === 'đi làm backend') {
            $expanded = array_merge($expanded, ['backend', 'php', 'laravel', 'sql']);
        }

        if ($goal === 'đi làm frontend') {
            $expanded = array_merge($expanded, ['frontend', 'react', 'javascript']);
        }

        if ($goal === 'đi làm fullstack') {
            $expanded = array_merge($expanded, ['fullstack', 'backend', 'frontend']);
        }

        return array_values(array_unique(array_filter($expanded)));
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && Str::contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $message): string
    {
        $value = trim(mb_strtolower($message));
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        $replacements = [
            '/\bko\b/u' => 'không',
            '/\bk0\b/u' => 'không',
            '/\bhok\b/u' => 'không',
            '/\bhk\b/u' => 'không',
            '/\bdc\b/u' => 'được',
            '/\bmk\b/u' => 'mình',
            '/\bmn\b/u' => 'mọi người',
            '/\bbn\b/u' => 'bạn',
            '/\bkm\b/u' => 'khuyến mãi',
            '/\bntn\b/u' => 'như thế nào',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value) ?? $value;
        }

        return $this->normalizeCategoryAliases(trim($value));
    }

    private function normalizeForIntent(string $value): string
    {
        $value = Str::ascii(mb_strtolower($value));
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = $this->normalizeCategoryAliases($value);

        return trim($value);
    }

    private function normalizeCategoryAliases(string $value): string
    {
        $replacements = [
            '/\bbe\b/u' => 'backend',
            '/\bfe\b/u' => 'frontend',
            '/\bback[\s\-]*end\b/u' => 'backend',
            '/\bfront[\s\-]*end\b/u' => 'frontend',
            '/\bfull[\s\-]*stack\b/u' => 'fullstack',
            '/\bdev[\s\-]*ops\b/u' => 'devops',
            '/\bui[\s\/\-]*ux\b/u' => 'ui ux',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value) ?? $value;
        }

        return $value;
    }

    private function parseMoneyAmount(string $value): ?float
    {
        $raw = mb_strtolower(trim($value));

        if ($raw === '') {
            return null;
        }

        $multiplier = 1;

        if (Str::contains($raw, ['triệu', 'trieu', 'tr', 'm'])) {
            $multiplier = 1000000;
        } elseif (Str::contains($raw, ['nghìn', 'nghin', 'ngàn', 'ngan', 'k', 'm'])) {
            $multiplier = 1000;
        }

        $number = preg_replace('/[^\d.,]/u', '', $raw) ?? '';

        // Nếu có cả . và , thì assume . là thousand, , là decimal
        if (str_contains($number, '.') && str_contains($number, ',')) {
            $number = str_replace('.', '', $number);
            $number = str_replace(',', '.', $number);
        } else {
            // chỉ có 1 loại dấu → xử lý linh hoạt
            $number = str_replace(',', '.', $number);
        }

        if ($number === '' || !is_numeric($number)) {
            return null;
        }

        return (float) $number * $multiplier;
    }

    private function buildCourseAnswer(array $courses, array $filters, bool $strictIntent = false): string
    {
        if (empty($courses)) {
            $descriptions = [];

            if (!empty($filters['goal'])) {
                $descriptions[] = 'mục tiêu ' . $filters['goal'];
            }

            if (!empty($filters['keywords'])) {
                $descriptions[] = 'từ khóa "' . implode(', ', $filters['keywords']) . '"';
            }

            if (($filters['min_price'] ?? null) !== null || ($filters['max_price'] ?? null) !== null) {
                if (($filters['min_price'] ?? null) !== null && ($filters['max_price'] ?? null) !== null) {
                    $descriptions[] = 'mức giá từ ' . $this->formatMoney((float) $filters['min_price']) . ' đến ' . $this->formatMoney((float) $filters['max_price']);
                } elseif (($filters['min_price'] ?? null) !== null) {
                    $descriptions[] = 'mức giá từ ' . $this->formatMoney((float) $filters['min_price']) . ' trở lên';
                } else {
                    $descriptions[] = 'mức giá dưới ' . $this->formatMoney((float) $filters['max_price']);
                }
            }

            if (!empty($filters['sort_cheapest'])) {
                $descriptions[] = 'nhóm khóa học giá thấp';
            }

            if (!empty($filters['discount_only'])) {
                $descriptions[] = 'đang giảm giá';
            }

            if (!empty($filters['newbie_only'])) {
                $descriptions[] = 'cho người mới';
            }

            $scope = empty($descriptions)
                ? 'trong dữ liệu công khai trên website'
                : implode(', ', $descriptions) . ' từ dữ liệu công khai trên website';

            if ($strictIntent) {
                return 'Mình chưa thấy khóa học phù hợp với ' . $scope . '. Bạn có thể đổi từ khóa, danh mục hoặc mức giá để mình lọc lại giúp bạn.';
            }

            return 'Mình chưa tìm thấy khóa học phù hợp với ' . $scope . '. Bạn có thể hỏi theo tên khóa học, danh mục hoặc khoảng giá để mình gợi ý chính xác hơn.';
        }

        if (!empty($filters['free_only'])) {
            return 'Mình thấy một số khóa học miễn phí với giá 0đ trong dữ liệu công khai trên website. Bạn xem thử các gợi ý bên dưới nhé.';
        }

        if (!empty($filters['sort_cheapest'])) {
            return 'Mình đang ưu tiên các khóa học giá rẻ dưới 500.000đ và sắp xếp từ thấp lên cao để bạn dễ chọn.';
        }

        if (!empty($filters['newbie_only'])) {
            return 'Mình thấy một vài khóa học phù hợp cho người mới bắt đầu. Bạn xem thử các gợi ý bên dưới nhé.';
        }

        if (!empty($filters['discount_only'])) {
            return 'Mình thấy một số khóa học đang giảm giá phù hợp với nhu cầu bạn hỏi. Bạn xem thử các gợi ý bên dưới nhé.';
        }

        if (!empty($filters['keywords'])) {
            return 'Mình thấy một vài khóa học khá sát với từ khóa "' . implode(', ', $filters['keywords']) . '" từ dữ liệu công khai trên website. Bạn xem thử các gợi ý này nhé.';
        }

        return 'Mình thấy một vài khóa học phù hợp từ dữ liệu công khai trên website. Bạn xem thử các gợi ý bên dưới nhé.';
    }

    private function extractSearchTerms(string $normalized): array
    {
        $stopWords = [
            'khoa',
            'hoc',
            'khóa',
            'học',
            'gia',
            'giá',
            're',
            'rẻ',
            'nhat',
            'nhất',
            'mien',
            'miễn',
            'phi',
            'phí',
            'sale',
            'giam',
            'giảm',
            'khuyen',
            'khuyến',
            'mai',
            'danh',
            'muc',
            'mục',
            'theo',
            'cho',
            'minh',
            'mình',
            'la',
            'là',
            'gi',
            'gì',
            'nao',
            'nào',
            'phu',
            'phù',
            'hop',
            'hợp',
            'duoi',
            'dưới',
            'tren',
            'trên',
            'tu',
            'từ',
            'den',
            'đến',
            'tam',
            'tầm',
            'khoang',
            'khoảng',
            'be',
            'fe',
            'xin',
            'chao',
            'hello',
            'hi',
            'alo',
            'liên',
            'hệ',
            'kiểu',
            'admin',
            'hotline',
            'facebook',
            'email',
            'support',
            'hỗ',
            'trợ',
            'co',
            'có',
            'khong',
            'không',
            'muon',
            'muốn',
            'nen',
            'nên',
            'thi',
            'thì',
            'con',
            'còn',
            'khac',
            'khác',
            'hon',
            'hơn',
            'thoi',
            'thôi',
            'loai',
            'loại',
            'de',
            'dễ',
        ];

        $terms = preg_split('/[^[:alnum:]\pL]+/u', $normalized) ?: [];

        return collect($terms)
            ->map(fn($term) => trim($term))
            ->filter(fn($term) => $term !== '' && mb_strlen($term) >= 2)
            ->reject(fn($term) => preg_match('/^\d+(?:[.,]\d+)?(?:k|tr|nghin|ngan|trieu)?$/u', $term) === 1)
            ->reject(fn($term) => in_array($term, $stopWords, true))
            ->take(8)
            ->values()
            ->all();
    }
    private function cleanPublicText(?string $value): string
    {
        if (!is_string($value) || trim($value) === '') {
            return '';
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function resolveCourseSummary(Courses $course, string $teacher, string $duration, array $categories): string
    {
        $summary = Str::limit($this->cleanPublicText($course->detail_locale), 160);

        if ($summary !== '' && !$this->looksCorruptedText($summary)) {
            return $summary;
        }

        return $this->buildFallbackSummary($this->cleanPublicText($course->name_locale), $teacher, $duration, $categories);
    }

    private function buildFallbackSummary(string $courseName, string $teacher, string $duration, array $categories): string
    {
        $parts = [];

        if ($courseName !== '') {
            $parts[] = 'Khóa học ' . $courseName . ' đang có thông tin công khai trên website.';
        }

        if (!empty($categories)) {
            $parts[] = 'Danh mục: ' . implode(', ', array_slice($categories, 0, 2)) . '.';
        }

        if ($teacher !== '') {
            $parts[] = 'Giảng viên: ' . $teacher . '.';
        }

        if ($duration !== '') {
            $parts[] = 'Thời lượng: ' . $duration . '.';
        }

        return Str::limit(implode(' ', $parts), 160);
    }

    private function looksCorruptedText(string $value): bool
    {
        return Str::contains($value, ['Ã', 'Â', 'Æ', 'â€', '�']);
    }

    private function repairMojibake(string $value): string
    {
        if (!mb_check_encoding($value, 'UTF-8')) {
            return mb_convert_encoding($value, 'UTF-8', 'auto');
        }

        return $value;
    }

    private function normalizeResponseValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->repairMojibake($value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalizeResponseValue($item);
            }
        }

        return $value;
    }

    private function baseResponse(string $answer, array $payload = []): array
    {
        return $this->normalizeResponseValue(array_merge([
            'answer' => $answer,
            'courses' => [],
            'coupons' => [],
            'suggestions' => [
                'Khóa rẻ nhất hiện nay là gì?',
                'Muốn đi làm backend thì nên học khóa nào?',
                'Có khóa nào phù hợp để thực tập không?',
                'Muốn học từ số 0 thì bắt đầu từ đâu?',
                'Hiện có mã giảm giá nào không?',
            ],
            'intent_tags' => [],
            'resolved_message' => null,
        ], $payload));
    }

    private function effectivePriceExpression(): string
    {
        return 'CASE 
                WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price 
                    THEN sale_price 
                ELSE price 
            END';
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 0, ',', '.') . ' đ';
    }


    private function isSupportIntent(string $normalized, string $intent): bool
    {
        return $this->containsAny($normalized, $this->supportKeywords)
            && !$this->containsAny($intent, [
                'khoa hoc',
                'course',
                'gia',
                'gia re',
                'duoi',
                'tren',
                'mien phi',
                'giam gia',
                'sale',
                'coupon',
            ]);
    }

    private function isAdviceIntent(string $normalized, string $intent): bool
    {
        return $this->containsAny($normalized, $this->adviceKeywords)
            || Str::contains($intent, [
                'di lam nen hoc gi',
                'hoc gi truoc',
                'bat dau tu dau',
                'phu hop nhat',
            ]);
    }

    private function isComparisonIntent(string $intent): bool
    {
        return $this->containsAny($intent, $this->comparisonKeywords)
            || preg_match('/\b(laravel|php|react|frontend|backend|fullstack|node|sql)\b.*\b(hay|voi|so voi)\b.*\b(laravel|php|react|frontend|backend|fullstack|node|sql)\b/u', $intent) === 1;
    }

    private function buildAdviceReply(string $normalized): array
    {
        $filters = $this->extractCourseFilters($normalized);

        $searchTerms = $filters['keywords'] ?? [];
        $searchText = !empty($searchTerms)
            ? implode(' ', $searchTerms)
            : $normalized;

        $courses = $this->searchCourses($searchText);

        if ($courses->isEmpty() && !empty($filters['goal'])) {
            if ($filters['goal'] === 'đi làm backend') {
                $courses = $this->searchCourses('backend php laravel sql');
            } elseif ($filters['goal'] === 'đi làm frontend') {
                $courses = $this->searchCourses('frontend react javascript html css');
            } elseif ($filters['goal'] === 'đi làm fullstack') {
                $courses = $this->searchCourses('fullstack backend frontend php laravel react javascript');
            }
        }

        if ($courses->isEmpty()) {
            return $this->baseResponse(
                'Mình chưa thấy khóa học thật sự phù hợp với nhu cầu bạn vừa mô tả. Bạn hãy nói rõ hơn bạn muốn học backend, frontend, fullstack, cho người mới hay theo ngân sách để mình gợi ý sát hơn.',
                ['intent_tags' => $filters['lead_tags'] ?? []]
            );
        }

        return $this->baseResponse(
            'Với mục tiêu đi làm, mình ưu tiên các khóa có nền tảng cốt lõi và tính ứng dụng thực tế để bạn bắt đầu dễ hơn. Bạn xem thử các gợi ý bên dưới nhé.',
            [
                'courses' => $courses->values()->all(),
                'intent_tags' => $filters['lead_tags'] ?? [],
            ]
        );
    }

    private function buildComparisonReply(string $normalized): array
    {
        $intent = $this->normalizeForIntent($normalized);

        if (Str::contains($intent, ['laravel hay react', 'react hay laravel'])) {
            return $this->baseResponse(
                'Nếu bạn muốn đi theo backend thì nên học Laravel trước. Nếu bạn thích giao diện và web phía người dùng thì nên học React trước. Nếu chưa biết chọn gì, bạn có thể nói mục tiêu là đi làm backend hay frontend để mình gợi ý sát hơn.',
                ['intent_tags' => ['quan tâm so sánh công nghệ']]
            );
        }

        if (Str::contains($intent, ['backend hay frontend', 'frontend hay backend'])) {
            return $this->baseResponse(
                'Nếu bạn thích xử lý logic, dữ liệu và hệ thống thì nên chọn backend. Nếu bạn thích giao diện, trải nghiệm người dùng và phần hiển thị web thì nên chọn frontend. Bạn nói mình mục tiêu đi làm gì, mình sẽ gợi ý khóa phù hợp hơn.',
                ['intent_tags' => ['quan tâm định hướng học']]
            );
        }

        if (Str::contains($intent, ['php hay node', 'node hay php'])) {
            return $this->baseResponse(
                'PHP phù hợp nếu bạn muốn học theo hướng web backend phổ biến và dễ tiếp cận với Laravel. Node phù hợp nếu bạn thích JavaScript và muốn dùng một ngôn ngữ cho cả frontend lẫn backend. Bạn muốn mình gợi ý khóa theo hướng nào thì nói mình nhé.',
                ['intent_tags' => ['quan tâm so sánh công nghệ']]
            );
        }

        return $this->baseResponse(
            'Mình có thể giúp bạn so sánh theo mục tiêu học, mức độ dễ học và hướng đi nghề nghiệp. Bạn nói rõ 2 lựa chọn muốn so sánh, mình sẽ gợi ý ngắn gọn cho bạn.',
            ['intent_tags' => ['quan tâm so sánh']]
        );
    }

    private function getActiveCoupons(): Collection
    {
        return Coupons::query()
            ->active()
            ->with(['courses.categories'])
            ->latest('id')
            ->limit(self::RESULT_LIMIT)
            ->get()
            ->map(function (Coupons $coupon) {
                $courseNames = $coupon->courses
                    ->map(fn(Courses $course) => $this->cleanPublicText($course->name_locale))
                    ->filter()
                    ->values()
                    ->all();

                $discountText = $coupon->discount_type === 'percent'
                    ? ((float) $coupon->discount_value) . '%'
                    : $this->formatMoney((float) $coupon->discount_value);

                return [
                    'code' => $this->cleanPublicText($coupon->code),
                    'discount_text' => $discountText,
                    'total_condition' => $coupon->total_condition
                        ? $this->formatMoney((float) $coupon->total_condition)
                        : null,
                    'end_date' => $coupon->end_date
                        ? date('d/m/Y', strtotime((string) $coupon->end_date))
                        : null,
                    'course_names' => $courseNames,
                    'applies_to_all' => empty($courseNames),
                ];
            });
    }

    private function mergeFollowUpMessageWithMemory(string $message, ?string $memoryContext): string
    {
        $message = trim($message);

        if ($memoryContext === null || $memoryContext === '') {
            return $message;
        }

        $normalizedIntent = $this->normalizeForIntent($this->normalize($message));

        if (!$this->looksLikeFollowUp($normalizedIntent)) {
            return $message;
        }

        return trim($memoryContext . ' | ' . $message);
    }

    private function buildFollowUpAwareSuggestions(array $context = []): array
    {
        $tags = $context['tags'] ?? [];
        $message = $this->normalizeForIntent($this->normalize((string) ($context['message'] ?? '')));

        if (in_array('quan tâm backend', $tags, true)) {
            return [
                'Có khóa backend nào rẻ hơn không?',
                'Cho mình khóa backend cho người mới',
                'Có khóa backend nào để đi thực tập không?',
            ];
        }

        if (in_array('quan tâm frontend', $tags, true)) {
            return [
                'Có khóa frontend nào rẻ hơn không?',
                'Cho mình khóa frontend cho người mới',
                'Nên học React hay JavaScript trước?',
            ];
        }

        if (Str::contains($message, ['giam gia', 'coupon', 'khuyen mai'])) {
            return [
                'Có mã giảm giá nào còn hiệu lực không?',
                'Khóa nào đang giảm giá?',
                'Có ưu đãi cho khóa PHP không?',
            ];
        }

        return [
            'Có khóa nào rẻ hơn không?',
            'Cho mình loại cho người mới',
            'Còn khóa nào khác không?',
        ];
    }

    private function isThanksIntent(string $normalized): bool
    {
        return $this->containsAny($normalized, $this->thanksKeywords);
    }

    private function isGoodbyeIntent(string $normalized): bool
    {
        return $this->containsAny($normalized, $this->goodbyeKeywords);
    }

    private function buildThanksReply(): array
    {
        return $this->baseResponse(
            'Không có gì đâu 😊 Nếu bạn cần mình gợi ý thêm khóa học hoặc ưu đãi phù hợp thì cứ hỏi mình nhé.',
            [
                'suggestions' => [
                    'Có khóa nào rẻ hơn không?',
                    'Cho mình khóa cho người mới',
                    'Hiện có mã giảm giá nào không?',
                ]
            ]
        );
    }

    private function buildGoodbyeReply(): array
    {
        return $this->baseResponse(
            'Cảm ơn bạn đã ghé BigK Udemy 🙌 Khi nào cần tìm khóa học hoặc ưu đãi thì quay lại hỏi mình nhé!',
            [
                'suggestions' => [
                    'Xem khóa backend',
                    'Xem khóa frontend',
                    'Xem mã giảm giá',
                ]
            ]
        );
    }
}
