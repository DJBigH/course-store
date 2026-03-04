@extends('layouts.backend')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Danh sach binh luan khoa hoc</h4>
            <p class="text-muted mb-0">Loc theo khoa hoc, trang thai hien thi va binh luan bi flag.</p>
        </div>
        <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">Ve danh sach khoa hoc</a>
    </div>

    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif

    <form method="GET" class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Khoa hoc</label>
                    <select name="course_id" class="form-select">
                        <option value="">Tat ca</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hien thi</label>
                    <select name="visibility" class="form-select">
                        <option value="">Tat ca</option>
                        <option value="visible" @selected(request('visibility') === 'visible')>Dang hien</option>
                        <option value="hidden" @selected(request('visibility') === 'hidden')>Dang an</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nguoi gui</label>
                    <select name="role" class="form-select">
                        <option value="">Tat ca</option>
                        <option value="student" @selected(request('role') === 'student')>Hoc vien</option>
                        <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Flag ngon ngu bay ba</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="flagged" name="flagged"
                            @checked(request()->boolean('flagged'))>
                        <label class="form-check-label" for="flagged">
                            Chi hien comment bi flag
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tim noi dung</label>
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Nhap noi dung can tim...">
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary">Loc</button>
                <a href="{{ route('courses.comments.admin') }}" class="btn btn-light border">Xoa loc</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Khoa hoc</th>
                        <th>Nguoi gui</th>
                        <th>Noi dung</th>
                        <th>Trang thai</th>
                        <th>Flag</th>
                        <th>Thoi gian</th>
                        <th>Xu ly</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comments as $comment)
                        <tr>
                            <td>{{ $comment->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $comment->course?->name ?? 'N/A' }}</div>
                                @if ($comment->parent_id)
                                    <small class="text-muted">Tra loi cho comment #{{ $comment->parent_id }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $comment->user_id ? 'bg-primary' : 'bg-success' }}">
                                    {{ $comment->user_id ? 'Admin' : 'Hoc vien' }}
                                </span>
                                <div class="mt-1">{{ $comment->author_name }}</div>
                            </td>
                            <td style="min-width: 320px;">
                                <div>{{ $comment->content }}</div>
                                @if ($comment->flagged_terms)
                                    <div class="small text-danger mt-1">Tu khoa: {{ $comment->flagged_terms }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $comment->is_visible ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $comment->is_visible ? 'Dang hien' : 'Dang an' }}
                                </span>
                            </td>
                            <td>
                                @if ($comment->is_flagged)
                                    <span class="badge bg-danger">Bi flag</span>
                                @else
                                    <span class="badge bg-light text-dark">Binh thuong</span>
                                @endif
                            </td>
                            <td>{{ optional($comment->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <div class="d-grid gap-2">
                                    <form method="POST"
                                        action="{{ route('courses.comments.admin-toggle', ['commentId' => $comment->id]) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary w-100">
                                            {{ $comment->is_visible ? 'An' : 'Hien' }}
                                        </button>
                                    </form>
                                    @if ($comment->course)
                                        <a href="{{ route('courses.detail', ['locale' => 'vi', 'slug' => $comment->course->slug]) }}#evaluate"
                                            target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                            Xem ngoai client
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Chua co binh luan nao.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $comments->links() }}
    </div>
@endsection
