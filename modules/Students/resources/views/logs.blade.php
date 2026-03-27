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
            'Trạng thái' => isset($student->status) ? ($student->status ? 'Kích hoạt' : 'Chưa kích hoạt') : 'N/A',
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
