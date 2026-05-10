@extends('layouts.backend')

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h4 class="fw-black mb-1">Cập nhật: {{ $package->name }}</h4>
            <p class="text-muted mb-0">Điều chỉnh biểu phí và quyền lợi đặc quyền của gói.</p>
        </div>
        <a href="{{ route('teacher-packages.index') }}" class="btn btn-light border-0 shadow-sm rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
        </a>
    </div>

    @if (session('msg'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('msg') }}
        </div>
    @endif

    <form method="POST" action="{{ route('teacher-packages.post-edit', $package->id) }}">
        @csrf
        @include('packages::_form')

        <div class="text-end mt-5 pb-5">
            <button class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg">
                <i class="fas fa-sync-alt me-2"></i>Cập nhật thay đổi
            </button>
        </div>
    </form>
</div>

<style>
    .fw-black { font-weight: 900; letter-spacing: -0.5px; color: #0f172a; }
</style>
@endsection
