@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? 'Lịch sử học viên',
        'backUrl' => route('students.index'),
        'backLabel' => 'Quay lại danh sách',
        'entityTitle' => 'Thông tin học viên',
        'entityItems' => [
            'Tên' => $student->name ?? 'N/A',
            'Email' => $student->email ?? 'N/A',
            'Kinh nghiệm' => $student->exp ?? 'N/A',
        ],
        'filterActions' => [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'assigned_coupon' => 'Được gán mã',
            'revoked_coupon' => 'Bị hủy mã',
        ],
        'logs' => $logs,
    ])
@endsection
