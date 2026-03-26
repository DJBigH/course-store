@extends('layouts.backend')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            Vui lòng kiểm tra lại dữ liệu đã nhập.
        </div>
    @endif
    <form action="" method="post" class="admin-form">
        @csrf
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
        <div class="row">
            <div class="col-12 lang-block lang-vi">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Tên (VI)</label>
                            <input type="text" class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                                placeholder="Tên..." value="{{ old('name') }}">
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
                                name="slug" placeholder="Auto Generate..." value="{{ old('slug') }}" readonly>
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
                                name="name_en" placeholder="Name..." value="{{ old('name_en') }}">
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (EN)</label>
                            <input type="text" class="form-control slug-en {{ $errors->has('slug_en') ? ' is-invalid' : '' }}"
                                name="slug_en" placeholder="Auto Generate..." value="{{ old('slug_en') }}" readonly>
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
                                name="name_ko" placeholder="Name..." value="{{ old('name_ko') }}">
                            @error('name_ko')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (KO)</label>
                            <input type="text" class="form-control slug-ko {{ $errors->has('slug_ko') ? ' is-invalid' : '' }}"
                                name="slug_ko" placeholder="Auto Generate..." value="{{ old('slug_ko') }}" readonly>
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
                                name="name_ja" placeholder="Name..." value="{{ old('name_ja') }}">
                            @error('name_ja')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (JA)</label>
                            <input type="text" class="form-control slug-ja {{ $errors->has('slug_ja') ? ' is-invalid' : '' }}"
                                name="slug_ja" placeholder="Auto Generate..." value="{{ old('slug_ja') }}" readonly>
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
                                name="name_zh" placeholder="Name..." value="{{ old('name_zh') }}">
                            @error('name_zh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="">Slug (ZH)</label>
                            <input type="text" class="form-control slug-zh {{ $errors->has('slug_zh') ? ' is-invalid' : '' }}"
                                name="slug_zh" placeholder="Auto Generate..." value="{{ old('slug_zh') }}" readonly>
                            @error('slug_zh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Cha</label>
                    <select name="parent_id" id=""
                        class="form-select{{ $errors->has('parent_id') ? ' is-invalid' : '' }}">
                        <option value="0">Không có</option>
                        {{ getCategories($categories, old('parent_id')) }}
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12 text-end admin-form__footer">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('categories.index') }}" class="btn btn-warning">Trở về</a>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        function getSlugVI(title) {
            let slug = (title || '').toLowerCase();
            slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, "a");
            slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, "e");
            slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, "i");
            slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, "o");
            slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, "u");
            slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, "y");
            slug = slug.replace(/đ/gi, "d");
            slug = slug.replace(/[^a-z0-9\s-]/g, "");
            slug = slug.replace(/\s+/g, "-").replace(/-+/g, "-").replace(/^-+|-+$/g, "");
            return slug;
        }

        function getSlugIntl(title) {
            return (title || '').toLowerCase().trim().replace(/[^\p{L}\p{N}\s-]/gu, '').replace(/\s+/g, '-')
                .replace(/-+/g, '-').replace(/^-+|-+$/g, '');
        }

        function bindAutoSlug(titleSelector, slugSelector, fn) {
            const titleEl = document.querySelector(titleSelector);
            const slugEl = document.querySelector(slugSelector);
            if (!titleEl || !slugEl) return;
            titleEl.addEventListener('input', (e) => {
                if (!slugEl.dataset.manual) slugEl.value = fn(e.target.value);
            });
            slugEl.addEventListener('input', () => {
                slugEl.dataset.manual = '1';
            });
        }

        bindAutoSlug('.title', '.slug', getSlugVI);
        bindAutoSlug('.title-en', '.slug-en', getSlugIntl);
        bindAutoSlug('.title-ko', '.slug-ko', (title) => getSlugByLocale(title, 'ko'));
        bindAutoSlug('.title-ja', '.slug-ja', (title) => getSlugByLocale(title, 'ja'));
        bindAutoSlug('.title-zh', '.slug-zh', (title) => getSlugByLocale(title, 'zh'));

        (function() {
            const viBtn = document.getElementById('lang_vi');
            const enBtn = document.getElementById('lang_en');
            const koBtn = document.getElementById('lang_ko');
            const jaBtn = document.getElementById('lang_ja');
            const zhBtn = document.getElementById('lang_zh');

            function showLang(lang) {
                document.querySelectorAll('.lang-block').forEach(el => el.classList.add('d-none'));
                document.querySelectorAll('.lang-' + lang).forEach(el => el.classList.remove('d-none'));
                localStorage.setItem('admin_category_lang', lang);
            }

            const saved = localStorage.getItem('admin_category_lang') || 'vi';
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
    </script>
@endsection
