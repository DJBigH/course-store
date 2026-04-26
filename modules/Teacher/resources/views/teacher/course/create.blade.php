@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <div class="teacher-panel">
            <div class="teacher-section-title mb-4">
                <div>
                    <h3 class="fw-bold mb-2">{{ $course ? __('teacher::teacher/course/edit.edit_title') : __('teacher::teacher/course/add.create_title') }}</h3>
                    <p class="text-muted mb-0">{{ $course ? __('teacher::teacher/course/edit.edit_description') : __('teacher::teacher/course/add.create_description') }}</p>
                    @if ($course?->package_locked_at)
                        <div class="teacher-limit-lock mt-3">
                            <span class="teacher-limit-lock__badge">{{ __('teacher::teacher/course/common.labels.limited_actions_only') }}</span>
                            <div class="teacher-limit-lock__text">{{ __('teacher::teacher/course/common.warnings.locked_manage_only') }}</div>
                        </div>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if ($usage)
                        <span class="teacher-chip">
                            {{ __('teacher::teacher/course/add.package_usage', ['used' => $usage['used'], 'limit' => $usage['limit_label']]) }}
                        </span>
                    @endif
                    <a href="{{ route('teacher.dashboard.courses') }}" class="btn btn-outline-secondary">
                        {{ __('teacher::teacher/course/common.actions.back') }}
                    </a>
                </div>
            </div>

            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif

            @if ($usage && ($usage['has_limit'] ?? false) && ($usage['is_over_limit'] ?? false))
                <div class="alert alert-warning">
                    {{ __('teacher::teacher/course/common.warnings.publish_limit_over', ['count' => $usage['over_limit_by'] ?? 0]) }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ __('teacher::teacher/course/common.validation_summary') }}</div>
            @endif

            <form method="POST" action="{{ $formAction }}" class="teacher-course-form">
                @csrf

                <div class="teacher-panel mb-4">
                    <div class="teacher-section-title mb-3">
                        <div>
                            <h4 class="h5 mb-1">{{ __('teacher::teacher/course/add.content_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('teacher::teacher/course/add.content_description') }}</p>
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
                                        {{ __('teacher::teacher/course/common.form.name_label', ['locale' => strtoupper($locale)]) }}
                                    </label>
                                    <input type="text" name="{{ $nameField }}" class="form-control @error($nameField) is-invalid @enderror"
                                        value="{{ old($nameField, data_get($course, $nameField)) }}">
                                    @error($nameField)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">
                                        {{ __('teacher::teacher/course/common.form.supports_label', ['locale' => strtoupper($locale)]) }}
                                    </label>
                                    <textarea name="{{ $supportsField }}" class="form-control @error($supportsField) is-invalid @enderror"
                                        rows="4">{{ old($supportsField, data_get($course, $supportsField)) }}</textarea>
                                    @error($supportsField)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">
                                        {{ __('teacher::teacher/course/common.form.detail_label', ['locale' => strtoupper($locale)]) }}
                                    </label>
                                    <textarea name="{{ $detailField }}" class="form-control @error($detailField) is-invalid @enderror"
                                        rows="7">{{ old($detailField, data_get($course, $detailField)) }}</textarea>
                                    @error($detailField)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">
                                        {{ __('teacher::teacher/course/common.form.price_label', ['locale' => strtoupper($locale)]) }}
                                    </label>
                                    @php
                                        $priceField = 'price' . $suffix;
                                        $currency = match($locale) {
                                            'en' => 'USD',
                                            'ko' => 'KRW',
                                            'ja' => 'JPY',
                                            'zh' => 'CNY',
                                            default => 'VND'
                                        };
                                        $symbol = match($locale) {
                                            'en' => '$',
                                            'ko' => '₩',
                                            'ja' => '¥',
                                            'zh' => '元',
                                            default => '₫'
                                        };
                                    @endphp
                                    <div class="teacher-money-input">
                                        <input type="hidden" name="{{ $priceField }}" id="teacher-course-{{ $priceField }}" 
                                            value="{{ old($priceField, data_get($course, $priceField, 0)) }}">
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            class="form-control @error($priceField) is-invalid @enderror"
                                            id="teacher-course-{{ $priceField }}-display"
                                            value="{{ old($priceField, data_get($course, $priceField, 0)) }}"
                                            data-money-input
                                            data-money-target="teacher-course-{{ $priceField }}"
                                            data-currency="{{ $currency }}"
                                            placeholder="0"
                                        >
                                        <span class="teacher-money-input__unit">{{ $symbol }}</span>
                                    </div>
                                    @error($priceField)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">
                                        {{ __('teacher::teacher/course/common.form.sale_price_label', ['locale' => strtoupper($locale)]) }}
                                    </label>
                                    @php
                                        $salePriceField = 'sale_price' . $suffix;
                                    @endphp
                                    <div class="teacher-money-input">
                                        <input type="hidden" name="{{ $salePriceField }}" id="teacher-course-{{ $salePriceField }}" 
                                            value="{{ old($salePriceField, data_get($course, $salePriceField, 0)) }}">
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            class="form-control @error($salePriceField) is-invalid @enderror"
                                            id="teacher-course-{{ $salePriceField }}-display"
                                            value="{{ old($salePriceField, data_get($course, $salePriceField, 0)) }}"
                                            data-money-input
                                            data-money-target="teacher-course-{{ $salePriceField }}"
                                            data-currency="{{ $currency }}"
                                            placeholder="0"
                                        >
                                        <span class="teacher-money-input__unit">{{ $symbol }}</span>
                                    </div>
                                    @error($salePriceField)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row g-4">
                    <div class="col-xl-7">
                        <div class="teacher-panel h-100">
                            <h4 class="h5 mb-3">{{ __('teacher::teacher/course/common.settings_title') }}</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::teacher/course/common.form.thumbnail') }}</label>
                                    <input type="text" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror"
                                        value="{{ old('thumbnail', $course?->thumbnail) }}" placeholder="https://...">
                                    @error('thumbnail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('teacher::teacher/course/common.form.code') }}</label>
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
                                    <label class="form-label">{{ __('teacher::teacher/course/common.form.status') }}</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="0" @selected(old('status', $course?->status ?? 0) == 0)>{{ __('teacher::teacher/course/common.status.draft') }}</option>
                                        <option value="1" @selected(old('status', $course?->status ?? 0) == 1)>{{ __('teacher::teacher/course/common.status.published') }}</option>
                                    </select>
                                    @if ($usage && ($usage['has_limit'] ?? false))
                                        <small class="text-muted d-block mt-2">
                                            {{ __('teacher::teacher/course/common.warnings.publish_limit_summary', [
                                                'published' => $usage['published'] ?? $usage['used'] ?? 0,
                                                'total' => $usage['total'] ?? $usage['used'] ?? 0,
                                                'limit' => $usage['limit_label'] ?? __('teacher::teacher/course/list.unlimited'),
                                            ]) }}
                                        </small>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher::teacher/course/common.form.is_document') }}</label>
                                    <select name="is_document" class="form-select @error('is_document') is-invalid @enderror">
                                        <option value="0" @selected(old('is_document', $course?->is_document ?? 0) == 0)>{{ __('teacher::teacher/course/common.actions.no') }}</option>
                                        <option value="1" @selected(old('is_document', $course?->is_document ?? 0) == 1)>{{ __('teacher::teacher/course/common.actions.yes') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('teacher::teacher/course/common.form.is_learning_locked') }}</label>
                                    <select name="is_learning_locked" class="form-select @error('is_learning_locked') is-invalid @enderror">
                                        <option value="0" @selected(old('is_learning_locked', $course?->is_learning_locked ?? 0) == 0)>{{ __('teacher::teacher/course/common.locking.open') }}</option>
                                        <option value="1" @selected(old('is_learning_locked', $course?->is_learning_locked ?? 0) == 1)>{{ __('teacher::teacher/course/common.locking.locked') }}</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4 opacity-10">

                            <h4 class="h5 mb-3">{{ __('teacher::teacher/course/common.form.stock_label') }}</h4>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch mt-2">
                                        <input type="hidden" name="is_coming_soon" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_coming_soon" value="1" id="is_coming_soon"
                                            {{ old('is_coming_soon', $course?->is_coming_soon) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-warning" for="is_coming_soon">
                                            {{ __('teacher::teacher/course/common.form.is_coming_soon_label') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12" id="coming_soon_date_wrapper" style="display: none;">
                                    <label class="form-label text-warning small fw-bold">{{ __('teacher::teacher/course/common.form.coming_soon_start_at') }}</label>
                                    <input type="datetime-local" name="coming_soon_start_at" class="form-control border-warning"
                                        value="{{ old('coming_soon_start_at', $course?->coming_soon_start_at ? $course->coming_soon_start_at->format('Y-m-d\TH:i') : '') }}">
                                    <small class="text-warning-emphasis d-block mt-1">{{ __('teacher::teacher/course/common.form.coming_soon_hint') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="teacher-panel h-100">
                            <h4 class="h5 mb-3">{{ __('teacher::teacher/course/add.categories_title') }}</h4>
                            <p class="text-muted">{{ __('teacher::teacher/course/add.categories_description') }}</p>

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
                        {{ __('teacher::teacher/course/common.actions.cancel') }}
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
            const exchangeRates = @json($exchangeRates);
            const conversionFee = {{ $conversionFee }};
            let currentLocale = 'vi';

            const getLangInput = (locale) => document.getElementById(`teacher_course_lang_${locale}`);

            const showLang = (locale) => {
                currentLocale = locale;
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

            const getCurrencyByLocale = (locale) => {
                return {
                    'en': 'USD',
                    'ko': 'KRW',
                    'ja': 'JPY',
                    'zh': 'CNY',
                    'vi': 'VND'
                }[locale];
            };

            const convertPrice = (amount, fromCurrency, toCurrency) => {
                if (fromCurrency === toCurrency) return amount;
                if (!exchangeRates[fromCurrency] || !exchangeRates[toCurrency]) return amount;

                // Convert to USD base first
                const usdAmount = amount / exchangeRates[fromCurrency];
                let targetAmount = usdAmount * exchangeRates[toCurrency];

                // Apply fee if converting AWAY from the active base
                if (conversionFee > 0) {
                    targetAmount *= (1 + (conversionFee / 100));
                }

                if (['VND', 'KRW', 'JPY'].includes(toCurrency)) {
                    return Math.round(targetAmount);
                }
                return Math.round(targetAmount * 100) / 100;
            };

            const syncPrices = (sourceLocale, isSale = false) => {
                const prefix = isSale ? 'sale_price' : 'price';
                const sourceField = sourceLocale === 'vi' ? prefix : `${prefix}_${sourceLocale}`;
                const sourceValue = Number(document.getElementById(`teacher-course-${sourceField}`).value || 0);
                const fromCurrency = getCurrencyByLocale(sourceLocale);

                supported.forEach(targetLocale => {
                    if (targetLocale === sourceLocale) return;

                    const targetField = targetLocale === 'vi' ? prefix : `${prefix}_${targetLocale}`;
                    const toCurrency = getCurrencyByLocale(targetLocale);
                    const convertedValue = convertPrice(sourceValue, fromCurrency, toCurrency);

                    const targetInput = document.getElementById(`teacher-course-${targetField}`);
                    const targetDisplay = document.getElementById(`teacher-course-${targetField}-display`);

                    if (targetInput && targetDisplay) {
                        targetInput.value = convertedValue;
                        targetDisplay.value = convertedValue;
                        // Trigger money input formatting if available
                        window.TeacherMoneyInput?.formatElement(targetDisplay);
                    }
                });
            };

            const form = document.querySelector('.teacher-course-form');
            
            // Listen for changes in price fields
            supported.forEach(locale => {
                const suffix = locale === 'vi' ? '' : `_${locale}`;
                
                const priceDisplay = document.getElementById(`teacher-course-price${suffix}-display`);
                const salePriceDisplay = document.getElementById(`teacher-course-sale_price${suffix}-display`);

                if (priceDisplay) {
                    priceDisplay.addEventListener('change', () => {
                        if (currentLocale === locale) {
                            syncPrices(locale, false);
                        }
                    });
                }

                if (salePriceDisplay) {
                    salePriceDisplay.addEventListener('change', () => {
                        if (currentLocale === locale) {
                            syncPrices(locale, true);
                        }
                    });
                }
            });

            document.addEventListener('teacher:money-input-sync', (e) => {
                // If the synced element is the current active locale's price, sync others
                const targetId = e.detail?.targetId;
                if (!targetId) return;

                supported.forEach(locale => {
                    if (currentLocale !== locale) return;
                    const suffix = locale === 'vi' ? '' : `_${locale}`;
                    if (targetId === `teacher-course-price${suffix}`) {
                        syncPrices(locale, false);
                    } else if (targetId === `teacher-course-sale_price${suffix}`) {
                        syncPrices(locale, true);
                    }
                });
            });

            window.TeacherMoneyInput?.initAll(form || document);

            // Coming Soon Toggle
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
        })();
    </script>
@endsection
