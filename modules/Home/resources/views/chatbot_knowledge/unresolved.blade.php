@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions mb-4">
                <div>
                    <h5 class="mb-1">Log bot khong hieu</h5>
                    <p class="text-muted mb-0">Theo dõi những câu hỏi bot đang trả lời để bổ sung tri thức mới</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('chatbot-knowledge.index') }}" class="btn btn-light border">
                        <i class="fas fa-arrow-left me-2"></i>
                        Về train bot
                    </a>
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
                <div class="col-lg-3 col-md-6">
                    <input type="text" class="form-control" name="q" value="{{ $search }}"
                        placeholder="Tìm câu hỏi hoặc câu trả lời...">
                </div>
                <div class="col-lg-2 col-md-6">
                    <select name="status" class="form-select">
                        <option value="pending" @selected($status === 'pending')>Đang cho xử lý</option>
                        <option value="handled" @selected($status === 'handled')>Đã xử lý</option>
                        <option value="ignored" @selected($status === 'ignored')>Bỏ qua</option>
                        <option value="all" @selected($status === 'all')>Tất cả</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <select name="source" class="form-select">
                        <option value="all" @selected($source === 'all')>Mọi nguồn</option>
                        <option value="local" @selected($source === 'local')>Local</option>
                        <option value="local_rule_bot" @selected($source === 'local_rule_bot')>Local rule bot</option>
                        <option value="gemini" @selected($source === 'gemini')>Gemini</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <select name="min_hits" class="form-select">
                        <option value="0" @selected($minHits === 0)>Mọi tuần suất</option>
                        <option value="2" @selected($minHits === 2)>Lập từ 2 lần</option>
                        <option value="3" @selected($minHits === 3)>Lập từ 3 lần</option>
                        <option value="5" @selected($minHits === 5)>Lập từ 5 lần</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <input type="text" class="form-control" name="student" value="{{ $studentSearch }}"
                        placeholder="Lọc theo học viên và email">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-outline-primary" type="submit">Lọc</button>
                    <a href="{{ route('chatbot-knowledge.unresolved') }}" class="btn btn-light border">Đặt lại</a>
                </div>
            </form>

            <div class="vstack gap-3">
                @forelse ($logs as $log)
                    <div class="border rounded-4 p-3">
                        <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
                            <div>
                                <div class="fw-semibold">{{ $log->message }}</div>
                                <div class="text-muted small mt-1">
                                    {{ $log->last_asked_at?->format('d/m/Y H:i') ?? $log->created_at?->format('d/m/Y H:i') }}
                                    | {{ strtoupper($log->locale ?? 'vi') }}
                                    | {{ $log->source ?: 'local' }}
                                    | Lập lại {{ $log->hit_count }} lần
                                </div>
                                @if ($log->student)
                                    <div class="text-muted small mt-1">Học viên: {{ $log->student->name }}{{ $log->student->email ? ' - ' . $log->student->email : '' }}</div>
                                @elseif ($log->student_id)
                                    <div class="text-muted small mt-1">Học viên ID: {{ $log->student_id }}</div>
                                @endif
                                @if (!empty($log->intent_tags))
                                    <div class="text-muted small mt-1">Tags: {{ implode(', ', (array) $log->intent_tags) }}</div>
                                @endif
                            </div>
                            <div>
                                <span class="badge bg-{{ $log->status === 'pending' ? 'warning' : ($log->status === 'handled' ? 'success' : 'secondary') }}">
                                    {{ $log->status === 'pending' ? 'Đang chờ xử lý' : ($log->status === 'handled' ? 'Đã xử lý' : 'Đã bỏ qua') }}
                                </span>
                            </div>
                        </div>

                        @if ($log->resolved_message)
                            <div class="mb-2">
                                <div class="text-muted small mb-1">Bot thường đã trả lời:</div>
                                <div class="bg-light rounded-3 p-3 small">{{ $log->resolved_message }}</div>
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a href="{{ route('chatbot-knowledge.add', ['from_log' => $log->id]) }}"
                                class="btn btn-sm btn-primary">Do san vao form train bot</a>

                            <form action="{{ route('chatbot-knowledge.unresolved.status', $log->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="handled">
                                <button type="submit" class="btn btn-sm btn-outline-success">Đánh dấu đã xử lý</button>
                            </form>

                            <form action="{{ route('chatbot-knowledge.unresolved.status', $log->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="ignored">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Bỏ qua</button>
                            </form>

                            @if ($log->status !== 'pending')
                                <form action="{{ route('chatbot-knowledge.unresolved.status', $log->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit" class="btn btn-sm btn-outline-warning">Mở lại</button>
                                </form>
                            @endif

                            @if ($log->knowledge)
                                <span class="small text-muted">Đã gắn với tri thức: {{ $log->knowledge->question }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5 border rounded-4">
                        Chưa có câu hỏi log nào.
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection
