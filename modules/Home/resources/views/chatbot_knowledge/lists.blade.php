@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions mb-4">
                <div>
                    <h5 class="mb-1">Train bot thường</h5>
                    <p class="text-muted mb-0">Quản lý các câu hỏi mẫu, từ khóa và câu trả lời mà bot thường sẽ ưu tiên dùng.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if (auth()->user()?->hasPermission('chatbot.logs'))
                        <a href="{{ route('chatbot-knowledge.unresolved') }}" class="btn btn-light border">
                            <i class="fas fa-life-ring me-2"></i>
                            Log bot không hiểu
                        </a>
                    @endif
                    @if (auth()->user()?->hasPermission('chatbot.create'))
                        <a href="{{ route('chatbot-knowledge.add') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Thêm tri thức
                        </a>
                    @endif
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif

            @if (($geminiHealth['has_issue'] ?? false) && in_array($geminiHealth['reason'] ?? '', ['quota_exceeded', 'auth_error'], true))
                <div class="alert alert-{{ ($geminiHealth['is_disabled'] ?? false) ? 'danger' : 'warning' }} border-0 rounded-4">
                    <div class="fw-semibold mb-1">Gemini gap loi {{ $geminiHealth['reason'] === 'quota_exceeded' ? 'quota' : 'xac thuc API' }}</div>
                    <div>
                        {{ ($geminiHealth['is_disabled'] ?? false) ? 'Gemini da bi tu dong tat. Bot thuong van tiep tuc tra loi.' : 'Gemini dang duoc canh bao va se tu tat neu loi lap lai them.' }}
                        Muc hien tai: {{ $geminiHealth['failure_count'] ?? 0 }}/{{ $geminiHealth['threshold'] ?? 3 }}.
                    </div>
                    @if (!empty($geminiHealth['last_at']))
                        <div class="small mt-1 text-muted">Lan loi gan nhat: {{ $geminiHealth['last_at']->format('d/m/Y H:i:s') }}</div>
                    @endif
                </div>
            @endif

            <form class="row g-3 mb-4" method="GET">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="q" value="{{ $search }}"
                        placeholder="Tìm theo câu hỏi, từ khóa, câu trả lời">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="all" @selected($status === 'all')>Tất cả</option>
                        <option value="active" @selected($status === 'active')>Đang bật</option>
                        <option value="inactive" @selected($status === 'inactive')>Đang tắt</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-primary" type="submit">Lọc</button>
                    <a href="{{ route('chatbot-knowledge.index') }}" class="btn btn-light border">Đặt lại</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Câu hỏi mẫu</th>
                            <th>Từ khóa</th>
                            <th>Ưu tiên</th>
                            <th>Trạng thái</th>
                            <th>Đã gỡ log</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($knowledgeItems as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->question }}</div>
                                    <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($item->answer), 140) }}</div>
                                </td>
                                <td>
                                    <div class="small text-muted">{{ $item->keywords ?: 'Chưa khai báo' }}</div>
                                </td>
                                <td>{{ $item->priority }}</td>
                                <td>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item->is_active ? 'Đang bật' : 'Đang tắt' }}
                                    </span>
                                </td>
                                <td>{{ $item->unresolved_questions_count }}</td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        @if (auth()->user()?->hasPermission('chatbot.edit'))
                                            <a href="{{ route('chatbot-knowledge.edit', $item->id) }}"
                                                class="btn btn-sm btn-warning">Sửa</a>
                                        @endif
                                        @if (auth()->user()?->hasPermission('chatbot.delete'))
                                            <form action="{{ route('chatbot-knowledge.delete', $item->id) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa tri thức này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chưa có tri thức chatbot nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $knowledgeItems->links() }}
        </div>
    </div>
@endsection
