@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-promotions-shell">
        <div class="teacher-promotions-hero">
            <div>
                <span class="teacher-promotions-kicker">{{ __('teacher::dashboard.promotions.hero_kicker') }}</span>
                <h3 class="teacher-promotions-title">{{ __('teacher::dashboard.promotions.title') }}</h3>
                <p class="teacher-promotions-desc mb-0">{{ __('teacher::dashboard.promotions.description') }}</p>
            </div>

            <div class="teacher-promotions-stat">
                <strong>{{ $recipientPreviewCount }}</strong>
                <span>{{ __('teacher::dashboard.promotions.recipient_preview') }}</span>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success border-0">{{ session('msg_success') }}</div>
        @endif

        @if (session('msg_danger'))
            <div class="alert alert-danger border-0">{{ session('msg_danger') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0">{{ __('teacher::dashboard.common.validation_summary') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-xl-5">
                <div class="teacher-promotions-card">
                    <div class="teacher-promotions-card__head">
                        <div>
                            <h4>{{ __('teacher::dashboard.promotions.form.title') }}</h4>
                            <p class="mb-0">{{ __('teacher::dashboard.promotions.form.description') }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('teacher.dashboard.promotions.store') }}" class="teacher-promotions-form">
                        @csrf
                        <div>
                            <label class="form-label">{{ __('teacher::dashboard.promotions.form.course') }}</label>
                            <select name="course_id" class="form-select">
                                <option value="">{{ __('teacher::dashboard.promotions.form.all_students') }}</option>
                                @foreach ($courseOptions as $courseOption)
                                    <option value="{{ $courseOption->id }}" @selected((int) old('course_id', $selectedCourseId) === (int) $courseOption->id)>
                                        {{ $courseOption->name_locale ?: $courseOption->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('teacher::dashboard.promotions.form.course_help') }}</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('teacher::dashboard.promotions.form.recent_purchase_days') }}</label>
                                <input type="number" min="1" max="3650" name="recent_purchase_days" class="form-control"
                                    value="{{ old('recent_purchase_days', $recentPurchaseDays > 0 ? $recentPurchaseDays : '') }}">
                                <div class="form-text">{{ __('teacher::dashboard.promotions.form.recent_purchase_days_help') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('teacher::dashboard.promotions.form.inactive_learning_days') }}</label>
                                <input type="number" min="1" max="3650" name="inactive_learning_days" class="form-control"
                                    value="{{ old('inactive_learning_days', $inactiveLearningDays > 0 ? $inactiveLearningDays : '') }}">
                                <div class="form-text">{{ __('teacher::dashboard.promotions.form.inactive_learning_days_help') }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">{{ __('teacher::dashboard.promotions.form.title_label') }}</label>
                            <input type="text" name="title" class="form-control" maxlength="160" value="{{ old('title') }}" required>
                        </div>
                        <div>
                            <label class="form-label">{{ __('teacher::dashboard.promotions.form.message_label') }}</label>
                            <textarea name="message" class="form-control" rows="6" maxlength="2000" required>{{ old('message') }}</textarea>
                            <div class="form-text">{{ __('teacher::dashboard.promotions.form.message_help') }}</div>
                        </div>
                        <div class="teacher-promotions-form__footer">
                            <div class="teacher-promotions-form__hint">
                                {{ __('teacher::dashboard.promotions.form.recipient_count', ['count' => $recipientPreviewCount]) }}
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('teacher::dashboard.promotions.form.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="teacher-promotions-card">
                    <div class="teacher-promotions-card__head">
                        <div>
                            <h4>{{ __('teacher::dashboard.promotions.history_title') }}</h4>
                            <p class="mb-0">{{ __('teacher::dashboard.promotions.history_description') }}</p>
                        </div>
                    </div>
                    <div class="teacher-promotions-history">
                        @forelse ($promotions as $promotion)
                            <div class="teacher-promotions-item">
                                <div class="teacher-promotions-item__meta">
                                    <span class="teacher-promotions-pill">
                                        {{ $promotion->course ? (localizedModelField($promotion->course, 'name', app()->getLocale()) ?: __('teacher::dashboard.common.unknown_course')) : __('teacher::dashboard.promotions.history_all_students') }}
                                    </span>
                                    <span class="teacher-promotions-time">{{ optional($promotion->created_at)->diffForHumans() }}</span>
                                </div>
                                <h5>{{ $promotion->title }}</h5>
                                <p>{{ $promotion->message }}</p>
                                <div class="teacher-promotions-item__footer">
                                    <strong>{{ __('teacher::dashboard.promotions.history_recipients', ['count' => $promotion->recipient_count]) }}</strong>
                                    <span>{{ optional($promotion->created_at)->format('d/m/Y H:i') }}</span>
                                </div>
                                @if (!empty($promotion->filters))
                                    <div class="teacher-promotions-item__filters">
                                        @if (!empty($promotion->filters['recent_purchase_days']))
                                            <span class="teacher-promotions-pill teacher-promotions-pill--soft">
                                                {{ __('teacher::dashboard.promotions.history_recent_purchase_days', ['days' => $promotion->filters['recent_purchase_days']]) }}
                                            </span>
                                        @endif
                                        @if (!empty($promotion->filters['inactive_learning_days']))
                                            <span class="teacher-promotions-pill teacher-promotions-pill--soft">
                                                {{ __('teacher::dashboard.promotions.history_inactive_learning_days', ['days' => $promotion->filters['inactive_learning_days']]) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="teacher-promotions-empty">
                                <div class="teacher-promotions-empty__icon"><i class="fas fa-bullhorn"></i></div>
                                <h4>{{ __('teacher::dashboard.promotions.empty') }}</h4>
                                <p class="mb-0">{{ __('teacher::dashboard.promotions.empty_description') }}</p>
                            </div>
                        @endforelse
                    </div>
                    @if ($promotions->hasPages())
                        <div class="mt-4">{{ $promotions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-promotions-shell { background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 28%), radial-gradient(circle at left bottom, rgba(16, 185, 129, 0.08), transparent 24%), linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%); }
        .teacher-promotions-hero { display: flex; justify-content: space-between; gap: 1.25rem; align-items: flex-start; margin-bottom: 1.5rem; }
        .teacher-promotions-kicker { display: inline-flex; padding: 0.45rem 0.8rem; border-radius: 999px; background: rgba(37, 99, 235, 0.14); color: #8fc3ff; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; }
        .teacher-promotions-title { margin-top: 1rem; margin-bottom: 0.55rem; font-size: clamp(2rem, 3vw, 2.7rem); font-weight: 900; color: #f8fbff; }
        .teacher-promotions-desc { max-width: 760px; color: #a9bbd5; line-height: 1.75; }
        .teacher-promotions-stat, .teacher-promotions-card, .teacher-promotions-item, .teacher-promotions-empty { background: rgba(18, 28, 50, 0.72); border: 1px solid rgba(96, 165, 250, 0.16); box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18); }
        .teacher-promotions-stat { min-width: 200px; padding: 1.15rem 1.25rem; border-radius: 22px; text-align: right; }
        .teacher-promotions-stat strong { display: block; font-size: clamp(1.8rem, 4vw, 2.8rem); line-height: 1; color: #f8fbff; }
        .teacher-promotions-stat span { display: block; margin-top: 0.45rem; color: #a9bbd5; }
        .teacher-promotions-card { border-radius: 24px; padding: 1.4rem; }
        .teacher-promotions-card__head { margin-bottom: 1rem; }
        .teacher-promotions-card__head h4 { margin-bottom: 0.35rem; color: #f8fbff; font-weight: 800; }
        .teacher-promotions-card__head p { color: #a9bbd5; }
        .teacher-promotions-form { display: grid; gap: 1rem; }
        .teacher-promotions-form .form-label { color: #dce9ff; font-weight: 700; }
        .teacher-promotions-form .form-control, .teacher-promotions-form .form-select { background: rgba(15, 23, 42, 0.72); border-color: rgba(96, 165, 250, 0.18); color: #f8fbff; }
        .teacher-promotions-form .form-text, .teacher-promotions-form__hint { color: #94a3b8; }
        .teacher-promotions-form__footer { display: flex; justify-content: space-between; gap: 1rem; align-items: center; }
        .teacher-promotions-history { display: grid; gap: 1rem; }
        .teacher-promotions-item { border-radius: 20px; padding: 1rem 1.05rem; }
        .teacher-promotions-item__meta, .teacher-promotions-item__footer { display: flex; justify-content: space-between; gap: 1rem; align-items: center; }
        .teacher-promotions-item__filters { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem; }
        .teacher-promotions-pill { display: inline-flex; align-items: center; padding: 0.3rem 0.7rem; border-radius: 999px; background: rgba(59, 130, 246, 0.16); color: #bfdbfe; font-size: 0.75rem; font-weight: 800; }
        .teacher-promotions-pill--soft { background: rgba(148, 163, 184, 0.16); color: #dbeafe; }
        .teacher-promotions-time, .teacher-promotions-item__footer span { color: #94a3b8; font-size: 0.9rem; }
        .teacher-promotions-item h5 { margin: 0.85rem 0 0.45rem; color: #f8fbff; font-weight: 800; }
        .teacher-promotions-item p { color: #a9bbd5; line-height: 1.75; margin-bottom: 0.85rem; white-space: pre-line; }
        .teacher-promotions-item__footer strong { color: #dce9ff; }
        .teacher-promotions-empty { border-radius: 22px; padding: 2.5rem 1.5rem; text-align: center; }
        .teacher-promotions-empty__icon { width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 22px; display: grid; place-items: center; background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(37, 99, 235, 0.24)); color: #dbeafe; font-size: 1.8rem; }
        .teacher-promotions-empty h4 { color: #f8fbff; margin-bottom: 0.55rem; }
        .teacher-promotions-empty p { color: #a9bbd5; max-width: 560px; margin: 0 auto; line-height: 1.75; }
        html[data-theme="light"] .teacher-promotions-shell { background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 28%), linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%); }
        html[data-theme="light"] .teacher-promotions-kicker { background: rgba(37, 99, 235, 0.12); color: #1d4ed8; }
        html[data-theme="light"] .teacher-promotions-title, html[data-theme="light"] .teacher-promotions-card__head h4, html[data-theme="light"] .teacher-promotions-item h5, html[data-theme="light"] .teacher-promotions-empty h4 { color: #0f172a; }
        html[data-theme="light"] .teacher-promotions-desc, html[data-theme="light"] .teacher-promotions-stat span, html[data-theme="light"] .teacher-promotions-card__head p, html[data-theme="light"] .teacher-promotions-item p, html[data-theme="light"] .teacher-promotions-empty p, html[data-theme="light"] .teacher-promotions-time, html[data-theme="light"] .teacher-promotions-item__footer span, html[data-theme="light"] .teacher-promotions-form .form-text, html[data-theme="light"] .teacher-promotions-form__hint { color: #475569; }
        html[data-theme="light"] .teacher-promotions-stat, html[data-theme="light"] .teacher-promotions-card, html[data-theme="light"] .teacher-promotions-item, html[data-theme="light"] .teacher-promotions-empty { background: var(--admin-surface); border-color: var(--admin-border); box-shadow: var(--admin-card-shadow); }
        html[data-theme="light"] .teacher-promotions-stat strong, html[data-theme="light"] .teacher-promotions-item__footer strong, html[data-theme="light"] .teacher-promotions-form .form-label { color: #0f172a; }
        html[data-theme="light"] .teacher-promotions-form .form-control, html[data-theme="light"] .teacher-promotions-form .form-select { background: #fff; border-color: var(--admin-border); color: #0f172a; }
        html[data-theme="light"] .teacher-promotions-pill { background: rgba(37, 99, 235, 0.12); color: #1d4ed8; }
        html[data-theme="light"] .teacher-promotions-pill--soft { background: rgba(148, 163, 184, 0.16); color: #334155; }
        @media (max-width: 991.98px) { .teacher-promotions-hero, .teacher-promotions-form__footer, .teacher-promotions-item__meta, .teacher-promotions-item__footer { flex-direction: column; align-items: flex-start; } .teacher-promotions-stat { text-align: left; } }
    </style>
@endsection
