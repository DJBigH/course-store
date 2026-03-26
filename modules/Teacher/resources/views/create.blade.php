@extends('layouts.backend')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="" method="post" class="admin-form">
        @csrf

        {{-- Toggle ngôn ngữ nội dung --}}
        <div class="admin-form__header d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Thông tin giảng viên</h5>

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

        <div class="row">
            <div class="col-12 lang-block lang-vi">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Tên (VI)</label>
                            <input type="text" class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}"
                                name="name" placeholder="Tên..." value="{{ old('name', $teacher->name ?? '') }}">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (VI)</label>
                            <input type="text" class="form-control slug {{ $errors->has('slug') ? ' is-invalid' : '' }}"
                                name="slug" placeholder="Auto Generate..." value="{{ old('slug', $teacher->slug ?? '') }}"
                                readonly>
                            @error('slug')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 lang-block lang-en d-none">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Tên (EN)</label>
                            <input type="text" class="form-control title-en {{ $errors->has('name_en') ? ' is-invalid' : '' }}"
                                name="name_en" placeholder="Name..." value="{{ old('name_en', $teacher->name_en ?? '') }}">
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (EN)</label>
                            <input type="text" class="form-control slug-en {{ $errors->has('slug_en') ? ' is-invalid' : '' }}"
                                name="slug_en" placeholder="Auto Generate..." value="{{ old('slug_en', $teacher->slug_en ?? '') }}"
                                readonly>
                            @error('slug_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 lang-block lang-ko d-none">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Tên (KO)</label>
                            <input type="text" class="form-control title-ko {{ $errors->has('name_ko') ? ' is-invalid' : '' }}"
                                name="name_ko" placeholder="Name..." value="{{ old('name_ko', $teacher->name_ko ?? '') }}">
                            @error('name_ko')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (KO)</label>
                            <input type="text" class="form-control slug-ko {{ $errors->has('slug_ko') ? ' is-invalid' : '' }}"
                                name="slug_ko" placeholder="Auto Generate..." value="{{ old('slug_ko', $teacher->slug_ko ?? '') }}"
                                readonly>
                            @error('slug_ko')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 lang-block lang-ja d-none">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Tên (JA)</label>
                            <input type="text" class="form-control title-ja {{ $errors->has('name_ja') ? ' is-invalid' : '' }}"
                                name="name_ja" placeholder="Name..." value="{{ old('name_ja', $teacher->name_ja ?? '') }}">
                            @error('name_ja')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (JA)</label>
                            <input type="text" class="form-control slug-ja {{ $errors->has('slug_ja') ? ' is-invalid' : '' }}"
                                name="slug_ja" placeholder="Auto Generate..." value="{{ old('slug_ja', $teacher->slug_ja ?? '') }}"
                                readonly>
                            @error('slug_ja')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 lang-block lang-zh d-none">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Tên (ZH)</label>
                            <input type="text" class="form-control title-zh {{ $errors->has('name_zh') ? ' is-invalid' : '' }}"
                                name="name_zh" placeholder="Name..." value="{{ old('name_zh', $teacher->name_zh ?? '') }}">
                            @error('name_zh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (ZH)</label>
                            <input type="text" class="form-control slug-zh {{ $errors->has('slug_zh') ? ' is-invalid' : '' }}"
                                name="slug_zh" placeholder="Auto Generate..." value="{{ old('slug_zh', $teacher->slug_zh ?? '') }}"
                                readonly>
                            @error('slug_zh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- EXP (chung) --}}
            <div class="col-12">
                <div class="mb-3">
                    <label for="">Kinh nghiệm</label>
                    <input type="number" class="form-control {{ $errors->has('exp') ? ' is-invalid' : '' }}" name="exp"
                        placeholder="Kinh nghiệm..." value="{{ old('exp', $teacher->exp ?? '') }}">
                    @error('exp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- DESCRIPTION (VI/EN) --}}
            <div class="col-12">
                {{-- VI --}}
                <div class="lang-block lang-vi">
                    <div class="mb-3">
                        <label for="">Mô tả (VI)</label>
                        <textarea name="description" class="form-control ckeditor {{ $errors->has('description') ? ' is-invalid' : '' }}"
                            cols="30" rows="10" placeholder="Mô tả...">{{ old('description', $teacher->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- EN --}}
                <div class="lang-block lang-en d-none">
                    <div class="mb-3">
                        <label for="">Mô tả (EN)</label>
                        <textarea name="description_en" class="form-control ckeditor {{ $errors->has('description_en') ? ' is-invalid' : '' }}"
                            cols="30" rows="10" placeholder="Mô tả...">{{ old('description_en', $teacher->description_en ?? '') }}</textarea>
                        @error('description_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="lang-block lang-ko d-none">
                    <div class="mb-3">
                        <label for="">Mô tả (KO)</label>
                        <textarea name="description_ko" class="form-control ckeditor {{ $errors->has('description_ko') ? ' is-invalid' : '' }}"
                            cols="30" rows="10" placeholder="Mô tả...">{{ old('description_ko', $teacher->description_ko ?? '') }}</textarea>
                        @error('description_ko')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="lang-block lang-ja d-none">
                    <div class="mb-3">
                        <label for="">Mô tả (JA)</label>
                        <textarea name="description_ja" class="form-control ckeditor {{ $errors->has('description_ja') ? ' is-invalid' : '' }}"
                            cols="30" rows="10" placeholder="Mô tả...">{{ old('description_ja', $teacher->description_ja ?? '') }}</textarea>
                        @error('description_ja')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="lang-block lang-zh d-none">
                    <div class="mb-3">
                        <label for="">Mô tả (ZH)</label>
                        <textarea name="description_zh" class="form-control ckeditor {{ $errors->has('description_zh') ? ' is-invalid' : '' }}"
                            cols="30" rows="10" placeholder="Mô tả...">{{ old('description_zh', $teacher->description_zh ?? '') }}</textarea>
                        @error('description_zh')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- IMAGE (chung) --}}
            <div class="col-12">
                <div class="mb-3">
                    <div class="row {{ $errors->has('image') ? 'g-2 align-items-center' : 'g-2 align-items-end' }}">
                        <label class="form-label">Hình ảnh</label>
                        <div class="col-7 position-relative">
                            <input type="text" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}"
                                name="image" placeholder="Ảnh đại diện..." id="image"
                                value="{{ old('image', $teacher->image ?? '') }}">

                            @error('image')
                                <div class="invalid-feedback position-absolute">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-2 d-grid">
                            <button type="button" class="btn btn-primary" id="lfm" data-input="image"
                                data-preview="holder">
                                Chọn ảnh <i class="fa-solid fa-file-arrow-up"></i>
                            </button>
                        </div>

                        <div class="col-3">
                            <div id="holder" class="rounded p-1 text-center">
                                @if (old('image', $teacher->image ?? null))
                                    <img src="{{ old('image', $teacher->image ?? '') }}" class="img-fluid">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="col-12 text-end admin-form__footer">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('teacher.index') }}" class="btn btn-warning">Trở về</a>
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
    </style>
@endsection

@section('scripts')
    <script>
        // Toggle VI/EN
        (function() {
            const viBtn = document.getElementById('lang_vi');
            const enBtn = document.getElementById('lang_en');
            const koBtn = document.getElementById('lang_ko');
            const jaBtn = document.getElementById('lang_ja');
            const zhBtn = document.getElementById('lang_zh');

            function showLang(lang) {
                document.querySelectorAll('.lang-block').forEach(el => el.classList.add('d-none'));
                document.querySelectorAll('.lang-' + lang).forEach(el => el.classList.remove('d-none'));
                localStorage.setItem('admin_teacher_lang', lang);
            }

            const saved = localStorage.getItem('admin_teacher_lang') || 'vi';
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

        function getSlugIntl(title) {
            return (title || '').toLowerCase().trim().replace(/[^\p{L}\p{N}\s-]/gu, '').replace(/\s+/g, '-')
                .replace(/-+/g, '-').replace(/^-+|-+$/g, '');
        }

        function bindSlug(titleSelector, slugSelector, slugFn) {
            const title = document.querySelector(titleSelector);
            const slug = document.querySelector(slugSelector);
            if (!title || !slug) return;

            title.addEventListener('input', (e) => {
                if (slug.value.trim() === '') {
                    slug.value = slugFn(e.target.value);
                }
            });
        }

        bindSlug('.title', '.slug', getSlugVI);
        bindSlug('.title-en', '.slug-en', getSlugIntl);
        bindSlug('.title-ko', '.slug-ko', (title) => getSlugByLocale(title, 'ko'));
        bindSlug('.title-ja', '.slug-ja', (title) => getSlugByLocale(title, 'ja'));
        bindSlug('.title-zh', '.slug-zh', (title) => getSlugByLocale(title, 'zh'));
    </script>
@endsection
