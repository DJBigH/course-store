@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ $pageTitle }}</h4>
                </div>
            </div>
        </div>

        @if (session('msg'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-all me-2"></i> {{ session('msg') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('teacher-packages.grant.store') }}" method="POST" id="grantForm">
                            @csrf
                            
                            {{-- Chọn Giáo viên --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold" for="teacher_select">
                                    1. Chọn giáo viên nhận gói <span class="text-danger">*</span>
                                </label>
                                <select class="form-control select2-ajax" id="teacher_select" name="teacher_id" 
                                        data-placeholder="Tìm kiếm theo tên hoặc email giáo viên..."
                                        data-url="{{ route('teacher-packages.grant.search-teachers') }}">
                                    @if ($teacher)
                                        <option value="{{ $teacher->id }}" selected>
                                            {{ $teacher->name_locale }} ({{ $teacher->student?->email }})
                                        </option>
                                    @endif
                                </select>
                                @error('teacher_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="teacher_info_box" class="mb-4 p-3 rounded bg-light border @if(!$teacher) d-none @endif">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $teacher->image ?? asset('admin/images/users/avatar-1.jpg') }}" 
                                             id="t_image" class="rounded-circle avatar-md" alt="">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mt-0 mb-1" id="t_name">{{ $teacher->name_locale ?? '' }}</h5>
                                        <p class="text-muted mb-0">
                                            <i class="mdi mdi-email-outline"></i> <span id="t_email">{{ $teacher->student?->email ?? '' }}</span>
                                        </p>
                                        <p class="text-primary mb-0">
                                            <i class="mdi mdi-package-variant-closed"></i> 
                                            Gói hiện tại: <span id="t_package" class="fw-bold">{{ $teacher?->currentPackage()?->name_locale ?? 'Chưa có' }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- Cấu hình gói tặng --}}
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold" for="package_id">
                                        2. Chọn gói đặc quyền <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select select2" id="package_id" name="package_id" required>
                                        <option value="">-- Chọn gói tặng --</option>
                                        @foreach ($packages as $pkg)
                                            <option value="{{ $pkg->id }}" data-cycle="{{ $pkg->billing_cycle }}">
                                                {{ $pkg->name_locale }} 
                                                ({{ $pkg->is_exclusive ? 'Exclusive' : 'Public' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold" for="conflict_mode">
                                        3. Chế độ kích hoạt <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="conflict_mode" name="conflict_mode" required>
                                        <option value="queue" selected>Xếp hàng (Chờ gói cũ hết hạn)</option>
                                        <option value="override">Ghi đè (Kích hoạt ngay lập tức)</option>
                                    </select>
                                    <div class="form-text">Gói mới sẽ bắt đầu sau khi giáo viên bấm "Nhận quà".</div>
                                </div>
                            </div>

                            {{-- Ghi chú Admin --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="admin_note">4. Lời nhắn / Ghi chú cho giáo viên</label>
                                <textarea class="form-control" id="admin_note" name="admin_note" 
                                          rows="3" maxlength="1000" 
                                          placeholder="Ví dụ: Chúc mừng bạn đã đạt KPI tháng! Đây là gói quà tặng dành riêng cho bạn.">{{ old('admin_note') }}</textarea>
                                <div class="form-text">Lời nhắn này sẽ hiển thị cho giáo viên thấy trên trang nhận quà.</div>
                            </div>

                            {{-- Tùy chọn gửi email --}}
                            <div class="mb-4">
                                <div class="p-3 rounded border" style="background:rgba(99,102,241,0.06);border-color:rgba(99,102,241,0.2)!important;">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="send_email" name="send_email" value="1" @checked(old('send_email'))>
                                        <label class="form-check-label fw-bold" for="send_email">
                                            <i class="mdi mdi-email-send-outline me-1 text-primary"></i> Gửi Email thông báo kèm link nhận quà
                                        </label>
                                        <div class="small text-muted mt-1">Mặc định hệ thống luôn gửi thông báo qua Notification trên web.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end border-top pt-3">
                                <a href="{{ route('teacher.index') }}" class="btn btn-light me-2">Hủy bỏ</a>
                                <button type="submit" class="btn btn-primary" id="btnSubmit">
                                    <i class="mdi mdi-gift-outline me-1"></i> Xác nhận tặng gói
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Hướng dẫn</h5>
                        <div class="alert alert-info border-0 mb-0">
                            <ul class="ps-3 mb-0">
                                <li class="mb-2"><b>Luồng hoạt động:</b> Sau khi tặng, giáo viên sẽ nhận được thông báo. Gói chưa được kích hoạt ngay mà ở trạng thái <b>Chờ nhận (Pending Claim)</b>.</li>
                                <li class="mb-2"><b>Xác nhận:</b> Giáo viên phải vào link thông báo và nhấn nút "Nhận gói" thì gói mới bắt đầu tính thời hạn.</li>
                                <li><b>Hạn dùng:</b> Link nhận gói có hiệu lực trong 30 ngày.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <link href="{{ asset('admin/css/vendor/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        /* Select2 Dark Mode Fix */
        .select2-container--default .select2-selection--single {
            height: 42px;
            line-height: 42px;
            background-color: #313a46;
            border: 1px solid #4a525d;
            border-radius: 5px;
            color: #ced4da;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            color: #ced4da;
            padding-left: 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .select2-dropdown {
            background-color: #313a46;
            border: 1px solid #4a525d;
            color: #ced4da;
            z-index: 9999;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #3bafda;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #3d4650;
        }
        .select2-search--dropdown {
            background-color: #313a46;
        }
        .select2-search--dropdown .select2-search__field {
            background-color: #3d4650;
            border: 1px solid #4a525d;
            color: #ced4da;
        }
        .select2-container--default .select2-results__option {
            color: #ced4da;
        }

        /* Info box styling */
        #teacher_info_box {
            background-color: rgba(59, 175, 218, 0.05) !important;
            border: 1px dashed rgba(59, 175, 218, 0.3) !important;
        }
        .avatar-md {
            height: 4.5rem;
            width: 4.5rem;
            object-fit: cover;
        }
        .form-label {
            color: #ced4da;
        }
        .form-text {
            color: #adb5bd;
        }
        .card {
            border: 1px solid #4a525d;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('admin/js/vendor/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            $('.select2-ajax').each(function() {
                var $this = $(this);
                $this.select2({
                    ajax: {
                        url: $this.data('url'),
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return { q: params.term };
                        },
                        processResults: function(data) {
                            return { results: data.results };
                        },
                        cache: true
                    },
                    minimumInputLength: 1
                });
            });

            $('#teacher_select').on('select2:select', function(e) {
                var data = e.params.data;
                $('#teacher_info_box').removeClass('d-none');
                $('#t_name').text(data.text);
                $('#t_email').text(data.email);
                $('#t_package').text(data.package);
                if (data.image) $('#t_image').attr('src', data.image);
            });

            $('#grantForm').on('submit', function() {
                $('#btnSubmit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...');
            });
        });
    </script>
@endsection
