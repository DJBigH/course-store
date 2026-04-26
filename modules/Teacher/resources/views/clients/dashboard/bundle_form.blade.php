@extends('layouts.teacher')

@php
    $selectedCourseIds = collect(old('course_ids', $bundle?->items?->pluck('course_id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

@section('content')
    <div class="teacher-panel teacher-bundle-form-shell">
        <div class="teacher-bundle-form-hero">
            <div>
                <span class="teacher-bundle-form-kicker">{{ __('teacher::teacher/bundle/list.hero_kicker') }}</span>
                <h3 class="teacher-bundle-form-title">{{ $bundle ? __('teacher::teacher/bundle/edit.edit_title') : __('teacher::teacher/bundle/add.create_title') }}</h3>
                <p class="teacher-bundle-form-desc mb-0">{{ $bundle ? __('teacher::teacher/bundle/edit.edit_description') : __('teacher::teacher/bundle/add.create_description') }}</p>
            </div>
            <a href="{{ route('teacher.dashboard.bundles') }}" class="btn btn-outline-secondary">
                {{ __('teacher::teacher/course/common.actions.back') }}
            </a>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success border-0">{{ session('msg_success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0">{{ __('teacher::teacher/course/common.validation_summary') }}</div>
        @endif

        <form method="POST"
            action="{{ $bundle ? route('teacher.dashboard.bundles.update', ['bundle' => $bundle->id]) : route('teacher.dashboard.bundles.store') }}"
            class="teacher-bundle-form-card">
            @csrf

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="teacher-bundle-form-block">
                        <h4>{{ __('teacher::teacher/bundle/common.form.content_title') }}</h4>
                        <div class="teacher-bundle-form-grid">
                            <div>
                                <label class="form-label">{{ __('teacher::teacher/bundle/common.form.name') }}</label>
                                <input type="text" name="name" class="form-control" maxlength="160"
                                    value="{{ old('name', $bundle?->name) }}" required>
                            </div>
                            <div>
                                <label class="form-label">{{ __('teacher::teacher/bundle/common.form.thumbnail') }}</label>
                                <input type="text" name="thumbnail" class="form-control" maxlength="255"
                                    value="{{ old('thumbnail', $bundle?->thumbnail) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('teacher::teacher/bundle/common.form.description') }}</label>
                                <textarea name="description" class="form-control" rows="6">{{ old('description', $bundle?->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="teacher-bundle-form-block">
                        <h4>{{ __('teacher::teacher/bundle/common.form.pricing_title') }}</h4>
                        <div class="teacher-bundle-form-grid">
                            <div>
                                <label class="form-label">{{ __('teacher::teacher/bundle/common.form.price') }} (VND) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-muted border-end-0">₫</span>
                                    <input type="text" name="price_display" id="price_display" class="form-control currency-format border-start-0"
                                        value="{{ number_format(old('price', $bundle?->price), 0, ',', '.') }}" required>
                                    <input type="hidden" name="price" id="price" value="{{ old('price', $bundle?->price) }}">
                                </div>
                                <div class="form-text">{{ __('teacher::teacher/bundle/common.form.price_help') }}</div>
                            </div>

                            <div>
                                <label class="form-label">{{ __('teacher::teacher/bundle/common.form.sale_price') }} (VND)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-muted border-end-0">₫</span>
                                    <input type="text" name="sale_price_display" id="sale_price_display" class="form-control currency-format border-start-0"
                                        value="{{ old('sale_price', $bundle?->sale_price) ? number_format(old('sale_price', $bundle->sale_price), 0, ',', '.') : '' }}">
                                    <input type="hidden" name="sale_price" id="sale_price" value="{{ old('sale_price', $bundle?->sale_price) }}">
                                </div>
                                <div class="form-text">{{ __('teacher::teacher/bundle/common.form.sale_price_help') }}</div>
                            </div>

                            <div>
                                <label class="form-label">{{ __('teacher::teacher/bundle/common.form.quantity') }}</label>
                                <input type="number" name="quantity" class="form-control" min="0"
                                    value="{{ old('quantity', $bundle?->quantity) }}">
                                <div class="form-text">{{ __('teacher::teacher/bundle/common.form.quantity_help') }}</div>
                            </div>

                            <div>
                                <label class="form-label">{{ __('teacher::teacher/bundle/common.form.end_at') }}</label>
                                <input type="datetime-local" name="end_at" class="form-control"
                                    value="{{ old('end_at', $bundle?->end_at ? $bundle->end_at->format('Y-m-d\TH:i') : '') }}">
                                <div class="form-text">{{ __('teacher::teacher/bundle/common.form.end_at_help') }}</div>
                            </div>
                            <div class="form-check teacher-bundle-form-check">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="bundle-status"
                                    {{ old('status', $bundle?->status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="bundle-status">
                                    {{ __('teacher::teacher/bundle/common.form.status') }}
                                </label>
                            </div>

                            <div class="form-check teacher-bundle-form-check mt-2">
                                <input type="hidden" name="is_coming_soon" value="0">
                                <input class="form-check-input" type="checkbox" name="is_coming_soon" value="1" id="is_coming_soon"
                                    {{ old('is_coming_soon', $bundle?->is_coming_soon) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_coming_soon">
                                    {{ __('teacher::teacher/bundle/common.form.is_coming_soon') }}
                                </label>
                            </div>

                            <div id="coming_soon_date_wrapper" style="display: none;">
                                <label class="form-label text-warning">{{ __('teacher::teacher/bundle/common.form.coming_soon_start_at') }}</label>
                                <input type="datetime-local" name="coming_soon_start_at" class="form-control border-warning"
                                    value="{{ old('coming_soon_start_at', $bundle?->coming_soon_start_at ? $bundle->coming_soon_start_at->format('Y-m-d\TH:i') : '') }}">
                                <div class="form-text text-warning-emphasis">{{ __('teacher::teacher/bundle/common.form.is_coming_soon_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="teacher-bundle-form-block mt-4">
                <h4>{{ __('teacher::teacher/bundle/common.form.courses_title') }}</h4>
                <p class="teacher-bundle-form-note">{{ __('teacher::teacher/bundle/common.form.courses_help') }}</p>

                <div class="teacher-bundle-course-list">
                    @foreach ($courseOptions as $courseOption)
                        @php
                            $coursePrice = $courseOption->sale_price && $courseOption->sale_price > 0 ? $courseOption->sale_price : $courseOption->price;
                        @endphp
                        <label class="teacher-bundle-course-item">
                            <input type="checkbox" name="course_ids[]" value="{{ $courseOption->id }}"
                                {{ in_array((int) $courseOption->id, $selectedCourseIds, true) ? 'checked' : '' }}>
                            <span class="teacher-bundle-course-item__body">
                                <strong>{{ $courseOption->name_locale ?: $courseOption->name }}</strong>
                                <small>{{ moneyLocale($coursePrice) }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="teacher-bundle-form-actions">
                <a href="{{ route('teacher.dashboard.bundles') }}" class="btn btn-outline-secondary">
                    {{ __('teacher::teacher/course/common.actions.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ $bundle ? __('teacher::teacher/bundle/common.actions.update') : __('teacher::teacher/bundle/common.actions.create') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-bundle-form-shell { background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 28%), linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(17, 24, 39, 0.98) 100%); }
        .teacher-bundle-form-hero { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; margin-bottom: 1.5rem; }
        .teacher-bundle-form-kicker { display: inline-flex; padding: 0.45rem 0.8rem; border-radius: 999px; background: rgba(59, 130, 246, 0.16); color: #bfdbfe; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; }
        .teacher-bundle-form-title { margin-top: 1rem; margin-bottom: 0.55rem; font-size: clamp(2rem, 3vw, 2.7rem); font-weight: 900; color: #f8fbff; }
        .teacher-bundle-form-desc { color: #a9bbd5; max-width: 720px; line-height: 1.75; }
        .teacher-bundle-form-card { background: rgba(18, 28, 50, 0.72); border: 1px solid rgba(96, 165, 250, 0.18); border-radius: 24px; padding: 1.5rem; box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18); }
        .teacher-bundle-form-block h4 { color: #f8fbff; margin-bottom: 1rem; font-weight: 800; }
        .teacher-bundle-form-grid { display: grid; gap: 1rem; }
        .teacher-bundle-form-card .form-label { color: #dce9ff; font-weight: 700; }
        .teacher-bundle-form-card .form-control { background: rgba(15, 23, 42, 0.72); border-color: rgba(96, 165, 250, 0.18); color: #f8fbff; }
        .teacher-bundle-form-card .form-text, .teacher-bundle-form-note { color: #94a3b8; }
        .teacher-bundle-form-check { padding: 0.85rem 1rem; border-radius: 18px; background: rgba(15, 23, 42, 0.48); border: 1px solid rgba(96, 165, 250, 0.14); }
        .teacher-bundle-course-list { display: grid; gap: 0.85rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        .teacher-bundle-course-item { display: flex; gap: 0.8rem; align-items: flex-start; padding: 1rem; border-radius: 18px; border: 1px solid rgba(96, 165, 250, 0.16); background: rgba(15, 23, 42, 0.52); cursor: pointer; }
        .teacher-bundle-course-item__body { display: flex; flex-direction: column; gap: 0.3rem; }
        .teacher-bundle-course-item__body strong { color: #f8fbff; }
        .teacher-bundle-course-item__body small { color: #94a3b8; }
        .teacher-bundle-form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
        html[data-theme="light"] .teacher-bundle-form-shell { background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 28%), linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%); }
        html[data-theme="light"] .teacher-bundle-form-card { background: var(--admin-surface); border-color: var(--admin-border); box-shadow: var(--admin-card-shadow); }
        html[data-theme="light"] .teacher-bundle-form-title, html[data-theme="light"] .teacher-bundle-form-block h4, html[data-theme="light"] .teacher-bundle-course-item__body strong { color: #0f172a; }
        html[data-theme="light"] .teacher-bundle-form-desc, html[data-theme="light"] .teacher-bundle-form-card .form-text, html[data-theme="light"] .teacher-bundle-form-note, html[data-theme="light"] .teacher-bundle-course-item__body small { color: #475569; }
        html[data-theme="light"] .teacher-bundle-form-card .form-control { background: #fff; border-color: var(--admin-border); color: #0f172a; }
        html[data-theme="light"] .teacher-bundle-course-item, html[data-theme="light"] .teacher-bundle-form-check { background: #fff; border-color: var(--admin-border); }
        @media (max-width: 991.98px) { .teacher-bundle-form-hero, .teacher-bundle-form-actions { flex-direction: column; align-items: flex-start; } }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isComingSoonCheck = document.getElementById('is_coming_soon');
            const comingSoonWrapper = document.getElementById('coming_soon_date_wrapper');
            const comingSoonInput = comingSoonWrapper ? comingSoonWrapper.querySelector('input') : null;

            function toggleComingSoon() {
                if (!isComingSoonCheck || !comingSoonWrapper) return;
                
                if (isComingSoonCheck.checked) {
                    comingSoonWrapper.style.display = 'block';
                } else {
                    comingSoonWrapper.style.display = 'none';
                    if (comingSoonInput) comingSoonInput.value = '';
                }
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

            document.querySelectorAll('.currency-format').forEach(input => {
                input.addEventListener('input', function() {
                    const rawValue = this.value.replace(/\D/g, '');
                    this.value = formatCurrency(this.value);
                    
                    // Update hidden input
                    const hiddenInputId = this.id.replace('_display', '');
                    const hiddenInput = document.getElementById(hiddenInputId);
                    if (hiddenInput) {
                        hiddenInput.value = rawValue;
                    }
                });
            });
        });
    </script>
@endsection
