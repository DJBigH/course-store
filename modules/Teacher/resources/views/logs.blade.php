@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? 'Lịch sử giảng viên',
        'backUrl' => route('teacher.index'),
        'backLabel' => 'Quay lại danh sách',
        'entityTitle' => 'Thông tin giảng viên',
        'entityItems' => [
            'Tên' => $teacher->name ?? 'N/A',
            'Kinh nghiệm' => $teacher->exp ?? 'N/A',
            'ID' => '#' . ($teacher->id ?? '-'),
        ],
        'filterActions' => [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
        ],
        'logs' => $logs,
    ])
@endsection
