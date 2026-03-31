@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 text-danger"
                style="width: 72px; height: 72px;">
                <i class="fa-solid fa-lock fs-3"></i>
            </div>
            <h2 class="fw-bold mb-2">Không đủ quyền truy cập</h2>
            <p class="text-muted mb-4">{{ $message ?? 'Bạn không có quyền truy cập khu vực này.' }}</p>
            <a href="{{ route('admin.index') }}" class="btn btn-primary">
                Quay về tổng quan
            </a>
        </div>
    </div>
@endsection
