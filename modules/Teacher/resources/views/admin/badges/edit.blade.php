@extends('layouts.backend')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="mb-1">Chỉnh sửa huy hiệu</h5>
                            <p class="text-muted mb-0">Cập nhật thông tin và giao diện của huy hiệu.</p>
                        </div>
                        <a href="{{ route('teacher.badges.index') }}" class="btn btn-light border rounded-3">
                            <i class="fa-solid fa-arrow-left me-2"></i> Trở về
                        </a>
                    </div>

                    @if (session('msg'))
                        <div class="alert alert-success border-0 rounded-4 mb-4">{{ session('msg') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('teacher.badges.update', $badge->id) }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tên huy hiệu (VI) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name_vi" value="{{ old('name_vi', $badge->name['vi'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tên huy hiệu (EN) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name_en" value="{{ old('name_en', $badge->name['en'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mã định danh (Slug) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="code" value="{{ old('code', $badge->code) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Icon (FontAwesome) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i id="icon-preview" class="{{ $badge->icon ?: 'fa-solid fa-certificate' }}"></i></span>
                                    <input type="text" class="form-control" name="icon" id="icon-input" value="{{ old('icon', $badge->icon) }}">
                                    <a href="https://fontawesome.com/v6/search?o=r&m=free" target="_blank" class="btn btn-light border" title="Tìm icon"><i class="fa-solid fa-magnifying-glass"></i></a>
                                </div>
                                <small class="text-muted">Ví dụ: fa-solid fa-award, fa-solid fa-star</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Màu nền <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" class="form-control form-control-color border-0 p-0" style="width: 60px; height: 40px;" name="color_bg" id="color_bg" value="{{ old('color_bg', $badge->color_bg) }}">
                                    <input type="text" class="form-control" id="color_bg_hex" value="{{ old('color_bg', $badge->color_bg) }}" style="max-width: 120px;">
                                    <a href="https://flatuicolors.com/" target="_blank" class="btn btn-light border" title="Xem bảng màu gợi ý"><i class="fa-solid fa-palette"></i></a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Màu chữ <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" class="form-control form-control-color border-0 p-0" style="width: 60px; height: 40px;" name="color_text" id="color_text" value="{{ old('color_text', $badge->color_text) }}">
                                    <input type="text" class="form-control" id="color_text_hex" value="{{ old('color_text', $badge->color_text) }}" style="max-width: 120px;">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="badge-live-preview p-4 rounded-4 text-center border mt-2" style="background: #f8fafc;">
                                    <p class="small text-muted mb-3">Xem trước hiển thị:</p>
                                    <span id="badge-preview" class="badge-style">
                                        <i id="badge-icon-preview" class="{{ $badge->icon ?: 'fa-solid fa-certificate' }} me-1"></i>
                                        <span id="badge-text-preview">{{ $badge->name['vi'] ?? 'Huy hiệu mẫu' }}</span>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Mô tả (VI)</label>
                                <textarea class="form-control" name="description_vi" rows="3">{{ old('description_vi', $badge->description['vi'] ?? '') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $badge->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_active">Kích hoạt huy hiệu này</label>
                                </div>
                            </div>

                            <div class="col-12 text-end pt-3">
                                <button type="submit" class="btn btn-success px-4 py-2 rounded-3">
                                    <i class="fa-solid fa-save me-2"></i> Cập nhật huy hiệu
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .badge-style {
            padding: 0.6rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .form-control-color::-webkit-color-swatch {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }

        /* Dark Mode Support */
        html[data-theme="dark"] .card {
            background-color: #1e293b;
            color: #f1f5f9;
        }

        html[data-theme="dark"] .badge-live-preview {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        html[data-theme="dark"] .form-control, 
        html[data-theme="dark"] .form-select,
        html[data-theme="dark"] .input-group-text {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }

        html[data-theme="dark"] .form-control:focus,
        html[data-theme="dark"] .form-select:focus {
            background-color: #0f172a;
            border-color: #3b82f6;
            color: #fff;
        }

        html[data-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        html[data-theme="dark"] .btn-light {
            background-color: #334155;
            border-color: #475569;
            color: #f1f5f9;
        }

        html[data-theme="dark"] .btn-light:hover {
            background-color: #475569;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            function updatePreview() {
                const nameVi = $('input[name="name_vi"]').val() || 'Huy hiệu mẫu';
                const icon = $('#icon-input').val() || 'fa-solid fa-certificate';
                const bg = $('#color_bg').val();
                const text = $('#color_text').val();

                $('#badge-text-preview').text(nameVi);
                $('#badge-icon-preview').attr('class', icon + ' me-1');
                $('#icon-preview').attr('class', icon);
                
                $('#badge-preview').css({
                    'background-color': bg,
                    'color': text
                });
            }

            $('input[name="name_vi"], #icon-input, #color_bg, #color_text').on('input', updatePreview);

            $('#color_bg').on('input', function() { $('#color_bg_hex').val($(this).val()); });
            $('#color_bg_hex').on('input', function() { $('#color_bg').val($(this).val()); updatePreview(); });
            
            $('#color_text').on('input', function() { $('#color_text_hex').val($(this).val()); });
            $('#color_text_hex').on('input', function() { $('#color_text').val($(this).val()); updatePreview(); });

            updatePreview();
        });
    </script>
@endsection
