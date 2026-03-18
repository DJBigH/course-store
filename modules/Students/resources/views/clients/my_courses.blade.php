@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page account-courses-page py-4">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                {{-- Content --}}
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
                                        <!-- Lọc theo giảng viên -->
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label fw-medium">{{ __('students::clients/account.my_course.instructor') }}</label>
                                            <select name="teacher_id" class="form-select js-select2">
                                                <option value="">{{ __('students::clients/account.my_course.all_instructors') }}</option>
                                                @foreach ($teacher as $item)
                                                    <option value="{{ $item->id }}"
                                                        {{ request()->teacher_id == $item->id ? 'selected' : '' }}>
                                                        {{ $item->name_locale }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <!-- Tìm kiếm theo tên khóa học -->
                                        <div class="col-lg-7 col-md-6">
                                            <label class="form-label fw-medium">{{ __('students::clients/account.my_course.search_course') }}</label>
                                            <div class="input-group account-courses-search">
                                                <span class="input-group-text">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                                <input type="text" name="keyword" class="form-control"
                                                    placeholder="{{ __('students::clients/account.my_course.placeholder_course_name') }}" value="{{ request()->keyword }}">
                                            </div>
                                        </div>


                                        <!-- Nút lọc -->
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
                                            <th class="text-center" style="width: 60px;">#</th>
                                            <th>{{ __('students::clients/account.my_course.course_name') }}</th>
                                            <th style="width: 220px;">{{ __('students::clients/account.my_course.instructor') }}</th>
                                            <th style="width: 150px;">{{ __('students::clients/account.my_course.status') }}</th>
                                            <th class="text-center" style="width: 140px;">{{ __('students::clients/account.my_course.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($courses as $course)
                                            <tr>
                                                <td class="text-center fw-medium">
                                                    {{ $loop->iteration }}
                                                </td>

                                                <td>
                                                    <div class="fw-semibold"><a
                                                            href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}">{{ $course->name_locale }}</a>
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ __('students::clients/account.my_course.updated_at') }}:
                                                        {{ format_date_dmy($course->updated_at) }}
                                                    </small>
                                                </td>

                                                <td>
                                                    <span class="badge bg-info-subtle text-info px-3 py-2">
                                                        <a href="#">{{ $course->teacher->name_locale ?? 'Nguyễn Văn A' }}</a>
                                                    </span>
                                                </td>

                                                <td>
                                                    @if ($course->pivot->status)
                                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            {{ __('students::clients/account.my_course.active') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                                            <i class="bi bi-x-circle me-1"></i>
                                                            {{ __('students::clients/account.my_course.stop_update') }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}"
                                                        class="btn btn-primary btn-sm px-3">
                                                        <i class="bi bi-play-circle me-1"></i>
                                                        {{ __('students::clients/account.my_course.enter_course') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
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
