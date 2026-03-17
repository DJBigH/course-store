@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? 'Lịch sử mã giảm giá',
        'backUrl' => route('coupons.index'),
        'backLabel' => 'Quay lại danh sách',
        'entityTitle' => 'Thông tin mã giảm giá',
        'entityItems' => [
            'Mã giảm giá' => $coupons->code ?? 'N/A',
            'Giá trị' => isset($coupons->value) ? $coupons->value : 'N/A',
            'ID' => '#' . ($coupons->id ?? '-'),
        ],
        'filterActions' => [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'assign_students' => 'Gán học viên',
            'revoke_students' => 'Hủy gán học viên',
            'sync_students' => 'Đồng bộ học viên',
        ],
        'logs' => $logs,
    ])
@endsection
