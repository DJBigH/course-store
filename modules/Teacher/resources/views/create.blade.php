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

        <div class="admin-form__header d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="mb-0">Thông tin giảng viên</h5>
                <p class="text-muted mb-0">Tạo hồ sơ giảng viên đa ngôn ngữ và thông tin hiển thị trên khóa học.</p>
            </div>

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
                            <label>Tên (VI)</label>
                            <input type="text" class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}"
                                name="name" placeholder="Tên..." value="{{ old('name', $teacher->name ?? '') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="mb-3">
                            <label>Slug (VI)</label>
                            <input type="text" class="form-control slug {{ $errors->has('slug') ? ' is-invalid' : '' }}"
                                name="slug" placeholder="Tự động tạo..." value="{{ old('slug', $teacher->slug ?? '') }}"
                                readonly>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            @foreach (['en' => 'EN', 'ko' => 'KO', 'ja' => 'JA', 'zh' => 'ZH'] as $locale => $label)
                <div class="col-12 lang-block lang-{{ $locale }} d-none">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Tên ({{ $label }})</label>
                                <input type="text" class="form-control title-{{ $locale }} {{ $errors->has('name_' . $locale) ? ' is-invalid' : '' }}"
                                    name="name_{{ $locale }}" placeholder="Name..." value="{{ old('name_' . $locale, $teacher->{'name_' . $locale} ?? '') }}">
                                @error('name_' . $locale)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Slug ({{ $label }})</label>
                                <input type="text" class="form-control slug-{{ $locale }} {{ $errors->has('slug_' . $locale) ? ' is-invalid' : '' }}"
                                    name="slug_{{ $locale }}" placeholder="Tự động tạo..." value="{{ old('slug_' . $locale, $teacher->{'slug_' . $locale} ?? '') }}"
                                    readonly>
                                @error('slug_' . $locale)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-12">
                <div class="mb-3">
                    <label>Kinh nghiệm</label>
                    <input type="number" class="form-control {{ $errors->has('exp') ? ' is-invalid' : '' }}" name="exp"
                        placeholder="Số năm kinh nghiệm..." value="{{ old('exp', $teacher->exp ?? '') }}">
                    @error('exp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @foreach (['vi' => 'description', 'en' => 'description_en', 'ko' => 'description_ko', 'ja' => 'description_ja', 'zh' => 'description_zh'] as $locale => $field)
                <div class="col-12">
                    <div class="lang-block lang-{{ $locale }} {{ $locale !== 'vi' ? 'd-none' : '' }}">
                        <div class="mb-3">
                            <label>Mô tả ({{ strtoupper($locale) }})</label>
                            <textarea name="{{ $field }}" class="form-control ckeditor {{ $errors->has($field) ? ' is-invalid' : '' }}"
                                cols="30" rows="10" placeholder="Mô tả...">{{ old($field, $teacher->{$field} ?? '') }}</textarea>
                            @error($field)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-12">
                <div class="mb-3">
                    <div class="row {{ $errors->has('image') ? 'g-2 align-items-center' : 'g-2 align-items-end' }}">
                        <label class="form-label">Hình ảnh</label>
                        <div class="col-7 position-relative">
                            <input type="text" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}"
                                name="image" placeholder="Ảnh đại diện..." id="image"
                                value="{{ old('image', $teacher->image ?? '') }}">

                            @error('image')
                                <div class="invalid-feedback position-absolute">{{ $message }}</div>
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
        (() => {
            const locales = ['vi', 'en', 'ko', 'ja', 'zh'];

            function showLang(lang) {
                document.querySelectorAll('.lang-block').forEach((el) => el.classList.add('d-none'));
                document.querySelectorAll('.lang-' + lang).forEach((el) => el.classList.remove('d-none'));
                localStorage.setItem('admin_teacher_lang', lang);
            }

            const saved = localStorage.getItem('admin_teacher_lang') || 'vi';
            const input = document.getElementById('lang_' + saved) || document.getElementById('lang_vi');

            if (input) {
                input.checked = true;
                showLang(saved);
            }

            locales.forEach((locale) => {
                document.getElementById('lang_' + locale)?.addEventListener('change', () => showLang(locale));
            });
        })();

        function toSlug(title) {
            return (title || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/[^\p{L}\p{N}\s-]/gu, '')
                .trim()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function bindSlug(titleSelector, slugSelector) {
            const title = document.querySelector(titleSelector);
            const slug = document.querySelector(slugSelector);
            if (!title || !slug) return;

            title.addEventListener('input', (e) => {
                if (!slug.dataset.manual) {
                    slug.value = toSlug(e.target.value);
                }
            });

            slug.addEventListener('input', () => {
                slug.dataset.manual = '1';
            });
        }

        bindSlug('.title', '.slug');
        bindSlug('.title-en', '.slug-en');
        bindSlug('.title-ko', '.slug-ko');
        bindSlug('.title-ja', '.slug-ja');
        bindSlug('.title-zh', '.slug-zh');
    </script>
@endsection
