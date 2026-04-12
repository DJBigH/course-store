@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-affiliate-form-shell">
        <div class="teacher-affiliate-form-hero">
            <div>
                <span class="teacher-affiliate-form-kicker">{{ __('teacher::dashboard.nav.affiliate_links') }}</span>
                <h3 class="teacher-affiliate-form-title">
                    {{ $link ? __('teacher::dashboard.affiliate_links.edit') : __('teacher::dashboard.affiliate_links.create') }}
                </h3>
                <p class="teacher-affiliate-form-desc mb-0">
                    {{ $link ? __('teacher::dashboard.affiliate_links.edit_description') : __('teacher::dashboard.affiliate_links.create_description') }}
                </p>
            </div>
            <a href="{{ route('teacher.dashboard.affiliate-links.index') }}" class="btn btn-outline-secondary">
                {{ __('teacher::dashboard.common.back') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0">{{ __('teacher::dashboard.common.validation_summary') }}</div>
        @endif

        <form method="POST"
            action="{{ $link ? route('teacher.dashboard.affiliate-links.update', $link->id) : route('teacher.dashboard.affiliate-links.store') }}"
            class="teacher-affiliate-form-card">
            @csrf

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="teacher-affiliate-form-block">
                        <h4>{{ __('teacher::dashboard.affiliate_links.form.content_title') }}</h4>
                        <div class="teacher-affiliate-form-grid">
                            <div>
                                <label class="form-label">{{ __('teacher::dashboard.affiliate_links.fields.name') }}</label>
                                <input type="text" name="name" class="form-control" maxlength="120"
                                    value="{{ old('name', $link?->name) }}" required>
                                <div class="form-text">{{ __('teacher::dashboard.affiliate_links.form.name_help') }}</div>
                            </div>
                            <div>
                                <label class="form-label">{{ __('teacher::dashboard.affiliate_links.fields.target_type') }}</label>
                                <select name="target_type" class="form-select affiliate-target-type" required>
                                    @foreach (['course', 'bundle', 'landing'] as $targetType)
                                        <option value="{{ $targetType }}"
                                            {{ old('target_type', $link?->target_type ?? 'course') === $targetType ? 'selected' : '' }}>
                                            {{ __('teacher::dashboard.affiliate_links.target_types.' . $targetType) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="affiliate-target-group" data-target-group="course">
                                <label class="form-label">{{ __('teacher::dashboard.affiliate_links.form.course_target') }}</label>
                                <select name="target_id_course" class="form-select">
                                    <option value="">{{ __('teacher::dashboard.affiliate_links.form.choose_course') }}</option>
                                    @foreach ($courseOptions as $courseOption)
                                        <option value="{{ $courseOption->id }}"
                                            {{ old('target_type', $link?->target_type ?? 'course') === 'course' && (int) old('target_id_course', $link?->target_type === 'course' ? $link?->target_id : null) === (int) $courseOption->id ? 'selected' : '' }}>
                                            {{ $courseOption->name_locale ?: $courseOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="affiliate-target-group" data-target-group="bundle">
                                <label class="form-label">{{ __('teacher::dashboard.affiliate_links.form.bundle_target') }}</label>
                                <select name="target_id_bundle" class="form-select">
                                    <option value="">{{ __('teacher::dashboard.affiliate_links.form.choose_bundle') }}</option>
                                    @foreach ($bundleOptions as $bundleOption)
                                        <option value="{{ $bundleOption->id }}"
                                            {{ old('target_type', $link?->target_type ?? 'course') === 'bundle' && (int) old('target_id_bundle', $link?->target_type === 'bundle' ? $link?->target_id : null) === (int) $bundleOption->id ? 'selected' : '' }}>
                                            {{ $bundleOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="affiliate-target-group" data-target-group="landing">
                                <div class="teacher-affiliate-form-hint">
                                    {{ __('teacher::dashboard.affiliate_links.form.landing_hint') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="teacher-affiliate-form-block">
                        <h4>{{ __('teacher::dashboard.affiliate_links.form.status_title') }}</h4>
                        <div class="teacher-affiliate-form-grid">
                            @if ($link)
                                <div>
                                    <label class="form-label">{{ __('teacher::dashboard.affiliate_links.fields.code') }}</label>
                                    <input type="text" class="form-control" value="{{ $link->code }}" readonly>
                                </div>
                                <div>
                                    <label class="form-label">{{ __('teacher::dashboard.affiliate_links.fields.public_url') }}</label>
                                    <input type="text" class="form-control" value="{{ $link->public_url ?? '' }}" readonly>
                                </div>
                            @endif

                            <div class="form-check teacher-affiliate-form-check">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="affiliate-status"
                                    {{ old('status', $link?->status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="affiliate-status">
                                    {{ __('teacher::dashboard.affiliate_links.fields.status') }}
                                </label>
                                <div class="form-text">{{ __('teacher::dashboard.affiliate_links.form.status_help') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="target_id" class="affiliate-target-id" value="{{ old('target_id', $link?->target_id) }}">

            <div class="teacher-affiliate-form-actions">
                <a href="{{ route('teacher.dashboard.affiliate-links.index') }}" class="btn btn-outline-secondary">
                    {{ __('teacher::dashboard.common.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ __('teacher::dashboard.affiliate_links.actions.save') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-affiliate-form-shell { background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.1), transparent 28%), linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(17, 24, 39, 0.98) 100%); }
        .teacher-affiliate-form-hero { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; margin-bottom: 1.5rem; }
        .teacher-affiliate-form-kicker { display: inline-flex; padding: 0.45rem 0.8rem; border-radius: 999px; background: rgba(14, 165, 233, 0.16); color: #bae6fd; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; }
        .teacher-affiliate-form-title { margin-top: 1rem; margin-bottom: 0.55rem; font-size: clamp(2rem, 3vw, 2.7rem); font-weight: 900; color: #f8fbff; }
        .teacher-affiliate-form-desc { color: #a9bbd5; max-width: 720px; line-height: 1.75; }
        .teacher-affiliate-form-card { background: rgba(18, 28, 50, 0.72); border: 1px solid rgba(56, 189, 248, 0.18); border-radius: 24px; padding: 1.5rem; box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18); }
        .teacher-affiliate-form-block h4 { color: #f8fbff; margin-bottom: 1rem; font-weight: 800; }
        .teacher-affiliate-form-grid { display: grid; gap: 1rem; }
        .teacher-affiliate-form-card .form-label { color: #dce9ff; font-weight: 700; }
        .teacher-affiliate-form-card .form-control, .teacher-affiliate-form-card .form-select { background: rgba(15, 23, 42, 0.72); border-color: rgba(56, 189, 248, 0.18); color: #f8fbff; }
        .teacher-affiliate-form-card .form-text { color: #94a3b8; }
        .teacher-affiliate-form-check, .teacher-affiliate-form-hint { padding: 0.95rem 1rem; border-radius: 18px; background: rgba(15, 23, 42, 0.48); border: 1px solid rgba(56, 189, 248, 0.14); color: #dce9ff; }
        .teacher-affiliate-form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
        html[data-theme="light"] .teacher-affiliate-form-shell { background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 28%), linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%); }
        html[data-theme="light"] .teacher-affiliate-form-card { background: var(--admin-surface); border-color: var(--admin-border); box-shadow: var(--admin-card-shadow); }
        html[data-theme="light"] .teacher-affiliate-form-title, html[data-theme="light"] .teacher-affiliate-form-block h4 { color: #0f172a; }
        html[data-theme="light"] .teacher-affiliate-form-desc, html[data-theme="light"] .teacher-affiliate-form-card .form-text, html[data-theme="light"] .teacher-affiliate-form-hint { color: #475569; }
        html[data-theme="light"] .teacher-affiliate-form-card .form-control, html[data-theme="light"] .teacher-affiliate-form-card .form-select { background: #fff; border-color: var(--admin-border); color: #0f172a; }
        html[data-theme="light"] .teacher-affiliate-form-check, html[data-theme="light"] .teacher-affiliate-form-hint { background: #fff; border-color: var(--admin-border); }
        @media (max-width: 991.98px) { .teacher-affiliate-form-hero, .teacher-affiliate-form-actions { flex-direction: column; align-items: flex-start; } }
    </style>
@endsection

@section('scripts')
    <script>
        (function () {
            const targetType = document.querySelector('.affiliate-target-type');
            const targetId = document.querySelector('.affiliate-target-id');
            const groups = document.querySelectorAll('.affiliate-target-group');
            const courseSelect = document.querySelector('select[name="target_id_course"]');
            const bundleSelect = document.querySelector('select[name="target_id_bundle"]');

            function syncState() {
                const current = targetType ? targetType.value : 'course';

                groups.forEach((group) => {
                    group.style.display = group.getAttribute('data-target-group') === current ? '' : 'none';
                });

                if (current === 'course') {
                    targetId.value = courseSelect.value || '';
                } else if (current === 'bundle') {
                    targetId.value = bundleSelect.value || '';
                } else {
                    targetId.value = '';
                }
            }

            if (!targetType || !targetId) {
                return;
            }

            targetType.addEventListener('change', syncState);
            courseSelect?.addEventListener('change', syncState);
            bundleSelect?.addEventListener('change', syncState);
            syncState();
        })();
    </script>
@endsection
