@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? 'Lịch sử cấu hình website',
        'backUrl' => route('settings.index'),
        'backLabel' => 'Quay lại cấu hình',
        'entityTitle' => 'Phạm vi theo dõi',
        'entityItems' => [
            'Module' => 'Cấu hình website',
            'Hành động chính' => 'Cập nhật cấu hình',
        ],
        'filterRoute' => route('settings.logs'),
        'filterActions' => [
            'update_settings' => 'Cập nhật cấu hình',
        ],
        'logs' => $logs,
    ])
@endsection
