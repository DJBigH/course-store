@extends('layouts.teacher')

@section('content')
    @php
        $maintPackage = $teacher->application?->package;
        $maintStudent = $maintPackage?->isFeatureInMaintenance('can_manage_students') ?? false;
        $maintImport = $maintPackage?->isFeatureInMaintenance('can_import_export') ?? false;
    @endphp
    <div class="teacher-panel teacher-students-shell">
        <div class="teacher-students-hero">
            <div>
                <span class="teacher-students-kicker">{{ __('students::teacher/messages.hero_kicker') }}</span>
                <h3 class="teacher-students-title">{{ __('students::teacher/messages.title') }}</h3>
                <p class="teacher-students-desc mb-0">
                    {{ __('students::teacher/messages.description') }}
                </p>
            </div>

            <form method="GET" action="{{ route('teacher.dashboard.students') }}" class="teacher-students-search">
                <label for="teacher-students-search" class="form-label mb-2">{{ __('students::teacher/messages.search_label') }}</label>
                <div class="teacher-students-search__row">
                    <input
                        id="teacher-students-search"
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $directory['search'] }}"
                        placeholder="{{ __('students::teacher/messages.search_placeholder') }}">

                    <select name="course_id" class="form-select">
                        <option value="0">{{ __('students::teacher/messages.all_courses') }}</option>
                        @foreach ($directory['courseOptions'] as $courseOption)
                            <option value="{{ $courseOption->id }}" @selected($directory['selectedCourse'] === (int) $courseOption->id)>
                                {{ $courseOption->name_locale ?: $courseOption->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="tag" class="form-select">
                        <option value="">{{ __('students::teacher/messages.all_tags') }}</option>
                        <option value="potential" @selected($directory['tag'] === 'potential')>{{ __('students::teacher/messages.tags.potential') }}</option>
                        <option value="support_needed" @selected($directory['tag'] === 'support_needed')>{{ __('students::teacher/messages.tags.support_needed') }}</option>
                        <option value="vip" @selected($directory['tag'] === 'vip')>{{ __('students::teacher/messages.tags.vip') }}</option>
                    </select>

                    <select name="access_type" class="form-select">
                        <option value="all" @selected(($directory['access_type'] ?? 'all') === 'all')>{{ __('students::teacher/messages.access_types.all') }}</option>
                        <option value="paid" @selected(($directory['access_type'] ?? 'all') === 'paid')>{{ __('students::teacher/messages.access_types.paid') }}</option>
                        <option value="granted" @selected(($directory['access_type'] ?? 'all') === 'granted')>{{ __('students::teacher/messages.access_types.granted') }}</option>
                        <option value="both" @selected(($directory['access_type'] ?? 'all') === 'both')>{{ __('students::teacher/messages.access_types.both') }}</option>
                    </select>

                    <select name="sort" class="form-select">
                        <option value="recent_purchase" @selected($directory['sort'] === 'recent_purchase')>{{ __('students::teacher/messages.sort_options.recent_purchase') }}</option>
                        <option value="highest_spent" @selected($directory['sort'] === 'highest_spent')>{{ __('students::teacher/messages.sort_options.highest_spent') }}</option>
                        <option value="recent_learning" @selected($directory['sort'] === 'recent_learning')>{{ __('students::teacher/messages.sort_options.recent_learning') }}</option>
                    </select>
                </div>

                <div class="teacher-students-search__actions">
                    <button type="submit" class="btn btn-primary">{{ __('students::teacher/messages.filter_submit') }}</button>
                    @if ($studentFeatureState['can_grant_courses'] ?? false)
                        @if($maintStudent)
                             <button class="btn btn-outline-secondary" disabled>
                                {{ __('students::teacher/messages.grant_button') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                            </button>
                        @else
                            <a href="{{ route('teacher.dashboard.students.grants.create') }}" class="btn btn-outline-secondary">
                                {{ __('students::teacher/messages.grant_button') }}
                            </a>
                        @endif
                    @endif
                    @if ($teacher->packageHasFeature('can_import_export'))
                        @if($maintImport)
                            <button class="btn btn-outline-secondary" disabled>
                                {{ __('students::teacher/messages.export_excel') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                            </button>
                            <button class="btn btn-outline-secondary" disabled>
                                {{ __('students::teacher/messages.export_csv') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                            </button>
                        @else
                            <a href="{{ route('teacher.dashboard.students.export', array_merge(['format' => 'excel'], request()->query())) }}"
                                class="btn btn-outline-secondary">
                                {{ __('students::teacher/messages.export_excel') }}
                            </a>
                            <a href="{{ route('teacher.dashboard.students.export', array_merge(['format' => 'csv'], request()->query())) }}"
                                class="btn btn-outline-secondary">
                                {{ __('students::teacher/messages.export_csv') }}
                            </a>
                        @endif
                    @endif
                </div>
            </form>
        </div>

        @php($importState = $teacher->getFeatureState('can_import_export'))
        @if (!$importState['can_use'])
            @include('teacher::clients.dashboard.partials.package_feature_notice', [
                'featureState' => $importState,
                'message' => __('packages::teacher.package_features.import_export_locked'),
            ])
        @endif

        @if (($studentFeatureState['is_feature_locked'] ?? false) && ($importState['can_use']))
            @include('teacher::clients.dashboard.partials.package_feature_notice', [
                'message' => !($studentFeatureState['can_view_progress'] ?? false)
                    ? __('packages::teacher.package_features.students_locked_progress')
                    : __('packages::teacher.package_features.students_locked_grants'),
            ])
        @endif

        <div class="teacher-students-summary">
            <div class="teacher-students-summary__item">
                <span>{{ __('students::teacher/messages.summary.total') }}</span>
                <strong>{{ $directory['summary']['total_students'] }}</strong>
            </div>
            <div class="teacher-students-summary__item">
                <span>{{ __('students::teacher/messages.summary.orders') }}</span>
                <strong>{{ $directory['summary']['total_orders'] }}</strong>
            </div>
            <div class="teacher-students-summary__item">
                <span>{{ __('students::teacher/messages.summary.courses') }}</span>
                <strong>{{ $directory['summary']['total_courses'] }}</strong>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($students as $student)
                <div class="col-xl-6">
                    <article class="teacher-student-card">
                        <div class="teacher-student-card__head">
                            <div>
                                <div class="teacher-student-card__name-row">
                                    <h4 class="teacher-student-card__name mb-0">{{ $student->name }}</h4>
                                    @if ($student->deleted_at)
                                        <span class="teacher-student-card__badge is-muted">{{ __('students::teacher/messages.card.status_deleted') }}</span>
                                    @elseif (!$student->email_verified_at)
                                        <span class="teacher-student-card__badge is-warning">{{ __('students::teacher/messages.card.status_unverified') }}</span>
                                    @else
                                        <span class="teacher-student-card__badge is-success">{{ __('students::teacher/messages.card.status_verified') }}</span>
                                    @endif
                                </div>
                                <p class="teacher-student-card__meta mb-0">
                                    {{ __('students::teacher/messages.card.meta', [
                                        'courses' => $student->teacher_course_count,
                                        'orders' => $student->teacher_order_count,
                                    ]) }}
                                </p>
                            </div>
                            <div class="teacher-student-card__contact">
                                <a href="mailto:{{ $student->email }}" class="btn btn-outline-secondary btn-sm">{{ __('students::teacher/messages.card.contact_email') }}</a>
                                @if ($student->phone)
                                    <a href="tel:{{ $student->phone }}" class="btn btn-outline-secondary btn-sm">{{ __('students::teacher/messages.card.contact_call') }}</a>
                                @endif
                            </div>
                        </div>

                        <div class="teacher-student-card__grid">
                            <div class="teacher-student-card__info">
                                <span>{{ __('students::teacher/messages.card.email_label') }}</span>
                                <strong>{{ $student->email }}</strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>{{ __('students::teacher/messages.card.phone_label') }}</span>
                                <strong>{{ $student->phone ?: __('students::teacher/messages.card.not_updated') }}</strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>{{ __('students::teacher/messages.card.total_spent') }}</span>
                                <strong>{{ money($student->teacher_total_spent) }}</strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>{{ __('students::teacher/messages.card.last_purchase') }}</span>
                                <strong>{{ optional($student->teacher_last_purchase_at)->format('d/m/Y H:i') ?: __('students::teacher/messages.card.no_data') }}</strong>
                            </div>
                        </div>

                        <div class="teacher-student-card__grid teacher-student-card__grid--secondary">
                            <div class="teacher-student-card__info">
                                <span>{{ __('students::teacher/messages.card.last_learning') }}</span>
                                <strong>{{ optional($student->teacher_last_learning_at)->format('d/m/Y H:i') ?: __('students::teacher/messages.card.no_learning') }}</strong>
                            </div>
                            @if ($studentFeatureState['can_view_progress'] ?? false)
                                <div class="teacher-student-card__info">
                                    <span>{{ __('students::teacher/messages.card.progress') }}</span>
                                    <strong>{{ $student->teacher_progress_percent ?? 0 }}%</strong>
                                    <small>{{ $student->teacher_progress_completed_lessons ?? 0 }} / {{ $student->teacher_progress_total_lessons ?? 0 }} {{ __('students::teacher/messages.card.unit_lesson') }}</small>
                                </div>
                            @endif
                            <div class="teacher-student-card__info">
                                <span>{{ __('students::teacher/messages.card.internal_tag') }}</span>
                                <strong>
                                    {{ match ($student->teacher_tag) {
                                        'potential' => __('students::teacher/messages.tags.potential'),
                                        'support_needed' => __('students::teacher/messages.tags.support_needed'),
                                        'vip' => __('students::teacher/messages.tags.vip'),
                                        default => __('students::teacher/messages.tags.unclassified'),
                                    } }}
                                </strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>{{ __('students::teacher/messages.card.grant_count') }}</span>
                                <strong>{{ $student->teacher_grant_count ?? 0 }}</strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>{{ __('students::teacher/messages.card.access_type') }}</span>
                                <strong>
                                    @if ($student->teacher_order_count > 0 && ($student->teacher_grant_count ?? 0) > 0)
                                        {{ __('students::teacher/messages.card.access_types.both') }}
                                    @elseif ($student->teacher_order_count > 0)
                                        {{ __('students::teacher/messages.card.access_types.paid') }}
                                    @elseif (($student->teacher_grant_count ?? 0) > 0)
                                        {{ __('students::teacher/messages.card.access_types.granted') }}
                                    @else
                                        {{ __('students::teacher/messages.card.access_types.unknown') }}
                                    @endif
                                </strong>
                            </div>
                        </div>

                        @if ($studentFeatureState['can_view_progress'] ?? false)
                            <div class="teacher-student-card__progress">
                                <div class="teacher-student-card__progress-head">
                                    <span>{{ __('students::teacher/messages.card.progress_label') }}</span>
                                    <strong>{{ $student->teacher_progress_percent ?? 0 }}%</strong>
                                </div>
                                <div class="teacher-student-card__progress-bar">
                                    <span style="width: {{ $student->teacher_progress_percent ?? 0 }}%"></span>
                                </div>
                            </div>
                        @endif

                        <div class="teacher-student-card__courses">
                            <div class="teacher-student-card__section-title">{{ __('students::teacher/messages.card.courses_title') }}</div>
                            <div class="teacher-student-card__course-list">
                                @forelse ($student->teacher_courses_preview as $course)
                                    <span class="teacher-student-card__course-chip">{{ $course->name_locale ?: $course->name }}</span>
                                @empty
                                    <span class="teacher-student-card__empty">{{ __('students::teacher/messages.card.no_courses') }}</span>
                                @endforelse
                                @if ($student->teacher_courses_remaining > 0)
                                    <span class="teacher-student-card__course-chip is-more">
                                        {{ __('students::teacher/messages.card.remaining_courses', ['count' => $student->teacher_courses_remaining]) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="teacher-student-card__footer">
                            <div>
                                <span>{{ __('students::teacher/messages.card.address_label') }}</span>
                                <strong>{{ $student->address ?: __('students::teacher/messages.card.no_address') }}</strong>
                            </div>
                            <div class="teacher-student-card__footer-actions">
                                @if (!empty($student->teacher_note_preview))
                                    <span class="teacher-student-card__note-badge">{{ __('students::teacher/messages.card.has_note') }}</span>
                                @endif
                                @if (!empty($student->teacher_tag))
                                    <span class="teacher-student-card__tag-badge {{ 'is-' . $student->teacher_tag }}">
                                        {{ match ($student->teacher_tag) {
                                            'potential' => __('students::teacher/messages.tags.potential'),
                                            'support_needed' => __('students::teacher/messages.tags.support_needed'),
                                            'vip' => __('students::teacher/messages.tags.vip'),
                                            default => $student->teacher_tag,
                                        } }}
                                    </span>
                                @endif
                                <a href="{{ route('teacher.dashboard.students.show', $student->id) }}" class="btn btn-primary btn-sm">
                                    {{ __('students::teacher/messages.card.view_detail') }}
                                </a>
                                @if ($studentFeatureState['can_grant_courses'] ?? false)
                                    @if($maintStudent)
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            {{ __('students::teacher/messages.card.grant_action') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                                        </button>
                                    @else
                                        <a href="{{ route('teacher.dashboard.students.grants.create', ['student_id' => $student->id]) }}" class="btn btn-outline-secondary btn-sm">
                                            {{ __('students::teacher/messages.card.grant_action') }}
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="teacher-students-empty">
                        <div class="teacher-students-empty__icon"><i class="fas fa-user-graduate"></i></div>
                        <h4>{{ __('students::teacher/messages.empty') }}</h4>
                        <p class="mb-0">
                            {{ __('students::teacher/messages.empty_description') }}
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $students->links() }}
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-students-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-students-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(360px, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .teacher-students-kicker {
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

        .teacher-students-title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-students-desc {
            max-width: 780px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-students-search {
            padding: 1.15rem;
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.16);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
            align-self: end;
        }

        .teacher-students-search label {
            color: #dce9ff;
            font-weight: 700;
        }

        .teacher-students-search__row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }

        #teacher-students-search {
            grid-column: span 2;
        }

        .teacher-students-shell .form-control,
        .teacher-students-shell .form-select {
            background-color: rgba(11, 19, 36, 0.72);
            border-color: rgba(96, 165, 250, 0.15);
            color: #f8fbff;
        }

        .teacher-students-shell .form-control::placeholder {
            color: #64748b;
        }

        .teacher-students-shell .form-control:focus,
        .teacher-students-shell .form-select:focus {
            background-color: rgba(11, 19, 36, 0.9);
            border-color: #38bdf8;
            color: #fff;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.15);
        }

        html[data-theme="light"] .teacher-students-shell .form-control,
        html[data-theme="light"] .teacher-students-shell .form-select {
            background-color: #fff;
            border-color: #e2e8f0;
            color: #1e293b;
        }

        .teacher-students-search__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.9rem;
        }

        .teacher-students-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .teacher-students-summary__item,
        .teacher-student-card,
        .teacher-students-empty {
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-students-summary__item {
            padding: 1rem 1.15rem;
        }

        .teacher-students-summary__item span {
            display: block;
            color: #8ca6c6;
            margin-bottom: 0.45rem;
        }

        .teacher-students-summary__item strong {
            color: #ffffff;
            font-size: 1.8rem;
            font-weight: 900;
        }

        .teacher-student-card {
            height: 100%;
            padding: 1.25rem;
        }

        .teacher-student-card__head,
        .teacher-student-card__footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .teacher-student-card__head {
            margin-bottom: 1rem;
        }

        .teacher-student-card__name-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            align-items: center;
            margin-bottom: 0.4rem;
        }

        .teacher-student-card__name {
            color: #f8fbff;
            font-weight: 900;
            font-size: 1.3rem;
        }

        .teacher-student-card__meta {
            color: #9fb5d0;
            line-height: 1.65;
        }

        .teacher-student-card__badge,
        .teacher-student-card__tag-badge,
        .teacher-student-card__note-badge,
        .teacher-student-card__course-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-weight: 700;
        }

        .teacher-student-card__badge {
            padding: 0.32rem 0.7rem;
            font-size: 0.76rem;
        }

        .teacher-student-card__badge.is-success {
            background: rgba(34, 197, 94, 0.16);
            color: #86efac;
        }

        .teacher-student-card__badge.is-warning {
            background: rgba(245, 158, 11, 0.16);
            color: #fcd34d;
        }

        .teacher-student-card__badge.is-muted {
            background: rgba(148, 163, 184, 0.14);
            color: #cbd5e1;
        }

        .teacher-student-card__contact {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-content: flex-start;
            justify-content: flex-end;
        }

        .teacher-student-card__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .teacher-student-card__info {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: rgba(11, 19, 36, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .teacher-student-card__info span,
        .teacher-student-card__footer span,
        .teacher-student-card__section-title {
            display: block;
            color: #8ca6c6;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }

        .teacher-student-card__info strong,
        .teacher-student-card__footer strong {
            display: block;
            color: #f8fbff;
            font-weight: 700;
            line-height: 1.55;
            word-break: break-word;
        }

        .teacher-student-card__info small {
            display: block;
            margin-top: 0.32rem;
            color: #7f97b8;
        }

        .teacher-student-card__progress {
            margin-top: -0.2rem;
            margin-bottom: 1rem;
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: var(--admin-subtle-bg);
            border: 1px solid var(--admin-border);
        }

        .teacher-student-card__progress-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.45rem;
        }

        .teacher-student-card__progress-head span {
            color: #8ca6c6;
            font-size: 0.92rem;
        }

        .teacher-student-card__progress-head strong {
            color: var(--admin-text);
            font-size: 1rem;
            font-weight: 900;
        }

        .teacher-student-card__progress-bar {
            width: 100%;
            height: 10px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(148, 163, 184, 0.22);
        }

        .teacher-student-card__progress-bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #38bdf8 0%, #22c55e 100%);
        }

        .teacher-student-card__courses {
            margin-bottom: 1rem;
        }

        .teacher-student-card__course-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .teacher-student-card__course-chip {
            padding: 0.52rem 0.85rem;
            background: rgba(37, 99, 235, 0.14);
            color: #dbeafe;
            font-size: 0.88rem;
        }

        .teacher-student-card__course-chip.is-more {
            background: rgba(14, 165, 233, 0.12);
            color: #9dd8ff;
        }

        .teacher-student-card__empty {
            color: #8ca6c6;
        }

        .teacher-student-card__footer {
            padding-top: 1rem;
            border-top: 1px solid rgba(96, 165, 250, 0.12);
            align-items: flex-end;
        }

        .teacher-student-card__footer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            justify-content: flex-end;
        }

        .teacher-student-card__note-badge {
            padding: 0.42rem 0.7rem;
            background: rgba(34, 197, 94, 0.16);
            color: #9bf6bc;
            font-size: 0.78rem;
        }

        .teacher-student-card__tag-badge {
            padding: 0.42rem 0.7rem;
            font-size: 0.78rem;
        }

        .teacher-student-card__tag-badge.is-potential {
            background: rgba(59, 130, 246, 0.16);
            color: #93c5fd;
        }

        .teacher-student-card__tag-badge.is-support_needed {
            background: rgba(245, 158, 11, 0.16);
            color: #fcd34d;
        }

        .teacher-student-card__tag-badge.is-vip {
            background: rgba(168, 85, 247, 0.16);
            color: #d8b4fe;
        }

        .teacher-students-empty {
            padding: 2rem;
            text-align: center;
        }

        .teacher-students-empty__icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 1rem;
            border-radius: 20px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(37, 99, 235, 0.24));
            color: #dbeafe;
            font-size: 1.6rem;
        }

        .teacher-students-empty h4 {
            color: #f8fbff;
            margin-bottom: 0.55rem;
        }

        .teacher-students-empty p {
            color: #a9bbd5;
            max-width: 640px;
            margin: 0 auto;
            line-height: 1.75;
        }

        html[data-theme="light"] .teacher-students-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%);
        }

        html[data-theme="light"] .teacher-students-kicker {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-students-title {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-students-desc {
            color: #475569;
        }

        html[data-theme="light"] .teacher-students-search,
        html[data-theme="light"] .teacher-students-summary__item,
        html[data-theme="light"] .teacher-student-card,
        html[data-theme="light"] .teacher-students-empty {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-students-search label {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-students-summary__item span,
        html[data-theme="light"] .teacher-student-card__meta,
        html[data-theme="light"] .teacher-student-card__info span,
        html[data-theme="light"] .teacher-student-card__footer span,
        html[data-theme="light"] .teacher-student-card__section-title,
        html[data-theme="light"] .teacher-students-empty p {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-students-summary__item strong,
        html[data-theme="light"] .teacher-student-card__name,
        html[data-theme="light"] .teacher-student-card__info strong,
        html[data-theme="light"] .teacher-student-card__footer strong,
        html[data-theme="light"] .teacher-students-empty h4 {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-student-card__info {
            background: var(--admin-subtle-bg);
            border-color: var(--admin-border);
        }

        html[data-theme="light"] .teacher-student-card__course-chip {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-student-card__course-chip.is-more {
            background: rgba(14, 165, 233, 0.12);
            color: #0284c7;
        }

        html[data-theme="light"] .teacher-students-empty__icon {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(56, 189, 248, 0.14));
            color: #1d4ed8;
        }

        @media (max-width: 1199.98px) {
            .teacher-students-hero {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .teacher-students-search__row,
            .teacher-students-summary,
            .teacher-student-card__grid,
            .teacher-student-card__head,
            .teacher-student-card__footer {
                grid-template-columns: 1fr;
                flex-direction: column;
            }

            .teacher-student-card__footer-actions,
            .teacher-students-search__actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
@endsection
