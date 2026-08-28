@if ($courseComingSoon->count() > 0)
    <section class="coming-soon-section py-5">
        <div class="container">
            <div class="section-header d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="section-title h3 fw-bold mb-1">
                        <i class="fa-solid fa-hourglass-start text-warning me-2"></i>
                        {{ __('home::common.coming_soon_courses') ?? 'Khóa học sắp ra mắt' }}
                    </h2>
                    <p class="text-muted mb-0">{{ __('home::common.coming_soon_desc') ?? 'Sắp có mặt tại hệ thống, hãy chờ đợi nhé!' }}</p>
                </div>
            </div>

            <div class="row g-4">
                @foreach ($courseComingSoon as $item)
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="course-card course-card--coming-soon h-100 shadow-sm border-0 rounded-4 overflow-hidden bg-white">
                            <div class="course-card__thumb position-relative">
                                <img src="{{ $item->thumbnail }}" alt="{{ $item->name_locale }}" class="w-100 object-fit-cover" style="height: 180px;">
                                <div class="course-card__badge position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                                        {{ __('courses::clients/common.coming_soon') ?? 'COMING SOON' }}
                                    </span>
                                </div>
                            </div>
                            <div class="course-card__body p-3">
                                <h3 class="course-card__title h6 fw-bold mb-2 line-clamp-2" style="min-height: 2.8em;">
                                    <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $item->slug_locale]) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $item->name_locale }}
                                    </a>
                                </h3>
                                
                                <div class="course-card__meta d-flex align-items-center gap-2 mb-3">
                                    <img src="{{ $item->teacher->student->avatar ?? asset('assets/clients/images/default-avatar.png') }}" 
                                         alt="{{ $item->teacher->name }}" 
                                         class="rounded-circle" width="24" height="24">
                                    <span class="small text-muted">{{ $item->teacher->name }}</span>
                                </div>

                                <div class="coming-soon-countdown p-2 rounded-3 bg-light border border-warning border-opacity-25 text-center">
                                    <div class="countdown-timer d-flex justify-content-center gap-2" 
                                         data-time="{{ $item->coming_soon_start_at->toIso8601String() }}">
                                        <div class="time-item"><span class="days fw-bold text-warning">00</span><small class="d-block text-muted" style="font-size: 0.7rem;">{{ __('home::common.day') }}</small></div>
                                        <div class="time-item"><span class="hours fw-bold text-warning">00</span><small class="d-block text-muted" style="font-size: 0.7rem;">{{ __('home::common.hour') }}</small></div>
                                        <div class="time-item"><span class="minutes fw-bold text-warning">00</span><small class="d-block text-muted" style="font-size: 0.7rem;">{{ __('home::common.minute') }}</small></div>
                                        <div class="time-item"><span class="seconds fw-bold text-warning">00</span><small class="d-block text-muted" style="font-size: 0.7rem;">{{ __('home::common.second') }}</small></div>
                                    </div>
                                </div>
                            </div>
                            <div class="course-card__footer p-3 pt-0 border-0 bg-transparent">
                                <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $item->slug_locale]) }}" 
                                   class="btn btn-outline-warning btn-sm w-100 rounded-pill fw-bold">
                                    {{ __('home::common.view_detail') ?? 'Xem chi tiết' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <style>
        .coming-soon-section {
            background: #f8fafc;
        }
        .course-card--coming-soon {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .course-card--coming-soon:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }
        .coming-soon-countdown .time-item span {
            font-size: 1.1rem;
            line-height: 1.2;
        }
        html[data-theme="dark"] .coming-soon-section {
            background: #0a1628;
        }
        html[data-theme="dark"] .coming-soon-section .section-title {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .coming-soon-section .text-muted {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .course-card--coming-soon {
            background: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
        }
        html[data-theme="dark"] .course-card--coming-soon .course-card__title a {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .coming-soon-countdown {
            background: rgba(245, 158, 11, 0.05) !important;
            border-color: rgba(245, 158, 11, 0.2) !important;
        }
        html[data-theme="dark"] .coming-soon-countdown .time-item small {
            color: #94a3b8 !important;
        }
    </style>
@endif
