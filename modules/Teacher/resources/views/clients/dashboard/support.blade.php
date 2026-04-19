@extends('layouts.teacher')

@section('content')
    @php
        $typeLabels = [
            'feedback' => __('courses::teacher/messages.support.types.feedback'),
            'report' => __('courses::teacher/messages.support.types.report'),
        ];

        $categoryLabels = [
            'feature_request' => __('courses::teacher/messages.support.categories.feature_request'),
            'ui_ux' => __('courses::teacher/messages.support.categories.ui_ux'),
            'teacher_portal' => __('courses::teacher/messages.support.categories.teacher_portal'),
            'student_portal' => __('courses::teacher/messages.support.categories.student_portal'),
            'payment_package' => __('courses::teacher/messages.support.categories.payment_package'),
            'system_bug' => __('courses::teacher/messages.support.categories.system_bug'),
            'course_lesson' => __('courses::teacher/messages.support.categories.course_lesson'),
            'comment_rating' => __('courses::teacher/messages.support.categories.comment_rating'),
            'content_violation' => __('courses::teacher/messages.support.categories.content_violation'),
            'account' => __('courses::teacher/messages.support.categories.account'),
            'other' => __('courses::teacher/messages.support.categories.other'),
        ];

        $statusLabels = [
            'new' => __('courses::teacher/messages.support.status.new'),
            'in_progress' => __('courses::teacher/messages.support.status.in_progress'),
            'need_info' => __('courses::teacher/messages.support.status.need_info'),
            'resolved' => __('courses::teacher/messages.support.status.resolved'),
            'rejected' => __('courses::teacher/messages.support.status.rejected'),
        ];
    @endphp

    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('courses::teacher/messages.pages.support') }}</h3>
                <p class="text-muted mb-0">{{ __('courses::teacher/messages.support.description') }}</p>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="teacher-panel h-100">
                    <h4 class="h5 fw-bold mb-3">{{ __('courses::teacher/messages.support.form.title') }}</h4>

                    <form method="POST" action="{{ route('teacher.dashboard.support.store') }}">
                        @csrf
                        <input type="hidden" name="page_url" value="{{ url()->current() }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('courses::teacher/messages.support.form.type_label') }}</label>
                            <select name="submission_type" class="form-select">
                                <option value="feedback" @selected(old('submission_type', 'feedback') === 'feedback')>{{ __('courses::teacher/messages.support.types.feedback') }}</option>
                                <option value="report" @selected(old('submission_type') === 'report')>{{ __('courses::teacher/messages.support.types.report') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('courses::teacher/messages.support.form.category_label') }}</label>
                            <select name="category" class="form-select">
                                @foreach ($categoryLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('courses::teacher/messages.support.form.subject_label') }}</label>
                            <input
                                type="text"
                                name="subject"
                                class="form-control"
                                value="{{ old('subject') }}"
                                placeholder="{{ __('courses::teacher/messages.support.form.subject_placeholder') }}"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('courses::teacher/messages.support.form.message_label') }}</label>
                            <textarea
                                name="message"
                                rows="7"
                                class="form-control"
                                placeholder="{{ __('courses::teacher/messages.support.form.message_placeholder') }}"
                            >{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">{{ __('courses::teacher/messages.support.form.submit') }}</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="teacher-panel h-100">
                    <h4 class="h5 fw-bold mb-3">{{ __('courses::teacher/messages.support.history.title') }}</h4>

                    @if ($items->isEmpty())
                        <div class="border rounded-3 p-4 text-muted">{{ __('courses::teacher/messages.support.history.empty') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('courses::teacher/messages.support.history.table.type') }}</th>
                                        <th>{{ __('courses::teacher/messages.support.history.table.category') }}</th>
                                        <th>{{ __('courses::teacher/messages.support.history.table.subject') }}</th>
                                        <th>{{ __('courses::teacher/messages.support.history.table.status') }}</th>
                                        <th>{{ __('courses::teacher/messages.support.history.table.submitted_at') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr>
                                            <td>{{ $typeLabels[$item->submission_type] ?? $item->submission_type }}</td>
                                            <td>{{ $categoryLabels[$item->category] ?? $item->category }}</td>
                                            <td class="fw-semibold">{{ $item->subject }}</td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                    {{ $statusLabels[$item->workflow_status] ?? $item->workflow_status }}
                                                </span>
                                            </td>
                                            <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
