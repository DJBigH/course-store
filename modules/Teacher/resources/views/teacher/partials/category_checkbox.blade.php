@php
    $selected = $selected ?? [];
    $depth = $depth ?? 0;
@endphp

<div class="teacher-category-item">
    <label class="teacher-category-item__label">
        <input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $selected))>
        <span>{{ $category->name_locale }}</span>
    </label>

    @if ($category->children && $category->children->isNotEmpty())
        <div class="teacher-category-item__children">
            @foreach ($category->children as $child)
                @include('teacher::clients.dashboard.partials.category_checkbox', [
                    'category' => $child,
                    'selected' => $selected,
                    'depth' => $depth + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
