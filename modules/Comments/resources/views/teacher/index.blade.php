@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        @if (!$teacher->packageHasFeature('can_manage_comments'))
            <div class="mb-4">
                @include('teacher::clients.dashboard.partials.package_feature_notice', [
                    'message' => __('packages::teacher.package_features.comments_locked_manage'),
                ])
            </div>
        @endif

        <section class="teacher-hero teacher-hero--comments mb-4">
            <div class="teacher-hero__content">
                <div class="teacher-hero__eyebrow">
                    <i class="fas fa-comments"></i>
                    {{ __('comments::teacher/messages.hero.kicker') }}
                </div>
                <h3 class="teacher-hero__title">
                    {{ __('comments::teacher/messages.hero.title') }}
                </h3>
                <p class="teacher-hero__desc">
                    {{ __('comments::teacher/messages.hero.description') }}
                </p>

                <div class="teacher-stats-row mt-4">
                    <div class="teacher-stat-card teacher-stat-card--glass">
                        <div class="teacher-stat-card__label">{{ __('comments::teacher/messages.stats.total') ?? 'Tổng bình luận' }}</div>
                        <div class="teacher-stat-card__value">{{ number_format($stats['total']) }}</div>
                    </div>
                    <div class="teacher-stat-card teacher-stat-card--glass">
                        <div class="teacher-stat-card__label text-warning">{{ __('comments::teacher/messages.stats.unread') ?? 'Chưa phản hồi' }}</div>
                        <div class="teacher-stat-card__value text-warning">{{ number_format($stats['unread']) }}</div>
                    </div>
                    <div class="teacher-stat-card teacher-stat-card--glass">
                        <div class="teacher-stat-card__label text-danger">{{ __('comments::teacher/messages.stats.flagged') ?? 'Bị báo cáo' }}</div>
                        <div class="teacher-stat-card__value text-danger">{{ number_format($stats['flagged']) }}</div>
                    </div>
                </div>
            </div>

            <div class="teacher-hero__rail">
                <div class="teacher-comments-filter-card">
                    <form method="GET" action="{{ route('teacher.dashboard.comments') }}">
                        <label for="teacher-comments-course" class="teacher-hero__mini-label">{{ __('comments::teacher/messages.filter.label') }}</label>
                        <select id="teacher-comments-course" name="course_id" class="form-select border-0 bg-white bg-opacity-10 text-white" onchange="this.form.submit()">
                            @forelse ($courses as $courseOption)
                                <option value="{{ $courseOption->id }}" class="text-dark" @selected($selectedCourse && (int) $selectedCourse->id === (int) $courseOption->id)>
                                    {{ $courseOption->name_locale ?: $courseOption->name }}
                                </option>
                            @empty
                                <option value="0" class="text-dark">{{ __('comments::teacher/messages.filter.empty_option') }}</option>
                            @endforelse
                        </select>
                    </form>
                </div>
                
                @if ($packageSummary && $packageSummary['can_upgrade'])
                    <div class="teacher-hero__mini teacher-hero__mini--package mt-3">
                        <span>{{ __('packages::teacher.upgrade.current_label') }}</span>
                        <strong>{{ $packageSummary['name'] }}</strong>
                        <a href="{{ $packageSummary['upgrade_url'] }}" class="btn btn-sm btn-light mt-2">
                             {{ __('packages::teacher.upgrade.upgrade_cta_simple') }}
                        </a>
                    </div>
                @endif
            </div>
        </section>

        @if (!$selectedCourse)
            <div class="teacher-panel">
                <div class="teacher-empty-state py-5">
                    <div class="teacher-empty-state__icon"><i class="fas fa-comment-slash"></i></div>
                    <h4>{{ __('comments::teacher/messages.empty.title') }}</h4>
                    <p class="text-muted mb-0">
                        {{ __('comments::teacher/messages.empty.description') }}
                    </p>
                </div>
            </div>
        @else
            <div class="teacher-panel p-0 overflow-hidden border-0 bg-transparent">
                @php $canManage = $teacher->packageHasFeature('can_manage_comments'); @endphp
                
                @include('comments::teacher.thread', [
                    'course' => $selectedCourse,
                    'threads' => $threads,
                    'teacher' => $teacher,
                ])
            </div>
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
