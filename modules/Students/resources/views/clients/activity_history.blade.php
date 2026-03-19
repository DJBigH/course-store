@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $loginBadgeClass = static fn($log) => !empty(($log->properties ?? [])['is_unusual'])
            ? 'history-badge history-badge--warning'
            : 'history-badge history-badge--success';

        $activityBadgeMap = [
            'update_profile' => 'history-badge history-badge--primary',
            'change_password' => 'history-badge history-badge--danger',
            'two_factor_code_sent' => 'history-badge history-badge--info',
            'enable_two_factor' => 'history-badge history-badge--success',
            'disable_two_factor' => 'history-badge history-badge--warning',
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
                        <div class="account-dashboard-block">
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
                                        class="btn btn-outline-secondary px-4">
                                        {{ __('students::clients/account.core.reset') }}
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="account-dashboard-block">
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

                        <div class="account-dashboard-block mt-4">
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

                                                    @if ($log->action === 'update_profile' && !empty($properties['old']) && !empty($properties['new']))
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