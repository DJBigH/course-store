@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? 'Lịch sử khóa học',
        'backUrl' => route('courses.index'),
        'backLabel' => 'Quay lại danh sách',
        'entityTitle' => 'Thông tin khóa học',
        'entityItems' => [
            'Khóa học' => $course->name ?? 'N/A',
            'Mã khóa học' => $course->code ?? 'N/A',
        ],
        'filterActions' => [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
        ],
        'logs' => $logs,
    ])
@endsection
