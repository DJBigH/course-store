@php
    $modules = getModuleByPosition($course);
@endphp

@if ($modules->isEmpty())
    <div class="alert alert-info text-center">
        Khóa học chưa có bài học nào
    </div>
@else
    @php $hasLesson = false; @endphp

    @foreach ($modules as $key => $module)
        @php
            $lessons = getLessonByPosition($course, $module->id);
            if ($lessons->count()) {
                $hasLesson = true;
            }
        @endphp

        <div class="accordion-group">
            <h4 class="accordion-title {{ $key == 0 ? 'active' : '' }}">
                {{ $module->name }}
                <span class="lesson-count">
                    {{ $module->children->count() }} bài học
                </span>
            </h4>

            <div class="accordion-detail" style="{{ $key == 0 ? 'display:block;' : '' }}">
                @forelse ($lessons as $lesson)
                    <div class="card-accordion">
                        <div class="lesson-item">
                            <div class="lesson-left">
                                <i class="fa-brands fa-youtube"></i>
                                <a href="{{ route('lessons.home', $lesson->slug) }}" class="lesson-title">
                                    {{ 'Bài ' . ++$index . ': ' . $lesson->name }}
                                </a>

                                @if ($lesson->is_trial)
                                    <p class="preview trial-btn" data-id="{{ $lesson->id }}">
                                        Học thử
                                    </p>
                                @endif
                            </div>
                            <span class="lesson-time">
                                {{ getTime($lesson->durations) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small px-3">
                        Chưa có bài học trong chương này
                    </p>
                @endforelse
            </div>
        </div>
    @endforeach

    @if (!$hasLesson)
        <div class="alert alert-info text-center mt-3">
            Khóa học chưa có bài học nào
        </div>
    @endif
@endif

@section('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('modal');
            const Modal = new bootstrap.Modal(modalEl); // 1 lần duy nhất
            const trialBtnList = document.querySelectorAll('.trial-btn');
            const activeBtnMap = new Map();

            trialBtnList.forEach(trialBtn => {
                trialBtn.addEventListener('click', async (e) => {
                    const initText = e.target.innerText;
                    const id = e.target.dataset.id;
                    if (!id) return alert('Không mở được video học thử!');

                    e.target.innerText = 'Đang mở...';
                    activeBtnMap.set('current', e.target); // lưu nút hiện tại

                    try {
                        const response = await fetch("{{ route('courses.data.trial') }}/" + id);
                        const {
                            success,
                            data
                        } = await response.json();
                        if (!success || data.is_trial != 1) return alert(
                            'Không được phép học thử!');

                        modalEl.querySelector('.modal-title').innerText = data.name;
                        modalEl.querySelector('.modal-body').innerHTML = `
                    <video id="my-video" class="video-js" controls preload="auto" data-setup="{}">
                        <source src="/data/stream?video=${data.video.url}" type="video/mp4"/>
                    </video>
                `;

                        Modal.show();
                        videojs(modalEl.querySelector('#my-video'));
                    } finally {
                        // e.target.innerText = initText;
                    }
                });
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                const videoEl = modalEl.querySelector('#my-video');
                if (videoEl && videojs.getPlayer(videoEl.id)) {
                    videojs.getPlayer(videoEl.id).dispose();
                }
                modalEl.querySelector('.modal-title').innerText = '';
                modalEl.querySelector('.modal-body').innerHTML = '';

                // Reset text nút
                const activeBtn = activeBtnMap.get('current');
                if (activeBtn) {
                    activeBtn.innerText = 'Học thử';
                    activeBtnMap.delete('current');
                }

                // Reset body style và backdrop
                if (!document.querySelector('.modal.show')) {
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            });
        });
    </script>
@endsection
