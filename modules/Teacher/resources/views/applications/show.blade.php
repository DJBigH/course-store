@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="mb-1">Ho so #{{ $application->id }}</h5>
                    <p class="text-muted mb-0">Xem thong tin ung tuyen, package va phe duyet ho so giang vien.</p>
                </div>
                <a href="{{ route('teacher-applications.index') }}" class="btn btn-light border">Quay lai</a>
            </div>

            @if (session('msg'))
                <div class="alert alert-success">{{ session('msg') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="border rounded-4 p-4 h-100">
                        <h6 class="fw-bold mb-3">Thong tin ung vien</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Ho ten</dt>
                            <dd class="col-sm-8">{{ $application->full_name }}</dd>
                            <dt class="col-sm-4">Ten hien thi</dt>
                            <dd class="col-sm-8">{{ $application->display_name ?: '-' }}</dd>
                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $application->email }}</dd>
                            <dt class="col-sm-4">So dien thoai</dt>
                            <dd class="col-sm-8">{{ $application->phone ?: '-' }}</dd>
                            <dt class="col-sm-4">Headline</dt>
                            <dd class="col-sm-8">{{ $application->headline ?: '-' }}</dd>
                            <dt class="col-sm-4">Kinh nghiem</dt>
                            <dd class="col-sm-8">{{ $application->experience_years ?: 0 }} nam</dd>
                            <dt class="col-sm-4">Package</dt>
                            <dd class="col-sm-8">{{ $application->package?->name ?: '-' }}</dd>
                            <dt class="col-sm-4">Trang thai</dt>
                            <dd class="col-sm-8">{{ $application->display_status }}</dd>
                            <dt class="col-sm-4">Specialties</dt>
                            <dd class="col-sm-8">{{ is_array($application->specialties) ? implode(', ', $application->specialties) : '-' }}</dd>
                            <dt class="col-sm-4">Portfolio</dt>
                            <dd class="col-sm-8">{{ $application->portfolio_url ?: '-' }}</dd>
                            <dt class="col-sm-4">Video</dt>
                            <dd class="col-sm-8">{{ $application->intro_video_url ?: '-' }}</dd>
                        </dl>

                        @if ($application->bio)
                            <hr>
                            <h6 class="fw-bold mb-2">Bio</h6>
                            <p class="mb-0">{{ $application->bio }}</p>
                        @endif
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="border rounded-4 p-4 mb-4">
                        <h6 class="fw-bold mb-3">Phe duyet</h6>
                        <form method="POST" action="{{ route('teacher-applications.approve', $application->id) }}" class="mb-3">
                            @csrf
                            <label class="form-label">Ghi chu admin</label>
                            <textarea name="admin_note" class="form-control" rows="4"
                                placeholder="Ghi chu cho hoc vien neu can...">{{ old('admin_note', $application->admin_note) }}</textarea>
                            <button type="submit" class="btn btn-success w-100 mt-3" {{ $application->status === 'approved' ? 'disabled' : '' }}>
                                Phe duyet ho so
                            </button>
                        </form>

                        <form method="POST" action="{{ route('teacher-applications.reject', $application->id) }}">
                            @csrf
                            <label class="form-label">Ly do tu choi</label>
                            <textarea name="admin_note" class="form-control" rows="4"
                                placeholder="Noi ro ly do can bo sung...">{{ old('admin_note', $application->admin_note) }}</textarea>
                            <button type="submit" class="btn btn-outline-danger w-100 mt-3" {{ $application->status === 'approved' ? 'disabled' : '' }}>
                                Tu choi ho so
                            </button>
                        </form>
                    </div>

                    <div class="border rounded-4 p-4">
                        <h6 class="fw-bold mb-3">Thong tin review</h6>
                        <p class="mb-2"><strong>Reviewed at:</strong> {{ optional($application->reviewed_at)->format('d/m/Y H:i') ?: '-' }}</p>
                        <p class="mb-2"><strong>Reviewed by:</strong> {{ $application->reviewer?->name ?: '-' }}</p>
                        <p class="mb-0"><strong>Teacher linked:</strong> {{ $application->teacher_id ? '#' . $application->teacher_id : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
