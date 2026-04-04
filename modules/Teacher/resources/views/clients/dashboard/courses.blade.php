@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-courses-shell">
        @php
            $usedCourses = (int) ($usage['used'] ?? 0);
            $limitLabel = $usage['limit_label'] ?? __('teacher::dashboard.courses.unlimited');
            $canCreate = (bool) ($usage['can_create'] ?? false);
        @endphp

        <div class="teacher-courses-hero">
            <div>
                <span class="teacher-courses-kicker">Khu vực soạn khóa học</span>
                <h3 class="teacher-courses-title">{{ __('teacher::dashboard.courses.title') }}</h3>
                <p class="teacher-courses-desc mb-0">
                    Đây là nơi dễ nhất để tạo, sửa và quản lý khóa học của bạn. Bạn chỉ cần làm theo 3 bước bên dưới.
                </p>
            </div>
            <div class="teacher-courses-hero__actions">
                @if (!empty($usage))
                    <div class="teacher-courses-usage">
                        <span>Đã tạo</span>
                        <strong>{{ $usedCourses }}</strong>
                        <span>/</span>
                        <strong>{{ $limitLabel }}</strong>
                        <span>khóa học</span>
                    </div>
                @endif
                @if ($canCreate)
                    <a href="{{ route('teacher.dashboard.courses.create') }}" class="btn btn-primary btn-lg">
                        Tạo khóa học mới
                    </a>
                @endif
                <a href="{{ route('teacher.dashboard.courses.trash') }}" class="btn btn-outline-secondary">
                    Xem thùng rác
                </a>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        <div class="teacher-courses-guide">
            <article class="teacher-courses-guide__item">
                <span class="teacher-courses-guide__step">1</span>
                <div>
                    <strong>Tạo khóa học</strong>
                    <p class="mb-0">Bấm nút <em>Tạo khóa học mới</em> để nhập tên, mô tả và chọn danh mục.</p>
                </div>
            </article>
            <article class="teacher-courses-guide__item">
                <span class="teacher-courses-guide__step">2</span>
                <div>
                    <strong>Soạn bài học</strong>
                    <p class="mb-0">Sau khi tạo xong, bấm <em>Soạn bài học</em> để thêm video, tài liệu và nội dung.</p>
                </div>
            </article>
            <article class="teacher-courses-guide__item">
                <span class="teacher-courses-guide__step">3</span>
                <div>
                    <strong>Cập nhật thông tin</strong>
                    <p class="mb-0">Nếu cần chỉnh sửa tiêu đề, giá bán hoặc ảnh, bấm <em>Chỉnh sửa thông tin</em>.</p>
                </div>
            </article>
        </div>

        <div class="row g-3">
            @forelse ($courses as $course)
                <div class="col-md-6">
                    <article class="teacher-course-card teacher-course-card--friendly">
                        <div class="teacher-course-card__head">
                            <div>
                                <strong class="d-block mb-2 teacher-course-card__title">{{ $course->name_locale }}</strong>
                                <div class="teacher-course-card__status {{ $course->status ? 'is-active' : 'is-hidden' }}">
                                    {{ $course->status ? 'Đang hiển thị cho học viên' : 'Đang ẩn / tạm ngưng' }}
                                </div>
                            </div>
                            <a href="{{ route('teacher.dashboard.courses.edit', $course->id) }}" class="btn btn-sm btn-outline-primary">
                                Chỉnh sửa thông tin
                            </a>
                        </div>

                        <div class="teacher-course-card__stats">
                            <div class="teacher-course-card__stat">
                                <span>Bài học</span>
                                <strong>{{ $course->lessons_count }}</strong>
                            </div>
                            <div class="teacher-course-card__stat">
                                <span>Học viên đã mua</span>
                                <strong>{{ $course->students_count }}</strong>
                            </div>
                            <div class="teacher-course-card__stat">
                                <span>Giá đang bán</span>
                                <strong>{{ money($course->sale_price ?: $course->price) }}</strong>
                            </div>
                        </div>

                        <div class="teacher-course-card__help">
                            Bạn muốn thêm nội dung cho khóa học này? Hãy bấm <strong>Soạn bài học</strong>.
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('teacher.dashboard.lessons.index', $course->id) }}" class="btn btn-primary">
                                Soạn bài học
                            </a>
                            <a href="{{ route('teacher.dashboard.courses.edit', $course->id) }}" class="btn btn-outline-secondary">
                                Chỉnh sửa thông tin
                            </a>
                            <form method="POST" action="{{ route('teacher.dashboard.courses.delete', $course->id) }}" onsubmit="return confirm('{{ __('teacher::dashboard.courses.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    Chuyển vào thùng rác
                                </button>
                            </form>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="teacher-courses-empty">
                        <div class="teacher-courses-empty__icon">+</div>
                        <h4>Bạn chưa có khóa học nào</h4>
                        <p class="mb-0">
                            Hãy bắt đầu bằng việc tạo khóa học đầu tiên. Sau đó bạn có thể thêm bài học, video và tài liệu rất dễ dàng.
                        </p>
                        @if ($canCreate)
                            <a href="{{ route('teacher.dashboard.courses.create') }}" class="btn btn-primary mt-3">
                                Tạo khóa học đầu tiên
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $courses->links() }}
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-courses-shell {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 28%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-courses-hero {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .teacher-courses-kicker {
            display: inline-flex;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.14);
            color: #8fc3ff;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .teacher-courses-title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-courses-desc {
            max-width: 760px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-courses-hero__actions {
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            justify-content: flex-end;
            gap: 0.9rem;
            min-width: 340px;
        }

        .teacher-courses-usage {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            min-height: 48px;
            gap: 0.35rem;
            padding: 0.75rem 1.1rem;
            border: 1px solid rgba(96, 165, 250, 0.18);
            border-radius: 16px;
            background: rgba(18, 28, 50, 0.75);
            color: #dce9ff;
            font-weight: 600;
            line-height: 1.45;
        }

        .teacher-courses-usage strong {
            color: #fff;
            font-weight: 800;
        }

        .teacher-courses-guide {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .teacher-courses-guide__item,
        .teacher-courses-empty {
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-courses-guide__item {
            display: flex;
            gap: 1rem;
            padding: 1.15rem 1.2rem;
        }

        .teacher-courses-guide__step {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: #fff;
            font-weight: 900;
        }

        .teacher-courses-guide__item strong,
        .teacher-courses-empty h4 {
            color: #f8fbff;
        }

        .teacher-courses-guide__item p,
        .teacher-courses-empty p {
            color: #a9bbd5;
            line-height: 1.65;
        }

        .teacher-course-card--friendly {
            height: 100%;
            padding: 1.4rem;
            border: 1px solid rgba(96, 165, 250, 0.18);
            border-radius: 24px;
            background: rgba(18, 28, 50, 0.72);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-course-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .teacher-course-card__title {
            font-size: 1.35rem;
            color: #f8fbff;
        }

        .teacher-course-card__status {
            display: inline-flex;
            align-items: center;
            padding: 0.48rem 0.8rem;
            border-radius: 999px;
            font-size: 0.86rem;
            font-weight: 700;
        }

        .teacher-course-card__status.is-active {
            background: rgba(34, 197, 94, 0.15);
            color: #9ef0b3;
        }

        .teacher-course-card__status.is-hidden {
            background: rgba(148, 163, 184, 0.14);
            color: #c9d7ea;
        }

        .teacher-course-card__stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .teacher-course-card__stat {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            border: 1px solid rgba(96, 165, 250, 0.14);
            background: rgba(15, 23, 42, 0.6);
        }

        .teacher-course-card__stat span {
            display: block;
            margin-bottom: 0.3rem;
            color: #8fb5e9;
            font-size: 0.85rem;
        }

        .teacher-course-card__stat strong {
            color: #fff;
            font-size: 1.05rem;
        }

        .teacher-course-card__help {
            padding: 0.9rem 1rem;
            border-radius: 16px;
            background: rgba(37, 99, 235, 0.12);
            color: #cfe4ff;
            line-height: 1.6;
        }

        .teacher-courses-empty {
            padding: 2rem;
            text-align: center;
        }

        .teacher-courses-empty__icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: #fff;
            font-size: 2rem;
            font-weight: 300;
        }

        @media (max-width: 991.98px) {
            .teacher-courses-hero,
            .teacher-course-card__head {
                flex-direction: column;
            }

            .teacher-courses-hero__actions {
                justify-content: flex-start;
                min-width: 0;
            }

            .teacher-courses-usage {
                width: 100%;
            }

            .teacher-courses-guide {
                grid-template-columns: 1fr;
            }

            .teacher-course-card__stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
