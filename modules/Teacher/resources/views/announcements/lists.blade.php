@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Thong bao teacher theo goi</h5>
                    <p class="text-muted mb-0">Quan ly cac announcement de day vao chuong teacher theo tung goi dang su dung.</p>
                </div>
                <a href="{{ route('teacher-announcements.add') }}" class="btn btn-primary">Them thong bao</a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Tieu de</th>
                            <th>Ap dung cho goi</th>
                            <th>Thoi gian</th>
                            <th>Trang thai</th>
                            <th class="text-end">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td>
                                    <strong>{{ $announcement->title }}</strong>
                                    <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit($announcement->message, 110) }}</div>
                                </td>
                                <td>
                                    @forelse ($announcement->packages as $package)
                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $package->name }}</span>
                                    @empty
                                        <span class="badge bg-info-subtle text-info-emphasis border">Tat ca goi</span>
                                    @endforelse
                                </td>
                                <td class="small text-muted">
                                    <div>Bat dau: {{ $announcement->starts_at?->format('d/m/Y H:i') ?: 'Ngay lap tuc' }}</div>
                                    <div>Ket thuc: {{ $announcement->ends_at?->format('d/m/Y H:i') ?: 'Khong gioi han' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $announcement->status ? 'success' : 'secondary' }}">
                                        {{ $announcement->status ? 'Dang hoat dong' : 'Dang tat' }}
                                    </span>
                                    @if ($announcement->is_pinned)
                                        <div class="small text-warning mt-1">Pinned</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('teacher-announcements.edit', $announcement->id) }}" class="btn btn-sm btn-warning">Sua</a>
                                    <form action="{{ route('teacher-announcements.delete', $announcement->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Xoa thong bao nay?')">Xoa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Chua co thong bao teacher nao.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
