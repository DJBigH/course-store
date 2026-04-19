@extends('layouts.teacher')

@php
    $accessLabel = match (true) {
        $summary['orders'] > 0 && $summary['grants'] > 0 => __('students::teacher/messages.show.summary.access_labels.paid_granted'),
        $summary['orders'] > 0 => __('students::teacher/messages.show.summary.access_labels.paid'),
        $summary['grants'] > 0 => __('students::teacher/messages.show.summary.access_labels.granted'),
        default => __('students::teacher/messages.show.summary.access_labels.unknown'),
    };
@endphp

@section('content')
    <div class="teacher-panel teacher-student-show-shell">
        <div class="teacher-student-show-hero">
            <div>
                <span class="teacher-student-show-kicker">{{ __('students::teacher/messages.show.hero_kicker') }}</span>
                <h3 class="teacher-student-show-title">{{ $student->name }}</h3>
                <p class="teacher-student-show-desc mb-0">
                    {{ __('students::teacher/messages.show.hero_description') }}
                </p>
            </div>
            <div class="teacher-student-show-actions">
                <a href="{{ route('teacher.dashboard.students') }}" class="btn btn-outline-secondary">{{ __('students::teacher/messages.show.back_cta') }}</a>
                @if ($studentFeatureState['can_grant_courses'])
                    <a href="{{ route('teacher.dashboard.students.grants.create', ['student_id' => $student->id]) }}" class="btn btn-outline-secondary">
                        {{ __('students::teacher/messages.show.grant_cta') }}
                    </a>
                @endif
                <a href="mailto:{{ $student->email }}" class="btn btn-primary">{{ __('students::teacher/messages.show.email_cta') }}</a>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif

        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        @if (!$studentFeatureState['can_grant_courses'] || !$studentFeatureState['can_view_progress'])
            @include('teacher::clients.dashboard.partials.package_feature_notice', [
                'message' => !$studentFeatureState['can_view_progress']
                    ? __('courses::teacher/messages.package_features.students_locked_progress')
                    : __('courses::teacher/messages.package_features.students_locked_grants'),
            ])
        @endif

        <div class="teacher-student-show-summary">
            <div class="teacher-student-show-summary__item">
                <span>{{ __('students::teacher/messages.show.summary.granted_courses') }}</span>
                <strong>{{ $summary['courses'] }}</strong>
            </div>
            <div class="teacher-student-show-summary__item">
                <span>{{ __('students::teacher/messages.show.summary.paid_orders') }}</span>
                <strong>{{ $summary['orders'] }}</strong>
            </div>
            <div class="teacher-student-show-summary__item">
                <span>{{ __('students::teacher/messages.show.summary.teacher_grants') }}</span>
                <strong>{{ $summary['grants'] }}</strong>
            </div>
            <div class="teacher-student-show-summary__item">
                <span>{{ __('students::teacher/messages.show.summary.access_type') }}</span>
                <strong>{{ $accessLabel }}</strong>
            </div>
            <div class="teacher-student-show-summary__item">
                <span>{{ __('students::teacher/messages.show.summary.total_spent') }}</span>
                <strong>{{ money($summary['spent']) }}</strong>
            </div>
            @if ($studentFeatureState['can_view_progress'])
                <div class="teacher-student-show-summary__item">
                    <span>{{ __('students::teacher/messages.show.summary.learning_progress') }}</span>
                    <strong>{{ $summary['progress_percent'] ?? 0 }}%</strong>
                    <small>{{ __('students::teacher/messages.show.summary.lessons_count', ['count' => $summary['completed_lessons'] ?? 0]) }} / {{ $summary['total_lessons'] ?? 0 }}</small>
                </div>
            @endif
            <div class="teacher-student-show-summary__item">
                <span>{{ __('students::teacher/messages.show.summary.last_purchase') }}</span>
                <strong>{{ optional($summary['last_purchase_at'])->format('d/m/Y H:i') ?: __('students::teacher/messages.show.summary.no_purchase_data') }}</strong>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4">
                <section class="teacher-student-show-card">
                    <h4>{{ __('students::teacher/messages.show.info.title') }}</h4>
                    <div class="teacher-student-show-info-list">
                        <div>
                            <span>{{ __('students::teacher/messages.show.info.email') }}</span>
                            <strong>{{ $student->email }}</strong>
                        </div>
                        <div>
                            <span>{{ __('students::teacher/messages.show.info.phone') }}</span>
                            <strong>{{ $student->phone ?: __('students::teacher/messages.show.info.not_updated') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('students::teacher/messages.show.info.address') }}</span>
                            <strong>{{ $student->address ?: __('students::teacher/messages.show.info.no_address') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('students::teacher/messages.show.info.status') }}</span>
                            <strong>
                                @if ($student->deleted_at)
                                    {{ __('students::teacher/messages.show.info.status_deleted') }}
                                @elseif (!$student->email_verified_at)
                                    {{ __('students::teacher/messages.show.info.status_unverified') }}
                                @else
                                    {{ __('students::teacher/messages.show.info.status_active') }}
                                @endif
                            </strong>
                        </div>
                    </div>
                </section>

                <section class="teacher-student-show-card">
                    <div class="teacher-student-show-card__head">
                        <div>
                            <h4 class="mb-1">{{ __('students::teacher/messages.show.internal_notes.title') }}</h4>
                            <p class="mb-0">{{ __('students::teacher/messages.show.internal_notes.help') }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('teacher.dashboard.students.note', $student->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('students::teacher/messages.show.internal_notes.tag_label') }}</label>
                            <select name="tag" class="form-select">
                                <option value="">{{ __('students::teacher/messages.show.internal_notes.no_tag') }}</option>
                                <option value="potential" @selected(old('tag', $note?->tag) === 'potential')>{{ __('students::teacher/messages.show.internal_notes.tag_potential') }}</option>
                                <option value="support_needed" @selected(old('tag', $note?->tag) === 'support_needed')>{{ __('students::teacher/messages.show.internal_notes.tag_support') }}</option>
                                <option value="vip" @selected(old('tag', $note?->tag) === 'vip')>{{ __('students::teacher/messages.show.internal_notes.tag_vip') }}</option>
                            </select>
                        </div>
                        <textarea
                            name="note"
                            class="form-control @error('note') is-invalid @enderror"
                            rows="8"
                            placeholder="{{ __('students::teacher/messages.show.internal_notes.placeholder') }}">{{ old('note', $note?->note) }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn btn-primary mt-3">{{ __('students::teacher/messages.show.internal_notes.submit') }}</button>
                    </form>
                </section>
            </div>

            <div class="col-xl-8">
                <section class="teacher-student-show-card">
                    <h4>{{ __('students::teacher/messages.show.courses.title') }}</h4>
                    <div class="teacher-student-show-course-grid">
                        @forelse ($courses as $course)
                            @php
                                $isGranted = $grants->contains(fn ($grant) => (int) $grant->course_id === (int) $course->id);
                                $isPaid = $details->contains(fn ($detail) => (int) $detail->course_id === (int) $course->id);
                            @endphp
                            <article class="teacher-student-show-course">
                                <div class="teacher-student-show-course__top">
                                    <strong>{{ $course->name_locale ?: $course->name }}</strong>
                                    <span>
                                        @if ($isPaid && $isGranted)
                                            {{ __('students::teacher/messages.show.courses.status.paid_granted') }}
                                        @elseif ($isPaid)
                                            {{ __('students::teacher/messages.show.courses.status.paid') }}
                                        @elseif ($isGranted)
                                            {{ __('students::teacher/messages.show.courses.status.granted') }}
                                        @else
                                            {{ __('students::teacher/messages.show.courses.status.active') }}
                                        @endif
                                    </span>
                                </div>

                                @if ($studentFeatureState['can_view_progress'])
                                <div class="teacher-student-show-course__progress">
                                    <div class="teacher-student-show-course__progress-head">
                                        <small>{{ __('students::teacher/messages.show.courses.progress_label') }}</small>
                                        <strong>{{ $course->teacher_progress_percent }}%</strong>
                                    </div>
                                    <div class="teacher-student-show-course__progress-bar">
                                        <span style="width: {{ $course->teacher_progress_percent }}%"></span>
                                    </div>
                                    <small>
                                        {{ __('students::teacher/messages.show.courses.lessons_completed', [
                                            'completed' => $course->teacher_progress_completed_lessons,
                                            'total' => $course->teacher_progress_total_lessons
                                        ]) }}
                                        @if ($course->teacher_progress_last_completed_at)
                                            • {{ __('students::teacher/messages.show.courses.last_activity', ['time' => optional($course->teacher_progress_last_completed_at)->format('d/m/Y H:i')]) }}
                                        @endif
                                    </small>
                                </div>
                                @endif
                            </article>
                        @empty
                            <p class="mb-0 text-white-50">{{ __('students::teacher/messages.show.courses.empty') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="teacher-student-show-card">
                    <h4>{{ __('students::teacher/messages.show.grants.title') }}</h4>
                    <div class="teacher-student-show-grant-list">
                        @forelse ($grants as $grant)
                            <article class="teacher-student-show-grant">
                                <div class="teacher-student-show-grant__head">
                                    <div>
                                        <strong>{{ $grant->course?->name_locale ?: $grant->course?->name ?: 'Khoa hoc' }}</strong>
                                        <span>
                                            {{ match ($grant->reason) {
                                                'gift' => __('students::teacher/messages.show.grants.reasons.gift'),
                                                'support' => __('students::teacher/messages.show.grants.reasons.support'),
                                                'special_trial' => __('students::teacher/messages.show.grants.reasons.special_trial'),
                                                'compensation' => __('students::teacher/messages.show.grants.reasons.compensation'),
                                                default => $grant->reason,
                                            } }}
                                            • {{ optional($grant->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    @if ($studentFeatureState['can_grant_courses'])
                                        <form method="POST" action="{{ route('teacher.dashboard.students.grants.revoke', ['student' => $student->id, 'grant' => $grant->id]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('{{ __('students::teacher/messages.show.grants.revoke_confirm') }}')">
                                                {{ __('students::teacher/messages.show.grants.revoke_cta') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                @if ($grant->note)
                                    <p class="mb-0">{{ $grant->note }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="mb-0 text-white-50">{{ __('students::teacher/messages.show.grants.empty') }}</p>
                        @endforelse
                    </div>
                </section>

                @if ($teacher->packageHasFeature('can_view_activity_logs'))
                    <section class="teacher-student-show-card">
                        <h4>{{ __('students::teacher/messages.show.activity.title') }}</h4>
                        <div class="teacher-student-show-activity-list">
                            @forelse ($activityHistory as $activity)
                                @php
                                    $activityProperties = is_array($activity->properties) ? $activity->properties : [];
                                @endphp
                                <article class="teacher-student-show-activity">
                                    <div class="teacher-student-show-activity__head">
                                        <div>
                                            <strong>
                                                {{ match ($activity->action) {
                                                    'note_saved' => __('courses::teacher/messages.activity_logs.actions.note_saved'),
                                                    'grant_created' => __('courses::teacher/messages.activity_logs.actions.grant_created'),
                                                    'grant_revoked' => __('courses::teacher/messages.activity_logs.actions.grant_revoked'),
                                                    default => $activity->description ?: $activity->action,
                                                } }}
                                            </strong>
                                            <span>{{ optional($activity->created_at)->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <span class="teacher-student-show-activity__badge">
                                            {{ $activityProperties['course_name'] ?? ($activityProperties['tag_label'] ?? __('courses::teacher/messages.activity_logs.type_labels.students')) }}
                                        </span>
                                    </div>
                                    <p class="mb-2">{{ $activity->description ?: __('students::teacher/messages.show.activity.no_desc') }}</p>
                                    @if (!empty($activityProperties['note_preview']))
                                        <small>{{ __('students::teacher/messages.show.activity.meta_note', ['content' => $activityProperties['note_preview']]) }}</small>
                                    @elseif (!empty($activityProperties['reason_label']) || !empty($activityProperties['reason']))
                                        <small>{{ __('students::teacher/messages.show.activity.meta_reason', ['content' => $activityProperties['reason_label'] ?? $activityProperties['reason']]) }}</small>
                                    @elseif (array_key_exists('had_paid_access', $activityProperties))
                                        <small>
                                            {{ $activityProperties['had_paid_access'] ? __('students::teacher/messages.show.activity.meta_paid_true') : __('students::teacher/messages.show.activity.meta_paid_false') }}
                                        </small>
                                    @endif
                                </article>
                            @empty
                                <p class="mb-0 text-white-50">{{ __('students::teacher/messages.show.activity.empty') }}</p>
                            @endforelse
                        </div>
                    </section>
                @else
                    <section class="teacher-student-show-card teacher-student-show-card--locked">
                        <h4>{{ __('students::teacher/messages.show.activity.title') }}</h4>
                        <p class="mb-3">{{ __('courses::teacher/messages.package_features.activity_logs_locked') }}</p>
                        <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-outline-warning">
                            {{ __('courses::teacher/messages.package_features.upgrade_cta') }}
                        </a>
                    </section>
                @endif

                <section class="teacher-student-show-card">
                    <h4>{{ __('students::teacher/messages.show.orders.title') }}</h4>
                    <div class="teacher-student-show-order-list">
                        @forelse ($orders as $order)
                            @php
                                $orderCourses = $details->where('order_id', $order->id)->pluck('courses')->filter()->unique('id')->values();
                            @endphp
                            <article class="teacher-student-show-order">
                                <div class="teacher-student-show-order__head">
                                    <div>
                                        <strong>{{ __('students::teacher/messages.show.orders.order_code', ['code' => $order->code ?: $order->id]) }}</strong>
                                        <span>
                                            {{ optional($order->payment_complete_date ?: $order->payment_date ?: $order->created_at)->format('d/m/Y H:i') ?: __('students::teacher/messages.show.orders.no_time') }}
                                        </span>
                                    </div>
                                    <span class="teacher-student-show-order__badge">
                                        {{ money((float) $details->where('order_id', $order->id)->sum('price')) }}
                                    </span>
                                </div>
                                <div class="teacher-student-show-order__courses">
                                    @foreach ($orderCourses as $course)
                                        <span>{{ $course->name_locale ?: $course->name }}</span>
                                    @endforeach
                                </div>
                            </article>
                        @empty
                            <p class="mb-0 text-white-50">{{ __('students::teacher/messages.show.orders.empty') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="teacher-student-show-card">
                    <h4>{{ __('students::teacher/messages.show.timeline.title') }}</h4>
                    <div class="teacher-student-show-timeline">
                        @forelse ($learningTimeline as $timelineItem)
                            <article class="teacher-student-show-timeline__item">
                                <div class="teacher-student-show-timeline__dot"></div>
                                <div class="teacher-student-show-timeline__content">
                                    <strong>{{ $timelineItem->lesson?->name_locale ?: $timelineItem->lesson?->name ?: 'Bai hoc' }}</strong>
                                    <span>
                                        {{ $timelineItem->course?->name_locale ?: $timelineItem->course?->name ?: 'Khoa hoc' }}
                                        • {{ __('students::teacher/messages.show.timeline.completed_at', ['time' => optional($timelineItem->completed_at)->format('d/m/Y H:i')]) }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <p class="mb-0 text-white-50">{{ __('students::teacher/messages.show.timeline.empty') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-student-show-shell {
            background:
                radial-gradient(circle at top right, var(--teacher-glow), transparent 30%),
                linear-gradient(180deg, var(--admin-surface-2) 0%, var(--admin-bg) 100%);
        }

        .teacher-student-show-hero {
            display: flex;
            justify-content: space-between;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .teacher-student-show-kicker {
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

        .teacher-student-show-title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.7rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-student-show-desc {
            max-width: 820px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-student-show-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-content: flex-start;
        }

        .teacher-student-show-summary {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .teacher-student-show-summary__item,
        .teacher-student-show-card {
            border: 1px solid var(--admin-border);
            border-radius: 24px;
            background: var(--admin-glass-bg);
            backdrop-filter: blur(12px);
            box-shadow: var(--admin-card-shadow);
        }

        .teacher-student-show-summary__item {
            padding: 1rem 1.15rem;
        }

        .teacher-student-show-summary__item span {
            display: block;
            color: #8ca6c6;
            margin-bottom: 0.45rem;
        }

        .teacher-student-show-summary__item strong {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 900;
        }

        .teacher-student-show-summary__item small {
            display: block;
            margin-top: 0.35rem;
            color: #7f97b8;
        }

        .teacher-student-show-card {
            padding: 1.25rem;
            height: 100%;
        }

        .teacher-student-show-card--locked {
            border-style: dashed;
            background: rgba(245, 158, 11, 0.08);
        }

        .teacher-student-show-card h4 {
            color: #f8fbff;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .teacher-student-show-card p {
            color: #9fb5d0;
        }

        .teacher-student-show-info-list {
            display: grid;
            gap: 0.95rem;
        }

        .teacher-student-show-info-list span,
        .teacher-student-show-course span {
            display: block;
            color: #8ca6c6;
            margin-bottom: 0.35rem;
        }

        .teacher-student-show-info-list strong,
        .teacher-student-show-course strong {
            color: #f8fbff;
            line-height: 1.55;
            word-break: break-word;
        }

        .teacher-student-show-card__head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .teacher-student-show-course-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .teacher-student-show-course {
            padding: 1rem;
            border-radius: 18px;
            background: rgba(11, 19, 36, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .teacher-student-show-course__top {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 0.8rem;
        }

        .teacher-student-show-course__progress {
            margin-top: 0.8rem;
        }

        .teacher-student-show-course__progress-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.35rem;
        }

        .teacher-student-show-course__progress-head small,
        .teacher-student-show-course__progress small {
            color: #8ca6c6;
        }

        .teacher-student-show-course__progress-head strong {
            color: #f8fbff;
            font-size: 0.95rem;
        }

        .teacher-student-show-course__progress-bar {
            height: 8px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(148, 163, 184, 0.16);
            margin-bottom: 0.45rem;
        }

        .teacher-student-show-activity-list {
            display: grid;
            gap: 0.85rem;
        }

        .teacher-student-show-activity {
            padding: 1rem;
            border-radius: 18px;
            background: rgba(11, 19, 36, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .teacher-student-show-activity__head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 0.45rem;
        }

        .teacher-student-show-activity__head span {
            color: #8ca6c6;
        }

        .teacher-student-show-activity__badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.72rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.14);
            color: #dbeafe !important;
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .teacher-student-show-activity small {
            color: #8ca6c6;
        }

        .teacher-student-show-course__progress-bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #38bdf8, #22c55e);
        }

        .teacher-student-show-grant-list,
        .teacher-student-show-order-list,
        .teacher-student-show-timeline {
            display: grid;
            gap: 0.85rem;
        }

        .teacher-student-show-grant,
        .teacher-student-show-order {
            padding: 1rem;
            border-radius: 18px;
            background: rgba(11, 19, 36, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .teacher-student-show-grant__head,
        .teacher-student-show-order__head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.8rem;
        }

        .teacher-student-show-grant__head strong,
        .teacher-student-show-order__head strong {
            display: block;
            color: #f8fbff;
            margin-bottom: 0.25rem;
        }

        .teacher-student-show-grant__head span,
        .teacher-student-show-order__head span {
            color: #8ca6c6;
        }

        .teacher-student-show-order__badge {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.14);
            color: #dbeafe !important;
            font-weight: 700;
            white-space: nowrap;
        }

        .teacher-student-show-order__courses {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .teacher-student-show-order__courses span {
            display: inline-flex;
            align-items: center;
            padding: 0.48rem 0.8rem;
            border-radius: 999px;
            background: rgba(14, 165, 233, 0.12);
            color: #9dd8ff;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .teacher-student-show-timeline__item {
            display: grid;
            grid-template-columns: 14px minmax(0, 1fr);
            gap: 0.9rem;
            align-items: start;
        }

        html[data-theme="light"] .teacher-student-show-shell {
            background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
        }

        html[data-theme="light"] .teacher-student-show-title {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-student-show-desc {
            color: #475569;
        }

        html[data-theme="light"] .teacher-student-show-summary__item,
        html[data-theme="light"] .teacher-student-show-card {
            background: #ffffff;
            border-color: var(--admin-border);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        }

        html[data-theme="light"] .teacher-student-show-summary__item strong,
        html[data-theme="light"] .teacher-student-show-card h4,
        html[data-theme="light"] .teacher-student-show-info-list strong,
        html[data-theme="light"] .teacher-student-show-course strong,
        html[data-theme="light"] .teacher-student-show-grant__head strong,
        html[data-theme="light"] .teacher-student-show-order__head strong,
        html[data-theme="light"] .teacher-student-show-timeline__content strong {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-student-show-course,
        html[data-theme="light"] .teacher-student-show-activity,
        html[data-theme="light"] .teacher-student-show-grant,
        html[data-theme="light"] .teacher-student-show-order {
            background: var(--admin-subtle-bg);
            border-color: var(--admin-border);
        }

        html[data-theme="light"] .teacher-student-show-summary__item span,
        html[data-theme="light"] .teacher-student-show-info-list span,
        html[data-theme="light"] .teacher-student-show-course span,
        html[data-theme="light"] .teacher-student-show-grant__head span,
        html[data-theme="light"] .teacher-student-show-order__head span,
        html[data-theme="light"] .teacher-student-show-timeline__content span,
        html[data-theme="light"] .teacher-student-show-activity__head span {
            color: #64748b;
        }

        html[data-theme="light"] .teacher-student-show-activity__badge,
        html[data-theme="light"] .teacher-student-show-order__badge {
            background: rgba(37, 99, 235, 0.08);
            color: #2563eb !important;
        }

        html[data-theme="light"] .teacher-student-show-order__courses span {
            background: rgba(14, 165, 233, 0.08);
            color: #0ea5e9;
        }

        @media (max-width: 1400px) {
            .teacher-student-show-summary {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 991px) {
            .teacher-student-show-hero {
                flex-direction: column;
            }

            .teacher-student-show-course-grid {
                grid-template-columns: 1fr;
            }

            .teacher-student-show-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 576px) {
            .teacher-student-show-summary {
                grid-template-columns: 1fr;
            }

            .teacher-student-show-grant__head,
            .teacher-student-show-order__head {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endsection
