@extends('layouts.client')
@section('content')
    @include('home::banner')
    @include('home::my_course_home')
    @include('home::all_course_home')
    {{-- @include('home::skill_extention') --}}
    @include('home::question')
    @include('home::cta-box')
    {{-- @include('home::partner') --}}
    @include('home::about_us')
@endsection
@section('scripts')
    <script>
        document.addEventListener('click', function(e) {
            const link = e.target.closest('#my-course-wrapper .pagination a');
            if (!link) return;

            e.preventDefault(); // ❌ chặn reload trang

            const url = link.getAttribute('href');

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('my-course-wrapper').innerHTML = html;

                    // scroll nhẹ cho UX
                    document.querySelector('.foundation-course')
                        ?.scrollIntoView({
                            behavior: 'smooth'
                        });
                })
                .catch(err => console.error(err));
        });
    </script>
@endsection
