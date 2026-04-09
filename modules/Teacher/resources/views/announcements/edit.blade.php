@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Cap nhat thong bao teacher</h5>
                    <p class="text-muted mb-0">Sua noi dung, goi ap dung va thoi gian hien thi cua announcement.</p>
                </div>
                <a href="{{ route('teacher-announcements.index') }}" class="btn btn-light border">Quay lai</a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">Vui long kiem tra lai du lieu vua nhap.</div>
            @endif

            <form method="POST" action="{{ route('teacher-announcements.post-edit', $announcement->id) }}">
                @csrf
                @include('teacher::announcements._form')
            </form>
        </div>
    </div>
@endsection
