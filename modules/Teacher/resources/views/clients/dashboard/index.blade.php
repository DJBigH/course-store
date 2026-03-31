@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <section class="teacher-hero">
            <div class="teacher-hero__eyebrow">
                <i class="fas fa-star"></i>
                Teacher Studio
            </div>
            <h3 class="teacher-hero__title">{{ $teacher->name }} dang xay dung kenh giang day cua minh</h3>
            <p class="teacher-hero__desc">
                Theo doi suc khoe kenh, doanh thu uoc tinh va nhung khoa hoc gan day trong mot workspace tach rieng cho giang vien.
            </p>
            <div class="teacher-chip-list mt-4">
                <span class="teacher-chip teacher-chip--dark">
                    <i class="fas fa-percent"></i>
                    Commission {{ rtrim(rtrim(number_format($teacher->commission_rate, 2, '.', ''), '0'), '.') }}%
                </span>
                <span class="teacher-chip teacher-chip--dark">
                    <i class="fas fa-book"></i>
                    {{ $stats['courses'] }} khoa hoc
                </span>
                <span class="teacher-chip teacher-chip--dark">
                    <i class="fas fa-user-graduate"></i>
                    {{ $stats['students'] }} hoc vien da mua
                </span>
            </div>
        </section>

        <div class="teacher-panel">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
                <div>
                    <h3 class="fw-bold mb-2">Bang dieu khien giang vien</h3>
                    <p class="text-muted mb-0">Theo doi khoa hoc, hoc vien va doanh thu du kien cua kenh giang day.</p>
                </div>
                <span class="badge bg-success px-3 py-2">Da kich hoat</span>
            </div>

            @include('teacher::clients.dashboard._tabs')

            <div class="row g-3">
                <div class="col-md-6 col-xl-4">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">Tong khoa hoc</div>
                        <div class="teacher-stat-card__value">{{ $stats['courses'] }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">Khoa hoc dang hoat dong</div>
                        <div class="teacher-stat-card__value">{{ $stats['active_courses'] }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">Hoc vien da mua</div>
                        <div class="teacher-stat-card__value">{{ $stats['students'] }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">Doanh thu gop</div>
                        <div class="teacher-stat-card__value">{{ money($stats['gross_revenue']) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">Discount phan bo</div>
                        <div class="teacher-stat-card__value">{{ money($stats['allocated_discount']) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">Doanh thu uoc tinh</div>
                        <div class="teacher-stat-card__value">{{ money($stats['estimated_revenue']) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">Phan nen tang giu lai</div>
                        <div class="teacher-stat-card__value">{{ money($stats['platform_revenue']) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">So du kha dung</div>
                        <div class="teacher-stat-card__value">{{ money($stats['available_balance']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title">
                        <div>
                            <h4 class="h5">Khoa hoc gan day</h4>
                            <p class="text-muted mb-0">Cac khoa hoc moi nhat dang gan voi profile giang vien.</p>
                        </div>
                        <a class="teacher-soft-link" href="{{ route('teacher.dashboard.courses') }}">Xem tat ca</a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($recentCourses as $course)
                            <div class="teacher-subtle-card">
                                <strong class="d-block">{{ $course->name_locale }}</strong>
                                <small class="text-muted">Trang thai: {{ $course->status ? 'Dang ban' : 'An / dung hoat dong' }}</small>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Chua co khoa hoc nao duoc gan cho giang vien nay.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title">
                        <div>
                            <h4 class="h5">Giao dich gan day</h4>
                            <p class="text-muted mb-0">Doanh thu moi nhat duoc ghi nhan cho kenh cua ban.</p>
                        </div>
                        <a class="teacher-soft-link" href="{{ route('teacher.dashboard.earnings') }}">Xem doanh thu</a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($recentSales as $detail)
                            <div class="teacher-subtle-card">
                                <strong class="d-block">{{ $detail->courses?->name_locale ?: 'Khong ro khoa hoc' }}</strong>
                                <small class="d-block text-muted">Don #{{ $detail->order?->code }} - {{ optional($detail->created_at)->format('d/m/Y H:i') }}</small>
                                <span class="text-primary fw-semibold">{{ money($detail->finance_breakdown['teacher_revenue']) }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Chua co giao dich thanh cong nao de thong ke.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

