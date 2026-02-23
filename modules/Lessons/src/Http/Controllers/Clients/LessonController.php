<?php

namespace Modules\Lessons\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;

class LessonController extends Controller
{
    protected $lessonRepository;
    public function __construct(LessonsRepositoryInterface $lessonRepository)
    {
       
        $this->lessonRepository = $lessonRepository;
    }

    public function index($locale,$slug)
    {
        $lesson = $this->lessonRepository->getLessonActive($slug);
        if(!$lesson){
            abort(404);
        }
        $pageTitle = $lesson->name_locale;
        $pageName = $lesson->name_locale;
        $course = $lesson->course;
        $index = 0;

        $lessons = $this->lessonRepository->getLessonByPosition($course);
        if(!$lessons){
            abort(404);
        }
        $currentLessonIndex = null;
        foreach($lessons as $key => $item){
            if($item->id == $lesson->id){
                $currentLessonIndex = $key;
                break;
            }
        }
        $nextLesson = null;
        $prevLesson = null;
        if(!empty($lessons[$currentLessonIndex+1])){
            $nextLesson = $lessons[$currentLessonIndex+1];
        }

        if(!empty($lessons[$currentLessonIndex-1])){
            $prevLesson = $lessons[$currentLessonIndex-1];
        }


        return view('lessons::clients.index', compact('pageTitle', 'pageName','lesson','course','index','nextLesson','prevLesson'));
    }
}
