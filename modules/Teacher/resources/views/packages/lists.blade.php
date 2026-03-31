@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Goi giang vien</h5>
                    <p class="text-muted mb-0">Quan ly bang gia va commission cho flow onboarding giang vien.</p>
                </div>
                <a href="{{ route('teacher-packages.add') }}" class="btn btn-primary">Them goi</a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Ten goi</th>
                            <th>Gia</th>
                            <th>Commission</th>
                            <th>Trang thai</th>
                            <th class="text-end">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($packages as $package)
                            <tr>
                                <td>{{ strtoupper($package->code) }}</td>
                                <td>
                                    <strong>{{ $package->name }}</strong>
                                    <div class="text-muted small">{{ $package->description }}</div>
                                </td>
                                <td>{{ money($package->price) }}</td>
                                <td>{{ rtrim(rtrim(number_format($package->commission_rate, 2, '.', ''), '0'), '.') }}%</td>
                                <td>
                                    <span class="badge bg-{{ $package->status ? 'success' : 'secondary' }}">
                                        {{ $package->status ? 'Dang bat' : 'Dang tat' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('teacher-packages.edit', $package->id) }}" class="btn btn-sm btn-warning">Sua</a>
                                    <form action="{{ route('teacher-packages.delete', $package->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Xoa goi nay?')">Xoa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chua co goi nao.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $packages->links() }}
        </div>
    </div>
@endsection
