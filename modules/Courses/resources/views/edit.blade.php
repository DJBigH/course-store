@extends('layouts.backend')
@section('content')
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    @if (session('msg_danger'))
        <div class="alert alert-danger">{{ session('msg_danger') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            Vui lòng kiểm tra lại dữ liệu đã nhập.
        </div>
    @endif
    <form action="" method="post" class="admin-form">
        <div class="admin-form__header d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0"></h5>

            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="content_lang" id="lang_vi" checked>
                <label class="btn btn-outline-primary" for="lang_vi">VI</label>

                <input type="radio" class="btn-check" name="content_lang" id="lang_en">
                <label class="btn btn-outline-primary" for="lang_en">EN</label>

                <input type="radio" class="btn-check" name="content_lang" id="lang_ko">
                <label class="btn btn-outline-primary" for="lang_ko">KO</label>

                <input type="radio" class="btn-check" name="content_lang" id="lang_ja">
                <label class="btn btn-outline-primary" for="lang_ja">JA</label>

                <input type="radio" class="btn-check" name="content_lang" id="lang_zh">
                <label class="btn btn-outline-primary" for="lang_zh">ZH</label>
            </div>
        </div>
        @csrf
        <div class="row">
            <div class="col-12">
                {{-- ================= VI ================= --}}
                <div class="lang-block lang-vi">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Tên khóa học (VI)</label>
                                <input type="text"
                                    class="form-control title {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name"
                                    placeholder="Tên khóa học..." value="{{ old('name', $courses->name ?? '') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label>Slug (VI)</label>
                                <input type="text"
                                    class="form-control slug {{ $errors->has('slug') ? 'is-invalid' : '' }}" name="slug"
                                    placeholder="Auto generate..." value="{{ old('slug', $courses->slug ?? '') }}" readonly>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá gốc (VI)</label>
                                <input type="text" class="form-control money-format" name="price"
                                    placeholder="Giá khóa học(Mặc định là 0đ)..."
                                    value="{{ number_format(old('price', $courses->price ?? 0)) }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá khuyến mãi (VI)</label>
                                <input type="text" class="form-control money-format" name="sale_price"
                                    placeholder="Giá khuyến mãi..."
                                    value="{{ number_format(old('sale_price', $courses->sale_price ?? 0)) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= EN ================= --}}
                <div class="lang-block lang-en d-none">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Tên (EN)</label>
                                <input type="text"
                                    class="form-control title-en {{ $errors->has('name_en') ? 'is-invalid' : '' }}"
                                    name="name_en" placeholder="Tên khóa học..."
                                    value="{{ old('name_en', $courses->name_en ?? '') }}">
                                @error('name_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label>Slug (EN)</label>
                                <input type="text"
                                    class="form-control slug-en {{ $errors->has('slug_en') ? 'is-invalid' : '' }}"
                                    name="slug_en" placeholder="Auto generate..."
                                    value="{{ old('slug_en', $courses->slug_en ?? '') }}" readonly>
                                @error('slug_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá gốc (EN)</label>
                                <input type="text" class="form-control price-en money-format" name="price_en"
                                    value="{{ number_format(old('price_en', $courses->price_en ?? 0), 2) }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá khuyến mãi (EN)</label>
                                <input type="text" class="form-control sale-price-en money-format" name="sale_price_en"
                                    value="{{ number_format(old('sale_price_en', $courses->sale_price_en ?? 0), 2) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lang-block lang-ko d-none">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Tên (KO)</label>
                                <input type="text"
                                    class="form-control title-ko {{ $errors->has('name_ko') ? 'is-invalid' : '' }}"
                                    name="name_ko" placeholder="Tên khóa học..."
                                    value="{{ old('name_ko', $courses->name_ko ?? '') }}">
                                @error('name_ko')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label>Slug (KO)</label>
                                <input type="text"
                                    class="form-control slug-ko {{ $errors->has('slug_ko') ? 'is-invalid' : '' }}"
                                    name="slug_ko" placeholder="Auto generate..."
                                    value="{{ old('slug_ko', $courses->slug_ko ?? '') }}" readonly>
                                @error('slug_ko')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá gốc (KO)</label>
                                <input type="text" class="form-control price-ko money-format" name="price_ko"
                                    value="{{ number_format(old('price_ko', $courses->price_ko ?? 0)) }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá khuyến mãi (KO)</label>
                                <input type="text" class="form-control sale-price-ko money-format" name="sale_price_ko"
                                    value="{{ number_format(old('sale_price_ko', $courses->sale_price_ko ?? 0)) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lang-block lang-ja d-none">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Tên (JA)</label>
                                <input type="text"
                                    class="form-control title-ja {{ $errors->has('name_ja') ? 'is-invalid' : '' }}"
                                    name="name_ja" placeholder="Tên khóa học..."
                                    value="{{ old('name_ja', $courses->name_ja ?? '') }}">
                                @error('name_ja')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label>Slug (JA)</label>
                                <input type="text"
                                    class="form-control slug-ja {{ $errors->has('slug_ja') ? 'is-invalid' : '' }}"
                                    name="slug_ja" placeholder="Auto generate..."
                                    value="{{ old('slug_ja', $courses->slug_ja ?? '') }}" readonly>
                                @error('slug_ja')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá gốc (JA)</label>
                                <input type="text" class="form-control price-ja money-format" name="price_ja"
                                    value="{{ number_format(old('price_ja', $courses->price_ja ?? 0)) }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá khuyến mãi (JA)</label>
                                <input type="text" class="form-control sale-price-ja money-format" name="sale_price_ja"
                                    value="{{ number_format(old('sale_price_ja', $courses->sale_price_ja ?? 0)) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lang-block lang-zh d-none">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Tên (ZH)</label>
                                <input type="text"
                                    class="form-control title-zh {{ $errors->has('name_zh') ? 'is-invalid' : '' }}"
                                    name="name_zh" placeholder="Tên khóa học..."
                                    value="{{ old('name_zh', $courses->name_zh ?? '') }}">
                                @error('name_zh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label>Slug (ZH)</label>
                                <input type="text"
                                    class="form-control slug-zh {{ $errors->has('slug_zh') ? 'is-invalid' : '' }}"
                                    name="slug_zh" placeholder="Auto generate..."
                                    value="{{ old('slug_zh', $courses->slug_zh ?? '') }}" readonly>
                                @error('slug_zh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá gốc (ZH)</label>
                                <input type="text" class="form-control price-zh money-format" name="price_zh"
                                    value="{{ number_format(old('price_zh', $courses->price_zh ?? 0)) }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Giá khuyến mãi (ZH)</label>
                                <input type="text" class="form-control sale-price-zh money-format" name="sale_price_zh"
                                    value="{{ number_format(old('sale_price_zh', $courses->sale_price_zh ?? 0)) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Giảng viên</label>
                    <select name="teacher_id" id=""
                        class="form-select{{ $errors->has('teacher_id') ? ' is-invalid' : '' }}">
                        <option value="0">Chọn giảng viên</option>
                        @if ($teacher)
                            @foreach ($teacher as $t)
                                <option value="{{ $t->id }}"
                                    {{ old('teacher_id') == $t->id || $courses->teacher_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('teacher_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label class="form-label">Mã khóa học</label>
                    <div class="input-group">
                        <input type="text" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}"
                            name="code" placeholder="Mã khóa học..." id="course_code"
                            value="{{ old('code') ?? $courses->code }}" readonly>

                        <button type="button" class="btn btn-outline-secondary" id="randomCode" disabled>
                            <i class="fa-solid fa-shuffle"></i>
                        </button>

                        @error('code')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>


            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label d-block">Loại khuyến mãi</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sale_type" id="sale_type_permanent" value="0" {{ !old('end_at', $courses->end_at) ? 'checked' : '' }}>
                        <label class="form-check-label" for="sale_type_permanent">Khuyến mãi vĩnh viễn</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sale_type" id="sale_type_timed" value="1" {{ old('end_at', $courses->end_at) ? 'checked' : '' }}>
                        <label class="form-check-label" for="sale_type_timed">Khuyến mãi có thời gian (Flash Sale)</label>
                    </div>
                </div>
            </div>

            <div class="col-6" id="sale_end_at_wrapper" style="display: {{ !old('end_at', $courses->end_at) ? 'none' : 'block' }}">
                <div class="mb-3">
                    <label for="">Ngày kết thúc khuyến mãi</label>
                    <input type="datetime-local" name="end_at" id="sale_end_at"
                        class="form-control{{ $errors->has('end_at') ? ' is-invalid' : '' }}"
                        value="{{ old('end_at', $courses->end_at ? $courses->end_at->format('Y-m-d\TH:i') : '') }}">
                    <small class="text-muted">Hệ thống sẽ tự động tắt Flash Sale và về giá gốc khi hết hạn.</small>
                    @error('end_at')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Tài liệu đính kèm</label>
                    <select name="is_document" id=""
                        class="form-select{{ $errors->has('is_document') ? ' is-invalid' : '' }}">
                        <option value="0"
                            {{ old('is_document' == 0, $courses->is_document == 0 ? 'selected' : false) }}>Không</option>
                        <option value="1"
                            {{ old('is_document' == 1, $courses->is_document == 1 ? 'selected' : false) }}>Có</option>
                    </select>
                    @error('is_document')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Trạng thái</label>
                    <select name="status" id="status"
                        class="form-select {{ $errors->has('status') ? 'is-invalid' : '' }}">
                        <option value="0" {{ old('status') == 0 || $courses->status == 0 ? 'selected' : false }}>Chưa
                            ra mắt (Nháp)</option>
                        <option value="1" {{ old('status') == 1 || $courses->status == 1 ? 'selected' : false }}>Đã
                            ra mắt (Công khai)</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Trạng thái bán hàng</label>
                    <select name="is_coming_soon" id="is_coming_soon"
                        class="form-select{{ $errors->has('is_coming_soon') ? ' is-invalid' : '' }}">
                        <option value="0" {{ old('is_coming_soon', $courses->is_coming_soon) == 0 ? 'selected' : false }}>Đang bán / Sẵn sàng</option>
                        <option value="1" {{ old('is_coming_soon', $courses->is_coming_soon) == 1 ? 'selected' : false }}>Sắp ra mắt (Coming Soon)</option>
                    </select>
                </div>
            </div>

            <div class="col-6" id="coming_soon_date_wrapper" style="display: {{ old('is_coming_soon', $courses->is_coming_soon) == 1 ? 'block' : 'none' }}">
                <div class="mb-3">
                    <label for="">Ngày mở bán chính thức</label>
                    <input type="datetime-local" name="coming_soon_start_at" 
                        class="form-control{{ $errors->has('coming_soon_start_at') ? ' is-invalid' : '' }}"
                        value="{{ old('coming_soon_start_at', $courses->coming_soon_start_at ? $courses->coming_soon_start_at->format('Y-m-d\TH:i') : '') }}">
                    <small class="text-muted">Dùng để đếm ngược Countdown ngoài trang chủ.</small>
                    @error('coming_soon_start_at')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Khóa học tập</label>
                    <select name="is_learning_locked"
                        class="form-select{{ $errors->has('is_learning_locked') ? ' is-invalid' : '' }}">
                        <option value="0"
                            {{ (string) old('is_learning_locked', $courses->is_learning_locked ?? 0) === '0' ? 'selected' : '' }}>
                            Không khóa, học viên đã mua vẫn được học
                        </option>
                        <option value="1"
                            {{ (string) old('is_learning_locked', $courses->is_learning_locked ?? 0) === '1' ? 'selected' : '' }}>
                            Khóa học tập, chặn cả học viên đã mua
                        </option>
                    </select>
                    <small class="text-muted d-block mt-2">
                        Ẩn khóa học chỉ ngừng hiển thị ngoài public. Tùy chọn này mới là phần chặn học tập thật sự.
                    </small>
                    @error('is_learning_locked')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Điều kiện cấp chứng chỉ</label>
                    <select name="completion_condition" class="form-select{{ $errors->has('completion_condition') ? ' is-invalid' : '' }}">
                        <option value="all_lessons" {{ old('completion_condition', $course->completion_condition ?? 'all_lessons') === 'all_lessons' ? 'selected' : '' }}>100% Bài giảng</option>
                        <option value="all_quizzes" {{ old('completion_condition', $course->completion_condition ?? 'all_lessons') === 'all_quizzes' ? 'selected' : '' }}>Thi đậu tất cả Quiz</option>
                        <option value="all" {{ old('completion_condition', $course->completion_condition ?? 'all_lessons') === 'all' ? 'selected' : '' }}>Bài giảng + Quiz</option>
                        <option value="none" {{ old('completion_condition', $course->completion_condition ?? 'all_lessons') === 'none' ? 'selected' : '' }}>Không cấp chứng chỉ</option>
                    </select>
                    @error('completion_condition')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                {{-- VI --}}
                <div class="lang-block lang-vi">
                    <div class="mb-3">
                        <label>Hỗ trợ (VI)</label>
                        <textarea name="supports" class="form-control ckeditor">{{ old('supports', $courses->supports ?? '') }}</textarea>
                    </div>
                </div>

                {{-- EN --}}
                <div class="lang-block lang-en d-none">
                    <div class="mb-3">
                        <label>Hỗ trợ (EN)</label>
                        <textarea name="supports_en" class="form-control ckeditor">{{ old('supports_en', $courses->supports_en ?? '') }}</textarea>
                    </div>
                </div>

                <div class="lang-block lang-ko d-none">
                    <div class="mb-3">
                        <label>Hỗ trợ (KO)</label>
                        <textarea name="supports_ko" class="form-control ckeditor">{{ old('supports_ko', $courses->supports_ko ?? '') }}</textarea>
                    </div>
                </div>

                <div class="lang-block lang-ja d-none">
                    <div class="mb-3">
                        <label>Hỗ trợ (JA)</label>
                        <textarea name="supports_ja" class="form-control ckeditor">{{ old('supports_ja', $courses->supports_ja ?? '') }}</textarea>
                    </div>
                </div>

                <div class="lang-block lang-zh d-none">
                    <div class="mb-3">
                        <label>Hỗ trợ (ZH)</label>
                        <textarea name="supports_zh" class="form-control ckeditor">{{ old('supports_zh', $courses->supports_zh ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="lang-block lang-vi">
                    <div class="mb-3">
                        <label>Nội dung (VI)</label>
                        <textarea name="detail" class="form-control ckeditor">{{ old('detail', $courses->detail ?? '') }}</textarea>
                    </div>
                </div>

                {{-- EN --}}
                <div class="lang-block lang-en d-none">
                    <div class="mb-3">
                        <label>Nội dung (EN)</label>
                        <textarea name="detail_en" class="form-control ckeditor">{{ old('detail_en', $courses->detail_en ?? '') }}</textarea>
                    </div>
                </div>

                <div class="lang-block lang-ko d-none">
                    <div class="mb-3">
                        <label>Nội dung (KO)</label>
                        <textarea name="detail_ko" class="form-control ckeditor">{{ old('detail_ko', $courses->detail_ko ?? '') }}</textarea>
                    </div>
                </div>

                <div class="lang-block lang-ja d-none">
                    <div class="mb-3">
                        <label>Nội dung (JA)</label>
                        <textarea name="detail_ja" class="form-control ckeditor">{{ old('detail_ja', $courses->detail_ja ?? '') }}</textarea>
                    </div>
                </div>

                <div class="lang-block lang-zh d-none">
                    <div class="mb-3">
                        <label>Nội dung (ZH)</label>
                        <textarea name="detail_zh" class="form-control ckeditor">{{ old('detail_zh', $courses->detail_zh ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label for="">Chuyên mục</label>
                    <div class="list-categories">
                        {{ getCategoriesCheckBox($categories, old('categories') ?? $categoriesId) }}
                    </div>
                    @error('categories')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <div class="row {{ $errors->has('thumbnail') ? 'g-2 align-items-center' : 'g-2 align-items-end' }}">
                        <div class="col-7">
                            <label for="">Ảnh đại diện</label>
                            <input type="text"
                                class="form-control{{ $errors->has('thumbnail') ? ' is-invalid' : '' }}" name="thumbnail"
                                placeholder="Ảnh đại diện..." id="thumbnail"
                                value="{{ old('thumbnail') ?? $courses->thumbnail }}">
                            @error('thumbnail')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-2 d-grid">
                            <button type="button" class="btn btn-primary" id="lfm" data-input="thumbnail"
                                data-preview="holder">Chọn ảnh <i class="fa-solid fa-file-arrow-up"></i></button>
                        </div>
                        <div class="col-3">
                            <div id="holder">
                                @if (old('thumbnail') || $courses->thumbnail)
                                    <img src="{{ asset(old('thumbnail') ?? $courses->thumbnail) }}" alt="">
                                @else
                                    <img src="https://placehold.co/600x400?text=Course" alt="">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 text-end admin-form__footer">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('courses.index') }}" class="btn btn-warning">Trở về</a>
            </div>
        </div>
    </form>
@endsection

@section('stylesheets')
    <style>
        img {
            max-width: 100%;
            height: auto !important;
        }

        #holder img {
            width: 100% !important;
        }

        .list-categories {
            max-height: 250px;
            overflow: auto;
            border: 1px solid #d1d1d1
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.getElementById('randomCode').addEventListener('click', function() {
            const prefix = 'KH';
            const random = Math.floor(100000 + Math.random() * 900000);
            document.getElementById('course_code').value = prefix + random;
        });

        function getSlugIntl(title) {
            return (title || '').toLowerCase().trim().replace(/[^\p{L}\p{N}\s-]/gu, '').replace(/\s+/g, '-')
                .replace(/-+/g, '-').replace(/^-+|-+$/g, '');
        }

        (function() {
            const viBtn = document.getElementById('lang_vi');
            const enBtn = document.getElementById('lang_en');
            const koBtn = document.getElementById('lang_ko');
            const jaBtn = document.getElementById('lang_ja');
            const zhBtn = document.getElementById('lang_zh');

            function showLang(lang) {
                document.querySelectorAll('.lang-block').forEach(el => el.classList.add('d-none'));
                document.querySelectorAll('.lang-' + lang).forEach(el => el.classList.remove('d-none'));
                localStorage.setItem('admin_course_lang', lang);
            }

            const saved = localStorage.getItem('admin_course_lang') || 'vi';
            if (saved === 'en') enBtn.checked = true;
            if (saved === 'ko') koBtn.checked = true;
            if (saved === 'ja') jaBtn.checked = true;
            if (saved === 'zh') zhBtn.checked = true;
            showLang(saved);

            viBtn.addEventListener('change', () => showLang('vi'));
            enBtn.addEventListener('change', () => showLang('en'));
            koBtn.addEventListener('change', () => showLang('ko'));
            jaBtn.addEventListener('change', () => showLang('ja'));
            zhBtn.addEventListener('change', () => showLang('zh'));
        })();

        window.AdminSlug.bindAuto('.title-ko', '.slug-ko', 'ko');
        window.AdminSlug.bindAuto('.title-ja', '.slug-ja', 'ja');
        window.AdminSlug.bindAuto('.title-zh', '.slug-zh', 'zh');

        const isComingSoonSelect = document.getElementById('is_coming_soon');
        const comingSoonWrapper = document.getElementById('coming_soon_date_wrapper');
        const comingSoonInput = comingSoonWrapper ? comingSoonWrapper.querySelector('input') : null;

        function toggleComingSoon() {
            if (!isComingSoonSelect || !comingSoonWrapper) return;
            
            if (isComingSoonSelect.value == 1) {
                comingSoonWrapper.style.display = 'block';
            } else {
                comingSoonWrapper.style.display = 'none';
                if (comingSoonInput) comingSoonInput.value = '';
            }
        }

        if (isComingSoonSelect) {
            isComingSoonSelect.addEventListener('change', toggleComingSoon);
            // Chạy ngay khi load trang
            toggleComingSoon();
        }

        // Sale Type Toggle
        const saleTypePermanent = document.getElementById('sale_type_permanent');
        const saleTypeTimed = document.getElementById('sale_type_timed');
        const saleEndWrapper = document.getElementById('sale_end_at_wrapper');
        const saleEndInput = document.getElementById('sale_end_at');

        function toggleSaleType() {
            if (!saleEndWrapper) return;
            if (saleTypeTimed.checked) {
                saleEndWrapper.style.display = 'block';
            } else {
                saleEndWrapper.style.display = 'none';
                if (saleEndInput) saleEndInput.value = '';
            }
        }

        if (saleTypePermanent && saleTypeTimed) {
            saleTypePermanent.addEventListener('change', toggleSaleType);
            saleTypeTimed.addEventListener('change', toggleSaleType);
        }

        // Auto Sync Prices Logic
        (function() {
            const supported = ['vi', 'en', 'ko', 'ja', 'zh'];
            const exchangeRates = @json($exchangeRates);
            const conversionFee = {{ $conversionFee }};
            let currentLocale = 'vi';

            // Update currentLocale based on tab changes
            ['vi', 'en', 'ko', 'ja', 'zh'].forEach(loc => {
                const btn = document.getElementById('lang_' + loc);
                if (btn) {
                    // Cả change và click để chắc chắn
                    ['change', 'click'].forEach(evt => {
                        btn.addEventListener(evt, () => {
                            currentLocale = loc;
                        });
                    });
                }
            });

            const getCurrencyByLocale = (locale) => ({
                'en': 'USD', 'ko': 'KRW', 'ja': 'JPY', 'zh': 'CNY', 'vi': 'VND'
            }[locale]);

            const convertPrice = (amount, fromCurrency, toCurrency) => {
                if (fromCurrency === toCurrency) return amount;
                if (!exchangeRates[fromCurrency] || !exchangeRates[toCurrency]) return amount;
                
                // Quy đổi về USD làm gốc
                const usdAmount = amount / exchangeRates[fromCurrency];
                let targetAmount = usdAmount * exchangeRates[toCurrency];
                
                if (conversionFee > 0) targetAmount *= (1 + (conversionFee / 100));
                
                return ['VND', 'KRW', 'JPY'].includes(toCurrency) ? Math.round(targetAmount) : Math.round(targetAmount * 100) / 100;
            };

            const formatMoney = (val) => {
                if (!val) return '0';
                // Remove existing commas and non-digits
                let num = val.toString().replace(/,/g, '');
                if (isNaN(num)) return '0';
                // Format with commas
                return num.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            };

            const unformatMoney = (val) => {
                return val.toString().replace(/,/g, '');
            };

            // Apply formatting to all money-format inputs on load
            document.querySelectorAll('.money-format').forEach(input => {
                input.addEventListener('input', (e) => {
                    let cursorPosition = e.target.selectionStart;
                    let originalLength = e.target.value.length;
                    
                    let rawValue = unformatMoney(e.target.value);
                    if (rawValue === '') rawValue = '0';
                    
                    e.target.value = formatMoney(rawValue);
                    
                    // Adjust cursor position
                    let newLength = e.target.value.length;
                    e.target.setSelectionRange(cursorPosition + (newLength - originalLength), cursorPosition + (newLength - originalLength));
                });
            });

            const syncPrices = (sourceLocale, isSale = false) => {
                const prefix = isSale ? 'sale_price' : 'price';
                const sourceFieldName = sourceLocale === 'vi' ? prefix : `${prefix}_${sourceLocale}`;
                const sourceInput = document.querySelector(`input[name="${sourceFieldName}"]`);
                if (!sourceInput) return;

                const sourceValue = Number(unformatMoney(sourceInput.value) || 0);
                const fromCurrency = getCurrencyByLocale(sourceLocale);

                supported.forEach(targetLocale => {
                    if (targetLocale === sourceLocale) return;
                    const targetFieldName = targetLocale === 'vi' ? prefix : `${prefix}_${targetLocale}`;
                    const toCurrency = getCurrencyByLocale(targetLocale);
                    const convertedValue = convertPrice(sourceValue, fromCurrency, toCurrency);
                    const targetInput = document.querySelector(`input[name="${targetFieldName}"]`);
                    if (targetInput) {
                        targetInput.value = formatMoney(convertedValue);
                    }
                });
            };

            // Lắng nghe sự kiện để tự động nhảy số
            supported.forEach(locale => {
                const suffix = locale === 'vi' ? '' : `_${locale}`;
                const priceInput = document.querySelector(`input[name="price${suffix}"]`);
                const salePriceInput = document.querySelector(`input[name="sale_price${suffix}"]`);

                if (priceInput) {
                    ['input', 'change'].forEach(evt => {
                        priceInput.addEventListener(evt, () => { 
                            if (currentLocale === locale) syncPrices(locale, false); 
                        });
                    });
                }
                if (salePriceInput) {
                    ['input', 'change'].forEach(evt => {
                        salePriceInput.addEventListener(evt, () => { 
                            if (currentLocale === locale) syncPrices(locale, true); 
                        });
                    });
                }
            });

            // Override form submission to unformat money values
            document.querySelector('.admin-form').addEventListener('submit', function() {
                document.querySelectorAll('.money-format').forEach(input => {
                    input.value = unformatMoney(input.value);
                });
            });
        })();
    </script>
@endsection
