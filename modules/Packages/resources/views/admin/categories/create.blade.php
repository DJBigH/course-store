@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Thêm danh mục gói mới</h5>
                    <p class="text-muted mb-0">Thiết lập các bản dịch và thứ tự hiển thị cho danh mục.</p>
                </div>
                <a href="{{ route('teacher-packages.categories.index') }}" class="btn btn-light border">Quay lại</a>
            </div>

            <form action="{{ route('teacher-packages.categories.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="border rounded-4 p-4 bg-light-subtle">
                            <h6 class="fw-bold mb-3">Tên danh mục theo ngôn ngữ</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tiếng Việt (Mặc định) *</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tiếng Anh (English)</label>
                                    <input type="text" class="form-control" name="name_en" value="{{ old('name_en') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tiếng Hàn (Korean)</label>
                                    <input type="text" class="form-control" name="name_ko" value="{{ old('name_ko') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tiếng Nhật (Japanese)</label>
                                    <input type="text" class="form-control" name="name_ja" value="{{ old('name_ja') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tiếng Trung (Chinese)</label>
                                    <input type="text" class="form-control" name="name_zh" value="{{ old('name_zh') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-4 p-4 bg-light-subtle h-100">
                            <h6 class="fw-bold mb-3">Cài đặt hiển thị</h6>
                            <div class="mb-3">
                                <label class="form-label">Thứ tự ưu tiên</label>
                                <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $nextSortOrder) }}" min="1">
                                <div class="form-text">Số nhỏ hơn sẽ hiện ra trước (trái sang phải).</div>
                            </div>
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="statusSwitch" checked>
                                <label class="form-check-label fw-bold" for="statusSwitch">Công khai danh mục</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button class="btn btn-primary px-5 fw-bold">Lưu danh mục</button>
                </div>
            </form>
        </div>
    </div>
@endsection
