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
                        name="name" placeholder="Tên..." value="{{ old('name') ?? $category->name }}">
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
                        name="slug" placeholder="Slug..." value="{{ old('slug') ?? $category->slug }}">
                    @error('slug')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Cha</label>
                    <select name="parent_id" id=""
                        class="form-select{{ $errors->has('parent_id') ? ' is-invalid' : '' }}">
                        <option value="0">Không có</option>
                        {{ getCategories($categories, old('parent_id') ?? $category->parent_id) }}
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('categories.index') }}" class="btn btn-warning">Trở về</a>
            </div>
        </div>
    </form>
@endsection
