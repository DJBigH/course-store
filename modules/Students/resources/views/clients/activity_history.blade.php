@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $loginBadgeClass = static fn($log) => !empty(($log->properties ?? [])['is_unusual'])
            ? 'history-badge history-badge--warning'
            : 'history-badge history-badge--success';
        $localizedValue = static function ($value) {
            if (!is_array($value)) {
                return $value;
            }

            $locale = app()->getLocale();

            return $value[$locale]
                ?? $value['vi']
                ?? $value['en']
                ?? collect($value)->filter()->first()
                ?? '-';
        };

        $activityBadgeMap = [
            'profile_updated' => 'history-badge history-badge--primary',
            'password_changed' => 'history-badge history-badge--danger',
            'two_factor_code_sent' => 'history-badge history-badge--info',
            'two_factor_enabled' => 'history-badge history-badge--success',
            'two_factor_disabled' => 'history-badge history-badge--warning',
            'account_deactivated' => 'history-badge history-badge--warning',
            'account_deleted' => 'history-badge history-badge--danger',
            'order_purchased' => 'history-badge history-badge--success',
            'lesson_learned' => 'history-badge history-badge--primary',
        ];
    @endphp

    <section class="account-page py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="account-content">
                        <div class="account-dashboard-block" id="activity-history-filter-block">
                            <div class="account-dashboard-block__head mb-3">
                                <h4>{{ __('students::clients/account.activity_history.title') }}</h4>
                            </div>

                            <form method="GET" action="{{ route('students.account.activity-history', ['locale' => app()->getLocale()]) }}"
                                class="activity-history-filter mb-4">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('students::clients/account.core.from_date') }}</label>
                                        <input type="date" name="from_date" class="form-control"
                                            value="{{ $filters['from_date'] ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('students::clients/account.core.to_date') }}</label>
                                        <input type="date" name="to_date" class="form-control"
                                            value="{{ $filters['to_date'] ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('students::clients/account.core.action') }}</label>
                                        <select name="action" class="form-select">
                                            <option value="">{{ __('students::clients/account.activity_history.all_actions') }}</option>
                                            @foreach ($availableActions as $action)
                                                <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>
                                                    {{ __('students::clients/account.activity_history.actions.' . $action) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary px-4">
                                        {{ __('students::clients/account.core.filter') }}
                                    </button>
                                    <a href="{{ route('students.account.activity-history', ['locale' => app()->getLocale()]) }}"
                                        data-history-reset
                                        class="btn btn-outline-secondary px-4">
                                        {{ __('students::clients/account.core.reset') }}
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="account-dashboard-block" id="login-history-block">
                            <div class="account-dashboard-block__head">
                                <h4>{{ __('students::clients/account.activity_history.login_title') }}</h4>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-profile align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ __('students::clients/account.activity_history.time') }}</th>
                                            <th>{{ __('students::clients/account.activity_history.device') }}</th>
                                            <th>{{ __('students::clients/account.activity_history.network') }}</th>
                                            <th>{{ __('students::clients/account.activity_history.note') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($loginLogs as $log)
                                            @php($properties = $log->properties ?? [])
                                            <tr>
                                                <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                                                <td>
                                                    <div class="fw-semibold">{{ $properties['browser'] ?? 'Unknown browser' }}</div>
                                                    <div class="text-muted small">{{ $properties['platform'] ?? 'Unknown platform' }} - {{ $properties['device'] ?? 'Unknown device' }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $properties['ip'] ?? $log->ip ?? '-' }}</div>
                                                    @if (!empty($properties['previous_ip']))
                                                        <div class="text-muted small">{{ __('students::clients/account.activity_history.previous') }}: {{ $properties['previous_ip'] }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="{{ $loginBadgeClass($log) }} mb-2">
                                                        {{ !empty($properties['is_unusual']) ? __('students::clients/account.activity_history.unusual') : __('students::clients/account.activity_history.normal') }}
                                                    </span>
                                                    <div class="small text-muted">{{ $log->description }}</div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">{{ __('students::clients/account.activity_history.empty_login') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{ $loginLogs->links() }}
                        </div>

                        <div class="account-dashboard-block mt-4" id="activity-history-block">
                            <div class="account-dashboard-block__head">
                                <h4>{{ __('students::clients/account.activity_history.activity_title') }}</h4>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-profile align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ __('students::clients/account.activity_history.time') }}</th>
                                            <th>{{ __('students::clients/account.activity_history.action') }}</th>
                                            <th>{{ __('students::clients/account.activity_history.detail') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($activityLogs as $log)
                                            @php($properties = $log->properties ?? [])
                                            <tr>
                                                <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                                                <td>
                                                    <span class="{{ $activityBadgeMap[$log->action] ?? 'history-badge history-badge--muted' }} mb-2">
                                                        {{ __('students::clients/account.activity_history.actions.' . $log->action) }}
                                                    </span>
                                                    <div class="text-muted small mt-2">{{ ucfirst(str_replace('_', ' ', $log->log_name)) }}</div>
                                                </td>
                                                <td>
                                                    <div class="small">{{ $log->description }}</div>

                                                    @if ($log->action === 'profile_updated' && !empty($properties['old']) && !empty($properties['new']))
                                                        <div class="small text-muted mt-2">
                                                            @foreach ($properties['new'] as $field => $value)
                                                                @if (($properties['old'][$field] ?? null) != $value)
                                                                    <div>
                                                                        <strong>{{ __('students::clients/account.activity_history.fields.' . $field) }}:</strong>
                                                                        {{ $properties['old'][$field] ?? '-' }} -> {{ $value ?: '-' }}
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @elseif ($log->action === 'two_factor_code_sent')
                                                        <div class="small text-muted mt-2">
                                                            <div><strong>{{ __('students::clients/account.activity_history.code_purpose') }}:</strong> {{ $properties['purpose'] ?? '-' }}</div>
                                                            <div><strong>{{ __('students::clients/account.activity_history.device') }}:</strong> {{ $properties['browser'] ?? '-' }} / {{ $properties['platform'] ?? '-' }}</div>
                                                        </div>
                                                    @elseif ($log->action === 'order_purchased')
                                                        <div class="small text-muted mt-2">
                                                            <div><strong>{{ __('students::clients/account.activity_history.order_code_label') }}:</strong> {{ $properties['order_code'] ?? '-' }}</div>
                                                            <div><strong>{{ __('students::clients/account.activity_history.order_status_label') }}:</strong> {{ $localizedValue($properties['order_status'] ?? '-') }}</div>
                                                            <div><strong>{{ __('students::clients/account.activity_history.amount_label') }}:</strong> {{ isset($properties['total_paid']) ? moneyLocale($properties['total_paid']) : '-' }}</div>
                                                            @if (!empty($properties['courses']) && is_array($properties['courses']))
                                                                <div><strong>{{ __('students::clients/account.activity_history.course_label') }}:</strong></div>
                                                                @foreach ($properties['courses'] as $courseName)
                                                                    <div>- {{ $localizedValue($courseName) }}</div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @elseif ($log->action === 'lesson_learned')
                                                        <div class="small text-muted mt-2">
                                                            <div><strong>{{ __('students::clients/account.activity_history.course_label') }}:</strong> {{ $localizedValue($properties['course_name'] ?? '-') }}</div>
                                                            <div><strong>{{ __('students::clients/account.activity_history.lesson_label') }}:</strong> {{ $localizedValue($properties['lesson_name'] ?? '-') }}</div>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">{{ __('students::clients/account.activity_history.empty_activity') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{ $activityLogs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        (() => {
            const pageRoot = document.querySelector('.account-page');
            const filterBlockSelector = '#activity-history-filter-block';
            const loginBlockSelector = '#login-history-block';
            const activityBlockSelector = '#activity-history-block';

            if (!pageRoot) {
                return;
            }

            const setLoadingState = (isLoading) => {
                [filterBlockSelector, loginBlockSelector, activityBlockSelector].forEach((selector) => {
                    const block = document.querySelector(selector);

                    if (block) {
                        block.style.opacity = isLoading ? '0.55' : '1';
                        block.style.pointerEvents = isLoading ? 'none' : 'auto';
                    }
                });
            };

            const replaceFilterBlock = (doc) => {
                const currentBlock = document.querySelector(filterBlockSelector);
                const nextBlock = doc.querySelector(filterBlockSelector);

                if (currentBlock && nextBlock) {
                    currentBlock.replaceWith(nextBlock);
                }
            };

            const replaceHistoryBlocks = (doc) => {
                [loginBlockSelector, activityBlockSelector].forEach((selector) => {
                    const currentBlock = document.querySelector(selector);
                    const nextBlock = doc.querySelector(selector);

                    if (currentBlock && nextBlock) {
                        currentBlock.replaceWith(nextBlock);
                    }
                });
            };

            const fetchHistoryPage = async (url, shouldPushState = true) => {
                setLoadingState(true);

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load activity history page.');
                    }

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    replaceFilterBlock(doc);
                    replaceHistoryBlocks(doc);

                    if (shouldPushState) {
                        window.history.pushState({
                            url
                        }, '', url);
                    }
                } catch (error) {
                    window.location.assign(url);
                } finally {
                    setLoadingState(false);
                }
            };

            pageRoot.addEventListener('click', (event) => {
                const link = event.target.closest('.pagination a.page-link, .pagination a.page-link-compact');

                if (link && pageRoot.contains(link)) {
                    event.preventDefault();
                    fetchHistoryPage(link.href);
                    return;
                }

                const resetLink = event.target.closest('a[data-history-reset]');

                if (!resetLink || !pageRoot.contains(resetLink)) {
                    return;
                }

                event.preventDefault();
                fetchHistoryPage(resetLink.href);
            });

            pageRoot.addEventListener('submit', (event) => {
                const form = event.target.closest('.activity-history-filter');

                if (!form || !pageRoot.contains(form)) {
                    return;
                }

                event.preventDefault();

                const formData = new FormData(form);
                const params = new URLSearchParams();

                formData.forEach((value, key) => {
                    if (String(value).trim() !== '') {
                        params.append(key, value);
                    }
                });

                const queryString = params.toString();
                const targetUrl = queryString ? `${form.action}?${queryString}` : form.action;

                fetchHistoryPage(targetUrl);
            });

            window.addEventListener('popstate', () => {
                fetchHistoryPage(window.location.href, false);
            });
        })();
    </script>
@endsection
