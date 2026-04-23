@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-student-grant-shell">
        <div class="teacher-student-grant-hero">
            <div>
                <span class="teacher-student-grant-kicker">{{ __('students::teacher/messages.hero_kicker') }}</span>
                <h3 class="teacher-student-grant-title">{{ __('students::teacher/messages.grant.hero_title') }}</h3>
                <p class="teacher-student-grant-desc mb-0">
                    {{ __('students::teacher/messages.grant.hero_description') }}
                </p>
            </div>
            <div class="teacher-student-grant-actions">
                <a href="{{ route('teacher.dashboard.students') }}" class="btn btn-outline-secondary">
                    {{ __('students::teacher/messages.grant.back_to_students') }}
                </a>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif

        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-xl-7">
                <section class="teacher-student-grant-card">
                    <h4>{{ __('students::teacher/messages.grant.form_title') }}</h4>
                    <form method="POST" action="{{ route('teacher.dashboard.students.grants.store') }}" class="teacher-student-grant-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ __('students::teacher/messages.grant.fields.email') }}</label>
                                <input
                                    type="email"
                                    name="student_email"
                                    class="form-control @error('student_email') is-invalid @enderror"
                                    value="{{ old('student_email', $selectedEmail ?? '') }}"
                                    placeholder="{{ __('students::teacher/messages.grant.fields.email_placeholder') }}">
                                @error('student_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-white-50 d-block mt-2">
                                    {{ __('students::teacher/messages.grant.fields.email_hint') }}
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('students::teacher/messages.grant.fields.course') }}</label>
                                <select name="course_id" class="form-select @error('course_id') is-invalid @enderror">
                                    <option value="">{{ __('students::teacher/messages.grant.fields.course_placeholder') }}</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" @selected((int) old('course_id') === (int) $course->id)>
                                            {{ $course->name_locale ?: $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('students::teacher/messages.grant.fields.reason') }}</label>
                                <select name="reason" class="form-select @error('reason') is-invalid @enderror">
                                    <option value="gift" @selected(old('reason', 'gift') === 'gift')>{{ __('students::teacher/messages.grant.reason_options.gift') }}</option>
                                    <option value="support" @selected(old('reason') === 'support')>{{ __('students::teacher/messages.grant.reason_options.support') }}</option>
                                    <option value="special_trial" @selected(old('reason') === 'special_trial')>{{ __('students::teacher/messages.grant.reason_options.special_trial') }}</option>
                                    <option value="compensation" @selected(old('reason') === 'compensation')>{{ __('students::teacher/messages.grant.reason_options.compensation') }}</option>
                                </select>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('students::teacher/messages.grant.fields.note') }}</label>
                                <input
                                    type="text"
                                    name="note"
                                    class="form-control @error('note') is-invalid @enderror"
                                    value="{{ old('note') }}"
                                    placeholder="{{ __('students::teacher/messages.grant.fields.note_placeholder') }}">
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="teacher-student-grant-form__hint">
                            <strong>{{ __('students::teacher/messages.grant.flow.title') }}</strong>
                            <ul class="mb-0 mt-2">
                                <li>{{ __('students::teacher/messages.grant.flow.step_1') }}</li>
                                <li>{{ __('students::teacher/messages.grant.flow.step_2') }}</li>
                                <li>{{ __('students::teacher/messages.grant.flow.step_3') }}</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('students::teacher/messages.grant.actions.submit') }}</button>
                    </form>
                </section>
            </div>

            <div class="col-xl-5">
                <section class="teacher-student-grant-card">
                    <h4>{{ __('students::teacher/messages.grant.recent_title') }}</h4>
                    <div class="teacher-student-grant-log">
                        @forelse ($recentGrants as $grant)
                            <article class="teacher-student-grant-log__item">
                                <strong>{{ $grant->student?->name ?: $grant->student?->email ?: __('students::teacher/messages.grant.fields.student') }}</strong>
                                <span>{{ $grant->course?->name_locale ?: $grant->course?->name ?: __('students::teacher/messages.grant.fields.course') }}</span>
                                <small>
                                    {{ __('students::teacher/messages.grant.reason_options.' . $grant->reason) }}
                                    •
                                    {{ $grant->status === 'accepted' ? __('students::teacher/messages.grant.status.accepted') : __('students::teacher/messages.grant.status.pending') }}
                                    • {{ optional($grant->invited_at ?: $grant->created_at)->format('d/m/Y H:i') }}
                                </small>
                            </article>
                        @empty
                            <p class="mb-0 text-white-50">{{ __('students::teacher/messages.grant.no_recent') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-student-grant-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-student-grant-hero {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .teacher-student-grant-kicker {
            display: inline-flex;
            padding: .45rem .8rem;
            border-radius: 999px;
            background: rgba(37,99,235,.14);
            color: #8fc3ff;
            font-size: .78rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .teacher-student-grant-title {
            margin: 1rem 0 .55rem;
            font-size: clamp(2rem, 3vw, 2.7rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-student-grant-desc {
            color: #a9bbd5;
            max-width: 760px;
            line-height: 1.75;
        }

        .teacher-student-grant-card {
            padding: 1.25rem;
            border: 1px solid rgba(96,165,250,.16);
            border-radius: 22px;
            background: rgba(18,28,50,.72);
            box-shadow: 0 18px 42px rgba(2,6,23,.18);
            height: 100%;
        }

        .teacher-student-grant-card h4 {
            color: #f8fbff;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .teacher-student-grant-form__hint {
            margin: 1rem 0 1.1rem;
            padding: 1rem 1.1rem;
            border-radius: 16px;
            background: rgba(37,99,235,.12);
            color: #cfe4ff;
            border: 1px solid rgba(96,165,250,.16);
        }

        .teacher-student-grant-form__hint ul {
            padding-left: 1.1rem;
        }

        .teacher-student-grant-log {
            display: grid;
            gap: .8rem;
        }

        .teacher-student-grant-log__item {
            padding: 1rem;
            border-radius: 18px;
            background: rgba(11,19,36,.72);
            border: 1px solid rgba(96,165,250,.12);
        }

        .teacher-student-grant-log__item strong,
        .teacher-student-grant-log__item span,
        .teacher-student-grant-log__item small {
            display: block;
        }

        .teacher-student-grant-log__item strong {
            color: #f8fbff;
        }

        .teacher-student-grant-log__item span {
            color: #d6e4ff;
            margin-top: .3rem;
        }

        .teacher-student-grant-log__item small {
            color: #8ca6c6;
            margin-top: .35rem;
        }

        @media (max-width: 767.98px) {
            .teacher-student-grant-hero {
                flex-direction: column;
            }
        }
    </style>
@endsection
