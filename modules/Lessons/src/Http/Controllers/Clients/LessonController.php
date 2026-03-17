<?php

namespace Modules\Lessons\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;

class LessonController extends Controller
{
    protected $lessonRepository;
    public function __construct(LessonsRepositoryInterface $lessonRepository)
    {
       
        $this->lessonRepository = $lessonRepository;
    }

    public function index($locale, $slug)
    {
        $lesson = $this->lessonRepository->getLessonActive($slug);
        if (!$lesson) {
            abort(404);
        }

        $student = Auth::guard('students')->user();
        $course = $lesson->course;

        if (!$course) {
            abort(404);
        }

        $hasCourse = $student
            ? $student
                ->courses()
                ->where('courses.id', $course->id)
                ->wherePivot('status', 1)
                ->exists()
            : false;

        if (!$hasCourse && (int) $lesson->is_trial !== 1) {
            return redirect()->route('courses.detail', [
                'locale' => $locale,
                'slug' => $course->slug_locale,
            ]);
        }

        $cacheKey = 'lesson_view_' . $lesson->id . '_' . request()->ip();

        if (!Cache::has($cacheKey)) {
            $lesson->increment('view');
            Cache::put($cacheKey, true, now()->addMinutes(30));
        }

        $pageTitle = $lesson->name_locale;
        $pageName = $lesson->name_locale;
        $index = 0;

        $lessons = $this->lessonRepository->getLessonByPosition($course);
        if (!$lessons) {
            abort(404);
        }

        $currentLessonIndex = null;

        foreach ($lessons as $key => $item) {
            if ($item->id == $lesson->id) {
                $currentLessonIndex = $key;
                break;
            }
        }

        $nextLesson = null;
        $prevLesson = null;

        if (!empty($lessons[$currentLessonIndex + 1])) {
            $nextLesson = $lessons[$currentLessonIndex + 1];
        }

        if (!empty($lessons[$currentLessonIndex - 1])) {
            $prevLesson = $lessons[$currentLessonIndex - 1];
        }

        return view('lessons::clients.index', compact('pageTitle', 'pageName', 'lesson', 'course', 'index', 'nextLesson', 'prevLesson', 'hasCourse'));
    }
}
