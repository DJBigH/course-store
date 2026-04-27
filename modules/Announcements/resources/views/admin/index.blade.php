@extends('layouts.backend')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Lịch sử thông báo hệ thống</h5>
                <p class="text-muted mb-0">Danh sách các thông báo đã gửi cho học viên và giảng viên.</p>
            </div>
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Soạn thông báo mới
            </a>
        </div>

        @if (session('msg'))
            <div class="alert alert-success">{{ session('msg') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Tiêu đề</th>
                        <th>Đối tượng</th>
                        <th>Email?</th>
                        <th>Người gửi</th>
                        <th>Lượt đọc</th>
                        <th>Ngày gửi</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($announcements as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>
                                <div class="fw-bold">{{ $item->title }}</div>
                                <div class="small text-muted">{{ Str::limit($item->message, 60) }}</div>
                            </td>
                            <td>
                                @switch($item->target_type)
                                    @case('all')
                                        <span class="badge bg-primary">Tất cả</span>
                                        @break
                                    @case('all_students')
                                        <span class="badge bg-info">Chỉ Học viên</span>
                                        @break
                                    @case('all_teachers')
                                        <span class="badge bg-success">Chỉ Giảng viên</span>
                                        @break
                                    @case('selected')
                                        <span class="badge bg-dark">Cá nhân chọn lọc</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                @if ($item->send_email)
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Có</span>
                                @else
                                    <span class="text-muted"><i class="fas fa-times-circle"></i> Không</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">{{ $item->creator ? $item->creator->name : 'Admin' }}</div>
                            </td>
                            <td>
                                <div class="small text-primary fw-bold">
                                    <i class="fas fa-eye me-1"></i> {{ $item->reads_count ?? 0 }}
                                </div>
                            </td>
                            <td>
                                <div class="small">{{ $item->sent_at ? $item->sent_at->format('d/m/Y H:i') : $item->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.announcements.show', $item->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.announcements.destroy', $item->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lịch sử thông báo này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Chưa có thông báo nào được gửi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection
