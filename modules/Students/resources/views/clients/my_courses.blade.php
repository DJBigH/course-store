@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page account-courses-page py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="account-content account-courses-content card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="account-courses-head d-flex align-items-center justify-content-between mb-3">
                                <h2 class="fw-semibold mb-0">
                                    {{ __('students::clients/account.my_course.title') }}
                                </h2>
                            </div>

                            <div class="table-responsive" style="overflow-x: unset;" data-pagination-scroll
                                data-filter-block="account-my-courses">
                                <form method="GET"
                                    action="{{ route('students.account.my-courses', ['locale' => app()->getLocale()]) }}"
                                    class="account-courses-filter mb-4 js-smooth-filter"
                                    data-filter-block-target="account-my-courses">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-lg-3 col-md-6">
                                            <label
                                                class="form-label fw-medium">{{ __('students::clients/account.my_course.instructor') }}</label>
                                            <select name="teacher_id" class="form-select js-select2">
                                                <option value="">
                                                    {{ __('students::clients/account.my_course.all_instructors') }}</option>
                                                @foreach ($teachers as $item)
                                                    <option value="{{ $item->id }}"
                                                        {{ request()->teacher_id == $item->id ? 'selected' : '' }}>
                                                        {{ $item->name_locale }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-7 col-md-6">
                                            <label
                                                class="form-label fw-medium">{{ __('students::clients/account.my_course.search_course') }}</label>
                                            <div class="input-group account-courses-search">
                                                <span class="input-group-text">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                                <input type="text" name="keyword" class="form-control"
                                                    placeholder="{{ __('students::clients/account.my_course.placeholder_course_name') }}"
                                                    value="{{ request()->keyword }}">
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-6 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bi bi-funnel me-1"></i>
                                                {{ __('students::clients/account.core.filter') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <div data-pagination-container="account-my-courses">
                                    <table class="table table-hover align-middle mb-0 account-courses-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center account-courses-col-index">#</th>
                                                <th class="account-courses-col-course">{{ __('students::clients/account.my_course.course_name') }}</th>
                                                <th class="account-courses-col-progress">{{ __('lessons::clients/common.course_progress') }}</th>
                                                <th class="account-courses-col-teacher">{{ __('students::clients/account.my_course.instructor') }}</th>
                                                <th class="account-courses-col-status">{{ __('students::clients/account.my_course.status') }}</th>
                                                <th class="text-center account-courses-col-action">{{ __('students::clients/account.my_course.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($courses as $course)
                                                <tr>
                                                    <td class="text-center fw-medium account-courses-index"
                                                        data-label="#">
                                                        {{ $loop->iteration }}
                                                    </td>

                                                    <td class="account-courses-course"
                                                        data-label="{{ __('students::clients/account.my_course.course_name') }}">
                                                        <div class="fw-semibold account-courses-course__title">
                                                            <a
                                                                href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}">{{ $course->name_locale }}</a>
                                                        </div>
                                                        @if ($course->student_certificate)
                                                            <div class="account-courses-certificate">
                                                                <a href="{{ route('students.account.certificates.show', ['locale' => app()->getLocale(), 'id' => $course->student_certificate->id]) }}"
                                                                    class="account-courses-certificate__badge">
                                                                    <i class="bi bi-award me-1"></i>
                                                                    Da co chung chi
                                                                </a>
                                                            </div>
                                                        @endif
                                                        <small class="text-muted account-courses-course__meta">
                                                            {{ __('students::clients/account.my_course.updated_at') }}:
                                                            {{ format_date_dmy($course->updated_at) }}
                                                        </small>
                                                    </td>

                                                    <td class="account-courses-progress-cell"
                                                        data-label="{{ __('lessons::clients/common.course_progress') }}">
                                                        <div class="account-course-progress">
                                                            <div class="account-course-progress__head">
                                                                <strong>{{ $course->progress_percent }}%</strong>
                                                                <span>
                                                                    {{ __('lessons::clients/common.completed_lessons', [
                                                                        'completed' => $course->progress_completed_lessons,
                                                                        'total' => $course->progress_total_lessons,
                                                                    ]) }}
                                                                </span>
                                                            </div>
                                                            <div class="progress account-course-progress__bar" role="progressbar"
                                                                aria-valuenow="{{ $course->progress_percent }}" aria-valuemin="0"
                                                                aria-valuemax="100">
                                                                <div class="progress-bar"
                                                                    style="width: {{ $course->progress_percent }}%"></div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td class="account-courses-teacher"
                                                        data-label="{{ __('students::clients/account.my_course.instructor') }}">
                                                        <span class="badge bg-info-subtle text-info px-3 py-2 account-courses-chip">
                                                            <a href="#">{{ $course->teacher->name_locale ?? 'Nguyen Van A' }}</a>
                                                        </span>
                                                    </td>

                                                    <td class="account-courses-status"
                                                        data-label="{{ __('students::clients/account.my_course.status') }}">
                                                        @if (!$course->pivot || $course->pivot->status)
                                                            <span class="badge bg-success-subtle text-success px-3 py-2 account-courses-chip">
                                                                <i class="bi bi-check-circle me-1"></i>
                                                                {{ __('students::clients/account.my_course.active') }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger px-3 py-2 account-courses-chip">
                                                                <i class="bi bi-x-circle me-1"></i>
                                                                {{ __('students::clients/account.my_course.stop_update') }}
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="text-center account-courses-action"
                                                        data-label="{{ __('students::clients/account.my_course.action') }}">
                                                        <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}"
                                                            class="btn btn-primary btn-sm px-3 account-courses-action__btn">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            {{ __('students::clients/account.my_course.enter_course') }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="account-courses-empty-row">
                                                    <td colspan="6" class="text-center py-4 text-muted">
                                                        <div class="account-courses-empty">
                                                            <i class="bi bi-inbox"></i>
                                                            <p>{{ __('students::clients/account.my_course.empty') }}</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <div class="mt-2">
                                        {{ $courses->links('students::clients.pagination.boostrap') }}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style data-account-page-style>
        .account-courses-table {
            border-collapse: separate;
            border-spacing: 0 12px;
            table-layout: auto;
            width: 100%;
        }

        .account-courses-table thead th {
            border: 0;
            background: linear-gradient(135deg, #dbeafe, #c7d2fe);
            color: #1e3a8a;
            font-weight: 700;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .account-courses-table thead th:first-child {
            border-top-left-radius: 16px;
            border-bottom-left-radius: 16px;
        }

        .account-courses-table thead th:last-child {
            border-top-right-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        .account-courses-table tbody td {
            padding: 18px 16px;
            background: #ffffff;
            border-top: 1px solid rgba(203, 213, 225, 0.78);
            border-bottom: 1px solid rgba(203, 213, 225, 0.78);
            vertical-align: middle;
        }

        .account-courses-table tbody td:first-child {
            border-left: 1px solid rgba(203, 213, 225, 0.78);
            border-top-left-radius: 18px;
            border-bottom-left-radius: 18px;
        }

        .account-courses-table tbody td:last-child {
            border-right: 1px solid rgba(203, 213, 225, 0.78);
            border-top-right-radius: 18px;
            border-bottom-right-radius: 18px;
        }

        .account-courses-col-index {
            width: 56px;
        }

        .account-courses-col-course {
            width: 35%;
        }

        .account-courses-col-progress {
            width: 24%;
        }

        .account-courses-col-teacher {
            width: 14%;
        }

        .account-courses-col-status {
            width: 12%;
        }

        .account-courses-col-action {
            width: 15%;
        }

        .account-courses-index {
            color: #2563eb;
        }

        .account-courses-course__title {
            line-height: 1.45;
            font-size: 18px;
            max-width: 420px;
        }

        .account-courses-course__title a {
            color: #0f172a;
            text-decoration: none;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
            word-break: keep-all;
            overflow-wrap: anywhere;
        }

        .account-courses-course__title a:hover {
            color: #2563eb;
        }

        .account-courses-course__meta {
            display: inline-block;
            margin-top: 8px;
            color: #64748b !important;
        }

        .account-courses-certificate {
            margin-top: 10px;
        }

        .account-courses-certificate__badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 0.42rem 0.8rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .account-courses-certificate__badge:hover {
            background: rgba(37, 99, 235, 0.18);
            color: #1e40af;
        }

        .account-courses-progress-cell {
            min-width: 220px;
        }

        .account-course-progress__head {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 10px;
            font-size: 13px;
            align-items: flex-start;
        }

        .account-course-progress__head strong {
            color: #2563eb;
            font-size: 18px;
        }

        .account-course-progress__head span {
            color: #64748b;
            text-align: left;
            line-height: 1.35;
        }

        .account-course-progress__bar {
            height: 8px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.14);
            overflow: hidden;
        }

        .account-course-progress__bar .progress-bar {
            background: linear-gradient(90deg, #2563eb, #38bdf8);
        }

        .account-courses-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding-inline: 14px !important;
            border-radius: 999px;
            min-height: 36px;
            white-space: normal;
            text-align: center;
            line-height: 1.3;
            width: 100%;
            max-width: 120px;
        }

        .account-courses-teacher a {
            color: inherit;
            text-decoration: none;
        }

        .account-courses-action__btn {
            min-width: 112px;
            white-space: nowrap;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .account-courses-table tbody tr:hover td {
            background: #f8fbff;
        }

        html[data-theme="dark"] .account-courses-table tbody td {
            background: transparent !important;
            color: #e5effc;
            border-color: transparent !important;
        }

        html[data-theme="dark"] .account-courses-table tbody tr:hover td {
            background: transparent !important;
        }

        html[data-theme="dark"] .account-courses-course__title a {
            color: #dbeafe;
        }

        html[data-theme="dark"] .account-courses-course__title a:hover {
            color: #93c5fd;
        }

        html[data-theme="dark"] .account-courses-course__meta,
        html[data-theme="dark"] .account-course-progress__head span {
            color: #9fb4cb !important;
        }

        html[data-theme="dark"] .account-courses-certificate__badge {
            background: rgba(96, 165, 250, 0.16);
            color: #bfdbfe;
        }

        html[data-theme="dark"] .account-courses-certificate__badge:hover {
            background: rgba(96, 165, 250, 0.22);
            color: #dbeafe;
        }

        html[data-theme="dark"] .account-courses-index,
        html[data-theme="dark"] .account-course-progress__head strong {
            color: #93c5fd;
        }

        html[data-theme="dark"] .account-courses-filter .form-select,
        html[data-theme="dark"] .account-courses-filter .form-control {
            background: #091321;
            color: #e5effc;
            border-color: rgba(148, 163, 184, 0.16);
        }

        html[data-theme="dark"] .account-courses-filter .form-select:focus,
        html[data-theme="dark"] .account-courses-filter .form-control:focus {
            border-color: rgba(96, 165, 250, 0.45);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.14);
        }

        html[data-theme="dark"] .account-courses-filter .form-select option {
            background: #091321;
            color: #e5effc;
        }

        html[data-theme="dark"] .account-courses-search .input-group-text {
            background: rgba(96, 165, 250, 0.12);
            color: #bfdbfe;
            border-color: rgba(148, 163, 184, 0.16);
        }

        @media (max-width: 1399.98px) {
            .account-courses-course__title {
                max-width: 320px;
            }

            .account-courses-chip {
                max-width: 108px;
                font-size: 13px;
            }

            .account-courses-action__btn {
                min-width: 98px;
                padding-inline: 12px !important;
                font-size: 13px;
            }
        }

        @media (max-width: 1199.98px) {
            .account-courses-table {
                min-width: 860px;
            }

            .account-courses-progress-cell {
                min-width: 180px;
            }
        }

        @media (max-width: 991.98px) {
            .account-courses-content .card-body {
                padding: 1.5rem !important;
            }

            .account-courses-head {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }

            .account-courses-content .table-responsive {
                overflow: visible !important;
            }

            .account-courses-filter .row {
                --bs-gutter-x: 0.85rem;
                --bs-gutter-y: 0.85rem;
            }

            .account-courses-filter .col-md-6 {
                width: 50%;
            }

            .account-courses-filter .col-lg-2 {
                width: 100%;
            }

            .account-courses-filter .btn {
                width: 100%;
                min-height: 44px;
            }

            .account-courses-table {
                min-width: 0;
                border-spacing: 0;
            }

            .account-courses-table thead {
                display: none;
            }

            .account-courses-table,
            .account-courses-table tbody,
            .account-courses-table tr,
            .account-courses-table td {
                display: block;
                width: 100%;
            }

            .account-courses-table tbody {
                display: grid;
                gap: 14px;
            }

            .account-courses-table tbody tr {
                border: 1px solid rgba(148, 163, 184, 0.16);
                border-radius: 18px;
                overflow: hidden;
                background: #ffffff;
                box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            }

            .account-courses-table tbody td,
            .account-courses-table tbody td:first-child,
            .account-courses-table tbody td:last-child {
                border: 0;
                border-radius: 0;
                padding: 12px 14px;
                background: transparent;
            }

            .account-courses-table tbody td + td {
                border-top: 1px solid rgba(203, 213, 225, 0.5);
            }

            .account-courses-table tbody td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 6px;
                color: #64748b;
                font-size: 0.78rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .account-courses-index {
                text-align: left !important;
            }

            .account-courses-course__title {
                max-width: none;
                font-size: 1rem;
            }

            .account-courses-course__meta {
                margin-top: 6px;
                font-size: 0.84rem;
            }

            .account-courses-progress-cell {
                min-width: 0;
            }

            .account-courses-chip {
                width: auto;
                max-width: none;
                justify-content: flex-start;
                padding-inline: 12px !important;
            }

            .account-courses-action {
                text-align: left !important;
            }

            .account-courses-action__btn {
                width: 100%;
                min-width: 0;
                min-height: 42px;
            }

            .account-courses-empty-row td::before {
                display: none;
            }

            .account-courses-empty-row .account-courses-empty {
                min-height: 120px;
                display: grid;
                place-items: center;
                text-align: center;
            }

            html[data-theme="dark"] .account-courses-table tbody tr {
                background: rgba(15, 23, 42, 0.92);
                border-color: rgba(148, 163, 184, 0.14);
                box-shadow: 0 12px 30px rgba(2, 6, 23, 0.26);
            }

            html[data-theme="dark"] .account-courses-table tbody td + td {
                border-top-color: rgba(148, 163, 184, 0.14);
            }

            html[data-theme="dark"] .account-courses-table tbody td::before {
                color: #94a7c0;
            }
        }

        @media (max-width: 767.98px) {
            .account-courses-content .card-body {
                padding: 1.25rem !important;
            }

            .account-courses-filter .col-lg-2,
            .account-courses-filter .btn {
                width: 100%;
            }

            .account-courses-course__title {
                font-size: 1rem;
            }

            .account-courses-course__meta {
                font-size: 0.84rem;
            }
        }
    </style>
@endsection
