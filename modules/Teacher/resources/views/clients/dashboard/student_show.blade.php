@extends('layouts.teacher')

@php
    $accessLabel = match (true) {
        $summary['orders'] > 0 && $summary['grants'] > 0 => 'Da mua + duoc tang',
        $summary['orders'] > 0 => 'Da mua',
        $summary['grants'] > 0 => 'Duoc tang',
        default => 'Chua xac dinh',
    };
@endphp

@section('content')
    <div class="teacher-panel teacher-student-show-shell">
        <div class="teacher-student-show-hero">
            <div>
                <span class="teacher-student-show-kicker">Chi tiet hoc vien</span>
                <h3 class="teacher-student-show-title">{{ $student->name }}</h3>
                <p class="teacher-student-show-desc mb-0">
                    Day la thong tin hoc vien da mua hoac duoc cap quyen hoc trong cac khoa hoc cua ban.
                    Ban co the theo doi lich su mua, lich su cap quyen va luu ghi chu noi bo de cham soc hoc vien tot hon.
                </p>
            </div>
            <div class="teacher-student-show-actions">
                <a href="{{ route('teacher.dashboard.students') }}" class="btn btn-outline-secondary">Quay lai danh sach</a>
                <a href="{{ route('teacher.dashboard.students.grants.create', ['student_id' => $student->id]) }}" class="btn btn-outline-secondary">
                    Cap quyen hoc
                </a>
                <a href="mailto:{{ $student->email }}" class="btn btn-primary">Gui email</a>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif

        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        <div class="teacher-student-show-summary">
            <div class="teacher-student-show-summary__item">
                <span>Khoa hoc dang co quyen</span>
                <strong>{{ $summary['courses'] }}</strong>
            </div>
            <div class="teacher-student-show-summary__item">
                <span>Don da thanh toan</span>
                <strong>{{ $summary['orders'] }}</strong>
            </div>
            <div class="teacher-student-show-summary__item">
                <span>Suat teacher da cap</span>
                <strong>{{ $summary['grants'] }}</strong>
            </div>
            <div class="teacher-student-show-summary__item">
                <span>Loai truy cap</span>
                <strong>{{ $accessLabel }}</strong>
            </div>
            <div class="teacher-student-show-summary__item">
                <span>Tong chi tieu</span>
                <strong>{{ money($summary['spent']) }}</strong>
            </div>
            <div class="teacher-student-show-summary__item">
                <span>Lan mua gan nhat</span>
                <strong>{{ optional($summary['last_purchase_at'])->format('d/m/Y H:i') ?: 'Chua co du lieu' }}</strong>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4">
                <section class="teacher-student-show-card">
                    <h4>Thong tin lien he</h4>
                    <div class="teacher-student-show-info-list">
                        <div>
                            <span>Email</span>
                            <strong>{{ $student->email }}</strong>
                        </div>
                        <div>
                            <span>So dien thoai</span>
                            <strong>{{ $student->phone ?: 'Chua cap nhat' }}</strong>
                        </div>
                        <div>
                            <span>Dia chi</span>
                            <strong>{{ $student->address ?: 'Chua cap nhat dia chi' }}</strong>
                        </div>
                        <div>
                            <span>Trang thai tai khoan</span>
                            <strong>
                                @if ($student->deleted_at)
                                    Da xoa mem
                                @elseif (!$student->email_verified_at)
                                    Chua xac minh email
                                @else
                                    Dang hoat dong
                                @endif
                            </strong>
                        </div>
                    </div>
                </section>

                <section class="teacher-student-show-card">
                    <div class="teacher-student-show-card__head">
                        <div>
                            <h4 class="mb-1">Ghi chu noi bo</h4>
                            <p class="mb-0">Chi teacher moi nhin thay phan nay.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('teacher.dashboard.students.note', $student->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tag noi bo</label>
                            <select name="tag" class="form-select">
                                <option value="">Chua phan loai</option>
                                <option value="potential" @selected(old('tag', $note?->tag) === 'potential')>Tiem nang</option>
                                <option value="support_needed" @selected(old('tag', $note?->tag) === 'support_needed')>Can ho tro</option>
                                <option value="vip" @selected(old('tag', $note?->tag) === 'vip')>VIP</option>
                            </select>
                        </div>
                        <textarea
                            name="note"
                            class="form-control @error('note') is-invalid @enderror"
                            rows="8"
                            placeholder="Vi du: hoc vien can ho tro them, nen uu tien lien he, da duoc tang khoa hoc...">{{ old('note', $note?->note) }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn btn-primary mt-3">Luu ghi chu noi bo</button>
                    </form>
                </section>
            </div>

            <div class="col-xl-8">
                <section class="teacher-student-show-card">
                    <h4>Khoa hoc hoc vien dang co quyen</h4>
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
                                            Da mua + duoc tang
                                        @elseif ($isPaid)
                                            Da mua
                                        @elseif ($isGranted)
                                            Duoc tang
                                        @else
                                            Dang co quyen
                                        @endif
                                    </span>
                                </div>

                                <div class="teacher-student-show-course__progress">
                                    <div class="teacher-student-show-course__progress-head">
                                        <small>Tien do hoc</small>
                                        <strong>{{ $course->teacher_progress_percent }}%</strong>
                                    </div>
                                    <div class="teacher-student-show-course__progress-bar">
                                        <span style="width: {{ $course->teacher_progress_percent }}%"></span>
                                    </div>
                                    <small>
                                        {{ $course->teacher_progress_completed_lessons }} / {{ $course->teacher_progress_total_lessons }} bai da hoc
                                        @if ($course->teacher_progress_last_completed_at)
                                            • gan nhat {{ optional($course->teacher_progress_last_completed_at)->format('d/m/Y H:i') }}
                                        @endif
                                    </small>
                                </div>
                            </article>
                        @empty
                            <p class="mb-0 text-white-50">Hoc vien nay chua co quyen vao khoa hoc nao cua ban.</p>
                        @endforelse
                    </div>
                </section>

                <section class="teacher-student-show-card">
                    <h4>Lich su cap quyen hoc</h4>
                    <div class="teacher-student-show-grant-list">
                        @forelse ($grants as $grant)
                            <article class="teacher-student-show-grant">
                                <div class="teacher-student-show-grant__head">
                                    <div>
                                        <strong>{{ $grant->course?->name_locale ?: $grant->course?->name ?: 'Khoa hoc' }}</strong>
                                        <span>
                                            {{ match ($grant->reason) {
                                                'gift' => 'Qua tang',
                                                'support' => 'Ho tro',
                                                'special_trial' => 'Hoc thu dac biet',
                                                'compensation' => 'Bu quyen truy cap',
                                                default => $grant->reason,
                                            } }}
                                            • {{ optional($grant->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    <form method="POST" action="{{ route('teacher.dashboard.students.grants.revoke', ['student' => $student->id, 'grant' => $grant->id]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Thu hoi suat cap quyen hoc nay? Khoa hoc da mua that se khong bi anh huong.')">
                                            Thu hoi quyen hoc
                                        </button>
                                    </form>
                                </div>
                                @if ($grant->note)
                                    <p class="mb-0">{{ $grant->note }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="mb-0 text-white-50">Chua co lan cap quyen hoc nao cho hoc vien nay.</p>
                        @endforelse
                    </div>
                </section>

                <section class="teacher-student-show-card">
                    <h4>Lich su mua khoa hoc cua ban</h4>
                    <div class="teacher-student-show-order-list">
                        @forelse ($orders as $order)
                            @php
                                $orderCourses = $details->where('order_id', $order->id)->pluck('courses')->filter()->unique('id')->values();
                            @endphp
                            <article class="teacher-student-show-order">
                                <div class="teacher-student-show-order__head">
                                    <div>
                                        <strong>Don #{{ $order->code ?: $order->id }}</strong>
                                        <span>
                                            {{ optional($order->payment_complete_date ?: $order->payment_date ?: $order->created_at)->format('d/m/Y H:i') ?: 'Chua co thoi gian' }}
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
                            <p class="mb-0 text-white-50">Hoc vien nay chua mua khoa hoc nao cua ban.</p>
                        @endforelse
                    </div>
                </section>

                <section class="teacher-student-show-card">
                    <h4>Timeline hoat dong hoc</h4>
                    <div class="teacher-student-show-timeline">
                        @forelse ($learningTimeline as $timelineItem)
                            <article class="teacher-student-show-timeline__item">
                                <div class="teacher-student-show-timeline__dot"></div>
                                <div class="teacher-student-show-timeline__content">
                                    <strong>{{ $timelineItem->lesson?->name_locale ?: $timelineItem->lesson?->name ?: 'Bai hoc' }}</strong>
                                    <span>
                                        {{ $timelineItem->course?->name_locale ?: $timelineItem->course?->name ?: 'Khoa hoc' }}
                                        • Hoan thanh luc {{ optional($timelineItem->completed_at)->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <p class="mb-0 text-white-50">Chua co du lieu tien do hoc trong cac khoa cua ban.</p>
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
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
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
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
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

        .teacher-student-show-card {
            padding: 1.25rem;
            height: 100%;
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

        .teacher-student-show-timeline__dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            margin-top: 0.35rem;
            background: linear-gradient(135deg, #38bdf8, #22c55e);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.12);
        }

        .teacher-student-show-timeline__content strong {
            display: block;
            color: #f8fbff;
            margin-bottom: 0.25rem;
        }

        .teacher-student-show-timeline__content span {
            color: #8ca6c6;
            line-height: 1.65;
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
