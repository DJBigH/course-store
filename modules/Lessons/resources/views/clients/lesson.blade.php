@foreach (getModuleByPosition($course) as $key => $module)
    <div class="accordion-group">
        <h4 class="accordion-title {{ $module->id == $lesson->parent_id ? 'active' : '' }}">
            {{ $module->name_locale }}
            <span class="lesson-count"> {{ $module->children->count() }} {{ __('lessons::clients/common.lesson_1') }}
            </span>
        </h4>
        <div class="accordion-detail" style="{{ $module->id == $lesson->parent_id ? 'display:block;' : '' }}">
            @foreach (getLessonByPosition($course, $module->id) as $item)
                <div class="card-accordion px-0 {{ $item->id == $lesson->id ? 'active' : '' }}">
                    <div class="lesson-item">
                        <div class="lesson-left">
                            <i class="fa-brands fa-youtube"></i>
                            <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $item->slug_locale]) }}"
                                class="lesson-title">{{ __('lessons::clients/common.lesson_item') . ' ' . ++$index . ': ' . $item->name_locale }}</a>
                            <span class="lesson-time">{{ getTime($item->durations) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
