@extends('layouts.backend')

@section('content')
    <form action="{{ route('teacher-package-features.post-edit', $feature->id) }}" method="POST">
        @csrf
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1">{{ $pageTitle }}</h4>
                <p class="text-muted mb-0">Thiết lập nôi dung hiển thị đa ngôn ngữ cho tính năng:
                    <code>{{ $feature->key }}</code></p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('teacher-package-features.index') }}" class="btn btn-light border">Hủy</a>
                <button type="submit" class="btn btn-primary px-4">Lưu thay đổi</button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h6 class="mb-0">Nội dung hiển thị (Đa ngôn ngữ)</h6>
                    </div>
                    <div class="card-body p-4">
                        <ul class="nav nav-pills mb-4 bg-light p-1 rounded-3" id="langTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="vi-tab" data-bs-toggle="pill"
                                    data-bs-target="#lang-vi" type="button" role="tab">Tiếng Việt</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="en-tab" data-bs-toggle="pill" data-bs-target="#lang-en"
                                    type="button" role="tab">English</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="ja-tab" data-bs-toggle="pill" data-bs-target="#lang-ja"
                                    type="button" role="tab">日本語</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="ko-tab" data-bs-toggle="pill" data-bs-target="#lang-ko"
                                    type="button" role="tab">한국어</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="zh-tab" data-bs-toggle="pill" data-bs-target="#lang-zh"
                                    type="button" role="tab">中文</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="langTabsContent">
                            @foreach (['vi', 'en', 'ja', 'ko', 'zh'] as $locale)
                                <div class="tab-pane fade {{ $locale == 'vi' ? 'show active' : '' }}"
                                    id="lang-{{ $locale }}" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tên tính năng ({{ strtoupper($locale) }})</label>
                                        <input type="text" name="name_{{ $locale }}" class="form-control"
                                            value="{{ old('name_' . $locale, $feature->{'name_' . $locale}) }}"
                                            placeholder="Ví dụ: AI Quiz Generation">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label fw-bold">Mô tả chi tiết ({{ strtoupper($locale) }})</label>
                                        <textarea name="description_{{ $locale }}" class="form-control" rows="4"
                                            placeholder="Nhập mô tả chi tiết giúp người dùng hiểu rõ về tính năng này...">{{ old('description_' . $locale, $feature->{'description_' . $locale}) }}</textarea>
                                        <div class="form-text mt-2 text-primary">
                                            <i class="fas fa-info-circle me-1"></i> Mô tả này sẽ hiển thị dưới dạng Tooltip
                                            khi người dùng di chuột vào tên tính năng.
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h6 class="mb-0">Cấu hình hiển thị</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Icon (FontAwesome)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i
                                        class="{{ $feature->icon ?: 'fas fa-check' }}"></i></span>
                                <input type="text" name="icon" class="form-control"
                                    value="{{ old('icon', $feature->icon) }}" placeholder="fas fa-robot">
                            </div>
                            <div class="form-text">Ví dụ: fas fa-robot, fas fa-chart-line</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Phân nhóm (Group)</label>
                            <select name="group" class="form-select">
                                <option value="course_management"
                                    {{ $feature->group == 'course_management' ? 'selected' : '' }}>Quản lý khóa học
                                </option>
                                <option value="advanced_tools"
                                    {{ $feature->group == 'advanced_tools' ? 'selected' : '' }}>Công cụ AI & Nâng cao
                                </option>
                                <option value="marketing" {{ $feature->group == 'marketing' ? 'selected' : '' }}>Marketing
                                    & Bán hàng</option>
                                <option value="student_care" {{ $feature->group == 'student_care' ? 'selected' : '' }}>
                                    Chăm sóc học viên</option>
                                <option value="finance" {{ $feature->group == 'finance' ? 'selected' : '' }}>Tài chính &
                                    Hệ thống</option>
                                <option value="branding" {{ $feature->group == 'branding' ? 'selected' : '' }}>Thương hiệu
                                    & Cá nhân hóa</option>
                                <option value="system" {{ $feature->group == 'system' ? 'selected' : '' }}>Bảo mật & Nhật
                                    ký</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Thứ tự hiển thị</label>
                            <input type="number" name="sort_order" class="form-control"
                                value="{{ old('sort_order', $feature->sort_order) }}">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Trạng thái tính năng</label>
                            <select name="is_enabled" class="form-select">
                                <option value="1" {{ $feature->is_enabled == 1 ? 'selected' : '' }}>Hoạt động</option>
                                <option value="2" {{ $feature->is_enabled == 2 ? 'selected' : '' }}>Bảo trì (Hiện bảng so sánh)</option>
                                <option value="3" {{ $feature->is_enabled == 3 ? 'selected' : '' }}>Bảo trì (Ẩn bảng so sánh)</option>
                                <option value="0" {{ $feature->is_enabled == 0 ? 'selected' : '' }}>Tạm khóa (Ẩn)</option>
                            </select>
                            <div class="form-text mt-2">
                                <i class="fas fa-info-circle me-1"></i> Tính năng ở trạng thái Bảo trì sẽ bị khóa chức năng nhưng vẫn hiện thông báo cho người dùng.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('stylesheets')
    <style>
        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
        }

        .nav-pills .nav-link.active {
            background: #fff;
            color: #2563eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.1);
        }
    </style>
@endsection
