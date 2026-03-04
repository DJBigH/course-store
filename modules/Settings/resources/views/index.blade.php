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
                                    value="{{ old('facebook', $settings['facebook'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Instagram</label>
                                <input type="text" name="instagram" class="form-control"
                                    value="{{ old('instagram', $settings['instagram'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Youtube</label>
                                <input type="text" name="youtube" class="form-control"
                                    value="{{ old('youtube', $settings['youtube'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">TikTok</label>
                                <input type="text" name="tiktok" class="form-control"
                                    value="{{ old('tiktok', $settings['tiktok'] ?? '') }}">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Banner --}}
            <div class="card mt-3">
                <div class="card-header fw-bold">
                    Banner trang chủ
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control">

                        @if (!empty($settings['logo']))
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings['logo']) }}" height="80">
                            </div>
                        @endif
                    </div>

                    {{-- Banner slider --}}
                    <div class="mb-3">
                        <label class="form-label">Banner Slider (Nhiều ảnh)</label>
                        <input type="file" name="banner_slider[]" class="form-control" multiple>

                        @if (!empty($settings['banner_slider']))
                            <div class="d-flex gap-2 mt-2">
                                @foreach (json_decode($settings['banner_slider'], true) as $img)
                                    <img src="{{ asset('storage/' . $img) }}" height="60">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Banner phải --}}
                    <div class="mb-3">
                        <label class="form-label">Banner bên phải (Tối đa 3 ảnh)</label>
                        <input type="file" name="banner_right[]" class="form-control" multiple>

                        @if (!empty($settings['banner_right']))
                            <div class="d-flex gap-2 mt-2">
                                @foreach (json_decode($settings['banner_right'], true) as $img)
                                    <img src="{{ asset('storage/' . $img) }}" height="60">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Banner full --}}
                    <div class="mb-3">
                        <label class="form-label">Banner full width</label>
                        <input type="file" name="banner_full" class="form-control">

                        @if (!empty($settings['banner_full']))
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings['banner_full']) }}" height="80">
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header fw-bold">
                    Cấu hình tiền tệ
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tỷ giá USD / VND</label>
                                <input type="number" step="0.01" min="0.01" name="currency_rate_usd"
                                    class="form-control"
                                    value="{{ old('currency_rate_usd', $settings['currency_rate_usd'] ?? config('currency.rates.usd')) }}">
                                <small class="text-muted">1 USD tương ứng bao nhiêu VND.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tỷ giá KRW / VND</label>
                                <input type="number" step="0.01" min="0.01" name="currency_rate_krw"
                                    class="form-control"
                                    value="{{ old('currency_rate_krw', $settings['currency_rate_krw'] ?? config('currency.rates.krw')) }}">
                                <small class="text-muted">1 Won Hàn tương ứng bao nhiêu VND.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tỷ giá JPY / VND</label>
                                <input type="number" step="0.01" min="0.01" name="currency_rate_jpy"
                                    class="form-control"
                                    value="{{ old('currency_rate_jpy', $settings['currency_rate_jpy'] ?? config('currency.rates.jpy')) }}">
                                <small class="text-muted">1 Yên Nhật tương ứng bao nhiêu VND.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tỷ giá CNY / VND</label>
                                <input type="number" step="0.01" min="0.01" name="currency_rate_cny"
                                    class="form-control"
                                    value="{{ old('currency_rate_cny', $settings['currency_rate_cny'] ?? config('currency.rates.cny')) }}">
                                <small class="text-muted">1 Nhân dân tệ tương ứng bao nhiêu VND.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="row">
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

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header fw-bold">
                            Cấu hình chung
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label">Tỷ giá USD/VND</label>
                                <input type="text" name="currency" class="form-control"
                                    value="{{ old('currency', $settings['currency'] ?? '') }}">
                            </div>

                        </div>
                    </div>
                </div> 
            </div> --}}

            {{-- Nút lưu --}}
            <div class="text-end mt-3">
                <a href="{{ route('settings.logs') }}" class="btn btn-warning">Lịch sử</a>
                <button type="submit" class="btn btn-primary">
                    Lưu cấu hình
                </button>
            </div>

        </form>
    </div>
@endsection
