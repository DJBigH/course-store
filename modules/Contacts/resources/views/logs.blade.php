@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? 'Lịch sử liên hệ',
        'backUrl' => route('contacts.index'),
        'backLabel' => 'Quay lại danh sách',
        'entityTitle' => 'Thông tin liên hệ',
        'entityItems' => [
            'Tên' => $contacts->name ?? 'N/A',
            'Email' => $contacts->email ?? 'N/A',
            'Số điện thoại' => $contacts->phone ?? 'N/A',
        ],
        'filterActions' => [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'accept' => 'Tiếp nhận',
            'view' => 'Xem chi tiết',
        ],
        'logs' => $logs,
    ])
@endsection
