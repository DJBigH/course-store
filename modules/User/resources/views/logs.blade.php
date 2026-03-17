@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? 'Lịch sử người dùng',
        'backUrl' => route('user.index'),
        'backLabel' => 'Quay lại danh sách',
        'entityTitle' => 'Thông tin người dùng',
        'entityItems' => [
            'Tên' => $user->name ?? 'N/A',
            'Email' => $user->email ?? 'N/A',
            'ID' => '#' . ($user->id ?? '-'),
        ],
        'filterActions' => [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
        ],
        'logs' => $logs,
    ])
@endsection
