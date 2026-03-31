@php
    $prefill = $prefill ?? [];
@endphp

@csrf
@if (!empty($fromLog?->id))
    <input type="hidden" name="unresolved_id" value="{{ $fromLog->id }}">
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label fw-semibold">Câu hỏi mẫu</label>
                <input type="text" name="question" class="form-control @error('question') is-invalid @enderror"
                    value="{{ old('question', $knowledge->question ?? ($prefill['question'] ?? '')) }}"
                    placeholder="Ví dụ: Có khóa học Laravel cho người mới không?">
                @error('question')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Từ khóa</label>
                <textarea name="keywords" rows="3" class="form-control @error('keywords') is-invalid @enderror"
                    placeholder="Mỗi từ khóa cách nhau bằng dấu phẩy hoặc xuống dòng">{{ old('keywords', $knowledge->keywords ?? ($prefill['keywords'] ?? '')) }}</textarea>
                @error('keywords')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Nếu tạo từ log chưa hiểu, hệ thống sẽ gợi ý sẵn tag và ngữ cảnh để bạn chỉnh sửa
                    nhanh hơn.</div>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Độ ưu tiên</label>
                <input type="number" min="0" max="999" name="priority"
                    class="form-control @error('priority') is-invalid @enderror"
                    value="{{ old('priority', $knowledge->priority ?? 0) }}">
                @error('priority')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                        {{ old('is_active', isset($knowledge) ? (int) $knowledge->is_active : 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Đang bật</label>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Câu trả lời</label>
                <textarea name="answer" rows="10" class="form-control @error('answer') is-invalid @enderror"
                    placeholder="Nhập câu trả lời mà bot sẽ sử dụng">{{ old('answer', $knowledge->answer ?? ($prefill['answer'] ?? '')) }}</textarea>
                @error('answer')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i>
        Lưu tri thức
    </button>
    <a href="{{ route('chatbot-knowledge.index') }}" class="btn btn-light border">Quay lại</a>
    @if (!empty($fromLog?->id))
        <a href="{{ route('chatbot-knowledge.unresolved') }}" class="btn btn-outline-secondary">Xem log chưa hiểu</a>
    @endif
</div>
