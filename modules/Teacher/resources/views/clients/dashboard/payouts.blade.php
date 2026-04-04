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
            <h3 class="h5 fw-bold mb-3">{{ __('teacher::dashboard.payouts.form.title') }}</h3>
            <form method="POST" action="{{ route('teacher.dashboard.payouts.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.amount') }}</label>
                        <input type="number" class="form-control" name="amount" min="10000" step="0.01" value="{{ old('amount') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_name') }}</label>
                        <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_name') }}</label>
                        <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.bank_account_number') }}</label>
                        <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('teacher::dashboard.payouts.form.note') }}</label>
                        <input type="text" class="form-control" name="note" value="{{ old('note') }}">
                    </div>
                </div>
                <button class="btn btn-primary mt-3">{{ __('teacher::dashboard.payouts.form.submit') }}</button>
            </form>
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
