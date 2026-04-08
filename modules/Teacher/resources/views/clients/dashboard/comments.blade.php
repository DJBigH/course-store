@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-comments-shell">
        <div class="teacher-comments-hero">
            <div>
                <span class="teacher-comments-kicker">{{ __('teacher::comments.hero.kicker') }}</span>
                <h3 class="teacher-comments-title">{{ __('teacher::comments.hero.title') }}</h3>
                <p class="teacher-comments-desc mb-0">
                    {{ __('teacher::comments.hero.description') }}
                </p>
            </div>

            <form method="GET" action="{{ route('teacher.dashboard.comments') }}" class="teacher-comments-filter">
                <label for="teacher-comments-course" class="form-label mb-2">{{ __('teacher::comments.filter.label') }}</label>
                <div class="teacher-comments-filter__row">
                    <select id="teacher-comments-course" name="course_id" class="form-select" onchange="this.form.submit()">
                        @forelse ($courses as $courseOption)
                            <option value="{{ $courseOption->id }}" @selected($selectedCourse && (int) $selectedCourse->id === (int) $courseOption->id)>
                                {{ $courseOption->name_locale ?: $courseOption->name }}
                            </option>
                        @empty
                            <option value="0">{{ __('teacher::comments.filter.empty_option') }}</option>
                        @endforelse
                    </select>
                    <noscript>
                        <button type="submit" class="btn btn-primary">{{ __('teacher::comments.filter.submit') }}</button>
                    </noscript>
                </div>
            </form>
        </div>

        @if (!$selectedCourse)
            <div class="teacher-comments-empty">
                <div class="teacher-comments-empty__icon"><i class="fas fa-comments"></i></div>
                <h4>{{ __('teacher::comments.empty.title') }}</h4>
                <p class="mb-0">
                    {{ __('teacher::comments.empty.description') }}
                </p>
            </div>
        @else
            @if (!$teacher->packageHasFeature('can_manage_comments'))
                <div class="alert alert-warning border-0 mb-4">
                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                        <div>
                            <strong>{{ __('teacher::dashboard.package_features.upsell_title') }}</strong>
                            <div class="mt-1 text-muted">{{ __('teacher::dashboard.package_features.comments_locked_manage') }}</div>
                        </div>
                        <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-warning">
                            {{ __('teacher::dashboard.package_features.upgrade_cta') }}
                        </a>
                    </div>
                </div>
            @endif
            @include('teacher::clients.dashboard.comments_thread', [
                'course' => $selectedCourse,
                'threads' => $threads,
                'teacher' => $teacher,
            ])
        @endif
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-comments-shell {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-comments-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(320px, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .teacher-comments-kicker {
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

        .teacher-comments-title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-comments-desc {
            max-width: 760px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-comments-filter {
            padding: 1.15rem;
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.16);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
            align-self: end;
        }

        .teacher-comments-filter label {
            color: #dce9ff;
            font-weight: 700;
        }

        .teacher-comments-filter__row {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.75rem;
        }

        .teacher-comments-empty {
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-comments-empty__icon {
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

        .teacher-comments-empty h4 {
            color: #f8fbff;
            margin-bottom: 0.55rem;
        }

        .teacher-comments-empty p {
            color: #a9bbd5;
            max-width: 640px;
            margin: 0 auto;
            line-height: 1.75;
        }

        html[data-theme="light"] .teacher-comments-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%);
        }

        html[data-theme="light"] .teacher-comments-kicker {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-comments-title {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-comments-desc {
            color: #475569;
        }

        html[data-theme="light"] .teacher-comments-filter,
        html[data-theme="light"] .teacher-comments-empty {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-comments-filter label {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-comments-empty h4 {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-comments-empty p {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-comments-empty__icon {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(56, 189, 248, 0.14));
            color: #1d4ed8;
        }

        @media (max-width: 1199.98px) {
            .teacher-comments-hero {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        (function () {
            const commentsRoot = document.querySelector('[data-teacher-comments]');
            if (!commentsRoot) {
                return;
            }

            const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const updateComments = (html) => {
                const target = document.querySelector('[data-teacher-comments]');
                if (target) {
                    target.outerHTML = html;
                }
            };

            document.addEventListener('submit', async (event) => {
                const form = event.target.closest('[data-teacher-comment-form]');
                if (!form) {
                    return;
                }

                event.preventDefault();
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                        },
                        body: new FormData(form),
                    });

                    const payload = await response.json();
                    if (payload?.success && payload.html) {
                        updateComments(payload.html);
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });

            document.addEventListener('click', async (event) => {
                const button = event.target.closest('[data-teacher-comment-toggle]');
                if (!button) {
                    return;
                }

                event.preventDefault();
                button.disabled = true;

                try {
                    const response = await fetch(button.dataset.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                        },
                    });

                    const payload = await response.json();
                    if (payload?.success && payload.html) {
                        updateComments(payload.html);
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    button.disabled = false;
                }
            });
        })();
    </script>
@endsection
