@foreach (getModuleByPosition($course) as $key => $module)
    <div class="accordion-group">
        <h4 class="accordion-title {{ $key == 0 ? 'active' : '' }}">
            {{ $module->name }}
            <span class="lesson-count"> {{ $module->children->count() }} bài học
            </span>
        </h4>
        <div class="accordion-detail" style="{{ $key == 0 ? 'display:block;' : '' }}">
            @foreach (getLessonByPosition($course, $module->id) as $lesson)
                <div class="card-accordion">
                    <div class="lesson-item">
                        <div class="lesson-left">
                            <i class="fa-brands fa-youtube"></i>
                            <a href="{{ route('lessons.home', $lesson->slug) }}"
                                class="lesson-title">{{ 'Bài ' . ++$index . ': ' . $lesson->name }}</a>
                            {!! $lesson->is_trial ? '<p class="preview trial-btn" data-id="' . $lesson->id . ' ">Học thử</p>' : '' !!}
                        </div>
                        <span class="lesson-time">{{ getTime($lesson->durations) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
@section('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('modal');
            const trialBtnList = document.querySelectorAll('.trial-btn');
            if (trialBtnList.length) {
                trialBtnList.forEach((trialBtn) => {
                    trialBtn.addEventListener('click', (e) => {
                        const initrialBtn = e.target.innerText;
                        const id = e.target.dataset.id;
                        if (!id) {
                            alert('Không mở được video học thử!');
                            return;
                        }
                        const Modal = new bootstrap.Modal(modalEl);
                        const url = "{{ route('courses.data.trial') }}/" + id;
                        e.target.innerText = 'Đang mở...';
                        fetch(url).then((response) => {
                            return response.json()
                        }).then(({
                            success,
                            data
                        }) => {
                            if (!success) {
                                alert('Bài giảng không tồn tại!');
                                return;
                            }
                            if (data.is_trial != 1) {
                                alert('Không được phép học thử!');
                                return;
                            }
                            const name = data.name;
                            const videoUrl = data.video.url;

                            Modal.show();
                            modalEl.querySelector('.modal-title').innerText = name;
                            modalEl.querySelector('.modal-body').innerHTML =
                                `<video id="my-video" class="video-js" controls preload="auto" data-setup="{}">
                                 <source src="/data/stream?video=${videoUrl}" type="video/mp4" />
                                <p class="vjs-no-js">
                                To view this video please enable JavaScript, and consider upgrading to a
                                web browser that
                                </p>
                                </video>`
                        }).finally(() => {
                            e.target.innerText = initrialBtn;
                            const myVideoEl = modalEl.querySelector('.modal-body')
                                .querySelector('#my-video');
                            videojs(myVideoEl);
                        });

                    })
                })
            }

            modalEl.addEventListener('hidden.bs.modal', (e) => {
                modalEl.querySelector('.modal-title').innerText = '';
                modalEl.querySelector('.modal-body').innerText = '';
            })
        })
    </script>
@endsection
