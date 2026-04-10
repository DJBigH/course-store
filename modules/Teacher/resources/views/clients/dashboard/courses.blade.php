@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-courses-shell">
        @php
            $usedCourses = (int) ($usage['used'] ?? 0);
            $publishedCourses = (int) ($usage['published'] ?? $usedCourses);
            $totalCourses = (int) ($usage['total'] ?? $usedCourses);
            $limitLabel = $usage['limit_label'] ?? __('teacher::dashboard.courses.unlimited');
            $canCreate = (bool) ($usage['can_create'] ?? false);
            $canPublishMore = (bool) ($usage['can_publish_more'] ?? true);
            $isOverLimit = (bool) ($usage['is_over_limit'] ?? false);
            $overLimitBy = (int) ($usage['over_limit_by'] ?? 0);
        @endphp

        <div class="teacher-courses-hero">
            <div>
                <span class="teacher-courses-kicker">Khu vuc quan ly khoa hoc</span>
                <h3 class="teacher-courses-title">{{ __('teacher::dashboard.courses.title') }}</h3>
                <p class="teacher-courses-desc mb-0">
                    Quan ly khoa hoc theo huong an toan khi ha goi: khoa cu van giu nguyen, con limit se ap vao so khoa dang publish.
                </p>
            </div>
            <div class="teacher-courses-hero__actions">
                @if (!empty($usage))
                    <div class="teacher-courses-usage">
                        <span>{{ __('teacher::dashboard.courses.labels.published_count') }}</span>
                        <strong>{{ $publishedCourses }}</strong>
                        <span>/</span>
                        <strong>{{ $limitLabel }}</strong>
                        <span>{{ __('teacher::dashboard.courses.labels.published_courses') }}</span>
                    </div>
                @endif
                @if ($canCreate)
                    <a href="{{ route('teacher.dashboard.courses.create') }}" class="btn btn-primary btn-lg">
                        Tao khoa hoc moi
                    </a>
                @else
                    <div class="teacher-disabled-action-wrap">
                        <span class="teacher-disabled-action" title="{{ __('teacher::dashboard.courses.flash.publish_limit_reached', ['limit' => $usage['limit']]) }}">
                            <button type="button" class="btn btn-primary btn-lg" disabled>
                                Tao khoa hoc moi
                            </button>
                        </span>
                        <div class="teacher-disabled-action__note">
                            {{ __('teacher::dashboard.courses.flash.publish_limit_reached', ['limit' => $usage['limit']]) }}
                        </div>
                    </div>
                @endif
                <a href="{{ route('teacher.dashboard.courses.trash') }}" class="btn btn-outline-secondary">
                    {{ __('teacher::dashboard.courses.actions.trash') }}
                </a>
            </div>
        </div>

        @if (!$teacher->packageHasFeature('can_duplicate_courses'))
            <div class="alert alert-warning border-0 mb-4">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                    <div>
                        <strong>{{ __('teacher::dashboard.package_features.upsell_title') }}</strong>
                        <div class="mt-1 text-muted">{{ __('teacher::dashboard.package_features.courses_locked_duplicate') }}</div>
                    </div>
                    <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-warning">
                        {{ __('teacher::dashboard.package_features.upgrade_cta') }}
                    </a>
                </div>
            </div>
        @endif

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        @if (!empty($usage))
            <div class="alert {{ $isOverLimit ? 'alert-warning' : 'alert-info' }} border-0 mb-4">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                    <div>
                        <strong>{{ __('teacher::dashboard.courses.warnings.publish_limit_title') }}</strong>
                        <div class="mt-1 text-muted">
                            {{ __('teacher::dashboard.courses.warnings.publish_limit_summary', [
                                'published' => $publishedCourses,
                                'total' => $totalCourses,
                                'limit' => $limitLabel,
                            ]) }}
                        </div>
                        @if ($isOverLimit)
                            <div class="mt-2">
                                {{ __('teacher::dashboard.courses.warnings.publish_limit_over', ['count' => $overLimitBy]) }}
                            </div>
                        @endif
                    </div>
                    @if ($isOverLimit)
                        <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-warning">
                            {{ __('teacher::dashboard.package_features.upgrade_cta') }}
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <div class="teacher-courses-guide">
            <article class="teacher-courses-guide__item">
                <span class="teacher-courses-guide__step">1</span>
                <div>
                    <strong>Tao hoac giu lai khoa cu</strong>
                    <p class="mb-0">Khi ha goi, khoa hoc cu khong bi huy. Ban van co the tiep tuc chinh sua noi dung nhu binh thuong.</p>
                </div>
            </article>
            <article class="teacher-courses-guide__item">
                <span class="teacher-courses-guide__step">2</span>
                <div>
                    <strong>Tu chon khoa nao tiep tuc publish</strong>
                    <p class="mb-0">Neu vuot limit, hay dua mot so khoa ve ban nhap de giai phong slot publish cho khoa quan trong hon.</p>
                </div>
            </article>
            <article class="teacher-courses-guide__item">
                <span class="teacher-courses-guide__step">3</span>
                <div>
                    <strong>Soan bai hoc va cap nhat noi dung</strong>
                    <p class="mb-0">Ngay ca khi dang vuot limit, ban van co the tao khoa nhap moi va cap nhat bai hoc truoc khi publish.</p>
                </div>
            </article>
        </div>

        <div class="row g-3">
            @forelse ($courses as $course)
                @php
                    $isLockedCourse = (bool) $course->package_locked_at;
                    $canPublishThisCourse = (int) $course->status === 1 || $canPublishMore || $isLockedCourse;
                @endphp
                <div class="col-md-6">
                    <article class="teacher-course-card teacher-course-card--friendly">
                        <div class="teacher-course-card__head">
                            <div>
                                <strong class="d-block mb-2 teacher-course-card__title">{{ $course->name_locale }}</strong>
                                <div class="teacher-course-card__status {{ $course->status ? 'is-active' : 'is-hidden' }}">
                                    {{ $course->status ? __('teacher::dashboard.courses.status.published') : __('teacher::dashboard.courses.status.draft') }}
                                </div>
                                @if ($isLockedCourse)
                                    <div class="teacher-course-card__limit-badge">
                                        {{ __('teacher::dashboard.courses.labels.limited_actions_only') }}
                                    </div>
                                @endif
                                @if ($course->is_package_priority)
                                    <div class="teacher-course-card__priority">
                                        {{ __('teacher::dashboard.courses.labels.priority_active') }}
                                    </div>
                                @endif
                            </div>
                            @if (!$isLockedCourse)
                                <a href="{{ route('teacher.dashboard.courses.edit', $course->id) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('teacher::dashboard.courses.actions.edit') }}
                                </a>
                            @endif
                        </div>

                        <div class="teacher-course-card__stats">
                            <div class="teacher-course-card__stat">
                                <span>Bai hoc</span>
                                <strong>{{ $course->lessons_count }}</strong>
                            </div>
                            <div class="teacher-course-card__stat">
                                <span>Hoc vien da mua</span>
                                <strong>{{ $course->students_count }}</strong>
                            </div>
                            <div class="teacher-course-card__stat">
                                <span>Gia dang ban</span>
                                <strong>{{ money($course->sale_price ?: $course->price) }}</strong>
                            </div>
                            <div class="teacher-course-card__stat">
                                <span>Danh gia</span>
                                <strong>{{ $course->ratings_count > 0 ? number_format((float) $course->ratings_avg_rating, 1) . ' / 5' : '0.0 / 5' }}</strong>
                            </div>
                        </div>

                        <div class="teacher-course-card__help">
                            @if ($isLockedCourse)
                                {{ __('teacher::dashboard.courses.warnings.locked_manage_only') }}
                            @else
                                Ban van co the soan bai hoc va cap nhat noi dung cho khoa nay, ke ca khi khoa dang o trang thai nhap.
                            @endif
                        </div>

                        @if ($teacher->packageHasFeature('can_view_activity_logs'))
                            <div class="teacher-course-card__history">
                                <div class="teacher-course-card__history-title">Lich su thao tac gan nhat</div>
                                <div class="teacher-course-card__history-list">
                                    @forelse ($course->teacher_activity_preview ?? collect() as $activity)
                                        <article class="teacher-course-card__history-item">
                                            <strong>
                                                {{ match ($activity->action) {
                                                    'course_created' => 'Tao khoa hoc',
                                                    'course_updated' => 'Cap nhat khoa hoc',
                                                    'course_duplicated' => 'Nhan ban khoa hoc',
                                                    'course_published' => 'Dua len publish',
                                                    'course_moved_to_draft' => 'Chuyen ve ban nhap',
                                                    'course_priority_enabled' => 'Bat uu tien goi',
                                                    'course_priority_disabled' => 'Tat uu tien goi',
                                                    'course_deleted' => 'Dua vao thung rac',
                                                    'course_restored' => 'Khoi phuc khoa hoc',
                                                    'course_force_deleted' => 'Xoa vinh vien',
                                                    default => $activity->description ?: $activity->action,
                                                } }}
                                            </strong>
                                            <span>{{ optional($activity->created_at)->format('d/m/Y H:i') }}</span>
                                        </article>
                                    @empty
                                        <div class="teacher-course-card__history-empty">Chua co thao tac nao duoc ghi lai.</div>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            <div class="teacher-course-card__history teacher-course-card__history--locked">
                                <div class="teacher-course-card__history-title">Lich su thao tac gan nhat</div>
                                <div class="teacher-course-card__history-empty">
                                    {{ __('teacher::dashboard.package_features.activity_logs_locked') }}
                                </div>
                                <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-outline-warning mt-3">
                                    {{ __('teacher::dashboard.package_features.upgrade_cta') }}
                                </a>
                            </div>
                        @endif

                        @if ($isLockedCourse)
                            <div class="teacher-course-card__notice">
                                {{ __('teacher::dashboard.courses.warnings.lock_reason_package_limit_locked') }}
                            </div>
                        @elseif (!(int) $course->status && !$canPublishThisCourse)
                            <div class="teacher-course-card__notice">
                                {{ __('teacher::dashboard.courses.warnings.course_publish_blocked') }}
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <form method="POST" action="{{ route('teacher.dashboard.courses.priority', $course->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning">
                                    {{ $course->is_package_priority ? __('teacher::dashboard.courses.actions.unprioritize') : __('teacher::dashboard.courses.actions.prioritize') }}
                                </button>
                            </form>
                            @if (!$isLockedCourse)
                                <a href="{{ route('teacher.dashboard.lessons.index', $course->id) }}" class="btn btn-primary">
                                    {{ __('teacher::dashboard.courses.actions.lessons') }}
                                </a>
                                <a href="{{ route('teacher.dashboard.courses.edit', $course->id) }}" class="btn btn-outline-secondary">
                                    {{ __('teacher::dashboard.courses.actions.edit') }}
                                </a>
                            @endif
                            @if (!$isLockedCourse && $teacher->packageHasFeature('can_duplicate_courses'))
                                <form method="POST" action="{{ route('teacher.dashboard.courses.duplicate', $course->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-info">
                                        {{ __('teacher::dashboard.courses.actions.duplicate') }}
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('teacher.dashboard.courses.visibility', $course->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $course->status ? 0 : 1 }}">
                                <button type="submit" class="btn btn-outline-light">
                                    {{ $course->status ? __('teacher::dashboard.courses.actions.move_to_draft') : __('teacher::dashboard.courses.actions.publish') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('teacher.dashboard.courses.delete', $course->id) }}" onsubmit="return confirm('{{ __('teacher::dashboard.courses.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    Dua vao thung rac
                                </button>
                            </form>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="teacher-courses-empty">
                        <div class="teacher-courses-empty__icon">+</div>
                        <h4>{{ __('teacher::dashboard.courses.empty') }}</h4>
                        <p class="mb-0">
                            Hay bat dau bang viec tao khoa hoc dau tien. Ban co the luu ban nhap truoc, sau do moi quyet dinh khoa nao se duoc publish.
                        </p>
                        @if ($canCreate)
                            <a href="{{ route('teacher.dashboard.courses.create') }}" class="btn btn-primary mt-3">
                                Tao khoa hoc dau tien
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $courses->links() }}
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-courses-shell {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 28%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-courses-hero {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .teacher-courses-kicker {
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

        .teacher-courses-title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-courses-desc {
            max-width: 760px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-courses-hero__actions {
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            justify-content: flex-end;
            gap: 0.9rem;
            min-width: 340px;
        }

        .teacher-courses-usage {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            min-height: 48px;
            gap: 0.35rem;
            padding: 0.75rem 1.1rem;
            border: 1px solid rgba(96, 165, 250, 0.18);
            border-radius: 16px;
            background: rgba(18, 28, 50, 0.75);
            color: #dce9ff;
            font-weight: 600;
            line-height: 1.45;
        }

        .teacher-courses-usage strong {
            color: #fff;
            font-weight: 800;
        }

        .teacher-disabled-action {
            display: inline-flex;
            cursor: not-allowed;
        }

        .teacher-disabled-action-wrap {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.45rem;
        }

        .teacher-disabled-action .btn[disabled] {
            pointer-events: none;
            opacity: 0.55;
        }

        .teacher-disabled-action__note {
            display: none;
            max-width: 320px;
            color: #fbbf24;
            font-size: 0.82rem;
            line-height: 1.45;
        }

        .teacher-courses-guide {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .teacher-courses-guide__item,
        .teacher-courses-empty {
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-courses-guide__item {
            display: flex;
            gap: 1rem;
            padding: 1.15rem 1.2rem;
        }

        .teacher-courses-guide__step {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: #fff;
            font-weight: 900;
        }

        .teacher-courses-guide__item strong,
        .teacher-courses-empty h4 {
            color: #f8fbff;
        }

        .teacher-courses-guide__item p,
        .teacher-courses-empty p {
            color: #a9bbd5;
            line-height: 1.65;
        }

        .teacher-course-card--friendly {
            height: 100%;
            padding: 1.4rem;
            border: 1px solid rgba(96, 165, 250, 0.18);
            border-radius: 24px;
            background: rgba(18, 28, 50, 0.72);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-course-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .teacher-course-card__title {
            font-size: 1.35rem;
            color: #f8fbff;
        }

        .teacher-course-card__status {
            display: inline-flex;
            align-items: center;
            padding: 0.48rem 0.8rem;
            border-radius: 999px;
            font-size: 0.86rem;
            font-weight: 700;
        }

        .teacher-course-card__status.is-active {
            background: rgba(34, 197, 94, 0.15);
            color: #9ef0b3;
        }

        .teacher-course-card__status.is-hidden {
            background: rgba(148, 163, 184, 0.14);
            color: #c9d7ea;
        }

        .teacher-course-card__priority {
            display: inline-flex;
            margin-top: 0.55rem;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            background: rgba(250, 204, 21, 0.18);
            color: #fde68a;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .teacher-course-card__limit-badge {
            display: inline-flex;
            margin-top: 0.55rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: rgba(248, 113, 113, 0.18);
            color: #fecaca;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .teacher-course-card__stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .teacher-course-card__stat {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            border: 1px solid rgba(96, 165, 250, 0.14);
            background: rgba(15, 23, 42, 0.6);
        }

        .teacher-course-card__stat span {
            display: block;
            margin-bottom: 0.3rem;
            color: #8fb5e9;
            font-size: 0.85rem;
        }

        .teacher-course-card__stat strong {
            color: #fff;
            font-size: 1.05rem;
        }

        .teacher-course-card__help {
            padding: 0.9rem 1rem;
            border-radius: 16px;
            background: rgba(37, 99, 235, 0.12);
            color: #cfe4ff;
            line-height: 1.6;
        }

        .teacher-course-card__history {
            margin-top: 0.9rem;
            padding: 0.95rem 1rem;
            border-radius: 16px;
            border: 1px solid rgba(96, 165, 250, 0.14);
            background: rgba(15, 23, 42, 0.58);
        }

        .teacher-course-card__history-title {
            margin-bottom: 0.7rem;
            color: #dbeafe;
            font-size: 0.9rem;
            font-weight: 800;
        }

        .teacher-course-card__history-list {
            display: grid;
            gap: 0.55rem;
        }

        .teacher-course-card__history-item {
            display: flex;
            justify-content: space-between;
            gap: 0.9rem;
            align-items: flex-start;
        }

        .teacher-course-card__history-item strong {
            color: #f8fbff;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .teacher-course-card__history-item span,
        .teacher-course-card__history-empty {
            color: #8fb5e9;
            font-size: 0.8rem;
            line-height: 1.5;
        }

        .teacher-course-card__history--locked {
            border-style: dashed;
            background: rgba(245, 158, 11, 0.08);
        }

        .teacher-course-card__notice {
            margin-top: 0.9rem;
            padding: 0.85rem 1rem;
            border-radius: 16px;
            background: rgba(245, 158, 11, 0.14);
            color: #fde68a;
            line-height: 1.6;
        }

        .teacher-courses-empty {
            padding: 2rem;
            text-align: center;
        }

        .teacher-courses-empty__icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: #fff;
            font-size: 2rem;
            font-weight: 300;
        }

        html[data-theme="light"] .teacher-courses-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 28%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%);
        }

        html[data-theme="light"] .teacher-courses-kicker {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-courses-title {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-courses-desc {
            color: #475569;
        }

        html[data-theme="light"] .teacher-courses-usage {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            color: var(--admin-text);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-courses-usage strong {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-disabled-action__note {
            color: #b45309;
        }

        html[data-theme="light"] .teacher-courses-guide__item,
        html[data-theme="light"] .teacher-course-card--friendly,
        html[data-theme="light"] .teacher-courses-empty {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-courses-guide__item strong,
        html[data-theme="light"] .teacher-course-card__title,
        html[data-theme="light"] .teacher-courses-empty h4 {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-courses-guide__item p,
        html[data-theme="light"] .teacher-courses-empty p {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-course-card__stat {
            background: var(--admin-subtle-bg);
            border-color: var(--admin-border);
        }

        html[data-theme="light"] .teacher-course-card__stat span {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-course-card__stat strong {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-course-card__help {
            background: rgba(37, 99, 235, 0.08);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-course-card__notice {
            background: rgba(245, 158, 11, 0.12);
            color: #92400e;
        }

        html[data-theme="light"] .teacher-course-card__status.is-active {
            background: rgba(34, 197, 94, 0.16);
            color: #166534;
        }

        html[data-theme="light"] .teacher-course-card__status.is-hidden {
            background: rgba(148, 163, 184, 0.2);
            color: #475569;
        }

        html[data-theme="light"] .teacher-course-card__priority {
            background: rgba(250, 204, 21, 0.2);
            color: #92400e;
        }

        html[data-theme="light"] .teacher-course-card__limit-badge {
            background: rgba(248, 113, 113, 0.14);
            color: #b91c1c;
        }

        html[data-theme="light"] .teacher-courses-empty__icon {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.14), rgba(59, 130, 246, 0.18));
            color: #1d4ed8;
        }

        @media (max-width: 991.98px) {
            .teacher-courses-hero,
            .teacher-course-card__head {
                flex-direction: column;
            }

            .teacher-courses-hero__actions {
                justify-content: flex-start;
                min-width: 0;
            }

            .teacher-courses-usage {
                width: 100%;
            }

            .teacher-courses-guide {
                grid-template-columns: 1fr;
            }

            .teacher-course-card__stats {
                grid-template-columns: 1fr;
            }

            .teacher-disabled-action__note {
                display: block;
            }
        }
    </style>
@endsection
