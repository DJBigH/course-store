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
            <div>
                <h5 class="mb-1">Thêm danh mục</h5>
                <p class="text-muted mb-0">Tạo danh mục đa ngôn ngữ và sắp xếp quan hệ cha con ngay trong một màn hình.</p>
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
                            <input type="text" class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                                placeholder="Tên..." value="{{ old('name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="mb-3">
                            <label>Slug (VI)</label>
                            <input type="text" class="form-control slug {{ $errors->has('slug') ? ' is-invalid' : '' }}"
                                name="slug" placeholder="Tự động tạo..." value="{{ old('slug') }}" readonly>
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
                                    name="name_{{ $locale }}" placeholder="Name..." value="{{ old('name_' . $locale) }}">
                                @error('name_' . $locale)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Slug ({{ $label }})</label>
                                <input type="text" class="form-control slug-{{ $locale }} {{ $errors->has('slug_' . $locale) ? ' is-invalid' : '' }}"
                                    name="slug_{{ $locale }}" placeholder="Tự động tạo..." value="{{ old('slug_' . $locale) }}" readonly>
                                @error('slug_' . $locale)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-6">
                <div class="mb-3">
                    <label>Danh mục cha</label>
                    <select name="parent_id" class="form-select{{ $errors->has('parent_id') ? ' is-invalid' : '' }}">
                        <option value="0">Không có</option>
                        {{ getCategories($categories, old('parent_id')) }}
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
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

        function bindAutoSlug(titleSelector, slugSelector) {
            const titleEl = document.querySelector(titleSelector);
            const slugEl = document.querySelector(slugSelector);

            if (!titleEl || !slugEl) {
                return;
            }

            titleEl.addEventListener('input', (e) => {
                if (!slugEl.dataset.manual) {
                    slugEl.value = toSlug(e.target.value);
                }
            });

            slugEl.addEventListener('input', () => {
                slugEl.dataset.manual = '1';
            });
        }

        bindAutoSlug('.title', '.slug');
        bindAutoSlug('.title-en', '.slug-en');
        bindAutoSlug('.title-ko', '.slug-ko');
        bindAutoSlug('.title-ja', '.slug-ja');
        bindAutoSlug('.title-zh', '.slug-zh');

        (() => {
            const locales = ['vi', 'en', 'ko', 'ja', 'zh'];

            function showLang(lang) {
                document.querySelectorAll('.lang-block').forEach((el) => el.classList.add('d-none'));
                document.querySelectorAll('.lang-' + lang).forEach((el) => el.classList.remove('d-none'));
                localStorage.setItem('admin_category_lang', lang);
            }

            const saved = localStorage.getItem('admin_category_lang') || 'vi';
            const input = document.getElementById('lang_' + saved) || document.getElementById('lang_vi');

            if (input) {
                input.checked = true;
                showLang(saved);
            }

            locales.forEach((locale) => {
                document.getElementById('lang_' + locale)?.addEventListener('change', () => showLang(locale));
            });
        })();
    </script>
@endsection
