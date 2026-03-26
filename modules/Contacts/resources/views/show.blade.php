@extends('layouts.backend')

@section('content')
    <div class="admin-form">
        <div class="admin-form__header">
            <div>
                <h5 class="mb-1">Chi tiết liên hệ</h5>
                <p class="text-muted mb-0">Xem nội dung khách gửi và xử lý nhanh ngay trong màn hình này.</p>
            </div>
            <a href="{{ route('contacts.index') }}" class="btn btn-light border">Quay lại danh sách</a>
        </div>

        @if (session('msg'))
            <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 180px;">Họ tên</th>
                                <td>{{ $contact->name }}</td>
                            </tr>
                            <tr>
                                <th>Số điện thoại</th>
                                <td><a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></td>
                            </tr>
                            <tr>
                                <th>Trạng thái</th>
                                <td>
                                    @if ($contact->status == 1)
                                        <span class="badge rounded-pill text-success-emphasis bg-success-subtle">Đã tiếp nhận</span>
                                    @else
                                        <span class="badge rounded-pill text-warning-emphasis bg-warning-subtle">Chờ tiếp xử</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Thời gian gửi</th>
                                <td>{{ $contact->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Nội dung</th>
                                <td>
                                    <div class="rounded-4 border bg-light p-3">
                                        {!! nl2br(e($contact->message)) !!}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="admin-form__footer">
            <a href="{{ route('contacts.index') }}" class="btn btn-light border px-4">Quay lại</a>
            @if ($contact->status == 0)
                <form method="POST" action="{{ route('contacts.accept', $contact->id) }}">
                    @csrf
                    <button class="btn btn-success px-4">
                        <i class="fa-solid fa-check me-1"></i>
                        Tiếp nhận
                    </button>
                </form>
            @endif
        </div>
    </div>

    @include('part.backend.delete')
@endsection
