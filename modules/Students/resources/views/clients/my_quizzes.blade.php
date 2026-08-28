@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page account-quizzes-page py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="account-content account-quizzes-content card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="account-quizzes-head d-flex align-items-center justify-content-between mb-4">
                                <h2 class="fw-semibold mb-0">
                                    {{ __('students::clients/account.my_quizzes.title') }}
                                </h2>
                            </div>

                            <div class="table-responsive" style="overflow-x: unset;">
                                <table class="table table-hover align-middle mb-0 account-quizzes-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 50px;">#</th>
                                            <th>{{ __('students::clients/account.my_quizzes.quiz_name') }}</th>
                                            <th>{{ __('students::clients/account.my_quizzes.course_name') }}</th>
                                            <th class="text-center">{{ __('students::clients/account.my_quizzes.best_score') }}</th>
                                            <th class="text-center">{{ __('students::clients/account.my_quizzes.deadline') }}</th>
                                            <th class="text-center">{{ __('students::clients/account.my_quizzes.status') }}</th>
                                            <th class="text-center">{{ __('students::clients/account.my_quizzes.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($quizzes as $quiz)
                                            @php
                                                $status = 'not_started';
                                                if ($quiz->my_submissions_count > 0) {
                                                    $status = 'completed';
                                                }
                                                // If there's an active (unfinished) submission, it could be 'in_progress'
                                                // But my current controller logic only counts finished ones for count.
                                            @endphp
                                            <tr>
                                                <td class="text-center fw-medium">
                                                    {{ $loop->iteration + ($quizzes->currentPage() - 1) * $quizzes->perPage() }}
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-primary">
                                                        {{ $quiz->title }}
                                                    </div>
                                                    @if($quiz->lesson)
                                                        <div class="small text-muted">
                                                            <i class="bi bi-journal-text me-1"></i>
                                                            {{ $quiz->lesson->name_locale }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="small fw-medium">
                                                        <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $quiz->course->slug_locale]) }}" class="text-dark">
                                                            {{ $quiz->course->name_locale }}
                                                        </a>
                                                    </div>
                                                    <div class="small text-muted">
                                                        {{ __('students::clients/account.core.instructor') }}: {{ $quiz->creator?->student?->name ?? 'System' }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if($quiz->my_submissions_count > 0)
                                                        <div class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                                            {{ $quiz->best_score }} / 100
                                                        </div>
                                                        <div class="small text-muted mt-1">
                                                            {{ $quiz->my_submissions_count }} {{ __('students::clients/account.my_quizzes.attempts') }}
                                                        </div>
                                                    @else
                                                        <span class="text-muted">---</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($quiz->deadline_at)
                                                        <div class="small {{ $quiz->deadline_at->isPast() ? 'text-danger fw-bold' : 'text-dark' }}">
                                                            {{ $quiz->deadline_at->format('d/m/Y H:i') }}
                                                        </div>
                                                    @else
                                                        <span class="text-muted small">{{ __('students::clients/account.my_quizzes.no_deadline') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($status == 'completed')
                                                        <span class="badge bg-success-soft text-success">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            {{ __('students::clients/account.my_quizzes.status_completed') }}
                                                        </span>
                                                    @elseif($status == 'in_progress')
                                                        <span class="badge bg-warning-soft text-warning">
                                                            <i class="bi bi-clock-history me-1"></i>
                                                            {{ __('students::clients/account.my_quizzes.status_in_progress') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary-soft text-secondary">
                                                            {{ __('students::clients/account.my_quizzes.status_not_started') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="{{ route('teacher.dashboard.quizzes.show', ['locale' => app()->getLocale(), 'course' => $quiz->course_id, 'quiz' => $quiz->id]) }}" 
                                                           class="btn btn-sm btn-primary">
                                                            {{ $quiz->my_submissions_count > 0 ? __('students::clients/account.my_quizzes.view_results') : __('students::clients/account.my_quizzes.take_quiz') }}
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                    {{ __('students::clients/account.my_quizzes.empty') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 d-flex justify-content-center">
                                {{ $quizzes->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
        .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
        .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
        
        .account-quizzes-table thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        
        .account-quizzes-table tbody td {
            padding: 1rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8125rem;
        }

        /* Dark Mode Fixes */
        html[data-theme="dark"] .account-quizzes-content {
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
        }

        html[data-theme="dark"] .account-quizzes-table thead {
            background-color: #334155;
        }

        html[data-theme="dark"] .account-quizzes-table thead th {
            color: #cbd5e1 !important;
            border-bottom: 2px solid #475569;
        }

        html[data-theme="dark"] .account-quizzes-table tbody td {
            color: #e2e8f0;
            border-bottom-color: #334155;
        }

        html[data-theme="dark"] .account-quizzes-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }

        html[data-theme="dark"] .text-dark {
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .text-primary {
            color: #60a5fa !important;
        }

        html[data-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        html[data-theme="dark"] .card-body h2 {
            color: #f8fafc;
        }
    </style>
@endsection
