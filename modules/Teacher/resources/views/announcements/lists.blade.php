@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Thông báo portal giảng viên theo gói</h5>
                    <p class="text-muted mb-0">Quản lý các thông báo đẩy vào portal giảng viên theo từng gói dịch vụ đang sử dụng.</p>
                </div>
                <a href="{{ route('teacher-announcements.add') }}" class="btn btn-primary">Thêm thông báo</a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th>Áp dụng cho gói</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td>
                                    <strong>{{ $announcement->title }}</strong>
                                    <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit($announcement->message, 110) }}</div>
                                </td>
                                <td>
                                    @forelse ($announcement->packages as $package)
                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $package->name }}</span>
                                    @empty
                                        <span class="badge bg-info-subtle text-info-emphasis border">Tất cả gói</span>
                                    @endforelse
                                </td>
                                <td class="small text-muted">
                                    <div>Bắt đầu: {{ $announcement->starts_at?->format('d/m/Y H:i') ?: 'Ngay lập tức' }}</div>
                                    <div>Kết thúc: {{ $announcement->ends_at?->format('d/m/Y H:i') ?: 'Không giới hạn' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $announcement->status ? 'success' : 'secondary' }}">
                                        {{ $announcement->status ? 'Đang hoạt động' : 'Đang tắt' }}
                                    </span>
                                    @if ($announcement->is_pinned)
                                        <div class="small text-warning mt-1">Pinned</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('teacher-announcements.edit', $announcement->id) }}" class="btn btn-sm btn-warning">Sửa</a>
                                    <form action="{{ route('teacher-announcements.delete', $announcement->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa thông báo này?')">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Chưa có thông báo portal giảng viên nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
