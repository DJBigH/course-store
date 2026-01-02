@extends('layouts.client')

@section('title', '404 - Không tìm thấy trang')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 70vh">
            <div class="col-md-6 text-center">

                {{-- Image --}}
                <img src="{{ asset('backend/assets/img/404.gif') }}" alt="404 Not Found" class="img-fluid mb-4"
                    style="max-width: 330px; height: auto;">

                {{-- Message --}}
                <p class="lead text-muted mb-2">
                    Trang bạn đang tìm không tồn tại,<br>
                    có thể đã bị xóa hoặc đường dẫn không đúng.
                </p>

                {{-- Actions --}}
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>

                    <a href="{{ route('index') }}" class="btn btn-primary">
                        <i class="fas fa-home"></i> Trang chủ
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
