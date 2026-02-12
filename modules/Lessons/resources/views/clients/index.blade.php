@extends('layouts.client')
@section('content')
    @include('part.clients.page_title')
    <section class="video">
        <div class="container">
            <h3>{{ $lesson->name_locale }}</h3>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="video-detail">
                        @php
                            $videoUrl = trim((string) ($lesson->video?->url ?? ''));

                            if (!function_exists('lessonEmbedUrl')) {
                                function lessonEmbedUrl($url)
                                {
                                    $url = trim((string) $url);
                                    if ($url === '') {
                                        return null;
                                    }

                                    if (!preg_match('~^https?://~i', $url)) {
                                        $url = 'https://' . ltrim($url, '/');
                                    }

                                    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
                                    $path = (string) parse_url($url, PHP_URL_PATH);

                                    if (str_contains($host, 'youtu.be')) {
                                        $id = trim($path, '/');
                                        return $id ? "https://www.youtube.com/embed/{$id}" : null;
                                    }

                                    if (str_contains($host, 'youtube.com') && str_contains($path, '/embed/')) {
                                        $id = trim(str_replace('/embed/', '', $path), '/');
                                        return $id ? "https://www.youtube.com/embed/{$id}" : null;
                                    }

                                    if (str_contains($host, 'youtube.com') && str_contains($path, '/shorts/')) {
                                        $id = trim(str_replace('/shorts/', '', $path), '/');
                                        return $id ? "https://www.youtube.com/embed/{$id}" : null;
                                    }

                                    if (str_contains($host, 'youtube.com')) {
                                        $query = (string) parse_url($url, PHP_URL_QUERY);
                                        parse_str($query, $q);
                                        $id = $q['v'] ?? null;
                                        return $id ? "https://www.youtube.com/embed/{$id}" : null;
                                    }

                                    if (str_contains($host, 'vimeo.com')) {
                                        $id = trim($path, '/');
                                        if (preg_match('~(\d+)$~', $id, $m)) {
                                            return "https://player.vimeo.com/video/{$m[1]}";
                                        }
                                        return null;
                                    }

                                    return null;
                                }
                            }

                            $embed = $videoUrl ? lessonEmbedUrl($videoUrl) : null;
                        @endphp

                        @if ($embed)
                            <div class="ratio ratio-16x9">
                                <iframe src="{{ $embed }}" title="Lesson video"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            </div>
                        @elseif ($videoUrl)
                            @php
                                $streamUrl =
                                    route('courses.data.stream', ['locale' => app()->getLocale()]) .
                                    '?video=' .
                                    urlencode(ltrim($videoUrl, '/'));
                            @endphp

                            <video id="my-video" class="video-js" controls preload="auto" data-setup="{}">
                                <source src="{{ $streamUrl }}" type="video/mp4" />
                                <p class="vjs-no-js">{{ __('lessons::clients/common.help') }}</p>
                            </video>
                        @else
                            <div class="alert alert-warning">{{ __('lessons::clients/common.no_video') }}</div>
                        @endif
                    </div>

                    <div class="lesson-nav d-flex justify-content-between mt-4">
                        <div>
                            @if ($prevLesson)
                                <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $prevLesson->slug_locale]) }}"
                                    class="btn-lesson btn-prev">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>{{ __('lessons::clients/common.back') }}</span>
                                </a>
                            @endif
                        </div>

                        <div>
                            @if ($nextLesson)
                                <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $nextLesson->slug_locale]) }}"
                                    class="btn-lesson btn-next">
                                    <span>{{ __('lessons::clients/common.next') }}</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="col-12 col-lg-4">
                    <div class="nav flex">
                        <p class="lesson active">{{ __('lessons::clients/common.lesson') }}</p>
                        <p class="document">{{ __('lessons::clients/common.document') }}</p>
                    </div>
                    <div class="group">
                        <div class="accordion active title">
                            @include('lessons::clients.lesson')
                        </div>
                        <div class="document-title title">
                            @include('lessons::clients.document')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('scripts')
    <script>
        const myVideoEl = document.querySelector('#my-video');
        if (myVideoEl) videojs(myVideoEl);
    </script>
@endsection


@section('stylesheets')
    <style>
        .group {
            /* position: relative; */
            display: block;
            gap: 20px !important;
            padding: 0px !important;
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            /* transition: all 0.35s ease; */
            height: auto;
        }

        .group:hover {
            border-color: #2563eb;
            transform: translateY(0px) !important;
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.12);
        }
    </style>
@endsection
