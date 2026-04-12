@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Them thong bao teacher</h5>
                    <p class="text-muted mb-0">Tao announcement de day vao chuong thong bao theo tung goi teacher.</p>
                </div>
                <a href="{{ route('teacher-announcements.index') }}" class="btn btn-light border">Quay lai</a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">Vui long kiem tra lai du lieu vua nhap.</div>
            @endif

            <form method="POST" action="{{ route('teacher-announcements.post-add') }}">
                @csrf
                @include('teacher::announcements._form')
            </form>
        </div>
    </div>
@endsection
