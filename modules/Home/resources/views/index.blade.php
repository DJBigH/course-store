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
        const homeCounters = document.querySelectorAll('.js-count-value');

        if (homeCounters.length) {
            const locale = document.documentElement.lang || 'vi';
            const numberFormatter = new Intl.NumberFormat(locale);

            const animateCounter = (counter) => {
                if (counter.dataset.counted === 'true') {
                    return;
                }

                counter.dataset.counted = 'true';

                const target = Number(counter.dataset.target || 0);
                const delay = Number(counter.dataset.delay || 0);
                const duration = 1400;

                window.setTimeout(() => {
                    let startAt = null;

                    const tick = (timestamp) => {
                        if (startAt === null) {
                            startAt = timestamp;
                        }

                        const progress = Math.min((timestamp - startAt) / duration, 1);
                        const eased = 1 - Math.pow(1 - progress, 3);
                        const currentValue = Math.round(target * eased);

                        counter.textContent = numberFormatter.format(currentValue);

                        if (progress < 1) {
                            window.requestAnimationFrame(tick);
                            return;
                        }

                        counter.textContent = numberFormatter.format(target);
                    };

                    window.requestAnimationFrame(tick);
                }, delay);
            };

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    });
                }, {
                    threshold: 0.6
                });

                homeCounters.forEach((counter) => observer.observe(counter));
            } else {
                homeCounters.forEach(animateCounter);
            }
        }

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
