@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-students-shell">
        <div class="teacher-students-hero">
            <div>
                <span class="teacher-students-kicker">Khu vuc hoc vien</span>
                <h3 class="teacher-students-title">Hoc vien cua toi</h3>
                <p class="teacher-students-desc mb-0">
                    Chi nhung hoc vien da mua khoa hoc cua ban moi xuat hien o day. Ban co the xem thong tin,
                    ghi chu noi bo va tim nhanh nhung hoc vien can cham soc nhieu hon.
                </p>
            </div>

            <form method="GET" action="{{ route('teacher.dashboard.students') }}" class="teacher-students-search">
                <label for="teacher-students-search" class="form-label mb-2">Tim theo ten, email hoac so dien thoai</label>
                <div class="teacher-students-search__row">
                    <input
                        id="teacher-students-search"
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $directory['search'] }}"
                        placeholder="Vi du: BigK hoac gmail.com">

                    <select name="course_id" class="form-select">
                        <option value="0">Tat ca khoa hoc</option>
                        @foreach ($directory['courseOptions'] as $courseOption)
                            <option value="{{ $courseOption->id }}" @selected($directory['selectedCourse'] === (int) $courseOption->id)>
                                {{ $courseOption->name_locale ?: $courseOption->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="tag" class="form-select">
                        <option value="">Tat ca tag noi bo</option>
                        <option value="potential" @selected($directory['tag'] === 'potential')>Tiem nang</option>
                        <option value="support_needed" @selected($directory['tag'] === 'support_needed')>Can ho tro</option>
                        <option value="vip" @selected($directory['tag'] === 'vip')>VIP</option>
                    </select>

                    <select name="access_type" class="form-select">
                        <option value="all" @selected(($directory['access_type'] ?? 'all') === 'all')>Da mua / duoc tang / tat ca</option>
                        <option value="paid" @selected(($directory['access_type'] ?? 'all') === 'paid')>Chi da mua</option>
                        <option value="granted" @selected(($directory['access_type'] ?? 'all') === 'granted')>Chi duoc tang</option>
                        <option value="both" @selected(($directory['access_type'] ?? 'all') === 'both')>Co ca hai</option>
                    </select>

                    <select name="sort" class="form-select">
                        <option value="recent_purchase" @selected($directory['sort'] === 'recent_purchase')>Moi mua gan day</option>
                        <option value="highest_spent" @selected($directory['sort'] === 'highest_spent')>Chi tieu cao nhat</option>
                        <option value="recent_learning" @selected($directory['sort'] === 'recent_learning')>Hoc gan nhat</option>
                    </select>
                </div>

                <div class="teacher-students-search__actions">
                    <button type="submit" class="btn btn-primary">Loc danh sach</button>
                    @if ($teacher->packageHasFeature('can_grant_courses'))
                        <a href="{{ route('teacher.dashboard.students.grants.create') }}" class="btn btn-outline-secondary">
                            Cap quyen hoc
                        </a>
                    @endif
                    @if ($teacher->packageHasFeature('can_export_students'))
                        <a href="{{ route('teacher.dashboard.students.export', array_merge(['format' => 'excel'], request()->query())) }}"
                            class="btn btn-outline-secondary">
                            Export Excel
                        </a>
                        <a href="{{ route('teacher.dashboard.students.export', array_merge(['format' => 'csv'], request()->query())) }}"
                            class="btn btn-outline-secondary">
                            Export CSV
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if (!$teacher->packageHasFeature('can_grant_courses') || !$teacher->packageHasFeature('can_export_students'))
            <div class="alert alert-warning border-0 mb-4">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                    <div>
                        <strong>{{ __('teacher::dashboard.package_features.upsell_title') }}</strong>
                        <div class="mt-1 text-muted">
                            @if (!$teacher->packageHasFeature('can_grant_courses') && !$teacher->packageHasFeature('can_export_students'))
                                {{ __('teacher::dashboard.package_features.students_locked_both') }}
                            @elseif (!$teacher->packageHasFeature('can_grant_courses'))
                                {{ __('teacher::dashboard.package_features.students_locked_grants') }}
                            @else
                                {{ __('teacher::dashboard.package_features.students_locked_export') }}
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-warning">
                        {{ __('teacher::dashboard.package_features.upgrade_cta') }}
                    </a>
                </div>
            </div>
        @endif

        <div class="teacher-students-summary">
            <div class="teacher-students-summary__item">
                <span>Tong hoc vien</span>
                <strong>{{ $directory['summary']['total_students'] }}</strong>
            </div>
            <div class="teacher-students-summary__item">
                <span>Don da thanh toan</span>
                <strong>{{ $directory['summary']['total_orders'] }}</strong>
            </div>
            <div class="teacher-students-summary__item">
                <span>Khoa hoc da ban</span>
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
                                        <span class="teacher-student-card__badge is-muted">Tai khoan da xoa mem</span>
                                    @elseif (!$student->email_verified_at)
                                        <span class="teacher-student-card__badge is-warning">Chua xac minh email</span>
                                    @else
                                        <span class="teacher-student-card__badge is-success">Co the lien he</span>
                                    @endif
                                </div>
                                <p class="teacher-student-card__meta mb-0">
                                    Mua {{ $student->teacher_course_count }} khoa hoc cua ban qua
                                    {{ $student->teacher_order_count }} don thanh toan.
                                </p>
                            </div>
                            <div class="teacher-student-card__contact">
                                <a href="mailto:{{ $student->email }}" class="btn btn-outline-secondary btn-sm">Email</a>
                                @if ($student->phone)
                                    <a href="tel:{{ $student->phone }}" class="btn btn-outline-secondary btn-sm">Goi</a>
                                @endif
                            </div>
                        </div>

                        <div class="teacher-student-card__grid">
                            <div class="teacher-student-card__info">
                                <span>Email</span>
                                <strong>{{ $student->email }}</strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>So dien thoai</span>
                                <strong>{{ $student->phone ?: 'Chua cap nhat' }}</strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>Chi tieu cho khoa hoc cua ban</span>
                                <strong>{{ money($student->teacher_total_spent) }}</strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>Lan mua gan nhat</span>
                                <strong>{{ optional($student->teacher_last_purchase_at)->format('d/m/Y H:i') ?: 'Chua co du lieu' }}</strong>
                            </div>
                        </div>

                        <div class="teacher-student-card__grid teacher-student-card__grid--secondary">
                            <div class="teacher-student-card__info">
                                <span>Hoc gan nhat</span>
                                <strong>{{ optional($student->teacher_last_learning_at)->format('d/m/Y H:i') ?: 'Chua hoc bai nao' }}</strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>Tag noi bo</span>
                                <strong>
                                    {{ match ($student->teacher_tag) {
                                        'potential' => 'Tiem nang',
                                        'support_needed' => 'Can ho tro',
                                        'vip' => 'VIP',
                                        default => 'Chua phan loai',
                                    } }}
                                </strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>Suat cap quyen hoc</span>
                                <strong>{{ $student->teacher_grant_count ?? 0 }}</strong>
                            </div>
                            <div class="teacher-student-card__info">
                                <span>Loai truy cap</span>
                                <strong>
                                    @if ($student->teacher_order_count > 0 && ($student->teacher_grant_count ?? 0) > 0)
                                        Da mua + duoc tang
                                    @elseif ($student->teacher_order_count > 0)
                                        Da mua
                                    @elseif (($student->teacher_grant_count ?? 0) > 0)
                                        Duoc tang
                                    @else
                                        Chua xac dinh
                                    @endif
                                </strong>
                            </div>
                        </div>

                        <div class="teacher-student-card__courses">
                            <div class="teacher-student-card__section-title">Khoa hoc hoc vien da mua cua ban</div>
                            <div class="teacher-student-card__course-list">
                                @forelse ($student->teacher_courses_preview as $course)
                                    <span class="teacher-student-card__course-chip">{{ $course->name_locale ?: $course->name }}</span>
                                @empty
                                    <span class="teacher-student-card__empty">Chua co khoa hoc nao.</span>
                                @endforelse
                                @if ($student->teacher_courses_remaining > 0)
                                    <span class="teacher-student-card__course-chip is-more">
                                        +{{ $student->teacher_courses_remaining }} khoa hoc khac
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="teacher-student-card__footer">
                            <div>
                                <span>Dia chi</span>
                                <strong>{{ $student->address ?: 'Chua cap nhat dia chi' }}</strong>
                            </div>
                            <div class="teacher-student-card__footer-actions">
                                @if (!empty($student->teacher_note_preview))
                                    <span class="teacher-student-card__note-badge">Da co ghi chu noi bo</span>
                                @endif
                                @if (!empty($student->teacher_tag))
                                    <span class="teacher-student-card__tag-badge {{ 'is-' . $student->teacher_tag }}">
                                        {{ match ($student->teacher_tag) {
                                            'potential' => 'Tiem nang',
                                            'support_needed' => 'Can ho tro',
                                            'vip' => 'VIP',
                                            default => $student->teacher_tag,
                                        } }}
                                    </span>
                                @endif
                                <a href="{{ route('teacher.dashboard.students.show', $student->id) }}" class="btn btn-primary btn-sm">
                                    Xem chi tiet
                                </a>
                                @if ($teacher->packageHasFeature('can_grant_courses'))
                                    <a href="{{ route('teacher.dashboard.students.grants.create', ['student_id' => $student->id]) }}" class="btn btn-outline-secondary btn-sm">
                                        Cap quyen hoc
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="teacher-students-empty">
                        <div class="teacher-students-empty__icon"><i class="fas fa-user-graduate"></i></div>
                        <h4>Chua co hoc vien nao thuoc kenh cua ban</h4>
                        <p class="mb-0">
                            Khi hoc vien mua khoa hoc cua ban va don duoc thanh toan thanh cong, thong tin hoc vien se hien o day.
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
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
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
