@extends('layouts.backend')
@section('content')
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
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
                        name="name" placeholder="Tên..." value="{{ old('name') ?? $teacher->name }}">
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
                        name="slug" placeholder="Auto Generate..." value="{{ old('slug') ?? $teacher->slug }}" readonly>
                    @error('slug')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label for="">Kinh nghiệm</label>
                    <input type="number" class="form-control {{ $errors->has('exp') ? ' is-invalid' : '' }}" name="exp"
                        placeholder="Kinh nghiệm..." value="{{ old('exp') ?? $teacher->exp }}">
                    @error('exp')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label for="">Mô tả</label>
                    <textarea name="description" class="form-control ckeditor {{ $errors->has('description') ? ' is-invalid' : '' }}"
                        cols="30" rows="10" placeholder="Hỗ trợ...">{{ old('description') ?? $teacher->description }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <div class="row {{ $errors->has('image') ? 'g-2 align-items-center' : 'g-2 align-items-end' }}">
                        <label class="form-label">Hình ảnh</label>
                        <div class="col-7 position-relative">
                            <input type="text" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}"
                                name="image" placeholder="Ảnh đại diện..." id="image"
                                value="{{ old('image') ?? $teacher->image }}">

                            @error('image')
                                <div class="invalid-feedback position-absolute">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-2 d-grid">
                            <button type="button" class="btn btn-primary" id="lfm" data-input="image"
                                data-preview="holder">
                                Chọn ảnh <i class="fa-solid fa-file-arrow-up"></i>
                            </button>
                        </div>

                        <div class="col-3">
                            <div id="holder" class="rounded p-1 text-center">
                                @if (old('image') || $teacher->image)
                                    <img src="{{ old('image') ?? $teacher->image }}" class="img-fluid">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('teacher.index') }}" class="btn btn-warning">Trở về</a>
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
