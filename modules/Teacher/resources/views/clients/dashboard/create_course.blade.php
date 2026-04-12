@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <div class="teacher-panel">
            <div class="teacher-section-title mb-4">
                <div>
                    <h3 class="fw-bold mb-2">{{ $course ? __('teacher::dashboard.courses.edit_title') : __('teacher::dashboard.courses.create_title') }}</h3>
                    <p class="text-muted mb-0">{{ $course ? __('teacher::dashboard.courses.edit_description') : __('teacher::dashboard.courses.create_description') }}</p>
                    @if ($course?->package_locked_at)
                        <div class="teacher-limit-lock mt-3">
                            <span class="teacher-limit-lock__badge">{{ __('teacher::dashboard.courses.labels.limited_actions_only') }}</span>
                            <div class="teacher-limit-lock__text">{{ __('teacher::dashboard.courses.warnings.locked_manage_only') }}</div>
                        </div>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if ($usage)
                        <span class="teacher-chip">
                            {{ __('teacher::dashboard.courses.package_usage', ['used' => $usage['used'], 'limit' => $usage['limit_label']]) }}
                        </span>
                    @endif
                    <a href="{{ route('teacher.dashboard.courses') }}" class="btn btn-outline-secondary">
                        {{ __('teacher::dashboard.common.back') }}
                    </a>
                </div>
            </div>

            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif

            @if ($usage && ($usage['has_limit'] ?? false) && ($usage['is_over_limit'] ?? false))
                <div class="alert alert-warning">
                    {{ __('teacher::dashboard.courses.warnings.publish_limit_over', ['count' => $usage['over_limit_by'] ?? 0]) }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ __('teacher::dashboard.common.validation_summary') }}</div>
            @endif

            <form method="POST" action="{{ $formAction }}" class="teacher-course-form">
                @csrf

                <div class="teacher-panel mb-4">
                    <div class="teacher-section-title mb-3">
                        <div>
                            <h4 class="h5 mb-1">{{ __('teacher::dashboard.courses.content_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('teacher::dashboard.courses.content_description') }}</p>
                        </div>
                        <div class="btn-group" role="group" aria-label="Course language tabs">
                            <input type="radio" class="btn-check" name="content_lang" id="teacher_course_lang_vi" checked>
                            <label class="btn btn-outline-primary" for="teacher_course_lang_vi">VI</label>

                            <input type="radio" class="btn-check" name="content_lang" id="teacher_course_lang_en">
                            <label class="btn btn-outline-primary" for="teacher_course_lang_en">EN</label>

                            <input type="radio" class="btn-check" name="content_lang" id="teacher_course_lang_ko">
                            <label class="btn btn-outline-primary" for="teacher_course_lang_ko">KO</label>

                            <input type="radio" class="btn-check" name="content_lang" id="teacher_course_lang_ja">
                            <label class="btn btn-outline-primary" for="teacher_course_lang_ja">JA</label>

                            <input type="radio" class="btn-check" name="content_lang" id="teacher_course_lang_zh">
                            <label class="btn btn-outline-primary" for="teacher_course_lang_zh">ZH</label>
                        </div>
                    </div>

                    @foreach (['vi', 'en', 'ko', 'ja', 'zh'] as $locale)
                        @php
                            $suffix = $locale === 'vi' ? '' : '_' . $locale;
                            $isDefault = $locale === 'vi';
                            $nameField = 'name' . $suffix;
                            $supportsField = 'supports' . $suffix;
                            $detailField = 'detail' . $suffix;
                        @endphp
                        <div class="teacher-lang-block {{ $isDefault ? '' : 'd-none' }}" data-lang-block="{{ $locale }}">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">
                                        {{ __('teacher::dashboard.courses.form.name_label', ['locale' => strtoupper($locale)]) }}
                                    </label>
                                    <input type="text" name="{{ $nameField }}" class="form-control @error($nameField) is-invalid @enderror"
                                        value="{{ old($nameField, data_get($course, $nameField)) }}">
                                    @error($nameField)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">
                                        {{ __('teacher::dashboard.courses.form.supports_label', ['locale' => strtoupper($locale)]) }}
                                    </label>
                                    <textarea name="{{ $supportsField }}" class="form-control @error($supportsField) is-invalid @enderror"
                                        rows="4">{{ old($supportsField, data_get($course, $supportsField)) }}</textarea>
                                    @error($supportsField)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">
                                        {{ __('teacher::dashboard.courses.form.detail_label', ['locale' => strtoupper($locale)]) }}
                                    </label>
                                    <textarea name="{{ $detailField }}" class="form-control @error($detailField) is-invalid @enderror"
                                        rows="7">{{ old($detailField, data_get($course, $detailField)) }}</textarea>
                                    @error($detailField)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row g-4">
                    <div class="col-xl-7">
                        <div class="teacher-panel h-100">
                            <h4 class="h5 mb-3">{{ __('teacher::dashboard.courses.settings_title') }}</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::dashboard.courses.form.thumbnail') }}</label>
                                    <input type="text" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror"
                                        value="{{ old('thumbnail', $course?->thumbnail) }}" placeholder="https://...">
                                    @error('thumbnail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::dashboard.courses.form.code') }}</label>
                                    <div class="input-group">
                                        <input type="text" name="code" id="teacher_course_code"
                                            class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $course?->code) }}"
                                            placeholder="KH-...">
                                        <button type="button" class="btn btn-outline-secondary" id="teacherRandomCode">
                                            <i class="fa-solid fa-shuffle"></i>
                                        </button>
                                    </div>
                                    @error('code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::dashboard.courses.form.price') }}</label>
                                    <input type="hidden" name="price" id="teacher-course-price" value="{{ old('price', $course?->price ?? 0) }}">
                                    <div class="teacher-money-input">
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            class="form-control @error('price') is-invalid @enderror"
                                            id="teacher-course-price-display"
                                            value="{{ old('price', $course?->price ?? 0) }}"
                                            data-money-input
                                            data-money-target="teacher-course-price"
                                            placeholder="0"
                                        >
                                        <span class="teacher-money-input__unit">đ</span>
                                    </div>
                                    <small class="text-muted d-block mt-2">Gia toi da 99,999,999d.</small>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::dashboard.courses.form.sale_price') }}</label>
                                    <input type="hidden" name="sale_price" id="teacher-course-sale-price" value="{{ old('sale_price', $course?->sale_price ?? 0) }}">
                                    <div class="teacher-money-input">
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            class="form-control @error('sale_price') is-invalid @enderror"
                                            id="teacher-course-sale-price-display"
                                            value="{{ old('sale_price', $course?->sale_price ?? 0) }}"
                                            data-money-input
                                            data-money-target="teacher-course-sale-price"
                                            placeholder="0"
                                        >
                                        <span class="teacher-money-input__unit">đ</span>
                                    </div>
                                    <small class="text-muted d-block mt-2">Khong duoc lon hon gia goc.</small>
                                    <div class="invalid-feedback d-none" id="teacher-course-sale-price-realtime-error">
                                        Gia khuyen mai khong duoc lon hon gia goc.
                                    </div>
                                    @error('sale_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher::dashboard.courses.form.status') }}</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="0" @selected(old('status', $course?->status ?? 0) == 0)>{{ __('teacher::dashboard.courses.status.draft') }}</option>
                                        <option value="1" @selected(old('status', $course?->status ?? 0) == 1)>{{ __('teacher::dashboard.courses.status.published') }}</option>
                                    </select>
                                    @if ($usage && ($usage['has_limit'] ?? false))
                                        <small class="text-muted d-block mt-2">
                                            {{ __('teacher::dashboard.courses.warnings.publish_limit_summary', [
                                                'published' => $usage['published'] ?? $usage['used'] ?? 0,
                                                'total' => $usage['total'] ?? $usage['used'] ?? 0,
                                                'limit' => $usage['limit_label'] ?? __('teacher::dashboard.courses.unlimited'),
                                            ]) }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher::dashboard.courses.form.is_document') }}</label>
                                    <select name="is_document" class="form-select @error('is_document') is-invalid @enderror">
                                        <option value="0" @selected(old('is_document', $course?->is_document ?? 0) == 0)>{{ __('teacher::dashboard.common.no') }}</option>
                                        <option value="1" @selected(old('is_document', $course?->is_document ?? 0) == 1)>{{ __('teacher::dashboard.common.yes') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher::dashboard.courses.form.is_learning_locked') }}</label>
                                    <select name="is_learning_locked" class="form-select @error('is_learning_locked') is-invalid @enderror">
                                        <option value="0" @selected(old('is_learning_locked', $course?->is_learning_locked ?? 0) == 0)>{{ __('teacher::dashboard.courses.locking.open') }}</option>
                                        <option value="1" @selected(old('is_learning_locked', $course?->is_learning_locked ?? 0) == 1)>{{ __('teacher::dashboard.courses.locking.locked') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="teacher-panel h-100">
                            <h4 class="h5 mb-3">{{ __('teacher::dashboard.courses.categories_title') }}</h4>
                            <p class="text-muted">{{ __('teacher::dashboard.courses.categories_description') }}</p>

                            <div class="teacher-category-list">
                                @foreach ($categories as $category)
                                    @include('teacher::clients.dashboard.partials.category_checkbox', [
                                        'category' => $category,
                                        'selected' => old('categories', $selectedCategories ?? []),
                                        'depth' => 0,
                                    ])
                                @endforeach
                            </div>

                            @error('categories')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <a href="{{ route('teacher.dashboard.courses') }}" class="btn btn-outline-secondary">
                        {{ __('teacher::dashboard.common.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ $submitLabel }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-limit-lock__badge {
            display: inline-flex;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: rgba(248, 113, 113, 0.14);
            color: #b91c1c;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .teacher-limit-lock__text {
            margin-top: 0.6rem;
            color: var(--admin-warning, #b45309);
            font-weight: 600;
        }

        .teacher-course-form .teacher-panel {
            overflow: visible;
        }

        .teacher-money-input {
            position: relative;
        }

        .teacher-money-input .form-control {
            padding-right: 2.75rem;
        }

        .teacher-money-input__unit {
            position: absolute;
            top: 50%;
            right: 0.95rem;
            transform: translateY(-50%);
            color: var(--admin-muted);
            font-weight: 700;
            pointer-events: none;
        }

        .teacher-category-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            max-height: 440px;
            overflow: auto;
            padding-right: 0.35rem;
        }

        .teacher-category-item {
            border: 1px solid var(--admin-border);
            border-radius: 16px;
            padding: 0.8rem 0.95rem;
            background: var(--admin-subtle-bg);
        }

        .teacher-category-item__label {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            font-weight: 600;
            color: var(--admin-text);
        }

        .teacher-category-item__children {
            margin-top: 0.65rem;
            padding-left: 1rem;
            border-left: 1px dashed var(--admin-border);
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const supported = ['vi', 'en', 'ko', 'ja', 'zh'];
            const getLangInput = (locale) => document.getElementById(`teacher_course_lang_${locale}`);

            const showLang = (locale) => {
                document.querySelectorAll('[data-lang-block]').forEach((block) => {
                    block.classList.toggle('d-none', block.dataset.langBlock !== locale);
                });
                localStorage.setItem('teacher_course_lang', locale);
            };

            const saved = localStorage.getItem('teacher_course_lang');
            const initial = supported.includes(saved) ? saved : 'vi';
            const input = getLangInput(initial);
            if (input) {
                input.checked = true;
            }
            showLang(initial);

            supported.forEach((locale) => {
                const radio = getLangInput(locale);
                if (!radio) {
                    return;
                }
                radio.addEventListener('change', () => showLang(locale));
            });

            const randomCodeButton = document.getElementById('teacherRandomCode');
            const codeInput = document.getElementById('teacher_course_code');
            if (randomCodeButton && codeInput) {
                randomCodeButton.addEventListener('click', () => {
                    const random = Math.floor(100000 + Math.random() * 900000);
                    codeInput.value = `KH${random}`;
                });
            }

            const form = document.querySelector('.teacher-course-form');
            const submitButton = form?.querySelector('button[type="submit"]');
            const priceHidden = document.getElementById('teacher-course-price');
            const salePriceHidden = document.getElementById('teacher-course-sale-price');
            const salePriceDisplay = document.getElementById('teacher-course-sale-price-display');
            const realtimeError = document.getElementById('teacher-course-sale-price-realtime-error');

            const validateSalePrice = () => {
                if (!priceHidden || !salePriceHidden || !salePriceDisplay || !realtimeError) {
                    return true;
                }

                const price = Number(priceHidden.value || 0);
                const salePrice = Number(salePriceHidden.value || 0);
                const invalid = salePrice > price;

                salePriceDisplay.classList.toggle('is-invalid', invalid);
                realtimeError.classList.toggle('d-none', !invalid);

                if (submitButton) {
                    submitButton.disabled = invalid;
                }

                return !invalid;
            };

            document.addEventListener('teacher:money-input-sync', validateSalePrice);
            window.TeacherMoneyInput?.initAll(form || document);

            validateSalePrice();
        })();
    </script>
@endsection
