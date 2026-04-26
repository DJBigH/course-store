@extends('layouts.backend')

@php
    $bundle = $bundle ?? null;
    $selectedCourseIds = collect(old('course_ids', $bundle?->items?->pluck('course_id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $isEdit = (bool) $bundle;
@endphp

@section('content')
    <div class="admin-bundle-form-shell">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">{{ $pageTitle }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('courses.bundles.index') }}">Quản lý Combo</a></li>
                        <li class="breadcrumb-item active">{{ $isEdit ? 'Chỉnh sửa' : 'Thêm mới' }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('courses.bundles.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
            action="{{ $isEdit ? route('courses.bundles.post-edit', ['id' => $bundle->id]) : route('courses.bundles.post-add') }}"
            id="bundle-form">
            @csrf

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Thông tin cơ bản</h5>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Giảng viên <span class="text-danger">*</span></label>
                                <select name="teacher_id" id="teacher_id" class="form-select @if($isEdit) bg-light @endif" @if($isEdit) disabled @endif required>
                                    <option value="">-- Chọn giảng viên --</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('teacher_id', $bundle?->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($isEdit)
                                    <input type="hidden" name="teacher_id" value="{{ $bundle->teacher_id }}">
                                @endif
                                <div class="form-text">Combo phải thuộc về một giảng viên cụ thể.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên Combo <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" maxlength="160"
                                    value="{{ old('name', $bundle?->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Thumbnail (URL)</label>
                                <div class="input-group">
                                    <input type="text" name="thumbnail" id="thumbnail" class="form-control"
                                        value="{{ old('thumbnail', $bundle?->thumbnail) }}">
                                    <button type="button" class="btn btn-outline-primary" id="lfm-btn" data-input="thumbnail">
                                        Chọn file
                                    </button>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold">Mô tả</label>
                                <textarea name="description" class="form-control" rows="6">{{ old('description', $bundle?->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Danh sách khóa học trong Combo</h5>
                                <span class="badge bg-primary rounded-pill" id="selected-count">0 được chọn</span>
                            </div>
                            
                            <div id="courses-container" class="border rounded-4 p-3 bg-light" style="min-height: 200px; max-height: 500px; overflow-y: auto;">
                                <div class="text-center text-muted py-5" id="no-teacher-msg">
                                    <i class="fa-solid fa-user-tie fa-3x mb-3 opacity-25"></i>
                                    <p>Vui lòng chọn giảng viên để xem danh sách khóa học</p>
                                </div>
                                <div id="courses-list" class="row g-3">
                                    <!-- AJAX content here -->
                                </div>
                            </div>
                            <div class="form-text mt-2 text-danger">Lưu ý: Combo cần tối thiểu 2 khóa học.</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Cài đặt bán hàng</h5>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Giá gốc Combo (VND) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">₫</span>
                                    <input type="text" name="price_display" id="price_display" class="form-control fw-bold text-primary border-start-0 currency-format"
                                        value="{{ number_format(old('price', $bundle?->price), 0, ',', '.') }}" required>
                                    <input type="hidden" name="price" id="price" value="{{ old('price', $bundle?->price) }}">
                                </div>
                                <div class="form-text">Giá niêm yết của cả gói combo.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Giá khuyến mãi (VND)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">₫</span>
                                    <input type="text" name="sale_price_display" id="sale_price_display" class="form-control fw-bold text-danger border-start-0 currency-format"
                                        value="{{ old('sale_price', $bundle?->sale_price) ? number_format(old('sale_price', $bundle->sale_price), 0, ',', '.') : '' }}">
                                    <input type="hidden" name="sale_price" id="sale_price" value="{{ old('sale_price', $bundle?->sale_price) }}">
                                </div>
                                <div class="form-text">Giá bán thực tế sau khi giảm giá. Để trống nếu không giảm giá.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Số lượng giới hạn</label>
                                <input type="number" name="quantity" class="form-control" min="0"
                                    value="{{ old('quantity', $bundle?->quantity) }}">
                                <div class="form-text">Để trống nếu không giới hạn.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Hạn đăng ký</label>
                                <input type="datetime-local" name="end_at" class="form-control"
                                    value="{{ old('end_at', $bundle?->end_at ? $bundle->end_at->format('Y-m-d\TH:i') : '') }}">
                                <div class="form-text">Sau thời gian này sẽ không thể mua.</div>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="bundle-status"
                                    {{ old('status', $bundle?->status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="bundle-status">Kích hoạt hiển thị</label>
                            </div>

                            <hr>

                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="is_coming_soon" value="0">
                                <input class="form-check-input" type="checkbox" name="is_coming_soon" value="1" id="is_coming_soon"
                                    {{ old('is_coming_soon', $bundle?->is_coming_soon) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-warning" for="is_coming_soon">Chế độ Sắp ra mắt</label>
                            </div>

                            <div id="coming_soon_date_wrapper" style="display: none;">
                                <label class="form-label fw-bold small text-muted text-uppercase">Ngày mở bán dự kiến</label>
                                <input type="datetime-local" name="coming_soon_start_at" class="form-control border-warning"
                                    value="{{ old('coming_soon_start_at', $bundle?->coming_soon_start_at ? $bundle->coming_soon_start_at->format('Y-m-d\TH:i') : '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <button type="submit" class="btn btn-primary w-100 mb-2 py-2 fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-2"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Tạo Combo' }}
                            </button>
                            <a href="{{ route('courses.bundles.index') }}" class="btn btn-light border w-100 py-2 fw-bold">
                                Hủy bỏ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $(document).ready(function() {
            $('#lfm-btn').filemanager('image');

            const isComingSoonCheck = document.getElementById('is_coming_soon');
            const comingSoonWrapper = document.getElementById('coming_soon_date_wrapper');

            function toggleComingSoon() {
                if (!isComingSoonCheck || !comingSoonWrapper) return;
                comingSoonWrapper.style.display = isComingSoonCheck.checked ? 'block' : 'none';
            }

            if (isComingSoonCheck) {
                isComingSoonCheck.addEventListener('change', toggleComingSoon);
                toggleComingSoon();
            }

            // Currency Formatting
            function formatCurrency(value) {
                value = value.toString().replace(/\D/g, '');
                return value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            $('.currency-format').on('input', function() {
                const input = $(this);
                const rawValue = input.val().replace(/\D/g, '');
                const formattedValue = formatCurrency(input.val());
                input.val(formattedValue);
                
                // Update hidden input
                const hiddenInputId = input.attr('id').replace('_display', '');
                $('#' + hiddenInputId).val(rawValue);
            });

            // AJAX Load Courses
            const teacherSelect = $('#teacher_id');
            const coursesList = $('#courses-list');
            const noTeacherMsg = $('#no-teacher-msg');
            const selectedCourseIds = @json($selectedCourseIds);

            function loadCourses(teacherId) {
                if (!teacherId) {
                    coursesList.empty();
                    noTeacherMsg.show();
                    updateCount();
                    return;
                }

                noTeacherMsg.hide();
                coursesList.html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

                fetchCourseOptions(teacherId);
            }

            function fetchCourseOptions(teacherId) {
                $.ajax({
                    url: "{{ route('teacher.courses', ['id' => ':id']) }}".replace(':id', teacherId),
                    success: function(courses) {
                        coursesList.empty();
                        if (courses.length > 0) {
                            courses.forEach(course => {
                                const isChecked = selectedCourseIds.includes(parseInt(course.id)) ? 'checked' : '';
                                const displayPrice = course.sale_price > 0 ? course.sale_price : course.price;
                                const html = `
                                    <div class="col-md-6 col-xl-4">
                                        <label class="course-option-item p-3 border rounded-3 w-100 h-100 cursor-pointer">
                                            <div class="d-flex gap-3">
                                                <input type="checkbox" name="course_ids[]" value="${course.id}" class="course-checkbox form-check-input mt-1" ${isChecked}>
                                                <div>
                                                    <div class="fw-bold small course-name-text">${course.name}</div>
                                                    <div class="text-primary fw-bold small mt-1">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(displayPrice)}</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                `;
                                coursesList.append(html);
                            });
                        } else {
                            coursesList.html('<div class="col-12 text-center py-5"><p class="text-muted">Giảng viên này chưa có khóa học nào hoạt động.</p></div>');
                        }
                        updateCount();
                    }
                });
            }

            function updateCount() {
                const count = $('.course-checkbox:checked').length;
                $('#selected-count').text(count + ' được chọn');
            }

            $(document).on('change', '.course-checkbox', updateCount);

            teacherSelect.on('change', function() {
                loadCourses($(this).val());
            });

            // Init if edit or old input
            if (teacherSelect.val()) {
                loadCourses(teacherSelect.val());
            }
        });
    </script>
@endsection

@section('stylesheets')
    <style>
        .course-option-item { transition: all 0.2s; background: #fff; border: 1px solid #e2e8f0; }
        .course-option-item:hover { border-color: #2563eb !important; background: #f0f7ff; }
        .course-option-item:has(.course-checkbox:checked) { border-color: #2563eb !important; background: #f0f7ff; }
        .cursor-pointer { cursor: pointer; }
        .admin-bundle-form-shell .card { border-radius: 20px; }
        
        /* Dark Mode fixes */
        html[data-theme="dark"] .course-option-item {
            background: #1e293b;
            border-color: #334155;
        }
        html[data-theme="dark"] .course-option-item:hover {
            background: #1e293b;
            border-color: #3b82f6 !important;
        }
        html[data-theme="dark"] .course-option-item:has(.course-checkbox:checked) {
            background: #1e293b;
            border-color: #3b82f6 !important;
        }
        html[data-theme="dark"] .course-name-text {
            color: #f1f5f9;
        }
        html[data-theme="dark"] .form-select.bg-light {
            background-color: #1e293b !important;
            color: #94a3b8;
        }
    </style>
@endsection
