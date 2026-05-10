<section class="foundation-course">
    <div class="container py-5">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
            <h3 class="section-title mb-0 text-danger">
                {{ auth('students')->check()
                    ? '🎓 ' . __('home::common.all_course_for_you')
                    : '📚 ' . __('home::common.all_course_home') }}
            </h3>

            <div class="course-filters-wrapper overflow-auto pb-1">
                <div class="course-filters d-flex gap-2">
                    <button class="btn btn-sm btn-filter active" data-filter="latest">{{ __('home::common.filter_all') ?? 'Tất cả' }}</button>
                    <button class="btn btn-sm btn-filter" data-filter="most_viewed">{{ __('home::common.filter_views') ?? 'Nhiều lượt xem' }}</button>
                    <button class="btn btn-sm btn-filter" data-filter="featured_teachers">{{ __('home::common.filter_teacher') ?? 'Giảng viên nổi bật' }}</button>
                    <button class="btn btn-sm btn-filter" data-filter="best_seller">{{ __('home::common.filter_seller') ?? 'Bán chạy' }}</button>
                </div>
            </div>
        </div>

        <div class="row g-4" id="home-course-list">
            @include('home::all_course_home_list')
        </div>

        {{-- NÚT XEM THÊM --}}
        <div class="text-center mt-5" id="home-course-load-more">
            @if ($courseAll instanceof \Illuminate\Pagination\AbstractPaginator && $courseAll->total() > 8)
                <a href="{{ route('courses.home') }}" class="btn btn-outline-primary px-5 py-2 rounded-pill fw-bold">
                    {{ __('home::common.all_course') }} →
                </a>
            @endif
        </div>

    </div>
</section>

<style>
    .foundation-course {
        background: #f8fafc;
    }

    .section-title,
    .foundation-course h3.section-title {
        font-size: 26px !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        position: relative;
        padding-bottom: 12px;
        margin-bottom: 20px;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        display: inline-block;
    }

    .section-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 45px;
        height: 4px;
        background: #ef4444;
        border-radius: 10px;
    }

        .course-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            transition: all .3s ease;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .course-thumb {
            width: 180px;
            flex-shrink: 0;
        }

        .course-thumb img {
            width: 180px;
            height: 180px;
            /* object-fit: cover; */
        }

        .course-content {
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .course-meta {
            display: flex;
            gap: 12px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .course-meta i {
            margin-right: 4px;
            color: #2563eb;
        }

        .course-title a {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            text-decoration: none;
            line-height: 1.4;
        }

        .course-title a:hover {
            color: #2563eb;
        }

        .course-teacher {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .course-teacher img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .course-teacher span {
            font-size: 14px;
            color: #374151;
        }

        .course-price {
            margin-top: 12px;
        }

        .course-rating {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            color: #b45309;
            font-size: 14px;
        }

        .course-rating i {
            color: #f59e0b;
        }

        .price-old {
            text-decoration: line-through;
            color: #9ca3af;
            margin-right: 10px;
        }

        .price-new {
            font-size: 18px;
            font-weight: 700;
            color: #dc2626;
        }

        .course-thumb img {
            width: 180px;
            height: 180px;
            object-fit: cover;
        }

        .course-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
        }

        .btn-view {
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid #2563eb;
            text-decoration: none;
            transition: all .25s ease;
        }

        .btn-view:hover {
            background: #2563eb;
            color: #fff;
        }

        /* Hover nổi nút */
        .course-card:hover .btn-view {
            transform: translateX(3px);
        }

        @media (max-width: 768px) {
            .course-card {
                flex-direction: column;
                border-radius: 18px;
            }

            .course-thumb {
                width: 100%;
            }

            .course-thumb img {
                width: 100%;
                height: 220px;
                object-position: center;
            }

            .course-bottom {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        @media (max-width: 575.98px) {
            .foundation-course .container {
                padding-top: 2rem !important;
                padding-bottom: 2rem !important;
            }

            .section-title {
                font-size: 1.2rem;
                line-height: 1.4;
            }

            .row.g-4 {
                --bs-gutter-y: 1rem;
            }

            .course-card {
                border-radius: 20px;
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            }

            .course-content {
                padding: 14px 14px 16px;
                gap: 12px;
            }

            .course-thumb img {
                height: 188px;
            }

            .course-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 0;
                font-size: 11px;
            }

            .course-meta span {
                display: inline-flex;
                align-items: center;
                padding: 6px 10px;
                border-radius: 999px;
                background: #eff6ff;
                color: #475569;
                line-height: 1.3;
            }

            .course-title a {
                font-size: 15px;
                line-height: 1.45;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .course-teacher {
                margin-top: 0;
            }

            .course-teacher span {
                font-size: 13px;
            }

            .course-price {
                width: 100%;
                margin-top: 0;
            }

            .price-old,
            .price-new {
                display: inline-block;
            }

            .price-new {
                font-size: 17px;
            }

            .btn-view {
                width: 100%;
                text-align: center;
                padding: 10px 14px;
                border-radius: 14px;
            }

            .course-bottom {
                width: 100%;
                margin-top: 0;
            }
        }

        html[data-theme="dark"] .foundation-course {
            background: #0a1628;
        }

        html[data-theme="dark"] .foundation-course .section-title,
        html[data-theme="dark"] .foundation-course h3 {
            background: transparent;
            color: #f8fafc !important;
            border: none;
            box-shadow: none;
        }

        html[data-theme="dark"] .foundation-course .course-card {
            background: linear-gradient(180deg, #0f1b2d 0%, #132238 100%);
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 14px 34px rgba(2, 6, 23, 0.28);
        }

        html[data-theme="dark"] .foundation-course .course-card:hover {
            box-shadow: 0 20px 44px rgba(2, 6, 23, 0.36);
        }

        html[data-theme="dark"] .foundation-course .course-title a {
            color: #e5eef9;
        }

        html[data-theme="dark"] .foundation-course .course-meta,
        html[data-theme="dark"] .foundation-course .course-teacher span,
        html[data-theme="dark"] .foundation-course .price-old,
        html[data-theme="dark"] .foundation-course p {
            color: #9fb4cb !important;
        }

        html[data-theme="dark"] .foundation-course .course-meta span {
            background: rgba(96, 165, 250, 0.1);
            color: #c7d5e8;
        }

        html[data-theme="dark"] .foundation-course .btn-view {
            color: #93c5fd;
            border-color: rgba(147, 197, 253, 0.38);
            background: rgba(96, 165, 250, 0.08);
        }

        html[data-theme="dark"] .foundation-course .btn-view:hover {
            background: #2563eb;
            color: #eff6ff;
        }

        /* Filter Buttons */
        .btn-filter {
            position: relative;
            z-index: 5;
            border: 1px solid transparent;
            border-radius: 50px;
            padding: 8px 22px;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f1f5f9;
            color: #64748b !important;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .btn-filter:hover {
            color: #2563eb !important;
            background: #e0e7ff;
            transform: translateY(-1px);
        }

        .btn-filter.active {
            background: #2563eb !important;
            color: #fff !important;
            border-color: #2563eb;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
        }

    html[data-theme="dark"] .btn-filter {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.1);
        color: #e2e8f0 !important; /* Lighter text for dark mode */
        box-shadow: none;
    }

    html[data-theme="dark"] .btn-filter:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.2);
    }

    html[data-theme="dark"] .btn-filter.active {
        background: #2563eb !important;
        color: #fff !important;
        border-color: #2563eb;
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.4);
    }

    html[data-theme="dark"] .section-title,
    html[data-theme="dark"] .foundation-course h3.section-title {
        color: #f8fafc !important;
        background: transparent !important;
        border: none !important;
    }

        .course-filters-wrapper {
            position: relative;
            z-index: 10;
            overflow-x: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .course-filters-wrapper::-webkit-scrollbar {
            display: none;
        }

        #home-course-list {
            transition: opacity 0.3s ease;
        }
        #home-course-list.loading {
            opacity: 0.4;
            pointer-events: none;
        }
    </style>

<script>
    (function() {
        function initFilters() {
            const filterBtns = document.querySelectorAll('.btn-filter');
            const courseList = document.getElementById('home-course-list');
            
            if (!filterBtns.length || !courseList) return;

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (this.classList.contains('active')) return;

                    const filter = this.dataset.filter;
                    
                    // Update UI
                    document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    courseList.classList.add('loading');

                    // Fetch data
                    fetch(`{{ route('home', ['locale' => app()->getLocale()]) }}?filter=${filter}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.text();
                    })
                    .then(html => {
                        courseList.innerHTML = html;
                        courseList.classList.remove('loading');
                    })
                    .catch(error => {
                        console.error('Error fetching courses:', error);
                        courseList.classList.remove('loading');
                    });
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initFilters);
        } else {
            initFilters();
        }
    })();
</script>
