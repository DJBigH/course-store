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

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Giá khóa học</label>
                    <input type="number" class="form-control{{ $errors->has('price') ? ' is-invalid' : '' }}"
                        name="price" placeholder="Giá khóa học(Mặc định là 0đ)..."
                        value="{{ old('price') ?? $courses->price }}">
                    @error('price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Giá khuyến mãi</label>
                    <input type="number" name="sale_price"
                        class="form-control {{ $errors->has('sale_price') ? 'is-invalid' : '' }}"
                        placeholder="Giá khuyến mãi..." id=""
                        value="{{ old('sale_price') ?? $courses->sale_price }}">
                    @error('sale_price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
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
                    <select name="status" id=""
                        class="form-select {{ $errors->has('status') ? 'is-invalid' : '' }}">
                        <option value="0" {{ old('status') == 0 || $courses->status == 0 ? 'selected' : false }}>Chưa
                            ra mắt</option>
                        <option value="1" {{ old('status') == 1 || $courses->status == 1 ? 'selected' : false }}>Đã
                            ra
                            mắt</option>
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
                                    <img src="{{ old('thumbnail') ?? $courses->thumbnail }}" alt="">
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
    </script>
@endsection
