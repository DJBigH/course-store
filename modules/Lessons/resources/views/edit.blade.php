@extends('layouts.backend')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            Vui lòng kiểm tra lại dữ liệu đã nhập.
        </div>
    @endif
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    <form action="" method="post">
        @csrf
        {{-- Header + switch language --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5></h5>
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="content_lang" id="lang_vi" checked>
                <label class="btn btn-outline-primary" for="lang_vi">VI</label>

                <input type="radio" class="btn-check" name="content_lang" id="lang_en">
                <label class="btn btn-outline-primary" for="lang_en">EN</label>
            </div>
        </div>
        <div class="row">
            <div class="col-12 lang-block lang-vi">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Tên (VI)</label>
                            <input type="text" class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}"
                                name="name" placeholder="Tên..." value="{{ old('name',$lesson->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (VI)</label>
                            <input type="text" class="form-control slug {{ $errors->has('slug') ? ' is-invalid' : '' }}"
                                name="slug" placeholder="Auto Generate..." value="{{ old('slug',$lesson->slug) }}" readonly>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- EN --}}
            <div class="col-12 lang-block lang-en d-none">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Name (EN)</label>
                            <input type="text"
                                class="form-control title-en {{ $errors->has('name_en') ? ' is-invalid' : '' }}"
                                name="name_en" placeholder="Lesson name..." value="{{ old('name_en',$lesson->name_en) }}">
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (EN)</label>
                            <input type="text"
                                class="form-control slug-en {{ $errors->has('slug_en') ? ' is-invalid' : '' }}"
                                name="slug_en" placeholder="Auto Generate..." value="{{ old('slug_en',$lesson->slug_en) }}" readonly>
                            @error('slug_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-4">
                <div class="mb-3">
                    <label for="">Nhóm bài giảng</label>
                    <select name="parent_id" id=""
                        class="form-select select2 {{ $errors->has('parent_id') ? 'is-invalid' : '' }}">
                        <option value="0">Trống</option>
                        {{ getLessons($lessons, old('parent_id', $lesson->parent_id)) }}
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-4">
                <div class="mb-3">
                    <label for="">Học thử</label>
                    <select name="is_trial" id=""
                        class="form-select {{ $errors->has('is_trial') ? ' is-invalid' : '' }}">
                        <option value="0" {{ old('is_trial', $lesson->is_trial) == 0 ? 'selected' : '' }}>Không
                        </option>
                        <option value="1" {{ old('is_trial', $lesson->is_trial) == 1 ? 'selected' : '' }}>Có</option>
                    </select>
                    @error('is_trial')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-4">
                <div class="mb-3">
                    <label for="">Sắp xếp</label>
                    <input type="number" class="form-control {{ $errors->has('position') ? ' is-invalid' : '' }}"
                        name="position" placeholder="Thứ tự..." value="{{ old('position', $lesson->position) }}">
                    @error('position')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Video</label>
                    <div class="input-group {{ $errors->has('video') ? ' is-invalid' : '' }}">
                        <input type="text" name="video" class="form-control" id="video_url"
                            placeholder="Video bài giảng (Bấm chọn hoặc gắn link)..."
                            value="{{ old('video', $lesson->video) }}">
                        <button type="button" class="btn btn-success" id="lfm-video" data-input="video_url">Chọn</button>
                    </div>
                    @error('video')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Tài liệu</label>
                    <div class="input-group {{ $errors->has('document') ? ' is-invalid' : '' }}">
                        <input type="text" class="form-control" name="document" id="document_url"
                            placeholder="Tài liệu bài giảng (Bấm chọn hoặc gắn link)..."
                            value="{{ old('document', $lesson->document) }}">
                        <button type="button" class="btn btn-success" id="lfm-document"
                            data-input="document_url">Chọn</button>
                    </div>
                    @error('document')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            {{-- Description VI --}}
            <div class="col-12 lang-block lang-vi">
                <div class="mb-3">
                    <label for="">Mô tả (VI)</label>
                    <textarea name="description" class="form-control ckeditor {{ $errors->has('description') ? ' is-invalid' : '' }}">{{ old('description',$lesson->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Description EN --}}
            <div class="col-12 lang-block lang-en d-none">
                <div class="mb-3">
                    <label for="">Description (EN)</label>
                    <textarea name="description_en"
                        class="form-control ckeditor {{ $errors->has('description_en') ? ' is-invalid' : '' }}">{{ old('description_en',$lesson->description_en) }}</textarea>
                    @error('description_en')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label>Trạng thái</label>
                    <label class="d-block">
                        <input type="checkbox" name="status" value="1"
                            {{ old('status', $lesson->status) ? 'checked' : '' }}> Kích
                        hoạt
                    </label>
                    @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('lessons.index', $courseId) }}" class="btn btn-danger">Trở về</a>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
    <script>
        // --- slug helper (VI: bỏ dấu, EN: basic) ---
        function getSlugVI(title) {
            let slug = (title || '').toLowerCase();

            slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, "a");
            slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, "e");
            slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, "i");
            slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, "o");
            slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, "u");
            slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, "y");
            slug = slug.replace(/đ/gi, "d");

            slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, "");
            slug = slug.replace(/\s+/g, "-");
            slug = slug.replace(/-+/g, "-");
            slug = slug.replace(/^-+|-+$/g, "");
            return slug;
        }

        function getSlugEN(title) {
            let slug = (title || '').toLowerCase();
            slug = slug.replace(/[^a-z0-9\s-]/g, "");
            slug = slug.replace(/\s+/g, "-");
            slug = slug.replace(/-+/g, "-");
            slug = slug.replace(/^-+|-+$/g, "");
            return slug;
        }

        function bindAutoSlug(titleSelector, slugSelector, fn) {
            const titleEl = document.querySelector(titleSelector);
            const slugEl = document.querySelector(slugSelector);
            if (!titleEl || !slugEl) return;

            let changed = false;

            if (!slugEl.value) {
                titleEl.addEventListener('keyup', (e) => {
                    if (!changed) slugEl.value = fn(e.target.value);
                });
            }

            slugEl.addEventListener('change', () => {
                if (!slugEl.value) slugEl.value = fn(titleEl.value);
                changed = true;
            });
        }

        // bind VI + EN
        bindAutoSlug('.title', '.slug', getSlugVI);
        bindAutoSlug('.title-en', '.slug-en', getSlugEN);

        // toggle lang blocks + remember
        (function() {
            const viBtn = document.getElementById('lang_vi');
            const enBtn = document.getElementById('lang_en');

            function showLang(lang) {
                document.querySelectorAll('.lang-block').forEach(el => el.classList.add('d-none'));
                document.querySelectorAll('.lang-' + lang).forEach(el => el.classList.remove('d-none'));
                localStorage.setItem('admin_lesson_lang', lang);
            }

            const saved = localStorage.getItem('admin_lesson_lang') || 'vi';
            if (saved === 'en') enBtn.checked = true;
            showLang(saved);

            viBtn.addEventListener('change', () => showLang('vi'));
            enBtn.addEventListener('change', () => showLang('en'));
        })();
    </script>
@endsection
