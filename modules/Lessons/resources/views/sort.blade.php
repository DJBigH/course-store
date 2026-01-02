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
        <div id="sorttable-list" class="list-group col">
            @foreach ($modules as $key => $module)
                <div id="item-{{ $module->id }}" data-id="{{ $module->id }}" class="list-group-item title">
                    {{ $module->name }}
                    <input type="hidden" name="lesson[]" value="{{ $module->id }}">
                </div>
                @if ($module->children)
                    @php
                        $lessons = $module->children()->orderBy('position','asc')->get();
                    @endphp
                    @foreach ($lessons as $lesson)
                        <div id="item-{{ $lesson->id }}" data-id="{{ $lesson->id }}" class="list-group-item children">
                            {{ $lesson->name }}
                            <input type="hidden" name="lesson[]" value="{{ $lesson->id }}">
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
        <div class="col-12 text-end mt-2">
            <button type="submit" class="btn btn-success">Lưu</button>
            <a href="{{ route('lessons.index', $courseId) }}" class="btn btn-danger">Trở về</a>
        </div>
    </form>
@endsection

@section('stylesheets')
    <style>
        .ghost {
            opacity: 0.4;
        }

        .list-group {
            margin-bottom: 20px;
        }

        .title {
            font-weight: bold;
        }

        .children {
            padding-left: 30px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        new Sortable(document.getElementById('sorttable-list'), {
            animation: 200,
            ghostClass: 'ghost',
            onSort: function() {
                console.log('success');
            }
        });
    </script>
@endsection
