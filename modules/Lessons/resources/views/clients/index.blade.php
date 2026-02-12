@extends('layouts.client')
@section('content')
    @include('part.clients.page_title')
    <section class="video">
        <div class="container">
            <h3>{{ $lesson->name }}</h3>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="video-detail">
                        @if ($lesson->video?->url)
                            <video id="my-video" class="video-js" controls preload="auto" data-setup="{}">
                                @php
                                    $streamUrl =
                                        route('courses.data.stream', ['locale' => app()->getLocale()]) .
                                        '?video=' .
                                        urlencode(ltrim($lesson->video->url, '/'));
                                @endphp

                                <source src="{{ $streamUrl }}" type="video/mp4" />

                                <p class="vjs-no-js">
                                    {{ __('lessons::clients/common.help') }}
                                </p>
                            </video>
                        @else
                            <div class="alert alert-warning">
                                {{ __('lessons::clients/common.no_video') }}
                            </div>
                        @endif
                    </div>
                    <div class="lesson-nav d-flex justify-content-between mt-4">
                        <div>
                            @if ($prevLesson)
                                <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $prevLesson->slug]) }}"
                                    class="btn-lesson btn-prev">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>{{ __('lessons::clients/common.back') }}</span>
                                </a>
                            @endif
                        </div>

                        <div>
                            @if ($nextLesson)
                                <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $nextLesson->slug]) }}"
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
        videojs(myVideoEl);
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
