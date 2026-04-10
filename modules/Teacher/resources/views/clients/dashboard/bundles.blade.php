@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-bundles-shell">
        <div class="teacher-bundles-hero">
            <div>
                <span class="teacher-bundles-kicker">{{ __('teacher::dashboard.bundles.hero_kicker') }}</span>
                <h3 class="teacher-bundles-title">{{ __('teacher::dashboard.bundles.title') }}</h3>
                <p class="teacher-bundles-desc mb-0">{{ __('teacher::dashboard.bundles.description') }}</p>
            </div>
            <a href="{{ route('teacher.dashboard.bundles.create') }}" class="btn btn-primary btn-lg">
                {{ __('teacher::dashboard.bundles.actions.create') }}
            </a>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success border-0">{{ session('msg_success') }}</div>
        @endif

        <div class="teacher-bundles-grid">
            @forelse ($bundles as $bundle)
                <div class="teacher-bundles-card">
                    <div class="teacher-bundles-card__head">
                        <div>
                            <span class="teacher-bundles-status {{ $bundle->status ? 'is-active' : 'is-draft' }}">
                                {{ $bundle->status ? __('teacher::dashboard.common.active') : __('teacher::dashboard.courses.status.draft') }}
                            </span>
                            <h4>{{ $bundle->name }}</h4>
                        </div>
                        <strong>{{ moneyLocale($bundle->price) }}</strong>
                    </div>

                    <p class="teacher-bundles-card__desc">
                        {{ $bundle->description ?: __('teacher::dashboard.bundles.empty_description') }}
                    </p>

                    <div class="teacher-bundles-card__meta">
                        <span>{{ __('teacher::dashboard.bundles.labels.course_count', ['count' => $bundle->items_count]) }}</span>
                        <span>{{ __('teacher::dashboard.bundles.labels.slug', ['slug' => $bundle->slug]) }}</span>
                    </div>

                    <div class="teacher-bundles-card__courses">
                        @foreach ($bundle->items->take(4) as $item)
                            <span class="teacher-bundles-course-pill">
                                {{ $item->course?->name_locale ?: __('teacher::dashboard.common.unknown_course') }}
                            </span>
                        @endforeach
                        @if ($bundle->items_count > 4)
                            <span class="teacher-bundles-course-pill">
                                +{{ $bundle->items_count - 4 }}
                            </span>
                        @endif
                    </div>

                    <div class="teacher-bundles-card__actions">
                        <a href="{{ route('teacher.dashboard.bundles.edit', ['bundle' => $bundle->id]) }}"
                            class="btn btn-outline-primary">
                            {{ __('teacher::dashboard.bundles.actions.edit') }}
                        </a>
                        <a href="{{ route('courses.bundle.detail', ['locale' => app()->getLocale(), 'slug' => $bundle->slug]) }}"
                            target="_blank" class="btn btn-outline-secondary">
                            {{ __('teacher::dashboard.bundles.actions.view_public') }}
                        </a>
                        <form action="{{ route('teacher.dashboard.bundles.delete', ['bundle' => $bundle->id]) }}" method="POST"
                            onsubmit="return confirm(@js(__('teacher::dashboard.bundles.confirm_delete')));">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                {{ __('teacher::dashboard.bundles.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="teacher-bundles-empty">
                    <div class="teacher-bundles-empty__icon"><i class="fas fa-layer-group"></i></div>
                    <h4>{{ __('teacher::dashboard.bundles.empty') }}</h4>
                    <p class="mb-0">{{ __('teacher::dashboard.bundles.empty_description') }}</p>
                </div>
            @endforelse
        </div>

        @if ($bundles->hasPages())
            <div class="mt-4">{{ $bundles->links() }}</div>
        @endif
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-bundles-shell { background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.12), transparent 28%), linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%); }
        .teacher-bundles-hero { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; margin-bottom: 1.5rem; }
        .teacher-bundles-kicker { display: inline-flex; padding: 0.45rem 0.8rem; border-radius: 999px; background: rgba(16, 185, 129, 0.18); color: #bbf7d0; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; }
        .teacher-bundles-title { margin-top: 1rem; margin-bottom: 0.55rem; font-size: clamp(2rem, 3vw, 2.7rem); font-weight: 900; color: #f8fbff; }
        .teacher-bundles-desc { max-width: 760px; color: #a9bbd5; line-height: 1.75; }
        .teacher-bundles-grid { display: grid; gap: 1rem; }
        .teacher-bundles-card, .teacher-bundles-empty { background: rgba(18, 28, 50, 0.72); border: 1px solid rgba(74, 222, 128, 0.16); border-radius: 22px; padding: 1.25rem; box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18); }
        .teacher-bundles-card__head, .teacher-bundles-card__meta, .teacher-bundles-card__actions { display: flex; justify-content: space-between; gap: 1rem; align-items: center; }
        .teacher-bundles-card__head h4 { margin: 0.6rem 0 0; color: #f8fbff; font-weight: 800; }
        .teacher-bundles-card__head strong { color: #f8fbff; font-size: 1.1rem; }
        .teacher-bundles-status { display: inline-flex; padding: 0.3rem 0.7rem; border-radius: 999px; font-size: 0.75rem; font-weight: 800; }
        .teacher-bundles-status.is-active { background: rgba(34, 197, 94, 0.18); color: #bbf7d0; }
        .teacher-bundles-status.is-draft { background: rgba(148, 163, 184, 0.18); color: #cbd5e1; }
        .teacher-bundles-card__desc, .teacher-bundles-card__meta { color: #a9bbd5; }
        .teacher-bundles-card__courses { display: flex; flex-wrap: wrap; gap: 0.5rem; margin: 1rem 0; }
        .teacher-bundles-course-pill { display: inline-flex; padding: 0.38rem 0.72rem; border-radius: 999px; background: rgba(59, 130, 246, 0.14); color: #dbeafe; font-size: 0.82rem; }
        .teacher-bundles-card__actions { margin-top: 1rem; flex-wrap: wrap; justify-content: flex-start; }
        .teacher-bundles-empty { text-align: center; padding: 2.4rem 1.5rem; }
        .teacher-bundles-empty__icon { width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 22px; display: grid; place-items: center; background: linear-gradient(135deg, rgba(16, 185, 129, 0.24), rgba(59, 130, 246, 0.24)); color: #ecfeff; font-size: 1.8rem; }
        .teacher-bundles-empty h4 { color: #f8fbff; margin-bottom: 0.5rem; }
        .teacher-bundles-empty p { color: #a9bbd5; max-width: 560px; margin: 0 auto; }
        html[data-theme="light"] .teacher-bundles-shell { background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 26%), linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%); }
        html[data-theme="light"] .teacher-bundles-card, html[data-theme="light"] .teacher-bundles-empty { background: var(--admin-surface); border-color: var(--admin-border); box-shadow: var(--admin-card-shadow); }
        html[data-theme="light"] .teacher-bundles-title, html[data-theme="light"] .teacher-bundles-card__head h4, html[data-theme="light"] .teacher-bundles-card__head strong, html[data-theme="light"] .teacher-bundles-empty h4 { color: #0f172a; }
        html[data-theme="light"] .teacher-bundles-desc, html[data-theme="light"] .teacher-bundles-card__desc, html[data-theme="light"] .teacher-bundles-card__meta, html[data-theme="light"] .teacher-bundles-empty p { color: #475569; }
        @media (max-width: 991.98px) { .teacher-bundles-hero, .teacher-bundles-card__head, .teacher-bundles-card__meta { flex-direction: column; align-items: flex-start; } }
    </style>
@endsection
