@extends('layouts.backend')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Thư viện Media</h5>
                    <p class="text-muted small mb-0">Quản lý toàn bộ tệp tin đã tải lên hệ thống</p>
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <a href="{{ route('settings.media.index', ['disk' => 'public']) }}" class="btn btn-{{ $disk === 'public' ? 'primary' : 'outline-primary' }} fw-bold rounded-start-pill px-4">Local (Public)</a>
                        <a href="{{ route('settings.media.index', ['disk' => 'google']) }}" class="btn btn-{{ $disk === 'google' ? 'primary' : 'outline-primary' }} fw-bold rounded-end-pill px-4">Google Drive</a>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                @if($error)
                    <div class="alert alert-danger rounded-4 border-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $error }}
                    </div>
                @endif

                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb bg-light p-3 rounded-4 border">
                        <li class="breadcrumb-item"><a href="{{ route('settings.media.index', ['disk' => $disk]) }}" class="text-decoration-none fw-bold"><i class="bi bi-hdd-fill me-1"></i> {{ strtoupper($disk) }}</a></li>
                        @php $currentPath = ''; @endphp
                        @if($path)
                            @foreach(explode('/', $path) as $segment)
                                @php $currentPath .= ($currentPath ? '/' : '') . $segment; @endphp
                                <li class="breadcrumb-item"><a href="{{ route('settings.media.index', ['disk' => $disk, 'path' => $currentPath]) }}" class="text-decoration-none">{{ $segment }}</a></li>
                            @endforeach
                        @endif
                    </ol>
                </nav>

                <div class="row g-3">
                    {{-- Thư mục --}}
                    @foreach($directories as $dir)
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <a href="{{ route('settings.media.index', ['disk' => $disk, 'path' => $dir['path']]) }}" class="card h-100 border text-decoration-none hover-shadow transition-all rounded-4">
                                <div class="card-body text-center p-3">
                                    <i class="bi bi-folder-fill text-warning fs-1"></i>
                                    <div class="text-dark small fw-bold mt-2 text-truncate" title="{{ $dir['name'] }}">{{ $dir['name'] }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach

                    {{-- Tệp tin --}}
                    @foreach($files as $file)
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2" id="file-{{ md5($file['path']) }}">
                            <div class="card h-100 border rounded-4 overflow-hidden hover-shadow transition-all position-relative">
                                <div class="ratio ratio-1x1 bg-light d-flex align-items-center justify-content-center overflow-hidden">
                                    @if($file['is_image'])
                                        <img src="{{ $file['url'] }}" class="object-fit-cover w-100 h-100" alt="{{ $file['name'] }}" onerror="this.src='https://placehold.co/400?text=Error'">
                                    @elseif($file['is_video'])
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-play-btn-fill text-primary fs-1"></i>
                                        </div>
                                    @else
                                        <i class="bi bi-file-earmark-fill text-muted fs-1"></i>
                                    @endif
                                </div>
                                <div class="card-body p-2 border-top">
                                    <div class="text-dark small fw-bold text-truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</div>
                                    <div class="text-muted extra-small d-flex justify-content-between mt-1">
                                        <span>{{ $file['size'] }}</span>
                                        <span>{{ date('d/m/y', strtotime($file['last_modified'])) }}</span>
                                    </div>
                                </div>
                                <div class="card-footer p-2 bg-white d-flex gap-1 border-top-0">
                                    <a href="{{ $file['url'] }}" target="_blank" class="btn btn-light btn-sm flex-grow-1 rounded-3" title="Xem">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(auth()->user()?->hasPermission('media.delete'))
                                        <button class="btn btn-light btn-sm text-danger flex-grow-1 rounded-3" onclick="deleteFile('{{ $file['path'] }}', '{{ $disk }}', '{{ md5($file['path']) }}')" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if(empty($files) && empty($directories))
                        <div class="col-12">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-folder2-open fs-1 mb-3 d-block"></i>
                                <p>Thư mục này trống</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .extra-small {
        font-size: 0.7rem;
    }
    .object-fit-cover {
        object-fit: cover;
    }
</style>

<script>
function deleteFile(path, disk, elementId) {
    if (confirm('Bạn có chắc chắn muốn xóa tệp này vĩnh viễn?')) {
        fetch("{{ route('settings.media.delete') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                path: path,
                disk: disk
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('file-' + elementId).remove();
                Swal.fire({
                    icon: 'success',
                    title: 'Đã xóa!',
                    text: 'Tệp đã được xóa thành công.',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Lỗi', data.error || 'Có lỗi xảy ra khi xóa tệp.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Lỗi', 'Không thể kết nối đến máy chủ.', 'error');
        });
    }
}
</script>
@endsection
