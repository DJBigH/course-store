@extends('layouts.client')
@section('content')
    @include('part.clients.page_title')
    <section class="video">
        <div class="container">
            <h3>{{ $lesson->name }}</h3>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="video-detail">
                        <video id="my-video" class="video-js" controls preload="auto" data-setup="{}">
                            <source src="/data/stream?video={{ $lesson->video->url }}" type="video/mp4" />
                            <p class="vjs-no-js">
                                To view this video please enable JavaScript, and consider upgrading to a
                                web browser that
                            </p>
                        </video>
                    </div>
                    <div class="lesson-nav d-flex justify-content-between mt-4">
                        <div>
                            @if ($prevLesson)
                                <a href="{{ route('lessons.home', $prevLesson->slug) }}" class="btn-lesson btn-prev">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>Quay lại</span>
                                </a>
                            @endif
                        </div>

                        <div>
                            @if ($nextLesson)
                                <a href="{{ route('lessons.home', $nextLesson->slug) }}" class="btn-lesson btn-next">
                                    <span>Tiếp theo</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
                <div class="col-12 col-lg-4">
                    <div class="nav flex">
                        <p class="lesson active">Bài học</p>
                        <p class="document">Tài liệu</p>
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
