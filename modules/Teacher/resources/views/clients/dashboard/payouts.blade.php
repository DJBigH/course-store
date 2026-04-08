@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.payouts.title') }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::dashboard.payouts.description') }}</p>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
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

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.payouts.summary.teacher_revenue') }}</div>
                    <div class="teacher-stat-card__value">{{ money($summary['teacher_revenue'], 'đ', '0 đ') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.payouts.summary.requested') }}</div>
                    <div class="teacher-stat-card__value">{{ money($requestedAmount, 'đ', '0 đ') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.payouts.summary.available') }}</div>
                    <div class="teacher-stat-card__value">{{ money($availableBalance, 'đ', '0 đ') }}</div>
                </div>
            </div>
        </div>

        <div class="teacher-panel mb-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h3 class="h5 fw-bold mb-1">{{ __('teacher::dashboard.payouts.saved_accounts.title') }}</h3>
                    <p class="text-muted mb-0">
                        {{ __('teacher::dashboard.payouts.saved_accounts.description', ['count' => $payoutAccounts->count(), 'limit' => $payoutAccountLimit]) }}
                    </p>
                </div>
                <span class="teacher-chip">{{ $payoutAccounts->count() }}/{{ $payoutAccountLimit }}</span>
            </div>

            <div class="row g-3">
                @forelse ($payoutAccounts as $account)
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="fw-semibold">{{ $account->bank_name }}</div>
                            <div>{{ $account->bank_account_name }}</div>
                            <div class="text-muted">{{ $account->bank_account_number }}</div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="border rounded-3 p-3 text-muted">{{ __('teacher::dashboard.payouts.saved_accounts.empty') }}</div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="teacher-panel mb-4">
            <h3 class="h5 fw-bold mb-2">{{ __('teacher::dashboard.payouts.form.title') }}</h3>
            <p class="text-muted mb-3">{{ __('teacher::dashboard.payouts.form.help') }}</p>

            <form method="POST" action="{{ route('teacher.dashboard.payouts.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.amount') }}</label>
                        <input type="number" class="form-control" name="amount" min="10000" step="0.01" value="{{ old('amount') }}">
                    </div>

                    @if ($payoutAccounts->isNotEmpty())
                        <div class="col-md-8">
                            <label class="form-label d-block">{{ __('teacher::dashboard.payouts.form.account_mode') }}</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="account_mode" id="account-mode-saved" value="saved"
                                    {{ old('account_mode', 'saved') === 'saved' ? 'checked' : '' }}>
                                <label class="form-check-label" for="account-mode-saved">{{ __('teacher::dashboard.payouts.form.use_saved_account') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="account_mode" id="account-mode-new" value="new"
                                    {{ old('account_mode') === 'new' ? 'checked' : '' }}>
                                <label class="form-check-label" for="account-mode-new">{{ __('teacher::dashboard.payouts.form.use_new_account') }}</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.saved_account') }}</label>
                            <select name="payout_account_id" class="form-select">
                                <option value="">{{ __('teacher::dashboard.payouts.form.select_saved_account') }}</option>
                                @foreach ($payoutAccounts as $account)
                                    <option value="{{ $account->id }}" {{ (string) old('payout_account_id') === (string) $account->id ? 'selected' : '' }}>
                                        {{ $account->bank_name }} - {{ $account->bank_account_name }} - {{ $account->bank_account_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="account_mode" value="new">
                    @endif

                    <div class="col-md-4">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_name') }}</label>
                        <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_name') }}</label>
                        <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_number') }}</label>
                        <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.note') }}</label>
                        <input type="text" class="form-control" name="note" value="{{ old('note') }}">
                    </div>
                </div>
                <button class="btn btn-primary mt-3">{{ __('teacher::dashboard.payouts.form.submit') }}</button>
            </form>
        </div>

        <div class="teacher-panel mb-4">
            <h3 class="h5 fw-bold mb-2">{{ __('teacher::dashboard.payouts.change_requests.title') }}</h3>
            <p class="text-muted mb-3">{{ __('teacher::dashboard.payouts.change_requests.description') }}</p>

            @if ($payoutAccounts->count() >= $payoutAccountLimit)
                <form method="POST" action="{{ route('teacher.dashboard.payouts.account-change.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.change_requests.replace_account') }}</label>
                            <select name="replace_payout_account_id" class="form-select">
                                <option value="">{{ __('teacher::dashboard.payouts.change_requests.select_replace_account') }}</option>
                                @foreach ($payoutAccounts as $account)
                                    <option value="{{ $account->id }}" {{ (string) old('replace_payout_account_id') === (string) $account->id ? 'selected' : '' }}>
                                        {{ $account->bank_name }} - {{ $account->bank_account_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_name') }}</label>
                            <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_name') }}</label>
                            <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_number') }}</label>
                            <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('teacher::dashboard.payouts.form.note') }}</label>
                            <input type="text" class="form-control" name="note" value="{{ old('note') }}">
                        </div>
                    </div>
                    <button class="btn btn-outline-primary mt-3">{{ __('teacher::dashboard.payouts.change_requests.submit') }}</button>
                </form>
            @else
                <div class="border rounded-3 p-3 text-muted">{{ __('teacher::dashboard.payouts.change_requests.limit_hint', ['limit' => $payoutAccountLimit]) }}</div>
            @endif

            <div class="table-responsive mt-4">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.id') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.replace_account') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.new_account') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.status') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.change_requests.table.submitted_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingAccountChangeRequests as $item)
                            <tr>
                                <td>#{{ $item->id }}</td>
                                <td>
                                    @if ($item->replace_bank_name)
                                        {{ $item->replace_bank_name }}<br>
                                        <small class="text-muted">{{ $item->replace_bank_account_name }} - {{ $item->replace_bank_account_number }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ $item->bank_name }}<br>
                                    <small class="text-muted">{{ $item->bank_account_name }} - {{ $item->bank_account_number }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ __('teacher::dashboard.payouts.change_requests.status.' . $item->status) }}</span>
                                    @if ($item->admin_note)
                                        <div class="small text-muted mt-1">{{ $item->admin_note }}</div>
                                    @endif
                                </td>
                                <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('teacher::dashboard.payouts.change_requests.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="teacher-panel">
            <h3 class="h5 fw-bold mb-3">{{ __('teacher::dashboard.payouts.history_title') }}</h3>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('teacher::dashboard.payouts.table.id') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.table.amount') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.table.bank') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.table.status') }}</th>
                            <th>{{ __('teacher::dashboard.payouts.table.submitted_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payouts as $payout)
                            <tr>
                                <td>#{{ $payout->id }}</td>
                                <td>{{ money($payout->amount, 'đ', '0 đ') }}</td>
                                <td>{{ $payout->bank_name }}<br><small class="text-muted">{{ $payout->bank_account_number }}</small></td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ __('teacher::dashboard.payouts.status.' . $payout->status) }}
                                    </span>
                                </td>
                                <td>{{ optional($payout->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('teacher::dashboard.payouts.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $payouts->links() }}
        </div>
    </div>
@endsection
