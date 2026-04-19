@extends('layouts.backend')

@section('content')
    @include('part.backend.activity_logs', [
        'pageTitle' => $pageTitle ?? __('courses::teacher/messages.logs.title'),
        'backUrl' => route('teacher.index'),
        'backLabel' => __('courses::teacher/messages.logs.back'),
        'entityTitle' => __('courses::teacher/messages.logs.entity_title'),
        'entityItems' => [
            __('courses::teacher/messages.logs.fields.name') => $teacher->name ?? 'N/A',
            __('courses::teacher/messages.logs.fields.exp') => $teacher->exp ?? 'N/A',
            'ID' => '#' . ($teacher->id ?? '-'),
        ],
        'filterActions' => [
            'create' => __('courses::teacher/messages.logs.actions.create'),
            'update' => __('courses::teacher/messages.logs.actions.update'),
            'delete' => __('courses::teacher/messages.logs.actions.delete'),
        ],
        'logs' => $logs,
    ])
@endsection
