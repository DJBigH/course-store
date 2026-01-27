@extends('layouts.client')

@section('title', '403 - Không có quyền truy cập')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">

                    <h1 class="display-1 fw-bold text-danger">403</h1>

                    <h3 class="mb-3">
                        🚫 Không có quyền truy cập
                    </h3>

                    {{-- 👉 THÔNG BÁO ĐỘNG TỪ abort() --}}
                    <p class="text-muted mb-4">
                        {{ $exception->getMessage() ?: 'Bạn không có quyền truy cập trang này.' }}
                    </p>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                            ← Quay lại
                        </a>

                        <a href="{{ route('home') }}" class="btn btn-primary">
                            🏠 Trang chủ
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
