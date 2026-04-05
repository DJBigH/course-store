@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-coupon-assign">
        <div class="teacher-coupon-assign__head">
            <div>
                <span class="teacher-coupon-assign__kicker">{{ __('teacher::coupons.hero.kicker') }}</span>
                <h3 class="teacher-coupon-assign__title">{{ __('teacher::coupons.assign_courses_title') }}</h3>
                <p class="teacher-coupon-assign__desc mb-0">
                    {{ __('teacher::coupons.assign_courses_desc', ['code' => $coupon->code]) }}
                </p>
            </div>
            <a href="{{ route('teacher.dashboard.coupons.edit', $coupon->id) }}" class="btn btn-outline-secondary">
                {{ __('teacher::coupons.actions.back') }}
            </a>
        </div>

        <form method="POST" action="{{ route('teacher.dashboard.coupons.courses.update', $coupon->id) }}" class="teacher-coupon-assign__body">
            @csrf
            <div class="teacher-coupon-assign__list">
                @forelse ($courses as $course)
                    <label class="teacher-coupon-assign__item">
                        <input type="checkbox" name="courses[]" value="{{ $course->id }}"
                            @checked(in_array((int) $course->id, $assignedCourseIds, true))>
                        <span class="teacher-coupon-assign__meta">
                            <strong>{{ $course->name_locale ?: $course->name }}</strong>
                            <small>{{ $course->code }}</small>
                        </span>
                    </label>
                @empty
                    <div class="teacher-coupon-assign__empty">
                        {{ __('teacher::coupons.assign_courses_empty') }}
                    </div>
                @endforelse
            </div>

            <div class="teacher-coupon-assign__actions">
                <button type="submit" class="btn btn-primary">
                    {{ __('teacher::coupons.actions.save_assignment') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-coupon-assign {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-coupon-assign__head {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .teacher-coupon-assign__kicker {
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

        .teacher-coupon-assign__title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.4rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-coupon-assign__desc {
            max-width: 720px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-coupon-assign__body {
            padding: 1.2rem;
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.16);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-coupon-assign__list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .teacher-coupon-assign__item {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            padding: 0.85rem 1rem;
            border-radius: 18px;
            border: 1px solid rgba(96, 165, 250, 0.12);
            background: rgba(11, 19, 36, 0.72);
            color: #e2e8f0;
        }

        .teacher-coupon-assign__item input {
            width: 18px;
            height: 18px;
        }

        .teacher-coupon-assign__meta strong {
            display: block;
            color: #f8fbff;
        }

        .teacher-coupon-assign__meta small {
            color: #9fb5d0;
        }

        .teacher-coupon-assign__actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.2rem;
        }

        .teacher-coupon-assign__empty {
            color: #9fb5d0;
        }

        @media (max-width: 767.98px) {
            .teacher-coupon-assign__list {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
