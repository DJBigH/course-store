@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? 'Lịch sử chuyên mục',
        'backUrl' => route('categories.index'),
        'backLabel' => 'Quay lại danh sách',
        'entityTitle' => 'Thông tin chuyên mục',
        'entityItems' => [
            'Chuyên mục' => $cate->name ?? 'N/A',
            'Slug' => $cate->slug ?? 'N/A',
            'ID' => '#' . ($cate->id ?? '-'),
        ],
        'filterActions' => [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
        ],
        'logs' => $logs,
    ])
@endsection
