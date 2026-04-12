@extends('layouts.teacher')

@section('content')
    @php
        $typeLabels = [
            'all' => __('teacher::dashboard.activity_logs.all_types'),
            'students' => __('teacher::dashboard.activity_logs.type_labels.students'),
            'courses' => __('teacher::dashboard.activity_logs.type_labels.courses'),
            'coupons' => __('teacher::dashboard.activity_logs.type_labels.coupons'),
        ];
        $typeBadgeClass = function ($logName) {
            return match ($logName) {
                'teacher_student_management' => 'is-students',
                'teacher_course_management' => 'is-courses',
                'teacher_coupon_management' => 'is-coupons',
                default => 'is-neutral',
            };
        };
        $typeLabel = function ($logName) {
            return match ($logName) {
                'teacher_student_management' => __('teacher::dashboard.activity_logs.type_labels.students'),
                'teacher_course_management' => __('teacher::dashboard.activity_logs.type_labels.courses'),
                'teacher_coupon_management' => __('teacher::dashboard.activity_logs.type_labels.coupons'),
                default => __('teacher::dashboard.activity_logs.all_types'),
            };
        };
        $activityTitle = function ($activity) {
            return match ($activity->action) {
                'note_saved' => 'Cap nhat ghi chu hoc vien',
                'grant_created' => 'Cap quyen hoc thu cong',
                'grant_revoked' => 'Thu hoi quyen hoc',
                'certificate_issued' => 'Cap chung chi',
                'certificate_reissued' => 'Cap lai chung chi',
                'certificate_revoked' => 'Thu hoi chung chi',
                'course_created' => 'Tao khoa hoc',
                'course_updated' => 'Cap nhat khoa hoc',
                'course_duplicated' => 'Nhan ban khoa hoc',
                'course_published' => 'Dua khoa hoc len publish',
                'course_moved_to_draft' => 'Chuyen khoa hoc ve nhap',
                'course_priority_enabled' => 'Bat uu tien giu active',
                'course_priority_disabled' => 'Tat uu tien giu active',
                'course_deleted' => 'Chuyen khoa hoc vao thung rac',
                'course_restored' => 'Khoi phuc khoa hoc',
                'course_force_deleted' => 'Xoa vinh vien khoa hoc',
                'coupon_created' => 'Tao ma giam gia',
                'coupon_updated' => 'Cap nhat ma giam gia',
                'coupon_deleted' => 'Xoa ma giam gia',
                'coupon_priority_enabled' => 'Bat uu tien ma giam gia',
                'coupon_priority_disabled' => 'Tat uu tien ma giam gia',
                'coupon_students_updated' => 'Cap nhat hoc vien ap dung coupon',
                'coupon_courses_updated' => 'Cap nhat khoa hoc ap dung coupon',
                default => $activity->description ?: $activity->action,
            };
        };
        $activitySubject = function ($activity) {
            $properties = is_array($activity->properties) ? $activity->properties : [];

            return match ($activity->log_name) {
                'teacher_student_management' => !empty($properties['student_name'])
                    ? __('teacher::dashboard.activity_logs.subject_student', ['name' => $properties['student_name']])
                    : null,
                'teacher_course_management' => !empty($properties['course_name'])
                    ? __('teacher::dashboard.activity_logs.subject_course', ['name' => $properties['course_name']])
                    : null,
                'teacher_coupon_management' => !empty($properties['coupon_code'])
                    ? __('teacher::dashboard.activity_logs.subject_coupon', ['code' => $properties['coupon_code']])
                    : null,
                default => null,
            };
        };
        $activityMeta = function ($activity) {
            $properties = is_array($activity->properties) ? $activity->properties : [];
            $meta = [];

            if (!empty($properties['reason_label'])) {
                $meta[] = $properties['reason_label'];
            }
            if (!empty($properties['certificate_code'])) {
                $meta[] = 'Ma chung chi: ' . $properties['certificate_code'];
            }
            if (!empty($properties['tag_label'])) {
                $meta[] = 'Tag: ' . $properties['tag_label'];
            }
            if (!empty($properties['duplicate_course_name'])) {
                $meta[] = 'Ban sao: ' . $properties['duplicate_course_name'];
            }
            if (isset($properties['student_count'])) {
                $meta[] = 'Hoc vien ap dung: ' . (int) $properties['student_count'];
            }
            if (isset($properties['course_count'])) {
                $meta[] = 'Khoa hoc ap dung: ' . (int) $properties['course_count'];
            }
            if (!empty($properties['issue_source'])) {
                $meta[] = 'Nguon: ' . match ($properties['issue_source']) {
                    'manual' => 'Thu cong',
                    'auto_completion' => 'Tu dong khi dat 100%',
                    default => $properties['issue_source'],
                };
            }

            return $meta;
        };
    @endphp

    <div class="teacher-panel teacher-activity-shell">
        <div class="teacher-activity-hero">
            <div>
                <span class="teacher-activity-kicker">{{ __('teacher::dashboard.nav.activity_logs') }}</span>
                <h3 class="teacher-activity-title">{{ __('teacher::dashboard.activity_logs.title') }}</h3>
                <p class="teacher-activity-desc mb-0">{{ __('teacher::dashboard.activity_logs.description') }}</p>
            </div>
            <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-outline-secondary">
                {{ __('teacher::dashboard.package_features.upgrade_cta') }}
            </a>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        <div class="teacher-activity-stats">
            <article class="teacher-activity-stat">
                <span>{{ __('teacher::dashboard.activity_logs.stats_total') }}</span>
                <strong>{{ $summary['total'] ?? 0 }}</strong>
            </article>
            <article class="teacher-activity-stat">
                <span>{{ __('teacher::dashboard.activity_logs.stats_students') }}</span>
                <strong>{{ $summary['students'] ?? 0 }}</strong>
            </article>
            <article class="teacher-activity-stat">
                <span>{{ __('teacher::dashboard.activity_logs.stats_courses') }}</span>
                <strong>{{ $summary['courses'] ?? 0 }}</strong>
            </article>
            <article class="teacher-activity-stat">
                <span>{{ __('teacher::dashboard.activity_logs.stats_coupons') }}</span>
                <strong>{{ $summary['coupons'] ?? 0 }}</strong>
            </article>
        </div>

        <form method="GET" class="teacher-activity-filters">
            <div class="teacher-activity-filter">
                <label for="teacher-activity-search">{{ __('teacher::dashboard.orders.filters.search') }}</label>
                <input
                    id="teacher-activity-search"
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="{{ __('teacher::dashboard.activity_logs.search_placeholder') }}">
            </div>
            <div class="teacher-activity-filter">
                <label for="teacher-activity-type">{{ __('teacher::dashboard.activity_logs.filter_type') }}</label>
                <select id="teacher-activity-type" name="type" class="form-select">
                    @foreach ($typeLabels as $typeKey => $label)
                        <option value="{{ $typeKey }}" @selected($selectedType === $typeKey)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="teacher-activity-filter teacher-activity-filter--actions">
                <button type="submit" class="btn btn-primary">{{ __('teacher::dashboard.activity_logs.actions.filter') }}</button>
                <a href="{{ route('teacher.dashboard.activity-logs') }}" class="btn btn-outline-secondary">{{ __('teacher::dashboard.activity_logs.actions.reset') }}</a>
            </div>
        </form>

        <section class="teacher-activity-timeline">
            <div class="teacher-activity-timeline__head">
                <div>
                    <h4>{{ __('teacher::dashboard.activity_logs.timeline_title') }}</h4>
                    <p class="mb-0">{{ __('teacher::dashboard.activity_logs.timeline_hint') }}</p>
                </div>
            </div>

            <div class="teacher-activity-list">
                @forelse ($logs as $activity)
                    @php
                        $properties = is_array($activity->properties) ? $activity->properties : [];
                        $subjectLine = $activitySubject($activity);
                        $metaItems = $activityMeta($activity);
                    @endphp
                    <article class="teacher-activity-item">
                        <div class="teacher-activity-item__rail"></div>
                        <div class="teacher-activity-item__body">
                            <div class="teacher-activity-item__head">
                                <div>
                                    <div class="teacher-activity-item__badges">
                                        <span class="teacher-activity-item__badge {{ $typeBadgeClass($activity->log_name) }}">
                                            {{ $typeLabel($activity->log_name) }}
                                        </span>
                                        <span class="teacher-activity-item__time">
                                            {{ optional($activity->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    <h5>{{ $activityTitle($activity) }}</h5>
                                </div>
                            </div>

                            @if ($subjectLine)
                                <div class="teacher-activity-item__subject">{{ $subjectLine }}</div>
                            @endif

                            @if ($activity->description)
                                <p class="teacher-activity-item__desc">{{ $activity->description }}</p>
                            @endif

                            @if (!empty($metaItems))
                                <div class="teacher-activity-item__meta">
                                    @foreach ($metaItems as $metaItem)
                                        <span>{{ $metaItem }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if (!empty($properties['note']) && in_array($activity->action, ['grant_created', 'note_saved', 'certificate_issued', 'certificate_reissued'], true))
                                <div class="teacher-activity-item__note">{{ $properties['note'] }}</div>
                            @endif
                            @if (!empty($properties['revoke_reason']) && $activity->action === 'certificate_revoked')
                                <div class="teacher-activity-item__note">{{ $properties['revoke_reason'] }}</div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="teacher-activity-empty">{{ __('teacher::dashboard.activity_logs.empty') }}</div>
                @endforelse
            </div>

            @if ($logs->hasPages())
                <div class="teacher-activity-pagination">
                    {{ $logs->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-activity-shell {
            display: grid;
            gap: 1.25rem;
            padding: 1.4rem;
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.12), transparent 28%),
                linear-gradient(180deg, rgba(13, 24, 45, 0.96), rgba(11, 21, 40, 0.98));
            border: 1px solid rgba(96, 165, 250, 0.14);
            box-shadow: 0 22px 60px rgba(2, 6, 23, 0.34);
        }

        .teacher-activity-hero,
        .teacher-activity-filters,
        .teacher-activity-timeline {
            border-radius: 24px;
            border: 1px solid rgba(96, 165, 250, 0.14);
            background: rgba(15, 23, 42, 0.78);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
        }

        .teacher-activity-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.5rem;
        }

        .teacher-activity-kicker {
            display: inline-flex;
            align-items: center;
            padding: 0.38rem 0.72rem;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.16);
            color: #93c5fd;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .teacher-activity-title {
            margin: 0.85rem 0 0.35rem;
            font-size: 2rem;
            font-weight: 800;
            color: #eff6ff;
        }

        .teacher-activity-desc {
            max-width: 760px;
            color: #9fb1cc;
            line-height: 1.7;
        }

        .teacher-activity-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .teacher-activity-stat {
            padding: 1rem 1.15rem;
            border-radius: 20px;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(96, 165, 250, 0.14);
        }

        .teacher-activity-stat span {
            display: block;
            color: #8da3c4;
            font-size: 0.86rem;
        }

        .teacher-activity-stat strong {
            display: block;
            margin-top: 0.45rem;
            color: #fff;
            font-size: 1.7rem;
            font-weight: 800;
        }

        .teacher-activity-filters {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(220px, 1fr) auto;
            gap: 1rem;
            padding: 1.25rem;
        }

        .teacher-activity-filter label {
            display: block;
            margin-bottom: 0.45rem;
            color: #cbd5e1;
            font-weight: 700;
        }

        .teacher-activity-filter--actions {
            display: flex;
            align-items: end;
            gap: 0.75rem;
        }

        .teacher-activity-timeline {
            padding: 1.3rem;
        }

        .teacher-activity-timeline__head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .teacher-activity-timeline__head h4 {
            margin: 0;
            color: #eff6ff;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .teacher-activity-timeline__head p {
            color: #8da3c4;
        }

        .teacher-activity-list {
            display: grid;
            gap: 1rem;
        }

        .teacher-activity-item {
            position: relative;
            display: grid;
            grid-template-columns: 18px minmax(0, 1fr);
            gap: 0.95rem;
            align-items: stretch;
        }

        .teacher-activity-item__rail {
            position: relative;
        }

        .teacher-activity-item__rail::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: -1rem;
            left: 8px;
            width: 2px;
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.55), rgba(30, 41, 59, 0.15));
        }

        .teacher-activity-item:last-child .teacher-activity-item__rail::before {
            bottom: 0;
        }

        .teacher-activity-item__rail::after {
            content: '';
            position: absolute;
            top: 0.55rem;
            left: 3px;
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: #60a5fa;
            box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.14);
        }

        .teacher-activity-item__body {
            padding: 1rem 1.1rem;
            border-radius: 20px;
            background: rgba(12, 19, 35, 0.76);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .teacher-activity-item__head h5 {
            margin: 0.5rem 0 0;
            color: #f8fafc;
            font-size: 1.08rem;
            font-weight: 800;
        }

        .teacher-activity-item__badges {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .teacher-activity-item__badge,
        .teacher-activity-item__time,
        .teacher-activity-item__meta span {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.32rem 0.68rem;
            font-size: 0.74rem;
            font-weight: 700;
        }

        .teacher-activity-item__badge.is-students {
            color: #bfdbfe;
            background: rgba(59, 130, 246, 0.18);
        }

        .teacher-activity-item__badge.is-courses {
            color: #a7f3d0;
            background: rgba(16, 185, 129, 0.16);
        }

        .teacher-activity-item__badge.is-coupons {
            color: #fdba74;
            background: rgba(249, 115, 22, 0.16);
        }

        .teacher-activity-item__badge.is-neutral,
        .teacher-activity-item__time,
        .teacher-activity-item__meta span {
            color: #cbd5e1;
            background: rgba(148, 163, 184, 0.12);
        }

        .teacher-activity-item__subject {
            margin-top: 0.8rem;
            color: #cfe0ff;
            font-weight: 700;
        }

        .teacher-activity-item__desc {
            margin: 0.55rem 0 0;
            color: #9fb1cc;
            line-height: 1.7;
        }

        .teacher-activity-item__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin-top: 0.8rem;
        }

        .teacher-activity-item__note {
            margin-top: 0.8rem;
            padding: 0.85rem 0.95rem;
            border-radius: 16px;
            color: #dbeafe;
            background: rgba(30, 41, 59, 0.78);
            border: 1px solid rgba(96, 165, 250, 0.1);
            line-height: 1.7;
        }

        .teacher-activity-empty {
            padding: 1.5rem;
            border-radius: 20px;
            background: rgba(15, 23, 42, 0.58);
            border: 1px dashed rgba(148, 163, 184, 0.22);
            color: #94a3b8;
            text-align: center;
        }

        .teacher-activity-pagination {
            margin-top: 1rem;
        }

        html[data-theme="light"] .teacher-activity-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 28%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(241, 245, 249, 0.98));
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-activity-hero,
        html[data-theme="light"] .teacher-activity-filters,
        html[data-theme="light"] .teacher-activity-timeline,
        html[data-theme="light"] .teacher-activity-stat,
        html[data-theme="light"] .teacher-activity-item__body {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-activity-title,
        html[data-theme="light"] .teacher-activity-timeline__head h4,
        html[data-theme="light"] .teacher-activity-item__head h5,
        html[data-theme="light"] .teacher-activity-stat strong {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-activity-desc,
        html[data-theme="light"] .teacher-activity-timeline__head p,
        html[data-theme="light"] .teacher-activity-stat span,
        html[data-theme="light"] .teacher-activity-item__desc {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-activity-item__subject {
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-activity-item__note {
            color: #0f172a;
            background: rgba(219, 234, 254, 0.5);
            border-color: rgba(96, 165, 250, 0.18);
        }

        @media (max-width: 991.98px) {
            .teacher-activity-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .teacher-activity-filters {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .teacher-activity-hero {
                flex-direction: column;
            }

            .teacher-activity-stats {
                grid-template-columns: 1fr;
            }

            .teacher-activity-shell {
                padding: 1rem;
                border-radius: 24px;
            }
        }
    </style>
@endsection
