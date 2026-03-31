@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Cap nhat goi giang vien</h5>
                    <p class="text-muted mb-0">Dieu chinh gia, commission va muc ho tro.</p>
                </div>
                <a href="{{ route('teacher-packages.index') }}" class="btn btn-light border">Quay lai</a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif

            <form method="POST" action="{{ route('teacher-packages.post-edit', $package->id) }}">
                @csrf
                @include('teacher::packages._form')

                <div class="text-end mt-4">
                    <button class="btn btn-primary">Cap nhat goi</button>
                </div>
            </form>
        </div>
    </div>
@endsection
