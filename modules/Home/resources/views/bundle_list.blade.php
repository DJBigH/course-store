@if ($courseBundles && $courseBundles->count() > 0)
    <section class="bundle-section py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="section-title mb-0">
                    🎁 {{ __('home::common.combo_courses') ?? 'Combo Khóa Học Tiết Kiệm' }}
                </h3>
            </div>

            <div class="row g-4">
                @foreach ($courseBundles as $bundle)
                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="bundle-card">
                            <div class="bundle-thumb">
                                <a href="{{ route('courses.bundle.detail', ['locale' => app()->getLocale(), 'slug' => $bundle->slug]) }}">
                                    <img src="{{ $bundle->thumbnail ? (str_starts_with($bundle->thumbnail, 'http') ? $bundle->thumbnail : asset($bundle->thumbnail)) : asset('assets/clients/images/course-placeholder.png') }}" 
                                         alt="{{ $bundle->name }}" class="img-fluid">
                                </a>
                                <div class="bundle-badge">{{ $bundle->items->count() }} {{ __('home::common.courses') ?? 'Khóa học' }}</div>
                            </div>
                            <div class="bundle-content">
                                <h4 class="bundle-title">
                                    <a href="{{ route('courses.bundle.detail', ['locale' => app()->getLocale(), 'slug' => $bundle->slug]) }}">
                                        {{ $bundle->name }}
                                    </a>
                                </h4>
                                <div class="bundle-teacher">
                                    <i class="fas fa-chalkboard-teacher me-1"></i>
                                    {{ $bundle->teacher->name_locale ?? $bundle->teacher->name }}
                                </div>
                                <div class="bundle-footer">
                                    <div class="bundle-price">
                                        @if ($bundle->sale_price)
                                            <span class="price-old">{{ number_format($bundle->price) }}{{ $bundle->currency_symbol }}</span>
                                            <span class="price-new text-danger fw-bold">{{ number_format($bundle->sale_price) }}{{ $bundle->currency_symbol }}</span>
                                        @else
                                            <span class="price-new text-danger fw-bold">{{ number_format($bundle->price) }}{{ $bundle->currency_symbol }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('courses.bundle.detail', ['locale' => app()->getLocale(), 'slug' => $bundle->slug]) }}" class="btn btn-sm btn-primary rounded-pill">
                                        {{ __('home::common.view_bundle') ?? 'Xem ngay' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <style>
        .bundle-section {
            background: #fff;
        }
        
        .bundle-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
        }
        
        .bundle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-color: #e2e8f0;
        }
        
        .bundle-thumb {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
        }
        
        .bundle-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .bundle-card:hover .bundle-thumb img {
            transform: scale(1.05);
        }
        
        .bundle-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(37, 99, 235, 0.9);
            color: #fff;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }
        
        .bundle-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .bundle-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .bundle-title a {
            color: #1e293b;
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .bundle-title a:hover {
            color: #2563eb;
        }
        
        .bundle-teacher {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 15px;
        }
        
        .bundle-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px dashed #e2e8f0;
        }
        
        .bundle-price {
            display: flex;
            flex-direction: column;
        }
        
        .price-old {
            font-size: 13px;
            text-decoration: line-through;
            color: #94a3b8;
        }
        
        .price-new {
            font-size: 18px;
        }

        html[data-theme="dark"] .bundle-section {
            background: #0f172a;
        }

        html[data-theme="dark"] .bundle-card {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.05);
        }

        html[data-theme="dark"] .bundle-title a {
            color: #f1f5f9;
        }

        html[data-theme="dark"] .bundle-footer {
            border-color: rgba(255, 255, 255, 0.05);
        }
        
        html[data-theme="dark"] .price-old {
            color: #64748b;
        }

        html[data-theme="dark"] .bundle-teacher {
            color: #94a3b8;
        }
    </style>
@endif
