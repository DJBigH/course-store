@extends('layouts.teacher')

@section('content')
    @php
        $maintPackage = $teacher->application?->package;
        $maintCertificate = $maintPackage?->isFeatureInMaintenance('can_issue_certificates') ?? false;
        $maintExport = $maintPackage?->isFeatureInMaintenance('can_import_export') ?? false;
    @endphp
    <div class="teacher-panel teacher-certificates-shell">
        <div class="teacher-certificates-hero">
            <div>
                <h3 class="fw-bold mb-2">{{ __('certificates::teacher/messages.certificates.title') }}</h3>
                <p class="text-muted mb-0">{{ __('certificates::teacher/messages.certificates.description') }}</p>
            </div>
            @if ($canExport)
                @if($maintExport)
                    <button class="btn btn-secondary" disabled>
                        <i class="fas fa-file-export me-2"></i> {{ __('certificates::teacher/messages.certificates.export_btn') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                    </button>
                @else
                    <a href="{{ route('teacher.dashboard.certificates.export', request()->query()) }}" class="btn btn-outline-success">
                        <i class="fas fa-file-export me-2"></i> {{ __('certificates::teacher/messages.certificates.export_btn') }}
                    </a>
                @endif
            @endif
        </div>

        @if (!$canExport)
            <div class="alert alert-warning mb-4">
                <i class="fas fa-lock me-2"></i> {{ __('certificates::teacher/messages.package_features.import_export_locked') }}
                <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="fw-bold text-decoration-underline ms-1">{{ __('certificates::teacher/messages.package_upgrade.upgrade_now') }}</a>
            </div>
        @endif

        <form method="GET" class="teacher-certificates-filter">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label">{{ __('certificates::teacher/messages.certificates.filter.q_label') }}</label>
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="{{ __('certificates::teacher/messages.certificates.filter.q_placeholder') }}">
                </div>
                <div class="col-lg-4">
                    <label class="form-label">{{ __('certificates::teacher/messages.certificates.filter.course_label') }}</label>
                    <select name="course_id" class="form-select">
                        <option value="0">{{ __('certificates::teacher/messages.certificates.filter.all_courses') }}</option>
                        @foreach ($courseOptions as $course)
                            <option value="{{ $course->id }}" @selected($selectedCourse === (int) $course->id)>
                                {{ $course->name_locale ?: $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">{{ __('certificates::teacher/messages.certificates.filter.status_label') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('certificates::teacher/messages.certificates.filter.status_all') }}</option>
                        <option value="issued" @selected($status === 'issued')>{{ __('certificates::teacher/messages.certificates.filter.status_issued') }}</option>
                        <option value="missing" @selected($status === 'missing')>{{ __('certificates::teacher/messages.certificates.filter.status_missing') }}</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">{{ __('certificates::teacher/messages.certificates.filter.submit') }}</button>
                </div>
            </div>
        </form>

        <div class="teacher-certificates-summary">
            <span><strong>{{ $rows->total() }}</strong> {{ __('certificates::teacher/messages.certificates.summary.total') }}</span>
        </div>

        @if ($rows->isEmpty())
            <div class="teacher-certificates-empty">
                <h4>{{ __('certificates::teacher/messages.certificates.empty_title') }}</h4>
                <p class="mb-0">{{ __('certificates::teacher/messages.certificates.empty_description') }}</p>
            </div>
        @else
            <div class="teacher-certificates-grid">
                @foreach ($rows as $row)
                    @php
                        $progressPercent = $row->total_lessons > 0 ? min((int) round(($row->completed_lessons * 100) / $row->total_lessons), 100) : 0;
                    @endphp
                    <article class="teacher-certificate-card">
                        <div class="teacher-certificate-card__head">
                            <div>
                                <h4>{{ $row->student_name }}</h4>
                                <p class="mb-0">{{ $row->student_email }}</p>
                            </div>
                            <span class="teacher-certificate-card__status {{ $row->is_revoked ? 'is-revoked' : ($row->is_issued ? 'is-issued' : 'is-missing') }}">
                                {{ $row->is_revoked ? __('certificates::teacher/messages.certificates.status_revoked') : ($row->is_issued ? __('certificates::teacher/messages.certificates.status_issued') : __('certificates::teacher/messages.certificates.status_missing')) }}
                            </span>
                        </div>

                        <div class="teacher-certificate-card__body">
                            <div class="teacher-certificate-card__meta">
                                <span>{{ __('certificates::teacher/messages.certificates.mail.course_label') }}</span>
                                <strong>{{ $row->course_name }}</strong>
                            </div>
                            <div class="teacher-certificate-card__meta">
                                <span>{{ __('certificates::teacher/messages.certificates.progress_label') }}</span>
                                <strong>{{ $progressPercent }}% - {{ $row->completed_lessons }}/{{ $row->total_lessons }} {{ __('certificates::teacher/messages.certificates.lessons_unit') }}</strong>
                            </div>
                            <div class="teacher-certificate-card__meta">
                                <span>{{ __('certificates::teacher/messages.certificates.access_source_label') }}</span>
                                <strong>{{ $row->access_type === 'grant' ? __('certificates::teacher/messages.certificates.access_grant') : __('certificates::teacher/messages.certificates.access_paid') }}</strong>
                            </div>
                            @if ($row->is_issued)
                                <div class="teacher-certificate-card__meta">
                                    <span>{{ __('certificates::teacher/messages.certificates.mail.code_label') }}</span>
                                    <strong>{{ $row->certificate_code }}</strong>
                                </div>
                                <div class="teacher-certificate-card__meta">
                                    <span>{{ __('certificates::teacher/messages.certificates.issued_date_label') }}</span>
                                    <strong>{{ $row->issued_at ? \Illuminate\Support\Carbon::parse($row->issued_at)->format('d/m/Y H:i') : '' }}</strong>
                                </div>
                                @if ($row->is_revoked && $row->revoked_at)
                                    <div class="teacher-certificate-card__meta">
                                        <span>{{ __('certificates::teacher/messages.certificates.revoked_date_label') }}</span>
                                        <strong>{{ \Illuminate\Support\Carbon::parse($row->revoked_at)->format('d/m/Y H:i') }}</strong>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="teacher-certificate-card__actions">
                            <button
                                type="button"
                                class="btn btn-primary js-certificate-issue-trigger"
                                @if($maintCertificate) disabled @else
                                data-bs-toggle="modal"
                                data-bs-target="#teacherCertificateIssueModal"
                                data-student-id="{{ $row->student_id }}"
                                data-course-id="{{ $row->course_id }}"
                                data-student-name="{{ $row->student_name }}"
                                data-course-name="{{ $row->course_name }}"
                                data-mode="{{ $row->is_issued ? 'reissue' : 'issue' }}"
                                data-note="{{ $row->note ?? '' }}"
                                @endif>
                                {{ $row->is_issued ? __('certificates::teacher/messages.certificates.reissue_btn') : __('certificates::teacher/messages.certificates.issue_btn') }}
                                @if($maintCertificate) ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }}) @endif
                            </button>

                            @if ($row->is_issued)
                                <a href="{{ route('teacher.dashboard.certificates.show', $row->certificate_id) }}" class="btn btn-outline-secondary">
                                    {{ __('certificates::teacher/messages.certificates.view_btn') }}
                                </a>
                                <a href="{{ route('teacher.dashboard.certificates.show', ['id' => $row->certificate_id, 'print' => 1]) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                                    {{ __('certificates::teacher/messages.certificates.print_btn') }}
                                </a>
                                @if (!$row->is_revoked)
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger js-certificate-revoke-trigger"
                                        @if($maintCertificate) disabled @else
                                        data-bs-toggle="modal"
                                        data-bs-target="#teacherCertificateRevokeModal"
                                        data-action="{{ route('teacher.dashboard.certificates.revoke', $row->certificate_id) }}"
                                        data-student-name="{{ $row->student_name }}"
                                        data-course-name="{{ $row->course_name }}"
                                        data-certificate-code="{{ $row->certificate_code }}"
                                        data-revoke-reason=""
                                        @endif>
                                        {{ __('certificates::teacher/messages.certificates.revoke_btn') }}
                                        @if($maintCertificate) ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }}) @endif
                                    </button>
                                @endif
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $rows->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="teacherCertificateIssueModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content teacher-certificate-modal">
                <form method="POST" action="{{ route('teacher.dashboard.certificates.issue') }}">
                    @csrf
                    <input type="hidden" name="student_id" id="teacher-certificate-issue-student-id">
                    <input type="hidden" name="course_id" id="teacher-certificate-issue-course-id">

                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold" id="teacher-certificate-issue-title">{{ __('certificates::teacher/messages.certificates.issue_btn') }}</h5>
                            <p class="text-muted mb-0" id="teacher-certificate-issue-description"></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body pt-3">
                        <div class="teacher-certificate-modal__summary">
                            <div>
                                <span>{{ __('certificates::teacher/messages.certificates.mail.student_label') }}</span>
                                <strong id="teacher-certificate-issue-student-name"></strong>
                            </div>
                            <div>
                                <span>{{ __('certificates::teacher/messages.certificates.mail.course_label') }}</span>
                                <strong id="teacher-certificate-issue-course-name"></strong>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="teacher-certificate-issue-note" class="form-label fw-semibold">{{ __('certificates::teacher/messages.certificates.issue_modal.note_label') }}</label>
                            <textarea
                                class="form-control"
                                id="teacher-certificate-issue-note"
                                name="note"
                                rows="4"
                                placeholder="{{ __('certificates::teacher/messages.certificates.issue_modal.note_placeholder') }}"></textarea>
                            <small class="text-muted">{{ __('certificates::teacher/messages.certificates.issue_modal.note_hint') }}</small>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('certificates::teacher/messages.certificates.common.close') }}</button>
                        <button type="submit" class="btn btn-primary" id="teacher-certificate-issue-submit">{{ __('certificates::teacher/messages.certificates.issue_btn') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="teacherCertificateRevokeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content teacher-certificate-modal">
                <form method="POST" id="teacher-certificate-revoke-form">
                    @csrf

                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold">{{ __('certificates::teacher/messages.certificates.revoke_btn') }}</h5>
                            <p class="text-muted mb-0" id="teacher-certificate-revoke-description"></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body pt-3">
                        <div class="teacher-certificate-modal__warning">
                            {{ __('certificates::teacher/messages.certificates.revoke_modal.warning') }}
                        </div>

                        <div class="teacher-certificate-modal__summary mt-3">
                            <div>
                                <span>{{ __('certificates::teacher/messages.certificates.mail.student_label') }}</span>
                                <strong id="teacher-certificate-revoke-student-name"></strong>
                            </div>
                            <div>
                                <span>{{ __('certificates::teacher/messages.certificates.mail.course_label') }}</span>
                                <strong id="teacher-certificate-revoke-course-name"></strong>
                            </div>
                            <div>
                                <span>{{ __('certificates::teacher/messages.certificates.mail.code_label') }}</span>
                                <strong id="teacher-certificate-revoke-code"></strong>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="teacher-certificate-revoke-reason" class="form-label fw-semibold">{{ __('certificates::teacher/messages.certificates.revoke_modal.reason_label') }}</label>
                            <textarea
                                class="form-control @error('revoke_reason') is-invalid @enderror"
                                id="teacher-certificate-revoke-reason"
                                name="revoke_reason"
                                rows="4"
                                required
                                placeholder="{{ __('certificates::teacher/messages.certificates.revoke_modal.reason_placeholder') }}"></textarea>
                            @error('revoke_reason')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('certificates::teacher/messages.certificates.revoke_modal.reason_hint') }}</small>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('certificates::teacher/messages.certificates.common.close') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('certificates::teacher/messages.certificates.revoke_modal.confirm_btn') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-certificates-shell {
            padding: 1.5rem;
        }

        .teacher-certificates-hero {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .teacher-certificates-filter {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 20px;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="dark"] .teacher-certificates-filter {
            background: color-mix(in srgb, var(--admin-card-bg, #121a2c) 82%, transparent);
        }

        .teacher-certificates-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            color: var(--admin-muted);
        }

        .teacher-certificates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1rem;
        }

        .teacher-certificate-card,
        .teacher-certificates-empty {
            padding: 1.15rem;
            border-radius: 20px;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="dark"] .teacher-certificate-card,
        html[data-theme="dark"] .teacher-certificates-empty {
            background: color-mix(in srgb, var(--admin-card-bg, #121a2c) 84%, transparent);
        }

        .teacher-certificate-card__head,
        .teacher-certificate-card__actions {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .teacher-certificate-card__head h4 {
            margin-bottom: 0.2rem;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--admin-text);
        }

        .teacher-certificate-card__head p,
        .teacher-certificate-card__meta span {
            color: var(--admin-muted);
        }

        .teacher-certificate-card__status {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .teacher-certificate-card__status.is-issued {
            background: var(--admin-success-bg);
            color: var(--admin-success-text);
            border: 1px solid var(--admin-success-border);
        }

        .teacher-certificate-card__status.is-missing {
            background: var(--admin-warning-bg);
            color: var(--admin-warning-text);
            border: 1px solid var(--admin-warning-border);
        }

        .teacher-certificate-card__status.is-revoked {
            background: rgba(239, 68, 68, 0.14);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.24);
        }

        .teacher-certificate-card__body {
            display: grid;
            gap: 0.75rem;
            margin: 1rem 0;
        }

        .teacher-certificate-card__meta {
            display: grid;
            gap: 0.15rem;
        }

        .teacher-certificate-card__meta strong {
            font-size: 0.98rem;
            color: var(--admin-text);
        }

        .teacher-certificate-card__actions form {
            margin: 0;
        }

        .teacher-certificate-modal {
            border-radius: 24px;
            border: 1px solid var(--admin-border);
            background: color-mix(in srgb, var(--admin-card-bg, #121a2c) 90%, transparent);
            color: var(--admin-text, #f8fafc);
        }

        .teacher-certificate-modal .modal-title {
            color: var(--admin-text, #f8fafc);
        }

        .teacher-certificate-modal .text-muted,
        .teacher-certificate-modal small {
            color: var(--admin-muted) !important;
        }

        .teacher-certificate-modal .form-control {
            background: color-mix(in srgb, var(--admin-card-bg, #0f172a) 82%, transparent);
            border-color: var(--admin-border);
            color: var(--admin-text, #f8fafc);
        }

        .teacher-certificate-modal__summary {
            display: grid;
            gap: 0.85rem;
            padding: 1rem;
            border-radius: 18px;
            border: 1px solid var(--admin-border);
            background: color-mix(in srgb, var(--admin-card-bg, #0f172a) 78%, transparent);
        }

        .teacher-certificate-modal__summary span {
            display: block;
            margin-bottom: 0.2rem;
            color: var(--admin-muted);
            font-size: 0.82rem;
        }

        .teacher-certificate-modal__summary strong {
            color: var(--admin-text, #f8fafc);
            font-size: 0.98rem;
        }

        .teacher-certificate-modal__warning {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.22);
            color: #fecaca;
            font-weight: 600;
            line-height: 1.65;
        }

        @media (max-width: 767.98px) {
            .teacher-certificates-shell {
                padding: 1rem;
            }

            .teacher-certificate-card__actions .btn,
            .teacher-certificate-card__actions form,
            .teacher-certificate-card__actions form .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const issueModal = document.getElementById('teacherCertificateIssueModal');
            const revokeModal = document.getElementById('teacherCertificateRevokeModal');
            const reopenModal = @json(session('certificate_modal'));
            const reopenPayload = @json(session('certificate_modal_payload', []));
            const reopenOldReason = @json(old('revoke_reason'));

            if (issueModal) {
                const titleEl = document.getElementById('teacher-certificate-issue-title');
                const descEl = document.getElementById('teacher-certificate-issue-description');
                const studentIdEl = document.getElementById('teacher-certificate-issue-student-id');
                const courseIdEl = document.getElementById('teacher-certificate-issue-course-id');
                const studentNameEl = document.getElementById('teacher-certificate-issue-student-name');
                const courseNameEl = document.getElementById('teacher-certificate-issue-course-name');
                const noteEl = document.getElementById('teacher-certificate-issue-note');
                const submitEl = document.getElementById('teacher-certificate-issue-submit');

                issueModal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;

                    if (!button) {
                        return;
                    }

                    const mode = button.getAttribute('data-mode') || 'issue';
                    const studentId = button.getAttribute('data-student-id') || '';
                    const courseId = button.getAttribute('data-course-id') || '';
                    const studentName = button.getAttribute('data-student-name') || '';
                    const courseName = button.getAttribute('data-course-name') || '';
                    const note = button.getAttribute('data-note') || '';

                    studentIdEl.value = studentId;
                    courseIdEl.value = courseId;
                    studentNameEl.textContent = studentName;
                    courseNameEl.textContent = courseName;
                    noteEl.value = note;

                    if (mode === 'reissue') {
                        titleEl.textContent = "{{ __('certificates::teacher/messages.certificates.issue_modal.title_reissue') }}";
                        descEl.textContent = "{{ __('certificates::teacher/messages.certificates.issue_modal.description_reissue') }}";
                        submitEl.textContent = "{{ __('certificates::teacher/messages.certificates.reissue_btn') }}";
                    } else {
                        titleEl.textContent = "{{ __('certificates::teacher/messages.certificates.issue_btn') }}";
                        descEl.textContent = "{{ __('certificates::teacher/messages.certificates.issue_modal.description_issue') }}";
                        submitEl.textContent = "{{ __('certificates::teacher/messages.certificates.issue_btn') }}";
                    }
                });
            }

            if (revokeModal) {
                const formEl = document.getElementById('teacher-certificate-revoke-form');
                const descEl = document.getElementById('teacher-certificate-revoke-description');
                const studentNameEl = document.getElementById('teacher-certificate-revoke-student-name');
                const courseNameEl = document.getElementById('teacher-certificate-revoke-course-name');
                const codeEl = document.getElementById('teacher-certificate-revoke-code');
                const reasonEl = document.getElementById('teacher-certificate-revoke-reason');

                const fillRevokeModal = ({
                    action = '',
                    studentName = '',
                    courseName = '',
                    code = '',
                    reason = '',
                } = {}) => {
                    formEl.action = action;
                    studentNameEl.textContent = studentName;
                    courseNameEl.textContent = courseName;
                    codeEl.textContent = code;
                    reasonEl.value = reason;
                    descEl.textContent = studentName
                        ? "{{ __('certificates::teacher/messages.certificates.revoke_modal.desc_with_name', ['name' => ':name']) }}".replace(':name', studentName)
                        : "{{ __('certificates::teacher/messages.certificates.revoke_modal.desc_confirm') }}";
                };

                revokeModal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;

                    if (!button) {
                        return;
                    }

                    const action = button.getAttribute('data-action') || '';
                    const studentName = button.getAttribute('data-student-name') || '';
                    const courseName = button.getAttribute('data-course-name') || '';
                    const code = button.getAttribute('data-certificate-code') || '';
                    const reason = button.getAttribute('data-revoke-reason') || '';

                    fillRevokeModal({
                        action,
                        studentName,
                        courseName,
                        code,
                        reason,
                    });
                });

                if (reopenModal === 'revoke') {
                    fillRevokeModal({
                        action: reopenPayload.action || '',
                        studentName: reopenPayload.student_name || '',
                        courseName: reopenPayload.course_name || '',
                        code: reopenPayload.certificate_code || '',
                        reason: reopenOldReason || '',
                    });

                    if (window.bootstrap?.Modal) {
                        window.requestAnimationFrame(() => {
                            window.bootstrap.Modal.getOrCreateInstance(revokeModal).show();
                        });
                    }
                }
            }
        })();
    </script>
@endsection
