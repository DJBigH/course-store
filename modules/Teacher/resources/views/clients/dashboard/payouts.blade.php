@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <h3 class="fw-bold mb-2">Rut tien</h3>
        <p class="text-muted mb-4">Gui yeu cau rut tien de admin xu ly. Ban MVP nay chua co auto payout.</p>

        @include('teacher::clients.dashboard._tabs')

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
                    <div class="teacher-stat-card__label">Tong thuc nhan tam tinh</div>
                    <div class="teacher-stat-card__value">{{ money($summary['teacher_revenue']) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">Da yeu cau rut</div>
                    <div class="teacher-stat-card__value">{{ money($requestedAmount) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">So du kha dung</div>
                    <div class="teacher-stat-card__value">{{ money($availableBalance) }}</div>
                </div>
            </div>
        </div>

        <div class="teacher-panel mb-4">
            <h3 class="h5 fw-bold mb-3">Gui yeu cau rut tien</h3>
            <form method="POST" action="{{ route('teacher.dashboard.payouts.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">So tien</label>
                        <input type="number" class="form-control" name="amount" min="10000" step="0.01"
                            value="{{ old('amount') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ten ngan hang</label>
                        <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ten chu tai khoan</label>
                        <input type="text" class="form-control" name="bank_account_name" value="{{ old('bank_account_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">So tai khoan</label>
                        <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ghi chu</label>
                        <input type="text" class="form-control" name="note" value="{{ old('note') }}">
                    </div>
                </div>
                <button class="btn btn-primary mt-3">Gui yeu cau</button>
            </form>
        </div>

        <div class="teacher-panel">
            <h3 class="h5 fw-bold mb-3">Lich su yeu cau</h3>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>So tien</th>
                            <th>Ngan hang</th>
                            <th>Trang thai</th>
                            <th>Gui luc</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payouts as $payout)
                            <tr>
                                <td>#{{ $payout->id }}</td>
                                <td>{{ money($payout->amount) }}</td>
                                <td>{{ $payout->bank_name }}<br><small class="text-muted">{{ $payout->bank_account_number }}</small></td>
                                <td><span class="badge bg-info">{{ ucfirst($payout->status) }}</span></td>
                                <td>{{ optional($payout->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Chua co yeu cau rut tien nao.</td>
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
