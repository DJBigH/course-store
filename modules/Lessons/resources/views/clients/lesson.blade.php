@foreach (getModuleByPosition($course) as $key => $module)
    <div class="cp-module">
        <div class="cp-module-header {{ $module->id == $lesson->parent_id ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#module-{{ $module->id }}">
            <div class="cp-module-info">
                <h6 class="cp-module-name">{{ $module->name_locale }}</h6>
                <span class="cp-module-meta">{{ $module->children->count() }} {{ __('lessons::clients/common.lesson_1') }}</span>
            </div>
            <i class="fa-solid fa-chevron-down cp-module-icon"></i>
        </div>
        
        <div id="module-{{ $module->id }}" class="collapse {{ $module->id == $lesson->parent_id ? 'show' : '' }}">
            <div class="cp-lesson-list">
                @foreach (getLessonByPosition($course, $module->id) as $item)
                    @php
                        $availability = $lessonAvailabilityMap[$item->id] ?? null;
                        $canOpenLesson = $availability['can_open'] ?? ($hasCourse || (int) $item->is_trial === 1);
                        $isCompleted = !empty($completedLessonIds[$item->id]);
                        $isActive = (int) $item->id === (int) $lesson->id;
                        $scheduleLocked = (bool) ($availability['schedule_locked'] ?? false);
                        $lockedMessage = $availability['message'] ?? __('courses::clients/common.lesson_purchase_required');
                    @endphp
                    
                    <div class="cp-lesson-item {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}">
                        <div class="cp-lesson-left">
                            @if ($hasCourse && !$scheduleLocked)
                                <form method="POST" action="{{ route('lessons.toggle-completion', ['locale' => app()->getLocale(), 'slug' => $item->slug_locale]) }}" class="lesson-toggle-form">
                                    @csrf
                                    <button type="submit" class="cp-lesson-check {{ $isCompleted ? 'checked' : '' }}" 
                                            title="{{ $isCompleted ? __('lessons::clients/common.mark_incomplete') : __('lessons::clients/common.mark_completed') }}">
                                        @if ($isCompleted)
                                            <i class="fa-solid fa-check"></i>
                                        @endif
                                    </button>
                                </form>
                            @else
                                <div class="cp-lesson-status">
                                    @if (!$canOpenLesson)
                                        <i class="fa-solid fa-lock text-muted"></i>
                                    @else
                                        <i class="fa-brands fa-youtube"></i>
                                    @endif
                                </div>
                            @endif
                            
                            <div class="cp-lesson-content">
                                @if ($canOpenLesson)
                                    <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $item->slug_locale]) }}" class="cp-lesson-link">
                                        {{ $item->name_locale }}
                                    </a>
                                @else
                                    <span class="cp-lesson-link locked js-locked-lesson" data-message="{{ $lockedMessage }}">
                                        {{ $item->name_locale }}
                                    </span>
                                @endif
                                
                                @if ($scheduleLocked && !empty($lockedMessage))
                                    <div class="cp-lesson-alert">{{ $lockedMessage }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="cp-lesson-right">
                            <span class="cp-lesson-duration">{{ getTime($item->durations) }}</span>
                            @if ((int) $item->is_trial === 1)
                                <span class="cp-trial-badge">
                                    {{ __('lessons::clients/common.trial') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
