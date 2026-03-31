@php
    $student = auth('students')->user();

    $chatbotConfig = [
        'endpoint' => route('home.sales-chatbot', ['locale' => app()->getLocale()]),
        'supportUrl' => route('home.student-support', ['locale' => app()->getLocale()]),
        'csrfToken' => csrf_token(),
        'storageKey' => 'bigk-sales-chatbot:v6:' . app()->getLocale(),
        'leadEndpoint' => route('home.sales-chatbot.lead', ['locale' => app()->getLocale()]),
        'messageTtlMinutes' => (int) (setting('chatbot_message_ttl_minutes', env('CHATBOT_MESSAGE_TTL_MINUTES', 0))),
        'title' => 'Trợ lý bán hàng',
        'subtitle' => 'Tư vấn khóa học và ưu đãi',
        'welcome' => 'Mình có thể giúp bạn tìm khóa học phù hợp, xem mã giảm giá đang còn hiệu lực và chỉ cách liên hệ hỗ trợ khi admin chưa phản hồi.',
        'placeholder' => 'Nhập câu hỏi của bạn...',
        'send' => 'Gửi',
        'typing' => 'Bot đang trả lời...',
        'supportLabel' => 'Trang hỗ trợ',
        'openLabel' => 'Mở trợ lý',
        'closeLabel' => 'Đóng trợ lý',
        'starterQuestions' => [
            'Hiện có mã giảm giá nào không?',
            'Các phương thức thanh toán là gì?',
            'Chính sách hủy/hoàn ở đâu?',
            'Câu hỏi thường gặp ở đâu?',
        ],
        'student' => $student ? [
            'name' => $student->name,
            'email' => $student->email,
            'phone' => $student->phone,
        ] : null,
    ];
@endphp

<div class="sales-chatbot" data-sales-chatbot='@json($chatbotConfig)'>
    <button
        class="sales-chatbot__toggle"
        type="button"
        aria-expanded="false"
        aria-label="{{ $chatbotConfig['openLabel'] }}">
        <span class="sales-chatbot__toggle-icon"><i class="fa-solid fa-headset"></i></span>
        <span class="sales-chatbot__toggle-copy">
            <strong>Tư vấn nhanh</strong>
            <small>Mở trợ lý bán hàng</small>
        </span>
        <span class="sales-chatbot__toggle-arrow"><i class="fa-solid fa-chevron-up"></i></span>
    </button>

    <section
        class="sales-chatbot__panel"
        hidden
        aria-hidden="true">
        <header class="sales-chatbot__header">
            <div class="sales-chatbot__header-copy">
                <p class="sales-chatbot__eyebrow">BigK Udemy</p>
                <h3 class="sales-chatbot__title">Trợ lý bán hàng</h3>
                <p class="sales-chatbot__subtitle">Tư vấn khóa học, ưu đãi và hướng dẫn liên hệ</p>
            </div>

            <div class="sales-chatbot__header-actions">
                <a class="sales-chatbot__support-link" href="{{ route('home.student-support', ['locale' => app()->getLocale()]) }}">
                    Trang hỗ trợ
                </a>
                <button
                    class="sales-chatbot__close"
                    type="button"
                    aria-label="{{ $chatbotConfig['closeLabel'] }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </header>

        <div class="sales-chatbot__hero">
            <div class="sales-chatbot__hero-badge">
                <i class="fa-solid fa-sparkles"></i>
                Sẵn sàng tư vấn
            </div>
            <p>{{ $chatbotConfig['welcome'] }}</p>
        </div>

        <div class="sales-chatbot__messages" aria-live="polite"></div>

        <div class="sales-chatbot__starters">
            @foreach ($chatbotConfig['starterQuestions'] as $question)
                <button type="button" class="sales-chatbot__starter">{{ $question }}</button>
            @endforeach
        </div>

        <form class="sales-chatbot__form">
            <label class="visually-hidden" for="sales-chatbot-message">Câu hỏi</label>
            <textarea
                id="sales-chatbot-message"
                class="sales-chatbot__input"
                rows="1"
                maxlength="500"
                placeholder="{{ $chatbotConfig['placeholder'] }}"></textarea>
            <button class="sales-chatbot__send" type="submit">
                <i class="fa-solid fa-paper-plane"></i>
                <span>{{ $chatbotConfig['send'] }}</span>
            </button>
        </form>
    </section>
</div>
