@extends('layouts.backend')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-1">Chi tiết thông báo</h5>
                            <p class="text-muted mb-0">Xem nội dung thông báo đã gửi cho người dùng.</p>
                        </div>
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Quay lại
                        </a>
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted text-uppercase fw-bold mb-2">Tiêu đề</label>
                        <h4 class="fw-bold">{{ $announcement->title }}</h4>
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted text-uppercase fw-bold mb-2">Lời nhắn ngắn (Inbox/Push)</label>
                        <div class="p-3 bg-light rounded-3 text-dark">
                            {{ $announcement->message }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted text-uppercase fw-bold mb-2">Nội dung chi tiết (HTML)</label>
                        <div class="p-4 border rounded-3 bg-white text-dark shadow-sm">
                            {!! $announcement->content !!}
                        </div>
                    </div>

                    @if($announcement->action_url)
                        <div class="mb-4">
                            <label class="small text-muted text-uppercase fw-bold mb-2">Nút hành động</label>
                            <div>
                                <a href="{{ $announcement->action_url }}" target="_blank" class="btn btn-primary">
                                    {{ $announcement->action_label ?: 'Xem ngay' }}
                                </a>
                                <span class="small text-muted ms-2">{{ $announcement->action_url }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Thông tin gửi</h6>
                    
                    <div class="mb-3">
                        <div class="small text-muted">Đối tượng:</div>
                        <div class="mt-1">
                            @switch($announcement->target_type)
                                @case('all')
                                    <span class="badge bg-primary">Tất cả người dùng</span>
                                    @break
                                @case('all_students')
                                    <span class="badge bg-info">Tất cả Học viên</span>
                                    @break
                                @case('all_teachers')
                                    <span class="badge bg-success">Tất cả Giảng viên</span>
                                    @break
                                @case('selected')
                                    <span class="badge bg-dark">Danh sách chọn lọc</span>
                                    @break
                            @endswitch
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Email thông báo:</div>
                        <div class="mt-1">
                            @if ($announcement->send_email)
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i> Có gửi email</span>
                            @else
                                <span class="text-muted"><i class="fas fa-times-circle me-1"></i> Không gửi email</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Người gửi:</div>
                        <div class="fw-bold">{{ $announcement->creator ? $announcement->creator->name : 'Admin' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Thời gian:</div>
                        <div class="fw-bold">{{ $announcement->sent_at ? $announcement->sent_at->format('d/m/Y H:i:s') : $announcement->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Lượt đọc:</div>
                        <div class="fw-bold text-primary">
                            <i class="fas fa-eye me-1"></i> {{ $announcement->reads_count }} lượt đọc
                        </div>
                    </div>

                    <hr>

                    @if($announcement->target_type === 'selected')
                        <h6 class="fw-bold mb-3">Danh sách người nhận ({{ $announcement->recipients->count() }})</h6>
                        <div class="recipient-list overflow-auto" style="max-height: 300px;">
                            @foreach($announcement->recipients as $user)
                                <div class="d-flex align-items-center mb-2 p-2 rounded bg-light border">
                                    <div class="flex-shrink-0 me-2">
                                        <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="small fw-bold text-truncate text-dark" title="{{ $user->name }}">{{ $user->name }}</div>
                                        <div class="small text-muted text-truncate" title="{{ $user->email }}">{{ $user->email }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="fas fa-info-circle me-1"></i> Gửi cho toàn bộ nhóm đối tượng mục tiêu.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card border-0 shadow-sm bg-danger-subtle border-danger border-opacity-25">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold text-danger mb-3">Vùng nguy hiểm</h6>
                    <p class="small text-muted mb-3">Việc xóa sẽ mất lịch sử gửi thông báo này.</p>
                    <form action="{{ route('admin.announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch sử thông báo này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-2"></i> Xóa lịch sử gửi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
