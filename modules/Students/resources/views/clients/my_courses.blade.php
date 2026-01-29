@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-4">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                {{-- Content --}}
                <div class="col-lg-9">
                    <div class="account-content card shadow-sm border-0">
                        <div class="card-body p-4">

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h2 class="fw-semibold mb-0">
                                    Khóa học của tôi
                                </h2>
                            </div>

                            <div class="table-responsive" style="overflow-x: unset;">
                                <form method="GET" action="#" class="mb-4">
                                    <div class="row g-2 align-items-end">
                                        <!-- Lọc theo giảng viên -->
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label fw-medium">Giảng viên</label>
                                            <select name="teacher_id" class="form-select js-select2">
                                                <option value="">Tất cả giảng viên</option>
                                                @foreach ($teacher as $item)
                                                    <option value="{{ $item->id }}"
                                                        {{ request()->teacher_id == $item->id ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <!-- Tìm kiếm theo tên khóa học -->
                                        <div class="col-lg-7 col-md-6">
                                            <label class="form-label fw-medium">Tìm kiếm khóa học</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                                <input type="text" name="keyword" class="form-control"
                                                    placeholder="Nhập tên khóa học..." value="{{ request()->keyword }}">
                                            </div>
                                        </div>


                                        <!-- Nút lọc -->
                                        <div class="col-lg-2 col-md-6 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bi bi-funnel me-1"></i>
                                                Lọc
                                            </button>
                                        </div>

                                    </div>
                                </form>

                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">#</th>
                                            <th>Tên khóa học</th>
                                            <th style="width: 220px;">Giảng viên</th>
                                            <th style="width: 150px;">Trạng thái</th>
                                            <th class="text-center" style="width: 140px;">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($courses as $course)
                                            <tr>
                                                <td class="text-center fw-medium">
                                                    {{ $loop->iteration }}
                                                </td>

                                                <td>
                                                    <div class="fw-semibold"><a
                                                            href="{{ route('courses.detail', $course->slug) }}">{{ $course->name }}</a>
                                                    </div>
                                                    <small class="text-muted">
                                                        Cập nhật lần cuối:
                                                        {{ format_date_dmy($course->updated_at) }}
                                                    </small>
                                                </td>

                                                <td>
                                                    <span class="badge bg-info-subtle text-info px-3 py-2">
                                                        <a href="#">{{ $course->teacher->name ?? 'Nguyễn Văn A' }}</a>
                                                    </span>
                                                </td>

                                                <td>
                                                    @if ($course->pivot->status)
                                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            Hoạt động
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                                            <i class="bi bi-x-circle me-1"></i>
                                                            Dừng cập nhập
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    <a href="{{ route('courses.detail', $course->slug) }}"
                                                        class="btn btn-primary btn-sm px-3">
                                                        <i class="bi bi-play-circle me-1"></i>
                                                        Vào học
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                                    Bạn chưa đăng ký khóa học nào
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="mt-2">
                                    {{ $courses->links('students::clients.pagination.boostrap') }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
