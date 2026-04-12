@foreach (getModuleByPosition($course) as $key => $module)
    <div class="accordion-group">
        <h4 class="accordion-title {{ $module->id == $lesson->parent_id ? 'active' : '' }}">
            {{ $module->name_locale }}
            <span class="lesson-count"> {{ $module->children->count() }} {{ __('lessons::clients/common.lesson_1') }}
            </span>
        </h4>
        <div class="accordion-detail" style="{{ $module->id == $lesson->parent_id ? 'display:block;' : '' }}">
            @foreach (getLessonByPosition($course, $module->id) as $item)
                @php
                    $availability = $lessonAvailabilityMap[$item->id] ?? null;
                    $canOpenLesson = $availability['can_open'] ?? ($hasCourse || (int) $item->is_trial === 1);
                    $isCompleted = !empty($completedLessonIds[$item->id]);
                    $scheduleLocked = (bool) ($availability['schedule_locked'] ?? false);
                    $lockedMessage = $availability['message'] ?? __('courses::clients/common.lesson_purchase_required');
                @endphp
                <div class="card-accordion px-0 {{ $item->id == $lesson->id ? 'active' : '' }}">
                    <div class="lesson-item {{ $isCompleted ? 'is-completed' : '' }}">
                        <div
                            class="lesson-left {{ $hasCourse ? 'lesson-left--trackable' : 'lesson-left--readonly' }} {{ !$hasCourse && (int) $item->is_trial === 1 ? 'lesson-left--trial' : '' }}">
                            @if ($hasCourse && !$scheduleLocked)
                                <form method="POST"
                                    action="{{ route('lessons.toggle-completion', ['locale' => app()->getLocale(), 'slug' => $item->slug_locale]) }}"
                                    class="lesson-toggle-form">
                                    @csrf
                                    <button type="submit" class="lesson-toggle-check {{ $isCompleted ? 'is-completed' : '' }}"
                                        data-label-complete="{{ __('lessons::clients/common.mark_completed') }}"
                                        data-label-incomplete="{{ __('lessons::clients/common.mark_incomplete') }}"
                                        aria-label="{{ $isCompleted ? __('lessons::clients/common.mark_incomplete') : __('lessons::clients/common.mark_completed') }}">
                                        @if ($isCompleted)
                                            <i class="fa-solid fa-check"></i>
                                        @endif
                                    </button>
                                </form>
                            @endif
                            <span class="lesson-status lesson-status--youtube">
                                <i class="fa-brands fa-youtube"></i>
                            </span>
                            <span class="lesson-text">
                                @if ($canOpenLesson)
                                    <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $item->slug_locale]) }}"
                                        class="lesson-title">
                                        {{ __('lessons::clients/common.lesson_item') . ' ' . ++$index . ': ' . $item->name_locale }}
                                    </a>
                                @else
                                    <a href="#"
                                        class="lesson-title text-muted js-locked-lesson"
                                        data-message="{{ $lockedMessage }}">
                                        {{ __('lessons::clients/common.lesson_item') . ' ' . ++$index . ': ' . $item->name_locale }}
                                    </a>
                                @endif
                                @if ($scheduleLocked && !empty($lockedMessage))
                                    <small class="lesson-availability-note">{{ $lockedMessage }}</small>
                                @endif
                            </span>
                            <span class="lesson-time">{{ getTime($item->durations) }}</span>
                            @if (!$hasCourse && (int) $item->is_trial === 1)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle ms-2">
                                    {{ __('courses::clients/common.trial') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
