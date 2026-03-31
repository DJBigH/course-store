@extends('layouts.backend')

@section('content')
    <div class="mb-3">
        <h4 class="mb-1">Thêm tri thức chatbot</h4>
        <p class="text-muted mb-0">Dạy bot thường trả lời đúng hơn với câu hỏi lặp lại, FAQ và kịch bản tư vấn.</p>
    </div>

    @if (session('msg'))
        <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
    @endif

    @if ($fromLog)
        <div class="alert alert-info border-0 rounded-4">
            <div class="fw-semibold mb-1">Đang tạo tri thức từ log chưa hiểu</div>
            <div><strong>Câu hỏi:</strong> {{ $fromLog->message }}</div>
            @if ($fromLog->resolved_message)
                <div class="mt-2"><strong>Câu bot thường đã trả lời:</strong> {{ $fromLog->resolved_message }}</div>
            @endif
            @if (!empty($fromLog->intent_tags))
                <div class="mt-2"><strong>Tag gợi ý:</strong> {{ implode(', ', (array) $fromLog->intent_tags) }}</div>
            @endif
            <div class="mt-2 small text-muted">Form bên dưới đã được đổ sẵn câu hỏi, từ khóa và bản nháp câu trả lời để bạn
                sửa nhanh.</div>
        </div>
    @endif

    <form action="{{ route('chatbot-knowledge.store') }}" method="POST">
        @include('home::chatbot_knowledge.form')
    </form>
@endsection
