@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-coupon-form">
        <div class="teacher-coupon-form__head">
            <div>
                <span class="teacher-coupon-form__kicker">{{ __('teacher::coupons.hero.kicker') }}</span>
                <h3 class="teacher-coupon-form__title">{{ $pageTitle }}</h3>
                <p class="teacher-coupon-form__desc mb-0">{{ __('teacher::coupons.form.description') }}</p>
                @if ($coupon?->package_locked_at)
                    <div class="teacher-limit-lock mt-3">
                        <span class="teacher-limit-lock__badge">{{ __('teacher::coupons.labels.limited_actions_only') }}</span>
                        <div class="teacher-limit-lock__text">{{ __('teacher::coupons.flash.locked_manage_only') }}</div>
                    </div>
                @endif
            </div>
            <a href="{{ route('teacher.dashboard.coupons.index') }}" class="btn btn-outline-secondary">
                {{ __('teacher::coupons.actions.back') }}
            </a>
        </div>

        <form method="POST" action="{{ $coupon ? route('teacher.dashboard.coupons.update', $coupon->id) : route('teacher.dashboard.coupons.store') }}" class="teacher-coupon-form__body">
            @csrf
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">{{ __('teacher::coupons.form.code') }}</label>
                    <div class="teacher-coupon-code-field">
                        <input type="text" name="code" id="teacher-coupon-code" class="form-control" value="{{ old('code', $coupon->code ?? '') }}" placeholder="WELCOME10">
                        <button
                            type="button"
                            class="btn btn-outline-secondary teacher-coupon-code-generate"
                            data-generate-code
                            title="{{ __('teacher::coupons.actions.random_code') }}"
                            aria-label="{{ __('teacher::coupons.actions.random_code') }}"
                        >
                            <i class="fas fa-random"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">{{ __('teacher::coupons.form.code_hint') }}</small>
                    @error('code')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-lg-3">
                    <label class="form-label">{{ __('teacher::coupons.form.discount_type') }}</label>
                    <select name="discount_type" class="form-select">
                        <option value="percent" @selected(old('discount_type', $coupon->discount_type ?? 'percent') === 'percent')>%</option>
                        <option value="value" @selected(old('discount_type', $coupon->discount_type ?? '') === 'value')>VND</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">{{ __('teacher::coupons.form.discount_value') }}</label>
                    <input type="hidden" name="discount_value" id="teacher-coupon-discount" value="{{ old('discount_value', $coupon->discount_value ?? '') }}">
                    <input
                        type="text"
                        inputmode="numeric"
                        class="form-control"
                        id="teacher-coupon-discount-display"
                        value="{{ old('discount_value', $coupon->discount_value ?? '') }}"
                        data-number-format
                        data-number-target="teacher-coupon-discount"
                        placeholder="0"
                    >
                </div>
                <div class="col-lg-4">
                    <label class="form-label">{{ __('teacher::coupons.form.total_condition') }}</label>
                    <input type="hidden" name="total_condition" id="teacher-coupon-total" value="{{ old('total_condition', $coupon->total_condition ?? '') }}">
                    <input
                        type="text"
                        inputmode="numeric"
                        class="form-control"
                        id="teacher-coupon-total-display"
                        value="{{ old('total_condition', $coupon->total_condition ?? '') }}"
                        data-number-format
                        data-number-target="teacher-coupon-total"
                        placeholder="0"
                    >
                </div>
                <div class="col-lg-4">
                    <label class="form-label">{{ __('teacher::coupons.form.count') }}</label>
                    <input type="hidden" name="count" id="teacher-coupon-count" value="{{ old('count', $coupon->count ?? '') }}">
                    <input
                        type="text"
                        inputmode="numeric"
                        class="form-control"
                        id="teacher-coupon-count-display"
                        value="{{ old('count', $coupon->count ?? '') }}"
                        data-number-format
                        data-number-target="teacher-coupon-count"
                        placeholder="0"
                    >
                </div>
                <div class="col-lg-4">
                    <label class="form-label">{{ __('teacher::coupons.form.per_student_once') }}</label>
                    <select name="per_student_once" class="form-select">
                        <option value="1" @selected((string) old('per_student_once', $coupon?->per_student_once ? '1' : '0') === '1')>{{ __('teacher::coupons.labels.once') }}</option>
                        <option value="0" @selected((string) old('per_student_once', $coupon?->per_student_once ? '1' : '0') === '0')>{{ __('teacher::coupons.labels.multi') }}</option>
                    </select>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ __('teacher::coupons.form.start_date') }}</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($coupon?->start_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ __('teacher::coupons.form.end_date') }}</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($coupon?->end_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <div class="teacher-coupon-assign">
                        <div class="teacher-coupon-assign__head">
                            <div>
                                <h5 class="mb-1">{{ __('teacher::coupons.assign_block.title') }}</h5>
                                <p class="text-muted mb-0">{{ __('teacher::coupons.assign_block.description') }}</p>
                            </div>
                            @error('students')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="teacher-coupon-assign__toggles">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-assign-toggle="students">
                                {{ __('teacher::coupons.assign_block.students') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-assign-toggle="courses">
                                {{ __('teacher::coupons.assign_block.courses') }}
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6" data-assign-panel="students">
                                <div class="teacher-coupon-assign__list">
                                    <div class="teacher-coupon-assign__label">{{ __('teacher::coupons.assign_block.students') }}</div>
                                    @forelse ($students as $student)
                                        <label class="teacher-coupon-assign__item">
                                            <input type="checkbox" name="students[]"
                                                value="{{ $student->id }}" @checked(in_array($student->id, old('students', $assignedStudentIds ?? []), true))>
                                            <span>{{ $student->name }} ({{ $student->email }})</span>
                                        </label>
                                    @empty
                                        <div class="text-muted small">{{ __('teacher::coupons.assign_students_empty') }}</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-lg-6" data-assign-panel="courses">
                                <div class="teacher-coupon-assign__list">
                                    <div class="teacher-coupon-assign__label">{{ __('teacher::coupons.assign_block.courses') }}</div>
                                    @forelse ($courses as $course)
                                        <label class="teacher-coupon-assign__item">
                                            <input type="checkbox" name="courses[]"
                                                value="{{ $course->id }}" @checked(in_array($course->id, old('courses', $assignedCourseIds ?? []), true))>
                                            <span>{{ $course->name }}</span>
                                        </label>
                                    @empty
                                        <div class="text-muted small">{{ __('teacher::coupons.assign_courses_empty') }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="teacher-coupon-form__actions">
                <button type="submit" class="btn btn-primary">
                    {{ $coupon ? __('teacher::coupons.actions.update') : __('teacher::coupons.actions.create') }}
                </button>
                @if ($coupon)
                    <a href="{{ route('teacher.dashboard.coupons.courses', $coupon->id) }}" class="btn btn-outline-secondary">
                        {{ __('teacher::coupons.actions.assign_courses') }}
                    </a>
                    <a href="{{ route('teacher.dashboard.coupons.students', $coupon->id) }}" class="btn btn-outline-secondary">
                        {{ __('teacher::coupons.actions.assign_students') }}
                    </a>
                @endif
            </div>
        </form>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-coupon-form {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-coupon-form__head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .teacher-coupon-form__kicker {
            display: inline-flex;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.14);
            color: #8fc3ff;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .teacher-coupon-form__title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-limit-lock__badge {
            display: inline-flex;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: rgba(248, 113, 113, 0.18);
            color: #fecaca;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .teacher-limit-lock__text {
            margin-top: 0.6rem;
            color: #fca5a5;
            font-weight: 600;
        }

        .teacher-coupon-form__desc {
            max-width: 720px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-coupon-form__body {
            padding: 1.2rem;
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.16);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-coupon-form__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.2rem;
        }

        .teacher-coupon-code-field {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.6rem;
            align-items: center;
        }

        .teacher-coupon-code-generate {
            white-space: nowrap;
            width: 42px;
            height: 42px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .teacher-coupon-assign {
            border-radius: 18px;
            border: 1px solid rgba(96, 165, 250, 0.16);
            background: rgba(11, 19, 36, 0.55);
            padding: 1rem;
        }

        .teacher-coupon-assign__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.85rem;
        }

        .teacher-coupon-assign__toggles {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.9rem;
        }

        .teacher-coupon-assign__toggles .btn {
            border-radius: 999px;
        }

        .teacher-coupon-assign__list {
            border-radius: 16px;
            border: 1px dashed rgba(148, 163, 184, 0.3);
            padding: 0.85rem;
            max-height: 240px;
            overflow: auto;
        }

        [data-assign-panel] {
            transition: max-height 0.28s ease, opacity 0.28s ease;
            max-height: 360px;
            opacity: 1;
            overflow: hidden;
        }

        [data-assign-panel].is-collapsed {
            max-height: 0;
            opacity: 0;
            pointer-events: none;
        }

        .teacher-coupon-assign__label {
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 0.6rem;
        }

        .teacher-coupon-assign__item {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.35rem 0.2rem;
            color: #d5e3f5;
            font-size: 0.95rem;
        }

        html[data-theme="light"] .teacher-coupon-form {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%);
        }

        html[data-theme="light"] .teacher-coupon-form__kicker {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-coupon-form__title {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-limit-lock__badge {
            background: rgba(248, 113, 113, 0.14);
            color: #b91c1c;
        }

        html[data-theme="light"] .teacher-limit-lock__text {
            color: #b45309;
        }

        html[data-theme="light"] .teacher-coupon-form__desc {
            color: #475569;
        }

        html[data-theme="light"] .teacher-coupon-form__body,
        html[data-theme="light"] .teacher-coupon-assign {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-coupon-assign__label {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-coupon-assign__item {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-coupon-assign__list {
            border-color: var(--admin-border);
            background: var(--admin-subtle-bg);
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const codeInput = document.querySelector('#teacher-coupon-code');
            const generateButton = document.querySelector('[data-generate-code]');
            const formattedInputs = document.querySelectorAll('[data-number-format]');

            if (!codeInput || !generateButton) {
                // still allow number formatting
            }

            const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            const randomCode = (length = 8) => {
                let value = '';
                for (let i = 0; i < length; i += 1) {
                    value += alphabet.charAt(Math.floor(Math.random() * alphabet.length));
                }
                return value;
            };

            if (codeInput && generateButton) {
                generateButton.addEventListener('click', () => {
                    codeInput.value = randomCode();
                    codeInput.focus();
                    codeInput.select();
                });
            }

            const formatNumber = (value) => {
                const digits = value.replace(/[^\d]/g, '');
                if (!digits) {
                    return '';
                }
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            };

            const syncNumberField = (displayEl, hiddenEl) => {
                const digits = displayEl.value.replace(/[^\d]/g, '');
                hiddenEl.value = digits;
                displayEl.value = formatNumber(digits);
            };

            formattedInputs.forEach((displayEl) => {
                const targetId = displayEl.getAttribute('data-number-target');
                if (!targetId) {
                    return;
                }
                const hiddenEl = document.getElementById(targetId);
                if (!hiddenEl) {
                    return;
                }

                displayEl.value = formatNumber(displayEl.value);
                displayEl.addEventListener('input', () => syncNumberField(displayEl, hiddenEl));
                displayEl.addEventListener('blur', () => syncNumberField(displayEl, hiddenEl));
                syncNumberField(displayEl, hiddenEl);
            });

            const panels = document.querySelectorAll('[data-assign-panel]');
            const toggles = document.querySelectorAll('[data-assign-toggle]');

            const setPanelVisible = (key, visible) => {
                const panel = document.querySelector(`[data-assign-panel="${key}"]`);
                if (!panel) {
                    return;
                }
                panel.classList.toggle('is-collapsed', !visible);
            };

            const hasSelection = (key) => {
                const panel = document.querySelector(`[data-assign-panel="${key}"]`);
                if (!panel) {
                    return false;
                }
                return panel.querySelector('input[type="checkbox"]:checked') !== null;
            };

            const initVisibility = () => {
                const showStudents = hasSelection('students');
                const showCourses = hasSelection('courses');

                setPanelVisible('students', showStudents);
                setPanelVisible('courses', showCourses);
            };

            initVisibility();

            toggles.forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    const key = toggle.getAttribute('data-assign-toggle');
                    const panel = document.querySelector(`[data-assign-panel="${key}"]`);
                    if (!panel) {
                        return;
                    }
                    panel.classList.toggle('is-collapsed');
                });
            });
        })();
    </script>
@endsection
