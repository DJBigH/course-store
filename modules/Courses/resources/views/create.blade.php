@extends('layouts.backend')
@section('content')
    @if (session('msg_danger'))
        <div class="alert alert-danger">{{ session('msg_danger') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            Vui lòng kiểm tra lại dữ liệu đã nhập.
        </div>
    @endif
    <form action="" method="post">
        @csrf
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="">Tên</label>
                    <input type="text" class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}"
                        name="name" placeholder="Tên..." value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Slug</label>
                    <input type="text" class="form-control slug {{ $errors->has('slug') ? ' is-invalid' : '' }}"
                        name="slug" placeholder="Auto Generate..." value="{{ old('slug') }}" readonly>
                    @error('slug')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Giảng viên</label>
                    <select name="teacher_id" id=""
                        class="form-select{{ $errors->has('teacher_id') ? ' is-invalid' : '' }}">
                        <option value="0">Chọn giảng viên</option>
                        @if ($teacher)
                            @foreach ($teacher as $t)
                                <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('teacher_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label class="form-label">Mã khóa học</label>

                    <div class="input-group">
                        <input type="text" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}"
                            name="code" placeholder="Mã khóa học..." id="course_code" value="{{ old('code') }}">

                        <button type="button" class="btn btn-outline-secondary" id="randomCode">
                            <i class="fa-solid fa-shuffle"></i>
                        </button>

                        @error('code')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Giá khóa học</label>
                    <input type="number" class="form-control{{ $errors->has('price') ? ' is-invalid' : '' }}"
                        name="price" placeholder="Giá khóa học(Mặc định là 0đ)..." value="{{ old('price') }}">
                    @error('price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Giá khuyến mãi</label>
                    <input type="number" class="form-control{{ $errors->has('sale_price') ? ' is-invalid' : '' }}"
                        name="sale_price" placeholder="Giá khuyến mãi(Mặc định là 0đ)..." value="{{ old('sale_price') }}">
                    @error('sale_price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Tài liệu đính kèm</label>
                    <select name="is_document" id=""
                        class="form-select{{ $errors->has('is_document') ? ' is-invalid' : '' }}">
                        <option value="0" {{ old('is_document' == 0 ? 'selected' : false) }}>Không</option>
                        <option value="1" {{ old('is_document' == 1 ? 'selected' : false) }}>Có</option>
                    </select>
                    @error('is_document')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Trạng thái</label>
                    <select name="status" id=""
                        class="form-select{{ $errors->has('status') ? ' is-invalid' : '' }}">
                        <option value="0" {{ old('status' == 0 ? 'selected' : false) }}>Chưa ra mắt</option>
                        <option value="1" {{ old('status' == 1 ? 'selected' : false) }}>Đã ra mắt</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label for="">Hỗ trợ</label>
                    <textarea name="supports" class="form-control{{ $errors->has('supports') ? ' is-invalid' : '' }}" cols="30"
                        rows="10" placeholder="Hỗ trợ...">{{ old('supports') }}</textarea>
                    @error('supports')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label for="">Nội dung</label>
                    <textarea name="detail" class="form-control ckeditor {{ $errors->has('detail') ? ' is-invalid' : '' }}" cols="30"
                        rows="10" placeholder="Nội dung...">{{ old('detail') }}</textarea>
                    @error('detail')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label for="">Chuyên mục</label>
                    <div class="list-categories">
                        {{ getCategoriesCheckBox($categories, old('categories')) }}
                    </div>
                    @error('categories')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <div class="row {{ $errors->has('thumbnail') ? 'g-2 align-items-center' : 'g-2 align-items-end' }}">
                        <label class="form-label">Ảnh đại diện</label>
                        <div class="col-7 position-relative">
                            <input type="text"
                                class="form-control{{ $errors->has('thumbnail') ? ' is-invalid' : '' }}" name="thumbnail"
                                placeholder="Ảnh đại diện..." id="thumbnail" value="{{ old('thumbnail') }}">

                            @error('thumbnail')
                                <div class="invalid-feedback position-absolute">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-2 d-grid">
                            <button type="button" class="btn btn-primary" id="lfm" data-input="thumbnail"
                                data-preview="holder">
                                Chọn ảnh <i class="fa-solid fa-file-arrow-up"></i>
                            </button>
                        </div>

                        <div class="col-3">
                            <div id="holder" class="rounded p-1 text-center">
                                @if (old('thumbnail'))
                                    <img src="{{ old('thumbnail') }}" class="img-fluid">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('courses.index') }}" class="btn btn-warning">Trở về</a>
            </div>
        </div>
    </form>
@endsection

@section('stylesheets')
    <style>
        img {
            max-width: 100%;
            height: auto !important;
        }

        #holder img {
            width: 100% !important;
        }

        .list-categories {
            max-height: 250px;
            overflow: auto;
            border: 1px solid #d1d1d1
        }
    </style>
@endsection
