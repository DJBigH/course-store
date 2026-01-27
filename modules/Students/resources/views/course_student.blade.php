@extends('layouts.backend')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between">
            <h5>
                🎓 Khóa học đã mua của:
                <span class="text-primary">{{ $student->name }}</span>

                <span class="badge bg-info ms-2">
                    {{ $student->courses->count() }} khóa học
                </span>
            </h5>
            <a href="{{ route('students.index') }}" class="btn btn-sm btn-secondary">
                ← Quay lại
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tên khóa học</th>
                        <th>Trạng thái</th>
                        <th>Ngày mua</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($student->courses as $course)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $course->name }}</td>
                            <td>
                                @if ($course->pivot->status == 1)
                                    <span class="badge bg-success">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Dừng hoạt động
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ $course->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Học viên chưa mua khóa học nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
