@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 mx-auto">

                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="bi bi-envelope-paper me-1 text-primary"></i>
                        Chi tiết liên hệ
                    </h4>

                    <a href="{{ route('contacts.index') }}" class="btn btn-secondary btn-sm">
                        ← Quay lại
                    </a>
                </div>
                {{-- Flash message --}}
                @if (session('msg'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ session('msg') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Card --}}
                <div class="card shadow-sm">
                    <div class="card-body">

                        {{-- Thông tin người liên hệ --}}
                        <table class="table table-bordered align-middle">
                            <tr>
                                <th width="180">Họ tên</th>
                                <td>{{ $contact->name }}</td>
                            </tr>

                            <tr>
                                <th>Số điện thoại</th>
                                <td>
                                    <a href="tel:{{ $contact->phone }}">
                                        {{ $contact->phone }}
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <th>Email</th>
                                <td>
                                    <a href="mailto:{{ $contact->email }}">
                                        {{ $contact->email }}
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <th>Trạng thái</th>
                                <td>
                                    @if ($contact->status == 1)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i>
                                            Đã tiếp nhận
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i>
                                            Chờ tiếp xử
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>Thời gian gửi</th>
                                <td>{{ $contact->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>

                            <tr>
                                <th>Nội dung liên hệ</th>
                                <td>
                                    <div class="border rounded p-3 bg-light">
                                        {!! nl2br(e($contact->message)) !!}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{-- Action --}}
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            @if ($contact->status == 0)
                                <form method="POST" action="{{ route('contacts.accept',$contact->id) }}">
                                    @csrf
                                    <button class="btn btn-success btn-sm">
                                        <i class="bi bi-check-lg"></i>
                                        Tiếp nhận
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @include('part.backend.delete')
@endsection
