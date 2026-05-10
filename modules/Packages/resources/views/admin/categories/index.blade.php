@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Quản lý danh mục gói</h5>
                    <p class="text-muted mb-0">Tạo các nhóm để phân loại các gói giảng viên.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('teacher-packages.index') }}" class="btn btn-light border">Quay lại DS gói</a>
                    <a href="{{ route('teacher-packages.categories.create') }}" class="btn btn-primary">Thêm danh mục</a>
                </div>
            </div>

            @if(session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif
            @if(session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="80">STT</th>
                            <th>Tên danh mục (VI)</th>
                            <th>Tiếng Anh (EN)</th>
                            <th width="100">Thứ tự</th>
                            <th width="100">Trạng thái</th>
                            <th width="150" class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold">{{ $category->name }}</td>
                                <td>{{ $category->name_en ?: '-' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $category->sort_order }}</span>
                                </td>
                                <td>
                                    @if($category->status)
                                        <span class="badge bg-success">Công khai</span>
                                    @else
                                        <span class="badge bg-secondary">Ẩn</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('teacher-packages.categories.edit', $category->id) }}" class="btn btn-sm btn-light border">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('teacher-packages.categories.delete', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận xóa danh mục này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-light border text-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có danh mục nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
