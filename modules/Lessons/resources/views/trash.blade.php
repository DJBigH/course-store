@extends('layouts.backend')

@section('content')
    <div class="admin-form">
        <div class="admin-form__header">
            <div>
                <h5 class="mb-1">Thùng rác bài giảng</h5>
                <p class="text-muted mb-0">Khôi phục hoặc xóa vĩnh viễn các bài giảng đã xóa mềm của khóa học này.</p>
            </div>
            <a href="{{ route('lessons.index', $courses->id) }}" class="btn btn-light border">Quay lại danh sách</a>
        </div>

        @if (session('msg'))
            <div class="alert alert-success">{{ session('msg') }}</div>
        @endif

        @if (empty($trashedLessonRows))
            <div class="alert alert-info mb-0">Hiện chưa có bài giảng nào trong thùng rác.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="datatable">
                    <thead>
                        <tr>
                            <th>Tên</th>
                            <th>Học thử</th>
                            <th>Tài liệu</th>
                            <th>Lượt xem</th>
                            <th>Thời lượng</th>
                            <th>Thời gian xóa</th>
                            <th>Khôi phục</th>
                            <th>Xóa vĩnh viễn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trashedLessonRows as $lesson)
                            <tr>
                                <td>{{ $lesson['name'] }}</td>
                                <td>{{ $lesson['is_trial'] }}</td>
                                <td>{{ $lesson['document'] }}</td>
                                <td>{{ $lesson['view'] }}</td>
                                <td>{{ $lesson['durations'] }}</td>
                                <td>{{ $lesson['deleted_at'] }}</td>
                                <td>
                                    @if (auth()->user()?->canAnyPermission(['lessons.restore', 'lessons.delete', 'lessons.soft_delete']))
                                        <form action="{{ route('lessons.restore', $lesson['id']) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Khôi phục</button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    @if (auth()->user()?->hasPermission('lessons.force_delete'))
                                        <a href="{{ route('lessons.force-delete', $lesson['id']) }}"
                                            class="btn btn-outline-danger btn-sm delete-action">Xóa vĩnh viễn</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @include('part.backend.delete')
@endsection
