<h2 class="mt-4">{{ !empty($pageTitle) ? $pageTitle : 'Không có dữ liệu' }}</h2>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}" class="text-decoration-none">Tổng quan</a></li>
    @if(!empty($breadcrumbs))
        @foreach($breadcrumbs as $bc)
            @if(!empty($bc['link']))
                <li class="breadcrumb-item"><a href="{{ $bc['link'] }}" class="text-decoration-none">{{ $bc['label'] }}</a></li>
            @else
                <li class="breadcrumb-item active">{{ $bc['label'] }}</li>
            @endif
        @endforeach
    @else
        <li class="breadcrumb-item active">{{ !empty($pageTitle) ? $pageTitle : 'Không có dữ liệu' }}</li>
    @endif
</ol>
