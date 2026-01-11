@extends('layouts.backend')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            Vui lòng kiểm tra lại dữ liệu đã nhập.
        </div>
    @endif
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    <form action="" method="post">
        @csrf
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="">Tên</label>
                    <input type="text" class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}"
                        name="name" placeholder="Tên..." value="{{ old('name', $lesson->name) }}">
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
                        name="slug" placeholder="Auto Generate..." value="{{ old('slug', $lesson->slug) }}" readonly>
                    @error('slug')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-4">
                <div class="mb-3">
                    <label for="">Nhóm bài giảng</label>
                    <select name="parent_id" id=""
                        class="form-select select2 {{ $errors->has('parent_id') ? 'is-invalid' : '' }}">
                        <option value="0">Trống</option>
                        {{ getLessons($lessons, old('parent_id', $lesson->parent_id)) }}
                    </select>
                    @error('parent_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-4">
                <div class="mb-3">
                    <label for="">Học thử</label>
                    <select name="is_trial" id=""
                        class="form-select {{ $errors->has('is_trial') ? ' is-invalid' : '' }}">
                        <option value="0" {{ old('is_trial', $lesson->is_trial) == 0 ? 'selected' : '' }}>Không
                        </option>
                        <option value="1" {{ old('is_trial', $lesson->is_trial) == 1 ? 'selected' : '' }}>Có</option>
                    </select>
                    @error('is_trial')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-4">
                <div class="mb-3">
                    <label for="">Sắp xếp</label>
                    <input type="number" class="form-control {{ $errors->has('position') ? ' is-invalid' : '' }}"
                        name="position" placeholder="Thứ tự..." value="{{ old('position', $lesson->position) }}">
                    @error('position')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Video</label>
                    <div class="input-group {{ $errors->has('video') ? ' is-invalid' : '' }}">
                        <input type="text" name="video" class="form-control" id="video_url"
                            placeholder="Video bài giảng (Bấm chọn hoặc gắn link)..."
                            value="{{ old('video', $lesson->video) }}">
                        <button type="button" class="btn btn-success" id="lfm-video" data-input="video_url">Chọn</button>
                    </div>
                    @error('video')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Tài liệu</label>
                    <div class="input-group {{ $errors->has('document') ? ' is-invalid' : '' }}">
                        <input type="text" class="form-control" name="document" id="document_url"
                            placeholder="Tài liệu bài giảng (Bấm chọn hoặc gắn link)..."
                            value="{{ old('document', $lesson->document) }}">
                        <button type="button" class="btn btn-success" id="lfm-document"
                            data-input="document_url">Chọn</button>
                    </div>
                    @error('document')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label for="">Mô tả</label>
                    <textarea name="description" class="form-control ckeditor {{ $errors->has('description') ? ' is-invalid' : '' }}">{{ old('description', $lesson->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label>Trạng thái</label>
                    <label class="d-block">
                        <input type="checkbox" name="status" value="1"
                            {{ old('status', $lesson->status) ? 'checked' : '' }}> Kích
                        hoạt
                    </label>
                    @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('lessons.index', $courseId) }}" class="btn btn-danger">Trở về</a>
            </div>
        </div>
    </form>
@endsection
