@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <h3 class="fw-bold mb-2">Khoa hoc cua toi</h3>
        <p class="text-muted mb-4">Danh sach khoa hoc dang duoc gan cho profile giang vien cua ban.</p>

        @include('teacher::clients.dashboard._tabs')

        <div class="row g-3">
            @forelse ($courses as $course)
                <div class="col-md-6">
                    <article class="teacher-stat-card">
                        <strong class="d-block mb-2">{{ $course->name_locale }}</strong>
                        <div class="text-muted small mb-3">
                            {{ $course->status ? 'Dang hoat dong' : 'Tam an / chua public' }}
                        </div>
                        <div class="d-flex flex-wrap gap-2 small">
                            <span class="badge bg-light text-dark">{{ $course->lessons_count }} bai hoc</span>
                            <span class="badge bg-light text-dark">{{ $course->students_count }} hoc vien</span>
                            <span class="badge bg-light text-dark">{{ money($course->sale_price ?: $course->price) }}</span>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="teacher-panel">
                        <p class="text-muted mb-0">Chua co khoa hoc nao duoc gan cho giang vien nay.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $courses->links() }}
        </div>
    </div>
@endsection
