@extends('layouts.backend')

@section('content')
    <div class="mb-3">
        <h4 class="mb-1">Sửa tri thức chatbot</h4>
        <p class="text-muted mb-0">Tinh chỉnh câu hỏi mẫu, từ khóa và câu trả lời của bot thường.</p>
    </div>

    @if (session('msg'))
        <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
    @endif

    <form action="{{ route('chatbot-knowledge.update', $knowledge->id) }}" method="POST">
        @include('home::chatbot_knowledge.form')
    </form>
@endsection
