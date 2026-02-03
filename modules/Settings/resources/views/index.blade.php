@extends('layouts.backend')

@section('content')
    <div class="container-fluid">

        {{-- Thông báo --}}
        @if (session('msg'))
            <div class="alert alert-success">
                {{ session('msg') }}
            </div>
        @endif

        <form action="{{ route('settings.post-setting') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                {{-- Cột trái --}}
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header fw-bold">
                            Thông tin chung
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label">Tên website</label>
                                <input type="text" name="site_name" class="form-control"
                                    value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', $settings['email'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', $settings['phone'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $settings['address'] ?? '') }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Cột phải --}}
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header fw-bold">
                            Mạng xã hội
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label">Facebook</label>
                                <input type="text" name="facebook" class="form-control"
                                    value="{{ old('facebook',$settings['facebook'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Instagram</label>
                                <input type="text" name="instagram" class="form-control"
                                    value="{{ old('instagram',$settings['instagram'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Youtube</label>
                                <input type="text" name="youtube" class="form-control"
                                    value="{{ old('youtube',$settings['youtube'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">TikTok</label>
                                <input type="text" name="tiktok" class="form-control"
                                    value="{{ old('',$settings['tiktok'] ?? '') }}">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card mt-3">
                <div class="card-header fw-bold">
                    Cấu hình SEO
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Mô tả website</label>
                        <textarea name="seo_description" class="form-control" rows="3">{{ $settings['seo_description'] ?? '' }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Từ khóa (SEO)</label>
                        <input type="text" name="seo_keywords" class="form-control"
                            value="{{ $settings['seo_keywords'] ?? '' }}">
                    </div>

                </div>
            </div>

            {{-- Nút lưu --}}
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary">
                    Lưu cấu hình
                </button>
            </div>

        </form>
    </div>
@endsection
